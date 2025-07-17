# 🚀 Примеры использования TegBot v2.0

## Обзор

TegBot v2.0 позволяет создавать сложные мультиботные экосистемы для любых бизнес-задач. В этом разделе представлены практические примеры:

- 🛒 **E-commerce экосистема**: Магазин + поддержка + аналитика + уведомления
- 📰 **Медиа-платформа**: Новости на разных языках + экстренные уведомления  
- 🏢 **Корпоративная система**: HR + IT-поддержка + объявления + бронирование
- 🎮 **Игровая платформа**: Основная игра + турниры + статистика
- 📊 **Аналитическая платформа**: Сбор данных + отчеты + алерты
- 🎓 **Образовательная система**: Курсы + тестирование + сертификация

## 🛒 E-commerce экосистема

### Архитектура решения

Создаем полноценную экосистему интернет-магазина из 4 ботов:

```bash
# Создание ботов для интернет-магазина
php artisan teg:set  # shop - основной магазин
php artisan teg:set  # support - служба поддержки  
php artisan teg:set  # analytics - аналитика для админов
php artisan teg:set  # notifications - уведомления о заказах
```

**Результат:**
```
🛒 E-commerce экосистема создана:
├── ShopBot - основные покупки
├── SupportBot - помощь клиентам
├── AnalyticsBot - бизнес-метрики
└── NotificationsBot - уведомления менеджеров
```

### 1. ShopBot - Основной магазин

```php
<?php
// app/Bots/ShopBot.php
namespace App\Bots;

use Teg\LightBot;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;

class ShopBot extends LightBot
{
    public function main(): void
    {
        $this->commands();
        
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText);
        } elseif ($this->hasCallbackQuery()) {
            $this->handleCallbacks();
        } else {
            $this->fallback();
        }
    }

    public function commands(): void
    {
        $this->registerCommand('start', function () {
            $this->showMainMenu();
        }, ['description' => 'Главное меню магазина']);

        $this->registerCommand('catalog', function () {
            $this->showCategories();
        }, ['description' => 'Каталог товаров']);

        $this->registerCommand('cart', function () {
            $this->showCart();
        }, ['description' => 'Моя корзина']);

        $this->registerCommand('orders', function () {
            $this->showMyOrders();
        }, ['description' => 'Мои заказы']);

        $this->registerCommand('search', function ($args) {
            $query = implode(' ', $args);
            $this->searchProducts($query);
        }, [
            'description' => 'Поиск товаров',
            'args' => ['запрос']
        ]);

        $this->registerCommand('help', function () {
            $this->showHelp();
        }, ['description' => 'Помощь по использованию']);
    }

    private function showMainMenu(): void
    {
        $userId = $this->getUserId;
        $cartCount = $this->getCartItemsCount($userId);
        
        $message = "🛍️ **Добро пожаловать в наш магазин!**\n\n";
        $message .= "✨ Качественные товары с быстрой доставкой\n";
        $message .= "🚀 Удобный заказ прямо в Telegram\n\n";
        $message .= "📱 Выберите раздел:";

        $keyboard = [
            [
                ['text' => '📱 Каталог', 'callback_data' => 'catalog'],
                ['text' => "🛒 Корзина ({$cartCount})", 'callback_data' => 'cart']
            ],
            [
                ['text' => '📦 Мои заказы', 'callback_data' => 'orders'],
                ['text' => '🔍 Поиск', 'callback_data' => 'search']
            ],
            [
                ['text' => '📞 Поддержка', 'url' => 'https://t.me/supportbot'],
                ['text' => '👤 Профиль', 'callback_data' => 'profile']
            ]
        ];

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);
    }

    private function showCategories(): void
    {
        $categories = Category::where('active', true)
            ->withCount(['products' => function ($query) {
                $query->where('active', true)->where('stock', '>', 0);
            }])
            ->get();

        if ($categories->isEmpty()) {
            $this->sendMessage($this->getChatId, '😔 Каталог временно пуст');
            return;
        }

        $message = "📱 **Каталог товаров**\n\n";
        $message .= "Выберите категорию:";

        $keyboard = [];
        foreach ($categories->chunk(2) as $chunk) {
            $row = [];
            foreach ($chunk as $category) {
                $row[] = [
                    'text' => "{$category->emoji} {$category->name} ({$category->products_count})",
                    'callback_data' => "category_{$category->id}"
                ];
            }
            $keyboard[] = $row;
        }

        $keyboard[] = [['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']];

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);
    }

    private function showCategoryProducts(int $categoryId, int $page = 1): void
    {
        $category = Category::find($categoryId);
        if (!$category) {
            $this->answerCallbackQuery('Категория не найдена');
            return;
        }

        $perPage = 5;
        $products = Product::where('category_id', $categoryId)
            ->where('active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        if ($products->isEmpty()) {
            $message = "😔 В категории \"{$category->name}\" нет доступных товаров";
            $keyboard = [[['text' => '📱 Каталог', 'callback_data' => 'catalog']]];
        } else {
            $message = "📱 **{$category->name}**\n\n";
            
            foreach ($products as $product) {
                $message .= $this->formatProductShort($product) . "\n\n";
            }

            $keyboard = [];
            
            // Кнопки товаров
            foreach ($products as $product) {
                $keyboard[] = [[
                    'text' => "👀 {$product->name}",
                    'callback_data' => "product_{$product->id}"
                ]];
            }

            // Пагинация
            $navRow = [];
            if ($page > 1) {
                $navRow[] = [
                    'text' => '⬅️ Назад',
                    'callback_data' => "category_{$categoryId}_page_" . ($page - 1)
                ];
            }
            if ($products->hasMorePages()) {
                $navRow[] = [
                    'text' => 'Далее ➡️',
                    'callback_data' => "category_{$categoryId}_page_" . ($page + 1)
                ];
            }
            if ($navRow) $keyboard[] = $navRow;

            $keyboard[] = [['text' => '📱 Каталог', 'callback_data' => 'catalog']];
        }

        $this->editMessageText($this->getChatId, $this->getMessageId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);
    }

    private function showProduct(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product || !$product->active || $product->stock <= 0) {
            $this->answerCallbackQuery('Товар недоступен');
            return;
        }

        $message = $this->formatProductDetailed($product);

        $keyboard = [
            [
                ['text' => '🛒 В корзину', 'callback_data' => "add_cart_{$productId}"],
                ['text' => '⚡ Купить сейчас', 'callback_data' => "buy_now_{$productId}"]
            ],
            [
                ['text' => '📱 Каталог', 'callback_data' => 'catalog'],
                ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
            ]
        ];

        if ($product->image_url) {
            $this->sendPhoto($this->getChatId, $product->image_url, [
                'caption' => $message,
                'reply_markup' => ['inline_keyboard' => $keyboard],
                'parse_mode' => 'Markdown'
            ]);
        } else {
            $this->editMessageText($this->getChatId, $this->getMessageId, $message, [
                'reply_markup' => ['inline_keyboard' => $keyboard],
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    private function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product || !$product->active) {
            $this->answerCallbackQuery('Товар недоступен');
            return;
        }

        $userId = $this->getUserId;
        
        // Добавляем в корзину через модель Cart
        $cartItem = \App\Models\Cart::firstOrCreate([
            'user_id' => $userId,
            'product_id' => $productId
        ], ['quantity' => 0]);

        if ($cartItem->quantity >= $product->stock) {
            $this->answerCallbackQuery('Недостаточно товара на складе');
            return;
        }

        $cartItem->increment('quantity');
        
        $this->answerCallbackQuery("✅ {$product->name} добавлен в корзину!");

        // Уведомляем аналитику
        $this->notifyAnalytics('cart_add', [
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $cartItem->quantity
        ]);
    }

    private function handleCallbacks(): void
    {
        $data = $this->getCallbackData;
        
        if ($data === 'main_menu') {
            $this->showMainMenu();
        } elseif ($data === 'catalog') {
            $this->showCategories();
        } elseif ($data === 'cart') {
            $this->showCart();
        } elseif (str_starts_with($data, 'category_')) {
            $parts = explode('_', $data);
            $categoryId = (int)$parts[1];
            $page = isset($parts[3]) ? (int)$parts[3] : 1;
            $this->showCategoryProducts($categoryId, $page);
        } elseif (str_starts_with($data, 'product_')) {
            $productId = (int)str_replace('product_', '', $data);
            $this->showProduct($productId);
        } elseif (str_starts_with($data, 'add_cart_')) {
            $productId = (int)str_replace('add_cart_', '', $data);
            $this->addToCart($productId);
        }
    }

    private function formatProductShort(Product $product): string
    {
        $status = $product->stock > 0 ? '✅ В наличии' : '❌ Нет в наличии';
        
        return "**{$product->name}**\n" .
               "💰 {$product->price} ₽\n" .
               "📦 {$status} ({$product->stock} шт.)";
    }

    private function formatProductDetailed(Product $product): string
    {
        $message = "**{$product->name}**\n\n";
        $message .= "{$product->description}\n\n";
        $message .= "💰 **Цена:** {$product->price} ₽\n";
        $message .= "📦 **В наличии:** {$product->stock} шт.\n";
        
        if ($product->specifications) {
            $message .= "\n📋 **Характеристики:**\n{$product->specifications}";
        }
        
        return $message;
    }

    private function getCartItemsCount(int $userId): int
    {
        return \App\Models\Cart::where('user_id', $userId)->sum('quantity');
    }

    private function notifyAnalytics(string $event, array $data): void
    {
        // Отправляем событие в аналитический бот
        $analyticsBot = new \App\Bots\AnalyticsBot();
        $analyticsBot->logEvent($event, $data);
    }

    public function fallback(): void
    {
        $this->sendMessage($this->getChatId, 
            "❓ Не понимаю команду. Используйте /help для справки или выберите действие в меню.",
            ['reply_markup' => ['inline_keyboard' => [[
                ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
            ]]]]
        );
    }
}
```

