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

    // GET /api/tma/{project}/products
    public function products(Project $project)
    {
        // Важно: отдаём только активные товары
        $products = Product::where('project_id', $project->id)
            ->where('is_active', true)
            ->orderBy('id', 'desc')
            ->get();

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
    
    
}
