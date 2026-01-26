<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Укажите ID проекта здесь
        $projectId = 13;
        
        $project = Project::find($projectId);
        
        if (!$project) {
            echo "❌ Проект с ID {$projectId} не найден.\n";
            echo "Доступные проекты:\n";
            Project::all(['id', 'name'])->each(function($p) {
                echo "  - ID: {$p->id}, Название: {$p->name}\n";
            });
            return;
        }

        echo "📦 Проект: {$project->name} (ID: {$project->id})\n\n";

        // ============================================
        // 1. УДАЛЕНИЕ СТАРЫХ ДАННЫХ
        // ============================================
        echo "🗑️  Удаление старых данных...\n";
        
        $deletedOrders = $project->orders()->count();
        $deletedCustomers = $project->customers()->count();
        $deletedProducts = $project->products()->count();
        
        // Удалить заказы (автоматически удалятся order_items через cascade)
        $project->orders()->delete();
        
        // Удалить покупателей
        $project->customers()->delete();
        
        // Удалить товары
        $project->products()->delete();
        
        echo "   ✓ Удалено товаров: {$deletedProducts}\n";
        echo "   ✓ Удалено заказов: {$deletedOrders}\n";
        echo "   ✓ Удалено покупателей: {$deletedCustomers}\n\n";

        // ============================================
        // 2. СОЗДАНИЕ ТОВАРОВ (21 шт)
        // ============================================
        echo "📦 Создание товаров...\n";
        
        $products = [
            // Электроника (7 товаров)
            ['name' => 'iPhone 15 Pro', 'price' => 99990, 'stock' => 15, 'category' => 'Электроника', 'description' => 'Флагманский смартфон Apple'],
            ['name' => 'Samsung Galaxy S24', 'price' => 79990, 'stock' => 20, 'category' => 'Электроника', 'description' => 'Топовый Android смартфон'],
            ['name' => 'AirPods Pro 2', 'price' => 24990, 'stock' => 30, 'category' => 'Электроника', 'description' => 'Беспроводные наушники'],
            ['name' => 'MacBook Air M3', 'price' => 129990, 'stock' => 10, 'category' => 'Электроника', 'description' => 'Ультрабук от Apple'],
            ['name' => 'iPad Pro 11"', 'price' => 89990, 'stock' => 12, 'category' => 'Электроника', 'description' => 'Планшет для профессионалов'],
            ['name' => 'Apple Watch Series 9', 'price' => 44990, 'stock' => 25, 'category' => 'Электроника', 'description' => 'Умные часы'],
            ['name' => 'PlayStation 5', 'price' => 54990, 'stock' => 8, 'category' => 'Электроника', 'description' => 'Игровая консоль Sony'],
            
            // Одежда (7 товаров)
            ['name' => 'Футболка Nike', 'price' => 2999, 'stock' => 50, 'category' => 'Одежда', 'description' => 'Хлопковая футболка'],
            ['name' => 'Джинсы Levi\'s 501', 'price' => 6999, 'stock' => 30, 'category' => 'Одежда', 'description' => 'Классические джинсы'],
            ['name' => 'Кроссовки Adidas', 'price' => 8999, 'stock' => 25, 'category' => 'Одежда', 'description' => 'Спортивная обувь'],
            ['name' => 'Куртка North Face', 'price' => 15999, 'stock' => 15, 'category' => 'Одежда', 'description' => 'Зимняя куртка'],
            ['name' => 'Худи Supreme', 'price' => 12999, 'stock' => 20, 'category' => 'Одежда', 'description' => 'Стильная толстовка'],
            ['name' => 'Кепка New Era', 'price' => 1999, 'stock' => 40, 'category' => 'Одежда', 'description' => 'Бейсболка'],
            ['name' => 'Рюкзак Herschel', 'price' => 5999, 'stock' => 18, 'category' => 'Одежда', 'description' => 'Городской рюкзак'],
            
            // Косметика (7 товаров)
            ['name' => 'Крем Nivea', 'price' => 499, 'stock' => 100, 'category' => 'Косметика', 'description' => 'Увлажняющий крем'],
            ['name' => 'Духи Dior Sauvage', 'price' => 7999, 'stock' => 25, 'category' => 'Косметика', 'description' => 'Мужской парфюм'],
            ['name' => 'Помада MAC', 'price' => 1999, 'stock' => 60, 'category' => 'Косметика', 'description' => 'Матовая помада'],
            ['name' => 'Тушь Maybelline', 'price' => 799, 'stock' => 80, 'category' => 'Косметика', 'description' => 'Удлиняющая тушь'],
            ['name' => 'Сыворотка для лица', 'price' => 2499, 'stock' => 45, 'category' => 'Косметика', 'description' => 'С витамином C'],
            ['name' => 'Шампунь L\'Oreal', 'price' => 599, 'stock' => 70, 'category' => 'Косметика', 'description' => 'Для окрашенных волос'],
            ['name' => 'Маска для лица', 'price' => 399, 'stock' => 90, 'category' => 'Косметика', 'description' => 'Увлажняющая маска'],
        ];

        $createdProducts = [];
        foreach ($products as $productData) {
            $product = $project->products()->create([
                'name' => $productData['name'],
                'price' => $productData['price'],
                'stock' => $productData['stock'],
                'category' => $productData['category'],
                'description' => $productData['description'],
                'is_active' => true,
            ]);
            $createdProducts[] = $product;
            echo "   ✓ {$product->name} - ₽{$product->price}\n";
        }
        
        echo "\n✅ Создано товаров: " . count($createdProducts) . "\n\n";

        // ============================================
        // 3. СОЗДАНИЕ ПОКУПАТЕЛЕЙ (5 шт)
        // ============================================
        echo "👥 Создание покупателей...\n";
        
        $customersData = [
            ['telegram_id' => 123456789, 'name' => 'Иван Иванов', 'username' => 'ivanov', 'phone' => '+7 999 123 4567'],
            ['telegram_id' => 987654321, 'name' => 'Мария Петрова', 'username' => 'petrova', 'phone' => '+7 999 765 4321'],
            ['telegram_id' => 555555555, 'name' => 'Алексей Сидоров', 'username' => 'sidorov', 'phone' => '+7 999 555 5555'],
            ['telegram_id' => 111222333, 'name' => 'Ольга Смирнова', 'username' => 'smirnova', 'phone' => '+7 999 111 2233'],
            ['telegram_id' => 444555666, 'name' => 'Дмитрий Козлов', 'username' => 'kozlov', 'phone' => '+7 999 444 5566'],
        ];

        $createdCustomers = [];
        foreach ($customersData as $customerData) {
            $customer = $project->customers()->create($customerData);
            $createdCustomers[] = $customer;
            echo "   ✓ {$customer->name} (@{$customer->username})\n";
        }
        
        echo "\n✅ Создано покупателей: " . count($createdCustomers) . "\n\n";

        // ============================================
        // 4. СОЗДАНИЕ ЗАКАЗОВ (8 шт)
        // ============================================
        echo "🛒 Создание заказов...\n";
        
        $statuses = ['pending', 'processing', 'completed', 'cancelled'];
        $createdOrders = 0;

        for ($i = 0; $i < 8; $i++) {
            // Случайный покупатель
            $customer = $createdCustomers[array_rand($createdCustomers)];
            
            // Случайное количество товаров в заказе (2-4)
            $orderProductsCount = rand(2, 4);
            $orderProducts = array_rand(array_flip(range(0, count($createdProducts) - 1)), $orderProductsCount);
            
            if (!is_array($orderProducts)) {
                $orderProducts = [$orderProducts];
            }
            
            $totalAmount = 0;
            $items = [];
            
            foreach ($orderProducts as $productIndex) {
                $product = $createdProducts[$productIndex];
                $quantity = rand(1, 3);
                $subtotal = $product->price * $quantity;
                $totalAmount += $subtotal;
                
                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal
                ];
            }
            
            $order = $project->orders()->create([
                'customer_id' => $customer->id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email ?? null,
                'shipping_address' => 'Москва, ул. ' . ['Тверская', 'Арбат', 'Ленина', 'Пушкина'][rand(0, 3)] . ', д. ' . rand(1, 100),
                'total_amount' => $totalAmount,
                'status' => $statuses[rand(0, 3)]
            ]);
            
            foreach ($items as $item) {
                $order->items()->create($item);
            }
            
            $createdOrders++;
            $itemsText = count($items) . ' ' . ['товар', 'товара', 'товаров'][min(count($items) - 1, 2)];
            echo "   ✓ {$order->order_number} - {$customer->name} - {$itemsText} - ₽{$totalAmount} - [{$order->status}]\n";
        }
        
        echo "\n✅ Создано заказов: {$createdOrders}\n\n";

        // ============================================
        // 5. ИТОГИ
        // ============================================
        echo "═══════════════════════════════════════\n";
        echo "📊 ИТОГОВАЯ СТАТИСТИКА\n";
        echo "═══════════════════════════════════════\n";
        echo "Проект: {$project->name} (ID: {$project->id})\n\n";
        echo "📦 Товаров: " . $project->products()->count() . "\n";
        echo "   └─ Электроника: " . $project->products()->where('category', 'Электроника')->count() . "\n";
        echo "   └─ Одежда: " . $project->products()->where('category', 'Одежда')->count() . "\n";
        echo "   └─ Косметика: " . $project->products()->where('category', 'Косметика')->count() . "\n";
        echo "\n";
        echo "👥 Покупателей: " . $project->customers()->count() . "\n";
        echo "\n";
        echo "🛒 Заказов: " . $project->orders()->count() . "\n";
        echo "   └─ Ожидает: " . $project->orders()->where('status', 'pending')->count() . "\n";
        echo "   └─ В обработке: " . $project->orders()->where('status', 'processing')->count() . "\n";
        echo "   └─ Выполнен: " . $project->orders()->where('status', 'completed')->count() . "\n";
        echo "   └─ Отменён: " . $project->orders()->where('status', 'cancelled')->count() . "\n";
        echo "\n";
        
        $totalRevenue = $project->orders()->where('status', '!=', 'cancelled')->sum('total_amount');
        echo "💰 Общая сумма заказов: ₽" . number_format($totalRevenue, 2, '.', ' ') . "\n";
        echo "═══════════════════════════════════════\n";
    }
}