### 2. SupportBot - Служба поддержки

```php
<?php
// app/Bots/SupportBot.php
namespace App\Bots;

use Teg\LightBot;
use App\Models\SupportTicket;

class SupportBot extends LightBot
{
    public function main(): void
    {
        $this->commands();
        
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText);
        } elseif ($this->hasCallbackQuery()) {
            $this->handleCallbacks();
        } else {
            $this->handleUserMessage();
        }
    }

    public function commands(): void
    {
        $this->registerCommand('start', function () {
            $this->showSupportMenu();
        }, ['description' => 'Главное меню поддержки']);

        $this->registerCommand('ticket', function () {
            $this->createTicket();
        }, ['description' => 'Создать обращение']);

        $this->registerCommand('status', function ($args) {
            if (empty($args)) {
                $this->showMyTickets();
            } else {
                $this->showTicketStatus((int)$args[0]);
            }
        }, [
            'description' => 'Статус обращений',
            'args' => ['номер_тикета?']
        ]);

        $this->registerCommand('faq', function () {
            $this->showFAQ();
        }, ['description' => 'Часто задаваемые вопросы']);

        // Команды для операторов
        $this->registerCommand('operator', function ($args) {
            $this->operatorPanel($args);
        }, [
            'description' => 'Панель оператора',
            'admin_only' => true
        ]);
    }

    private function showSupportMenu(): void
    {
        $message = "🎫 **Служба поддержки**\n\n";
        $message .= "Мы готовы помочь вам с любыми вопросами!\n\n";
        $message .= "🕐 **Время работы:** 9:00 - 18:00 (МСК)\n";
        $message .= "⚡ **Среднее время ответа:** 15 минут\n\n";
        $message .= "Выберите действие:";

        $keyboard = [
            [['text' => '🆕 Создать обращение', 'callback_data' => 'new_ticket']],
            [['text' => '📋 Мои обращения', 'callback_data' => 'my_tickets']],
            [['text' => '❓ Часто задаваемые вопросы', 'callback_data' => 'faq']],
            [['text' => '🛒 Вернуться в магазин', 'url' => 'https://t.me/shopbot']]
        ];

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);
    }

    private function createTicket(): void
    {
        $message = "🆕 **Создание обращения**\n\n";
        $message .= "Опишите вашу проблему или вопрос.\n";
        $message .= "Чем подробнее описание, тем быстрее мы сможем помочь.";

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['force_reply' => true]
        ]);

        // Устанавливаем состояние ожидания сообщения
        $this->setState('awaiting_ticket_description');
    }

    private function handleUserMessage(): void
    {
        $state = $this->getState();
        $messageText = $this->getMessageText;

        if ($state === 'awaiting_ticket_description') {
            $this->processNewTicket($messageText);
        } else {
            // Автоматически создаем тикет из произвольного сообщения
            $this->processNewTicket($messageText);
        }
    }

    private function processNewTicket(string $description): void
    {
        $userId = $this->getUserId;
        $userName = $this->getFromFirstName . ' ' . $this->getFromLastName;

        $ticket = SupportTicket::create([
            'user_id' => $userId,
            'user_name' => $userName,
            'subject' => 'Обращение от ' . $userName,
            'description' => $description,
            'status' => 'open',
            'priority' => 'normal',
            'created_at' => now()
        ]);

        $message = "✅ **Обращение создано!**\n\n";
        $message .= "🎫 **Номер:** #{$ticket->id}\n";
        $message .= "📝 **Описание:** {$description}\n";
        $message .= "⏱️ **Статус:** Открыто\n\n";
        $message .= "Наш оператор ответит в ближайшее время.";

        $keyboard = [
            [['text' => '📋 Мои обращения', 'callback_data' => 'my_tickets']],
            [['text' => '🏠 Главное меню', 'callback_data' => 'support_menu']]
        ];

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);

        $this->clearState();

        // Уведомляем операторов
        $this->notifyOperators($ticket);
    }

    private function showMyTickets(): void
    {
        $userId = $this->getUserId;
        $tickets = SupportTicket::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($tickets->isEmpty()) {
            $message = "📋 **Ваши обращения**\n\n";
            $message .= "У вас пока нет обращений в поддержку.";

            $keyboard = [
                [['text' => '🆕 Создать обращение', 'callback_data' => 'new_ticket']],
                [['text' => '🏠 Главное меню', 'callback_data' => 'support_menu']]
            ];
        } else {
            $message = "📋 **Ваши обращения**\n\n";

            foreach ($tickets as $ticket) {
                $statusEmoji = $this->getStatusEmoji($ticket->status);
                $message .= "🎫 #{$ticket->id} {$statusEmoji} {$ticket->status}\n";
                $message .= "📝 " . mb_substr($ticket->description, 0, 50) . "...\n";
                $message .= "📅 " . $ticket->created_at->format('d.m.Y H:i') . "\n\n";
            }

            $keyboard = [];
            foreach ($tickets->take(5) as $ticket) {
                $keyboard[] = [[
                    'text' => "🎫 #{$ticket->id}",
                    'callback_data' => "ticket_{$ticket->id}"
                ]];
            }

            $keyboard[] = [['text' => '🏠 Главное меню', 'callback_data' => 'support_menu']];
        }

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);
    }

    private function notifyOperators(SupportTicket $ticket): void
    {
        // Получаем список операторов (администраторов бота)
        $operators = $this->getBotModel()->admin_ids ?? [];

        $message = "🆕 **Новое обращение**\n\n";
        $message .= "🎫 **Номер:** #{$ticket->id}\n";
        $message .= "👤 **От:** {$ticket->user_name}\n";
        $message .= "📝 **Описание:** {$ticket->description}\n";
        $message .= "📅 **Время:** " . $ticket->created_at->format('d.m.Y H:i');

        foreach ($operators as $operatorId) {
            $this->sendMessage($operatorId, $message, [
                'parse_mode' => 'Markdown',
                'reply_markup' => ['inline_keyboard' => [[
                    ['text' => '📞 Ответить', 'callback_data' => "respond_{$ticket->id}"]
                ]]]
            ]);
        }
    }

    private function getStatusEmoji(string $status): string
    {
        return match($status) {
            'open' => '🟡',
            'in_progress' => '🔵', 
            'resolved' => '🟢',
            'closed' => '⚫',
            default => '❓'
        };
    }

    public function fallback(): void
    {
        $this->sendMessage($this->getChatId,
            "Опишите вашу проблему, и я создам обращение в службу поддержки.",
            ['reply_markup' => ['inline_keyboard' => [[
                ['text' => '🏠 Главное меню', 'callback_data' => 'support_menu']
            ]]]]
        );
    }
}
```

