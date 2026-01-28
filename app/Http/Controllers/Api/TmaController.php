<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Product;
use Illuminate\Http\Request;

class TmaController extends Controller
{
    // GET /api/tma/{project}/config
    public function config(Project $project)
    {
        // Минимальный публичный конфиг магазина (без bot_token и служебных полей)
        return response()->json([
            'id' => $project->id,
            'name' => $project->name,
            'category' => $project->category,
            'status' => $project->status,
            'theme_config' => $project->theme_config,
            // если у тебя есть поля description/logo_url в таблице projects — добавь их тут
            'description' => $project->description ?? null,
            'logo_url' => $project->logo_url ?? null,
        ]);
    }

   /**
 * Получить продукты для TMA
 * GET /api/tma/{project:slug}/products
 */
public function getProducts(Project $project)
{
    // Получаем разрешённые типы для этого шаблона
    // $allowedTypes = $project->getAllowedProductTypes();

    $products = $project->products()
        // ->whereIn('type', $allowedTypes)
        ->where('is_active', true)
        ->with(['files'])
        ->orderByDesc('is_featured')
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($product) {
            return [
                'id' => $product->id,
                'type' => $product->type,
                'name' => $product->name,
                'description' => $product->description,
                'price' => (float) $product->price,
                'old_price' => $product->old_price ? (float) $product->old_price : null,
                'discount' => $product->discount,
                'image_url' => $product->image_url,
                'category' => $product->category,
                // 'category_id' => $product->category_id,
                'is_featured' => (bool) $product->is_featured,
                'rating' => $product->rating ? (float) $product->rating : null,
                'reviews_count' => $product->reviews_count ?? 0,
                'metadata' => $product->metadata,
                'files' => $product->files->map(fn($f) => [
                    'id' => $f->id,
                    'name' => $f->file_name,
                    'type' => $f->file_type,
                    'size' => $f->formatted_size,
                ]),
            ];
        });

    return response()->json($products);
}


    // POST /api/tma/{project}/orders
    public function createOrder(Request $request, Project $project)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'shipping_address' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.product_name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
        ]);
    
        // Customer уже авторизован через Sanctum (auth:sanctum middleware)
        $customer = $request->user();
    
        // Генерировать уникальный order_number
        $orderNumber = 'ORD-' . strtoupper(uniqid());
    
        // Создать заказ
        $order = $project->orders()->create([
            'customer_id' => $customer->id,
            'order_number' => $orderNumber,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_phone' => $validated['customer_phone'],
            'shipping_address' => $validated['shipping_address'],
            'notes' => $validated['notes'] ?? null,
            'total_amount' => $validated['total_amount'],
            'status' => 'pending',
        ]);
    
        // Создать позиции заказа (OrderItem)
        foreach ($validated['items'] as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            
            $order->items()->create([
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
            ]);
        }
    
        // Обновить статистику customer
        $customer->increment('orders_count');
        $customer->increment('total_spent', $validated['total_amount']);
    
        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order->load('items'), // вернуть заказ с позициями
        ], 201);
    }

    /**
 * Получить уроки курса (для LMS)
 * GET /api/tma/{project:slug}/courses/{course}/lessons
 */
public function getCourseLessons(Project $project, Product $course)
{
    if ($course->type !== Product::TYPE_COURSE) {
        return response()->json(['error' => 'Not a course'], 400);
    }

    $lessons = $course->children()
        ->get()
        ->map(fn($rel) => [
            'id' => $rel->child->id,
            'name' => $rel->child->name,
            'description' => $rel->child->description,
            'order' => $rel->sort_order,
            'files' => $rel->child->files,
        ]);

    return response()->json($lessons);
}

/**
 * Скачать цифровой файл (для Digital Products)
 * POST /api/tma/{project:slug}/products/{product}/download
 */
public function downloadDigital(Request $request, Project $project, Product $product)
{
    if ($product->type !== Product::TYPE_DIGITAL_PRODUCT) {
        return response()->json(['error' => 'Not a digital product'], 400);
    }

    // TODO: проверка оплаты/доступа через заказы

    $file = $product->files()->first();
    
    if (!$file) {
        return response()->json(['error' => 'No files found'], 404);
    }

    $file->incrementDownloadCount();

    return response()->json([
        'download_url' => $file->full_url,
        'file_name' => $file->file_name,
        'file_size' => $file->formatted_size,
    ]);
}

/**
 * Создать запрос на КП (для Catalog)
 * POST /api/tma/{project:slug}/quote-request
 */
public function createQuoteRequest(Request $request, Project $project)
{
    $validated = $request->validate([
        'telegram_id' => 'nullable|string',
        'customer_name' => 'required|string',
        'customer_phone' => 'required|string',
        'customer_email' => 'nullable|email',
        'items' => 'required|array',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'notes' => 'nullable|string',
    ]);

    // Создать заказ типа quote_request
    $order = $project->orders()->create([
        'telegram_id' => $validated['telegram_id'] ?? null,
        'customer_name' => $validated['customer_name'],
        'customer_phone' => $validated['customer_phone'],
        'customer_email' => $validated['customer_email'] ?? null,
        'order_type' => Order::ORDER_TYPE_QUOTE_REQUEST,
        'status' => 'pending',
        'notes' => $validated['notes'] ?? null,
        'total_amount' => 0, // для КП цена обсуждается
    ]);

    // Добавить товары
    foreach ($validated['items'] as $item) {
        $product = Product::find($item['product_id']);
        
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $item['quantity'],
            'price' => $product->price,
        ]);
    }

    return response()->json([
        'message' => 'Quote request created successfully',
        'order_id' => $order->id,
    ], 201);
}

    
    
}
