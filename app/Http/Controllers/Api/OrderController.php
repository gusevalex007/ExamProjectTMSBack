<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Project;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders for a project.
     */
    public function index(Project $project)
    {
        // Проверка доступа к проекту
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $orders = $project->orders()
            ->with(['customer', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    /**
     * Store a newly created order.
     */
    // public function store(Request $request, Project $project)
    // {
    //     // Проверка доступа к проекту
    //     if ($project->user_id !== auth()->id()) {
    //         return response()->json(['message' => 'Forbidden'], 403);
    //     }

    //     $validated = $request->validate([
    //         'customer_id' => 'required|exists:customers,id',
    //         'customer_name' => 'required|string|max:255',
    //         'customer_email' => 'nullable|email',
    //         'customer_phone' => 'nullable|string',
    //         'shipping_address' => 'nullable|string',
    //         'notes' => 'nullable|string',
    //         'items' => 'required|array|min:1',
    //         'items.*.product_id' => 'required|exists:products,id',
    //         'items.*.product_name' => 'required|string',
    //         'items.*.price' => 'required|numeric|min:0',
    //         'items.*.quantity' => 'required|integer|min:1',
    //     ]);

    //     // Генерация номера заказа
    //     $orderNumber = 'ORD-' . strtoupper(uniqid());

    //     // Подсчет общей суммы
    //     $totalAmount = 0;
    //     foreach ($validated['items'] as $item) {
    //         $totalAmount += $item['price'] * $item['quantity'];
    //     }

    //     // Создание заказа
    //     $order = $project->orders()->create([
    //         'customer_id' => $validated['customer_id'],
    //         'order_number' => $orderNumber,
    //         'customer_name' => $validated['customer_name'],
    //         'customer_email' => $validated['customer_email'] ?? null,
    //         'customer_phone' => $validated['customer_phone'] ?? null,
    //         'shipping_address' => $validated['shipping_address'] ?? null,
    //         'notes' => $validated['notes'] ?? null,
    //         'total_amount' => $totalAmount,
    //         'status' => 'pending',
    //     ]);

    //     // Создание позиций заказа
    //     foreach ($validated['items'] as $item) {
    //         $order->items()->create([
    //             'product_id' => $item['product_id'],
    //             'product_name' => $item['product_name'],
    //             'price' => $item['price'],
    //             'quantity' => $item['quantity'],
    //             'subtotal' => $item['price'] * $item['quantity'],
    //         ]);
    //     }

    //     // Загрузить связи
    //     $order->load(['customer', 'items']);

    //     return response()->json($order, 201);
    // }
   
    public function store(Request $request, Project $project)
    {
        // Проверка доступа к проекту (оставляем как есть)
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
    
        $validated = $request->validate([
            // customer_id больше НЕ required (может прийти или нет)
            'customer_id' => 'nullable|integer',
    
            // telegram_id для TMA (может прийти или нет)
            'telegram_id' => 'nullable|integer',
    
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'notes' => 'nullable|string',
    
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.product_name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
    
        /**
         * MVP customer logic:
         * 1) Если есть telegram_id -> ищем customer в рамках ЭТОГО проекта.
         * 2) Если не найден -> создаём нового customer.
         * 3) Если telegram_id нет:
         *    - если есть customer_id -> используем его (без проверок принадлежности для MVP)
         *    - иначе создаём нового customer с уникальным telegram_id-заглушкой.
         */
        $customer = null;
    
        if (!empty($validated['telegram_id'])) {
            $customer = Customer::where('project_id', $project->id)
                ->where('telegram_id', $validated['telegram_id'])
                ->first();
    
            if (!$customer) {
                $customer = $project->customers()->create([
                    'telegram_id' => $validated['telegram_id'],
                    'name' => $validated['customer_name'],
                    'username' => null,
                    'phone' => $validated['customer_phone'] ?? null,
                    'email' => $validated['customer_email'] ?? null,
                    'orders_count' => 0,
                    'total_spent' => 0,
                ]);
            }
        } else {
            if (!empty($validated['customer_id'])) {
                $customer = Customer::find($validated['customer_id']);
            }
    
            if (!$customer) {
                // Заглушка, чтобы пройти UNIQUE(telegram_id) в customers
                $fakeTelegramId = (int) (now()->timestamp . rand(100, 999));
    
                $customer = $project->customers()->create([
                    'telegram_id' => $fakeTelegramId,
                    'name' => $validated['customer_name'],
                    'username' => null,
                    'phone' => $validated['customer_phone'] ?? null,
                    'email' => $validated['customer_email'] ?? null,
                    'orders_count' => 0,
                    'total_spent' => 0,
                ]);
            }
        }
    
        // Минимальная проверка, что товары принадлежат проекту (без Rule)
        foreach ($validated['items'] as $item) {
            $exists = Product::where('project_id', $project->id)
                ->where('id', $item['product_id'])
                ->exists();
    
            if (!$exists) {
                return response()->json([
                    'message' => 'Invalid product_id for this project: ' . $item['product_id']
                ], 422);
            }
        }
    
        // Генерация номера заказа
        $orderNumber = 'ORD-' . strtoupper(uniqid());
    
        // Подсчет общей суммы
        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }
    
        // Создание заказа
        $order = $project->orders()->create([
            'customer_id' => $customer->id,
            'order_number' => $orderNumber,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'shipping_address' => $validated['shipping_address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'total_amount' => $totalAmount,
            'status' => 'pending',
        ]);
    
        // Создание позиций заказа
        foreach ($validated['items'] as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['price'] * $item['quantity'],
            ]);
        }
    
        $order->load(['customer', 'items']);
    
        return response()->json($order, 201);
    }
    


    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        // Проверка доступа к проекту
        if ($order->project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order->load(['customer', 'items']);

        return response()->json($order);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Проверка доступа к проекту
        if ($order->project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order->update(['status' => $validated['status']]);

        return response()->json($order);
    }

    /**
     * Remove the specified order.
     */
    public function destroy(Order $order)
    {
        // Проверка доступа к проекту
        if ($order->project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
}