### 3. AnalyticsBot - Бизнес-аналитика

```php
<?php
// app/Bots/AnalyticsBot.php
namespace App\Bots;

use Teg\LightBot;
use App\Models\AnalyticsEvent;
use Illuminate\Support\Facades\DB;

class AnalyticsBot extends LightBot
{
    public function main(): void
    {
        $this->commands();
        
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText);
        } elseif ($this->hasCallbackQuery()) {
            $this->handleCallbacks();
        }
    }

    public function commands(): void
    {
        $this->registerCommand('start', function () {
            $this->showDashboard();
        }, [
            'description' => 'Панель аналитики',
            'admin_only' => true
        ]);

        $this->registerCommand('sales', function ($args) {
            $period = $args[0] ?? 'today';
            $this->showSalesStats($period);
        }, [
            'description' => 'Статистика продаж',
            'args' => ['период?'],
            'admin_only' => true
        ]);

        $this->registerCommand('users', function () {
            $this->showUserStats();
        }, [
            'description' => 'Статистика пользователей',
            'admin_only' => true
        ]);

        $this->registerCommand('products', function () {
            $this->showProductStats();
        }, [
            'description' => 'Популярные товары',
            'admin_only' => true
        ]);

        $this->registerCommand('report', function ($args) {
            $type = $args[0] ?? 'daily';
            $this->generateReport($type);
        }, [
            'description' => 'Генерация отчетов',
            'args' => ['тип?'],
            'admin_only' => true
        ]);
    }

    private function showDashboard(): void
    {
        $todayOrders = $this->getOrdersCount('today');
        $todayRevenue = $this->getRevenue('today');
        $activeUsers = $this->getActiveUsers('today');
        $conversionRate = $this->getConversionRate('today');

        $message = "📊 **Панель аналитики**\n\n";
        $message .= "📅 **Сегодня:**\n";
        $message .= "📦 Заказов: {$todayOrders}\n";
        $message .= "💰 Выручка: {$todayRevenue} ₽\n";
        $message .= "👥 Активных пользователей: {$activeUsers}\n";
        $message .= "📈 Конверсия: {$conversionRate}%\n\n";

        $weekRevenue = $this->getRevenue('week');
        $monthRevenue = $this->getRevenue('month');
        
        $message .= "📈 **Динамика:**\n";
        $message .= "📅 За неделю: {$weekRevenue} ₽\n";
        $message .= "📅 За месяц: {$monthRevenue} ₽\n\n";

        $topProducts = $this->getTopProducts(3);
        $message .= "🔥 **Топ товары:**\n";
        foreach ($topProducts as $i => $product) {
            $message .= ($i + 1) . ". {$product->name} ({$product->sales_count})\n";
        }

        $keyboard = [
            [
                ['text' => '📊 Продажи', 'callback_data' => 'sales_stats'],
                ['text' => '👥 Пользователи', 'callback_data' => 'user_stats']
            ],
            [
                ['text' => '📦 Товары', 'callback_data' => 'product_stats'],
                ['text' => '📄 Отчеты', 'callback_data' => 'reports']
            ],
            [
                ['text' => '🔄 Обновить', 'callback_data' => 'dashboard'],
                ['text' => '⚙️ Настройки', 'callback_data' => 'settings']
            ]
        ];

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);
    }

    private function showSalesStats(string $period = 'today'): void
    {
        $stats = $this->calculateSalesStats($period);
        
        $message = "📊 **Статистика продаж**\n\n";
        $message .= "📅 **Период:** " . $this->getPeriodLabel($period) . "\n\n";
        $message .= "📦 **Заказов:** {$stats['orders_count']}\n";
        $message .= "💰 **Выручка:** {$stats['revenue']} ₽\n";
        $message .= "🛒 **Средний чек:** {$stats['avg_order']} ₽\n";
        $message .= "📈 **Конверсия:** {$stats['conversion']}%\n\n";

        $message .= "📈 **По часам:**\n";
        foreach ($stats['hourly'] as $hour => $data) {
            $message .= "{$hour}:00 - {$data['orders']} заказов ({$data['revenue']} ₽)\n";
        }

        $keyboard = [
            [
                ['text' => 'Сегодня', 'callback_data' => 'sales_today'],
                ['text' => 'Вчера', 'callback_data' => 'sales_yesterday']
            ],
            [
                ['text' => 'Неделя', 'callback_data' => 'sales_week'],
                ['text' => 'Месяц', 'callback_data' => 'sales_month']
            ],
            [['text' => '🏠 Главная', 'callback_data' => 'dashboard']]
        ];

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);
    }

    public function logEvent(string $event, array $data): void
    {
        AnalyticsEvent::create([
            'event_type' => $event,
            'data' => $data,
            'user_id' => $data['user_id'] ?? null,
            'created_at' => now()
        ]);

        // Проверяем на критические события
        $this->checkCriticalEvents($event, $data);
    }

    private function checkCriticalEvents(string $event, array $data): void
    {
        // Алерт на крупную покупку
        if ($event === 'order_completed' && ($data['total'] ?? 0) > 10000) {
            $this->sendCriticalAlert("🚨 Крупный заказ: {$data['total']} ₽");
        }

        // Алерт на много ошибок
        if ($event === 'error' && $this->getErrorsCountLastHour() > 10) {
            $this->sendCriticalAlert("⚠️ Много ошибок за последний час");
        }
    }

    private function sendCriticalAlert(string $message): void
    {
        $admins = $this->getBotModel()->admin_ids ?? [];
        
        foreach ($admins as $adminId) {
            $this->sendMessage($adminId, $message);
        }
    }

    public function fallback(): void
    {
        $this->sendMessage($this->getChatId,
            "Этот бот доступен только администраторам для просмотра аналитики."
        );
    }
}
```

### 4. NotificationsBot - Уведомления

```php
<?php
// app/Bots/NotificationsBot.php
namespace App\Bots;

use Teg\LightBot;
use App\Models\Order;

class NotificationsBot extends LightBot
{
    public function main(): void
    {
        // Этот бот в основном получает уведомления
        $this->commands();
        
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText);
        }
    }

    public function commands(): void
    {
        $this->registerCommand('start', function () {
            $this->showNotificationSettings();
        }, [
            'description' => 'Настройки уведомлений',
            'admin_only' => true
        ]);

        $this->registerCommand('subscribe', function ($args) {
            $type = $args[0] ?? 'all';
            $this->subscribe($type);
        }, [
            'description' => 'Подписаться на уведомления',
            'args' => ['тип?'],
            'admin_only' => true
        ]);
    }

    public function notifyNewOrder(Order $order): void
    {
        $message = "🛒 **Новый заказ!**\n\n";
        $message .= "📦 **Номер:** #{$order->id}\n";
        $message .= "👤 **Клиент:** {$order->customer_name}\n";
        $message .= "💰 **Сумма:** {$order->total} ₽\n";
        $message .= "📅 **Время:** " . $order->created_at->format('d.m.Y H:i');

        $keyboard = [
            [
                ['text' => '👀 Просмотреть', 'callback_data' => "order_{$order->id}"],
                ['text' => '✅ Принять', 'callback_data' => "accept_{$order->id}"]
            ]
        ];

        $this->sendToSubscribers('new_order', $message, $keyboard);
    }

    public function notifyOrderStatusChange(Order $order, string $oldStatus): void
    {
        $message = "📦 **Изменение статуса заказа**\n\n";
        $message .= "📦 **Номер:** #{$order->id}\n";
        $message .= "📈 **Было:** {$oldStatus}\n";
        $message .= "📊 **Стало:** {$order->status}\n";
        $message .= "📅 **Время:** " . now()->format('d.m.Y H:i');

        $this->sendToSubscribers('status_change', $message);
    }

    private function sendToSubscribers(string $type, string $message, array $keyboard = []): void
    {
        $subscribers = $this->getBotModel()->admin_ids ?? [];

        foreach ($subscribers as $subscriberId) {
            $options = ['parse_mode' => 'Markdown'];
            if (!empty($keyboard)) {
                $options['reply_markup'] = ['inline_keyboard' => $keyboard];
            }
            
            $this->sendMessage($subscriberId, $message, $options);
        }
    }

    public function fallback(): void
    {
        $this->sendMessage($this->getChatId,
            "Этот бот отправляет уведомления о событиях в магазине."
        );
    }
}
```

### Интеграция между ботами

```php
// app/Services/EcommerceService.php
class EcommerceService
{
    public function processOrder(Order $order): void
    {
        // Уведомляем через NotificationsBot
        $notificationBot = new \App\Bots\NotificationsBot();
        $notificationBot->notifyNewOrder($order);

        // Логируем в аналитику
        $analyticsBot = new \App\Bots\AnalyticsBot();
        $analyticsBot->logEvent('order_created', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'total' => $order->total,
            'products_count' => $order->items->count()
        ]);

        // Создаем тикет поддержки если нужно
        if ($order->total > 50000) {
            $supportBot = new \App\Bots\SupportBot();
            $supportBot->createVipTicket($order);
        }
    }
}
```

### Запуск экосистемы

```bash
# 1. Создание всех ботов
php artisan teg:set  # shop
php artisan teg:set  # support  
php artisan teg:set  # analytics
php artisan teg:set  # notifications

# 2. Настройка администраторов
php artisan teg:bot admin shop --add=123456789
php artisan teg:bot admin support --add=123456789,987654321
php artisan teg:bot admin analytics --add=123456789
php artisan teg:bot admin notifications --add=123456789,987654321

# 3. Проверка работы
php artisan teg:health

# 4. Запуск статистики
php artisan teg:stats
```

**Результат:**
```
🛒 E-commerce экосистема запущена:
  ✅ ShopBot: https://yourdomain.com/webhook/shop
  ✅ SupportBot: https://yourdomain.com/webhook/support  
  ✅ AnalyticsBot: https://yourdomain.com/webhook/analytics
  ✅ NotificationsBot: https://yourdomain.com/webhook/notifications

📊 Статистика:
  • Всего ботов: 4
  • Активных: 4
  • Webhook'ов: 4
  • Администраторов: 6 уникальных
```

## 📰 Медиа-платформа

### Архитектура новостной системы

```bash
# Создание ботов для медиа-платформы
php artisan teg:set  # news_ru - русские новости
php artisan teg:set  # news_en - английские новости  
php artisan teg:set  # breaking - экстренные новости
php artisan teg:set  # editor - редакторская панель
```

### NewsRuBot - Русские новости

```php
<?php
// app/Bots/NewsRuBot.php
namespace App\Bots;

use Teg\LightBot;
use App\Models\Article;
use App\Models\Category;

class NewsRuBot extends LightBot
{
    public function main(): void
    {
        $this->commands();
        
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText);
        } elseif ($this->hasCallbackQuery()) {
            $this->handleCallbacks();
        }
    }

    public function commands(): void
    {
        $this->registerCommand('start', function () {
            $this->showNewsMenu();
        }, ['description' => 'Главное меню новостей']);

        $this->registerCommand('latest', function () {
            $this->showLatestNews();
        }, ['description' => 'Последние новости']);

        $this->registerCommand('categories', function () {
            $this->showCategories();
        }, ['description' => 'Категории новостей']);

        $this->registerCommand('subscribe', function ($args) {
            $category = $args[0] ?? 'all';
            $this->subscribeToCategory($category);
        }, [
            'description' => 'Подписка на категорию',
            'args' => ['категория?']
        ]);

        $this->registerCommand('search', function ($args) {
            $query = implode(' ', $args);
            $this->searchNews($query);
        }, [
            'description' => 'Поиск новостей',
            'args' => ['запрос']
        ]);
    }

    private function showNewsMenu(): void
    {
        $latestCount = Article::where('language', 'ru')
            ->where('published_at', '>=', now()->subHours(24))
            ->count();

        $message = "📰 **Новости России**\n\n";
        $message .= "🕐 Последние 24 часа: {$latestCount} новостей\n";
        $message .= "🔄 Обновления каждые 15 минут\n\n";
        $message .= "Выберите раздел:";

        $keyboard = [
            [
                ['text' => '🔥 Последние', 'callback_data' => 'latest'],
                ['text' => '📂 Категории', 'callback_data' => 'categories']
            ],
            [
                ['text' => '🔍 Поиск', 'callback_data' => 'search'],
                ['text' => '⚙️ Подписки', 'callback_data' => 'subscriptions']
            ],
            [
                ['text' => '🌍 English News', 'url' => 'https://t.me/news_en_bot'],
                ['text' => '🚨 Экстренные', 'url' => 'https://t.me/breaking_news_bot']
            ]
        ];

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);
    }

    private function showLatestNews(int $page = 1): void
    {
        $perPage = 5;
        $articles = Article::where('language', 'ru')
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        if ($articles->isEmpty()) {
            $this->sendMessage($this->getChatId, '📰 Пока нет новостей');
            return;
        }

        $message = "🔥 **Последние новости**\n\n";

        foreach ($articles as $article) {
            $message .= $this->formatArticleShort($article) . "\n\n";
        }

        $keyboard = [];
        
        // Кнопки статей
        foreach ($articles as $article) {
            $keyboard[] = [[
                'text' => "📖 " . mb_substr($article->title, 0, 30) . "...",
                'callback_data' => "article_{$article->id}"
            ]];
        }

        // Пагинация
        $navRow = [];
        if ($page > 1) {
            $navRow[] = [
                'text' => '⬅️ Назад',
                'callback_data' => "latest_page_" . ($page - 1)
            ];
        }
        if ($articles->hasMorePages()) {
            $navRow[] = [
                'text' => 'Далее ➡️',
                'callback_data' => "latest_page_" . ($page + 1)
            ];
        }
        if ($navRow) $keyboard[] = $navRow;

        $keyboard[] = [['text' => '🏠 Главная', 'callback_data' => 'news_menu']];

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);
    }

    private function showArticle(int $articleId): void
    {
        $article = Article::find($articleId);
        if (!$article || $article->status !== 'published') {
            $this->answerCallbackQuery('Статья не найдена');
            return;
        }

        $message = $this->formatArticleFull($article);

        $keyboard = [
            [
                ['text' => '📤 Поделиться', 'switch_inline_query' => $article->title],
                ['text' => '💾 Сохранить', 'callback_data' => "save_{$article->id}"]
            ],
            [
                ['text' => '📂 Категория', 'callback_data' => "category_{$article->category_id}"],
                ['text' => '🔥 Последние', 'callback_data' => 'latest']
            ]
        ];

        if ($article->image_url) {
            $this->sendPhoto($this->getChatId, $article->image_url, [
                'caption' => $message,
                'reply_markup' => ['inline_keyboard' => $keyboard],
                'parse_mode' => 'Markdown'
            ]);
        } else {
            $this->sendMessage($this->getChatId, $message, [
                'reply_markup' => ['inline_keyboard' => $keyboard],
                'parse_mode' => 'Markdown'
            ]);
        }

        // Логируем просмотр
        $this->logView($articleId);
    }

    private function formatArticleShort(Article $article): string
    {
        $timeAgo = $article->published_at->diffForHumans();
        $category = $article->category->emoji ?? '📰';
        
        return "{$category} **{$article->title}**\n" .
               "⏰ {$timeAgo} | 👀 {$article->views}\n" .
               mb_substr($article->summary, 0, 100) . "...";
    }

    private function formatArticleFull(Article $article): string
    {
        $timeAgo = $article->published_at->diffForHumans();
        $category = $article->category->name ?? 'Новости';
        
        $message = "**{$article->title}**\n\n";
        $message .= "{$article->content}\n\n";
        $message .= "📂 {$category} | ⏰ {$timeAgo} | 👀 {$article->views}";
        
        if ($article->source_url) {
            $message .= "\n🔗 [Источник]({$article->source_url})";
        }
        
        return $message;
    }

    private function logView(int $articleId): void
    {
        // Увеличиваем счетчик просмотров
        Article::where('id', $articleId)->increment('views');

        // Отправляем в аналитику
        $editorBot = new \App\Bots\EditorBot();
        $editorBot->logEvent('article_view', [
            'article_id' => $articleId,
            'user_id' => $this->getUserId,
            'timestamp' => now()
        ]);
    }

    public function publishArticle(Article $article): void
    {
        // Рассылаем подписчикам категории
        $subscribers = $this->getCategorySubscribers($article->category_id);
        
        $message = "🔥 **Новая статья**\n\n";
        $message .= $this->formatArticleShort($article);

        foreach ($subscribers as $subscriberId) {
            $this->sendMessage($subscriberId, $message, [
                'reply_markup' => ['inline_keyboard' => [[
                    ['text' => '📖 Читать', 'callback_data' => "article_{$article->id}"]
                ]]],
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    public function fallback(): void
    {
        $this->sendMessage($this->getChatId,
            "Добро пожаловать в новостной бот! Используйте /start для навигации.",
            ['reply_markup' => ['inline_keyboard' => [[
                ['text' => '🏠 Главная', 'callback_data' => 'news_menu']
            ]]]]
        );
    }
}
```

### BreakingBot - Экстренные новости

```php
<?php
// app/Bots/BreakingBot.php
namespace App\Bots;

use Teg\LightBot;
use App\Models\BreakingNews;

class BreakingBot extends LightBot
{
    public function main(): void
    {
        $this->commands();
        
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText);
        }
    }

    public function commands(): void
    {
        $this->registerCommand('start', function () {
            $this->showBreakingMenu();
        }, ['description' => 'Экстренные новости']);

        $this->registerCommand('alert', function ($args) {
            $message = implode(' ', $args);
            $this->publishBreakingNews($message);
        }, [
            'description' => 'Опубликовать экстренную новость',
            'args' => ['текст'],
            'admin_only' => true
        ]);
    }

    private function showBreakingMenu(): void
    {
        $message = "🚨 **Экстренные новости**\n\n";
        $message .= "Здесь публикуются только самые важные события\n";
        $message .= "📢 Мгновенные уведомления\n";
        $message .= "🔔 Высокий приоритет\n\n";

        $recent = BreakingNews::latest()->limit(3)->get();
        if ($recent->isNotEmpty()) {
            $message .= "📋 **Последние сообщения:**\n";
            foreach ($recent as $news) {
                $timeAgo = $news->created_at->diffForHumans();
                $message .= "🚨 {$news->message}\n⏰ {$timeAgo}\n\n";
            }
        }

        $keyboard = [
            [['text' => '🔔 Подписаться', 'callback_data' => 'subscribe_breaking']],
            [['text' => '📰 Обычные новости', 'url' => 'https://t.me/news_ru_bot']]
        ];

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);
    }

    public function publishBreakingNews(string $message): void
    {
        $breaking = BreakingNews::create([
            'message' => $message,
            'published_at' => now()
        ]);

        // Рассылаем всем подписчикам
        $subscribers = $this->getAllSubscribers();
        
        $alertMessage = "🚨 **ЭКСТРЕННО**\n\n{$message}";

        foreach ($subscribers as $subscriberId) {
            $this->sendMessage($subscriberId, $alertMessage, [
                'parse_mode' => 'Markdown'
            ]);
        }

        // Уведомляем другие боты
        $this->notifyOtherBots($breaking);
    }

    public function fallback(): void
    {
        $this->sendMessage($this->getChatId,
            "🚨 Бот экстренных новостей. Используйте /start для подписки."
        );
    }
}
```

### EditorBot - Редакторская панель

```php
<?php
// app/Bots/EditorBot.php
namespace App\Bots;

use Teg\LightBot;
use App\Models\Article;
use App\Models\AnalyticsEvent;

class EditorBot extends LightBot
{
    public function main(): void
    {
        $this->commands();
        
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText);
        } elseif ($this->hasCallbackQuery()) {
            $this->handleCallbacks();
        }
    }

    public function commands(): void
    {
        $this->registerCommand('start', function () {
            $this->showEditorPanel();
        }, [
            'description' => 'Панель редактора',
            'admin_only' => true
        ]);

        $this->registerCommand('stats', function () {
            $this->showStatistics();
        }, [
            'description' => 'Статистика публикаций',
            'admin_only' => true
        ]);

        $this->registerCommand('publish', function ($args) {
            $articleId = (int)$args[0];
            $this->publishArticle($articleId);
        }, [
            'description' => 'Опубликовать статью',
            'args' => ['ID'],
            'admin_only' => true
        ]);

        $this->registerCommand('breaking', function ($args) {
            $message = implode(' ', $args);
            $this->createBreakingNews($message);
        }, [
            'description' => 'Экстренная новость',
            'args' => ['текст'],
            'admin_only' => true
        ]);
    }

    private function showEditorPanel(): void
    {
        $todayArticles = Article::whereDate('created_at', today())->count();
        $pendingArticles = Article::where('status', 'pending')->count();
        $todayViews = AnalyticsEvent::where('event_type', 'article_view')
            ->whereDate('created_at', today())
            ->count();

        $message = "📝 **Редакторская панель**\n\n";
        $message .= "📊 **Сегодня:**\n";
        $message .= "📰 Статей создано: {$todayArticles}\n";
        $message .= "⏳ На модерации: {$pendingArticles}\n";
        $message .= "👀 Просмотров: {$todayViews}\n\n";

        $topArticles = $this->getTopArticlesToday();
        $message .= "🔥 **Популярные сегодня:**\n";
        foreach ($topArticles->take(3) as $i => $article) {
            $message .= ($i + 1) . ". {$article->title} ({$article->views})\n";
        }

        $keyboard = [
            [
                ['text' => '📊 Статистика', 'callback_data' => 'editor_stats'],
                ['text' => '⏳ На модерации', 'callback_data' => 'pending_articles']
            ],
            [
                ['text' => '🚨 Экстренное', 'callback_data' => 'create_breaking'],
                ['text' => '📈 Аналитика', 'callback_data' => 'analytics']
            ],
            [
                ['text' => '🔄 Обновить', 'callback_data' => 'editor_panel'],
                ['text' => '⚙️ Настройки', 'callback_data' => 'settings']
            ]
        ];

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);
    }

    public function logEvent(string $event, array $data): void
    {
        AnalyticsEvent::create([
            'event_type' => $event,
            'data' => $data,
            'created_at' => now()
        ]);
    }

    public function fallback(): void
    {
        $this->sendMessage($this->getChatId,
            "Панель доступна только редакторам и администраторам."
        );
    }
}
```

### Запуск медиа-платформы

```bash
# Создание ботов
php artisan teg:set  # news_ru
php artisan teg:set  # news_en  
php artisan teg:set  # breaking
php artisan teg:set  # editor

# Настройка прав
php artisan teg:bot admin editor --add=123456789
php artisan teg:bot admin breaking --add=123456789

# Настройка интеграций
php artisan teg:config set news_ru.auto_publish true
php artisan teg:config set breaking.priority_alerts true

php artisan teg:health
```

## 🏢 Корпоративная система

### Архитектура корпоративного решения

```bash
# Создание корпоративной экосистемы
php artisan teg:set  # hr - HR и персонал
php artisan teg:set  # it_support - IT поддержка
php artisan teg:set  # announcements - объявления
php artisan teg:set  # booking - бронирование ресурсов
```

### HrBot - HR и персонал

```php
<?php
// app/Bots/HrBot.php
namespace App\Bots;

use Teg\LightBot;
use App\Models\Employee;
use App\Models\LeaveRequest;

class HrBot extends LightBot
{
    public function commands(): void
    {
        $this->registerCommand('vacation', function ($args) {
            $days = (int)$args[0];
            $this->requestVacation($days);
        }, [
            'description' => 'Заявка на отпуск',
            'args' => ['дни']
        ]);

        $this->registerCommand('sick', function () {
            $this->reportSickLeave();
        }, ['description' => 'Больничный']);

        $this->registerCommand('schedule', function () {
            $this->showMySchedule();
        }, ['description' => 'Мое расписание']);

        $this->registerCommand('team', function () {
            $this->showTeamInfo();
        }, ['description' => 'Информация о команде']);
    }

    private function requestVacation(int $days): void
    {
        $employee = $this->getEmployee();
        if (!$employee) {
            $this->sendMessage($this->getChatId, "❌ Сотрудник не найден");
            return;
        }

        $request = LeaveRequest::create([
            'employee_id' => $employee->id,
            'type' => 'vacation',
            'days' => $days,
            'status' => 'pending',
            'requested_at' => now()
        ]);

        $message = "✅ **Заявка на отпуск подана**\n\n";
        $message .= "📅 **Дней:** {$days}\n";
        $message .= "🆔 **Номер заявки:** #{$request->id}\n";
        $message .= "⏳ **Статус:** На рассмотрении\n\n";
        $message .= "Уведомление отправлено руководителю.";

        $this->sendMessage($this->getChatId, $message, [
            'parse_mode' => 'Markdown'
        ]);

        // Уведомляем руководителя
        $this->notifyManager($request);
    }
}
```

### ItSupportBot - IT поддержка

```php
<?php
// app/Bots/ItSupportBot.php
namespace App\Bots;

use Teg\LightBot;
use App\Models\ItTicket;

class ItSupportBot extends LightBot
{
    public function commands(): void
    {
        $this->registerCommand('ticket', function ($args) {
            $description = implode(' ', $args);
            $this->createItTicket($description);
        }, [
            'description' => 'Создать IT заявку',
            'args' => ['описание']
        ]);

        $this->registerCommand('password', function () {
            $this->resetPassword();
        }, ['description' => 'Сброс пароля']);

        $this->registerCommand('access', function ($args) {
            $system = $args[0] ?? '';
            $this->requestAccess($system);
        }, [
            'description' => 'Запрос доступа',
            'args' => ['система?']
        ]);
    }

    private function createItTicket(string $description): void
    {
        $ticket = ItTicket::create([
            'user_id' => $this->getUserId,
            'description' => $description,
            'priority' => 'normal',
            'status' => 'open'
        ]);

        $message = "🎫 **IT заявка создана**\n\n";
        $message .= "🆔 **Номер:** #{$ticket->id}\n";
        $message .= "📝 **Описание:** {$description}\n";
        $message .= "⏱️ **Статус:** Открыта\n\n";
        $message .= "Наш IT-специалист свяжется с вами в ближайшее время.";

        $this->sendMessage($this->getChatId, $message, [
            'parse_mode' => 'Markdown'
        ]);
    }
}
```

## 🎮 Игровая платформа

### Архитектура игровой системы

```bash
# Создание игровой экосистемы
php artisan teg:set  # game - основная игра
php artisan teg:set  # tournaments - турниры
php artisan teg:set  # stats - статистика игроков
```

### GameBot - Основная игра

```php
<?php
// app/Bots/GameBot.php
namespace App\Bots;

use Teg\LightBot;
use App\Models\Player;
use App\Models\GameSession;

class GameBot extends LightBot
{
    public function commands(): void
    {
        $this->registerCommand('play', function () {
            $this->startGame();
        }, ['description' => 'Начать игру']);

        $this->registerCommand('profile', function () {
            $this->showProfile();
        }, ['description' => 'Мой профиль']);

        $this->registerCommand('leaderboard', function () {
            $this->showLeaderboard();
        }, ['description' => 'Таблица лидеров']);

        $this->registerCommand('battle', function ($args) {
            $opponentId = (int)$args[0];
            $this->startBattle($opponentId);
        }, [
            'description' => 'Битва с игроком',
            'args' => ['ID_игрока']
        ]);
    }

    private function startGame(): void
    {
        $player = $this->getOrCreatePlayer();
        
        $session = GameSession::create([
            'player_id' => $player->id,
            'started_at' => now()
        ]);

        $message = "🎮 **Игра началась!**\n\n";
        $message .= "🏆 **Уровень:** {$player->level}\n";
        $message .= "⭐ **Опыт:** {$player->experience}\n";
        $message .= "💰 **Монеты:** {$player->coins}\n\n";
        $message .= "Выберите действие:";

        $keyboard = [
            [
                ['text' => '⚔️ Битва', 'callback_data' => 'battle_menu'],
                ['text' => '🗺️ Квест', 'callback_data' => 'quest_menu']
            ],
            [
                ['text' => '🏪 Магазин', 'callback_data' => 'shop'],
                ['text' => '🎁 Награды', 'callback_data' => 'rewards']
            ]
        ];

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);
    }
}
```

## 📊 Аналитическая платформа

### Архитектура аналитической системы

```bash
# Создание аналитической экосистемы
php artisan teg:set  # collector - сбор данных
php artisan teg:set  # reports - отчеты
php artisan teg:set  # alerts - алерты
```

### CollectorBot - Сбор данных

```php
<?php
// app/Bots/CollectorBot.php
namespace App\Bots;

use Teg\LightBot;
use App\Models\DataPoint;

class CollectorBot extends LightBot
{
    public function commands(): void
    {
        $this->registerCommand('metric', function ($args) {
            $name = $args[0];
            $value = $args[1];
            $this->recordMetric($name, $value);
        }, [
            'description' => 'Записать метрику',
            'args' => ['название', 'значение']
        ]);

        $this->registerCommand('event', function ($args) {
            $event = implode(' ', $args);
            $this->recordEvent($event);
        }, [
            'description' => 'Записать событие',
            'args' => ['описание']
        ]);
    }

    private function recordMetric(string $name, float $value): void
    {
        DataPoint::create([
            'metric_name' => $name,
            'value' => $value,
            'timestamp' => now(),
            'source' => 'telegram'
        ]);

        $this->sendMessage($this->getChatId, 
            "✅ Метрика '{$name}' = {$value} записана"
        );

        // Проверяем алерты
        $this->checkAlerts($name, $value);
    }
}
```

## 🎓 Образовательная система

### Архитектура образовательной платформы

```bash
# Создание образовательной экосистемы
php artisan teg:set  # courses - курсы
php artisan teg:set  # testing - тестирование
php artisan teg:set  # certificates - сертификация
```

### CoursesBot - Курсы

```php
<?php
// app/Bots/CoursesBot.php
namespace App\Bots;

use Teg\LightBot;
use App\Models\Course;
use App\Models\StudentProgress;

class CoursesBot extends LightBot
{
    public function commands(): void
    {
        $this->registerCommand('courses', function () {
            $this->showCourses();
        }, ['description' => 'Доступные курсы']);

        $this->registerCommand('enroll', function ($args) {
            $courseId = (int)$args[0];
            $this->enrollCourse($courseId);
        }, [
            'description' => 'Записаться на курс',
            'args' => ['ID_курса']
        ]);

        $this->registerCommand('progress', function () {
            $this->showProgress();
        }, ['description' => 'Мой прогресс']);

        $this->registerCommand('lesson', function ($args) {
            $lessonId = (int)$args[0];
            $this->startLesson($lessonId);
        }, [
            'description' => 'Начать урок',
            'args' => ['ID_урока']
        ]);
    }

    private function showCourses(): void
    {
        $courses = Course::where('active', true)->get();

        $message = "📚 **Доступные курсы**\n\n";

        foreach ($courses as $course) {
            $message .= "📖 **{$course->title}**\n";
            $message .= "⏱️ {$course->duration} часов\n";
            $message .= "🎯 {$course->level}\n";
            $message .= "💰 {$course->price} ₽\n\n";
        }

        $keyboard = [];
        foreach ($courses as $course) {
            $keyboard[] = [[
                'text' => "📚 {$course->title}",
                'callback_data' => "course_{$course->id}"
            ]];
        }

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);
    }

    private function enrollCourse(int $courseId): void
    {
        $course = Course::find($courseId);
        if (!$course) {
            $this->sendMessage($this->getChatId, "❌ Курс не найден");
            return;
        }

        $progress = StudentProgress::create([
            'student_id' => $this->getUserId,
            'course_id' => $courseId,
            'enrolled_at' => now(),
            'progress_percentage' => 0
        ]);

        $message = "✅ **Вы записаны на курс!**\n\n";
        $message .= "📚 **Курс:** {$course->title}\n";
        $message .= "🎯 **Уровень:** {$course->level}\n";
        $message .= "📅 **Начало:** " . now()->format('d.m.Y') . "\n\n";
        $message .= "Можете начинать обучение!";

        $keyboard = [
            [['text' => '▶️ Начать первый урок', 'callback_data' => "start_course_{$courseId}"]],
            [['text' => '📋 Программа курса', 'callback_data' => "curriculum_{$courseId}"]]
        ];

        $this->sendMessage($this->getChatId, $message, [
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'Markdown'
        ]);
    }
}
```

## 🚀 Быстрый старт для любой экосистемы

### Универсальный шаблон настройки

```bash
# 1. Планирование архитектуры
php artisan teg:config show  # проверяем текущие настройки

# 2. Создание ботов (интерактивно)
php artisan teg:set    # основной бот
php artisan teg:set    # вспомогательный бот 1
php artisan teg:set    # вспомогательный бот 2
php artisan teg:set    # административный бот

# 3. Настройка администраторов
php artisan teg:bot admin main_bot --add=123456789
php artisan teg:bot admin admin_bot --add=123456789,987654321

# 4. Конфигурация ботов
php artisan teg:bot config main_bot --set auto_responses=true
php artisan teg:bot config admin_bot --set notifications=true

# 5. Установка webhook'ов
php artisan teg:webhook set

# 6. Проверка работоспособности
php artisan teg:health

# 7. Тестирование ботов
php artisan teg:bot test main_bot
php artisan teg:bot test admin_bot

# 8. Мониторинг
php artisan teg:stats
```

### Типовые паттерны интеграции

```php
// Базовый класс для интеграции между ботами
abstract class IntegratedBot extends LightBot
{
    protected function notifyOtherBot(string $botName, string $method, array $data): void
    {
        $botClass = "App\\Bots\\" . ucfirst($botName) . "Bot";
        if (class_exists($botClass)) {
            $bot = new $botClass();
            $bot->$method($data);
        }
    }

    protected function logToAnalytics(string $event, array $data): void
    {
        $this->notifyOtherBot('analytics', 'logEvent', [
            'event' => $event,
            'data' => $data,
            'timestamp' => now()
        ]);
    }

    protected function sendNotification(string $message, string $type = 'info'): void
    {
        $this->notifyOtherBot('notifications', 'send', [
            'message' => $message,
            'type' => $type,
            'source_bot' => class_basename(static::class)
        ]);
    }
}
```

### Мониторинг экосистемы

```bash
# Здоровье всех ботов
php artisan teg:health

# Статистика по ботам
php artisan teg:stats

# Детальная информация
php artisan teg:bot show bot_name

# Логи ошибок
php artisan teg:bot logs bot_name --errors

# Производительность
php artisan teg:bot performance
```

### Best Practices для мультиботных экосистем

1. **🏗️ Архитектура:**
   - Каждый бот решает одну задачу
   - Четкое разделение ответственности
   - Единая система логирования

2. **🔗 Интеграция:**
   - Используйте общие модели данных
   - Создавайте сервисные классы для бизнес-логики
   - Уведомляйте ботов о важных событиях

3. **🛡️ Безопасность:**
   - Разные права для разных ботов
   - Изоляция конфигураций
   - Индивидуальные webhook secrets

4. **📊 Мониторинг:**
   - Централизованная аналитика
   - Алерты на критические события
   - Регулярные health checks

5. **🚀 Масштабирование:**
   - Горизонтальное добавление ботов
   - Балансировка нагрузки через очереди
   - Кеширование часто используемых данных

---

🎯 **TegBot v2.0** позволяет создавать любые мультиботные экосистемы - от простых магазинов до сложных корпоративных систем! 