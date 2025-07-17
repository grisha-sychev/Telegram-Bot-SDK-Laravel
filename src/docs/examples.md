# 🚀 Примеры использования TegBot

## Обзор примеров

В этом разделе представлены готовые к использованию примеры ботов для различных сценариев:

- 🛒 **E-commerce бот**: Онлайн магазин с каталогом и заказами
- 📰 **Новостной бот**: Подписки и рассылка новостей
- 🎫 **Служба поддержки**: Тикеты и обработка обращений
- 🎮 **Игровой бот**: Мини-игры и развлечения
- 📊 **Аналитический бот**: Сбор данных и отчеты
- 🏢 **Корпоративный бот**: Внутренние процессы компании

## 🛒 E-commerce бот

### Полный пример интернет-магазина

```php
<?php

namespace App\Bots;

use Teg\Modules\UserModule;
use Teg\Modules\StateModule;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;

class EcommerceBot extends AdstractBot
{
    use StateModule, UserModule;

    public function main(): void
    {
        // Глобальные middleware
        $this->globalMiddleware([
            'spam_protection',
            'user_tracking',
            'activity_logging',
        ]);

        // Регистрация команд
        $this->registerCommands();

        // Обработка медиа каталога
        $this->mediaWithCaption(function ($mediaInfo, $caption) {
            $this->handleProductImage($mediaInfo, $caption);
        });

        // Обработка команд и сообщений
        if ($this->hasMessageText()) {
            if ($this->isMessageCommand()) {
                $this->handleCommand($this->getMessageText);
            } else {
                $this->handleTextMessage();
            }
        }

        // Обработка callback'ов
        $this->handleCallbacks();
    }

    private function registerCommands(): void
    {
        $this->registerCommand('start', function () {
            $this->showMainMenu();
        }, [
            'description' => 'Главное меню магазина',
        ]);

        $this->registerCommand('catalog', function () {
            $this->showCategories();
        }, [
            'description' => 'Каталог товаров',
        ]);

        $this->registerCommand('cart', function () {
            $this->showCart();
        }, [
            'description' => 'Корзина покупок',
        ]);

        $this->registerCommand('orders', function () {
            $this->showOrders();
        }, [
            'description' => 'Мои заказы',
        ]);

        $this->registerCommand('search', function ($args) {
            $this->searchProducts($args);
        }, [
            'description' => 'Поиск товаров',
            'args' => ['query'],
        ]);

        // Админские команды
        $this->registerCommand('admin', function ($args) {
            $this->adminPanel($args);
        }, [
            'description' => 'Панель администратора',
            'admin_only' => true,
            'private_only' => true,
        ]);
    }

    private function showMainMenu(): void
    {
        $user = $this->getUser();
        $cartCount = $this->getCartItemsCount($user->id);

        $message = "🛍️ **Добро пожаловать в наш интернет-магазин!**\n\n";
        $message .= "💫 Качественные товары с быстрой доставкой\n";
        $message .= "🚀 Удобный заказ прямо в Telegram\n\n";
        $message .= "Выберите раздел:";

        $buttons = [
            ['callback:show_categories', '📱 Каталог товаров'],
            ['callback:show_cart', "🛒 Корзина ({$cartCount})"],
            ['callback:show_orders', '📦 Мои заказы'],
            ['callback:show_profile', '👤 Профиль'],
        ];

        $this->sendSelfInline($message, $buttons);
    }

    private function showCategories(): void
    {
        $categories = Category::where('active', true)->get();

        if ($categories->isEmpty()) {
            $this->sendSelf('😔 К сожалению, каталог временно пуст');
            return;
        }

        $message = "📱 **Выберите категорию:**\n\n";
        $buttons = [];

        foreach ($categories as $category) {
            $productCount = $category->products()->where('active', true)->count();
            $buttons[] = [
                "callback:category_{$category->id}",
                "{$category->emoji} {$category->name} ({$productCount})"
            ];
        }

        // Добавляем кнопки управления
        $buttons[] = ['callback:search_products', '🔍 Поиск'];
        $buttons[] = ['callback:show_main_menu', '⬅️ Главное меню'];

        $this->sendSelfInline($message, $buttons);
    }

    private function showCategoryProducts(int $categoryId, int $page = 1): void
    {
        $category = Category::find($categoryId);
        if (!$category) {
            $this->sendSelf('❌ Категория не найдена');
            return;
        }

        $perPage = 5;
        $products = $category->products()
            ->where('active', true)
            ->paginate($perPage, ['*'], 'page', $page);

        if ($products->isEmpty()) {
            $this->sendSelf("😔 В категории \"{$category->name}\" пока нет товаров");
            return;
        }

        $message = "📱 **{$category->name}**\n\n";

        foreach ($products as $product) {
            $message .= $this->formatProduct($product) . "\n\n";
        }

        // Пагинация
        $buttons = [];
        if ($products->hasPages()) {
            if ($products->currentPage() > 1) {
                $buttons[] = [
                    "callback:category_{$categoryId}_page_" . ($page - 1),
                    '⬅️ Назад'
                ];
            }
            if ($products->hasMorePages()) {
                $buttons[] = [
                    "callback:category_{$categoryId}_page_" . ($page + 1),
                    '➡️ Далее'
                ];
            }
        }

        $buttons[] = ['callback:show_categories', '📱 Категории'];
        $buttons[] = ['callback:show_main_menu', '🏠 Главное меню'];

        $this->sendSelfInline($message, $buttons);
    }

    private function showProduct(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product || !$product->active) {
            $this->sendSelf('❌ Товар не найден');
            return;
        }

        $message = $this->formatProductDetailed($product);

        $buttons = [
            ["callback:add_to_cart_{$productId}", '🛒 В корзину'],
            ["callback:buy_now_{$productId}", '⚡ Купить сейчас'],
            ['callback:show_categories', '📱 Каталог'],
        ];

        // Если есть изображение товара
        if ($product->image) {
            $this->sendPhoto($product->image, $message, $buttons);
        } else {
            $this->sendSelfInline($message, $buttons);
        }
    }

    private function addToCart(int $productId, int $quantity = 1): void
    {
        $product = Product::find($productId);
        if (!$product || !$product->active) {
            $this->sendSelf('❌ Товар не доступен');
            return;
        }

        $user = $this->getUser();
        
        // Проверяем наличие на складе
        if ($product->stock < $quantity) {
            $this->sendSelf("❌ Недостаточно товара на складе. Доступно: {$product->stock} шт.");
            return;
        }

        // Добавляем в корзину
        $cartItem = $user->cartItems()->firstOrCreate(
            ['product_id' => $productId],
            ['quantity' => 0]
        );

        $cartItem->increment('quantity', $quantity);

        $total = $cartItem->quantity * $product->price;
        
        $message = "✅ **Товар добавлен в корзину!**\n\n";
        $message .= "📦 {$product->name}\n";
        $message .= "💰 {$product->price} ₽ × {$cartItem->quantity} = {$total} ₽\n\n";

        $buttons = [
            ['callback:show_cart', '🛒 Перейти в корзину'],
            ["callback:product_{$productId}", '↩️ К товару'],
            ['callback:show_categories', '📱 Продолжить покупки'],
        ];

        $this->sendSelfInline($message, $buttons);

        // Логируем добавление в корзину
        $this->logActivity('cart_add', [
            'product_id' => $productId,
            'quantity' => $quantity,
            'user_id' => $user->id,
        ]);
    }

    private function showCart(): void
    {
        $user = $this->getUser();
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            $message = "🛒 **Ваша корзина пуста**\n\n";
            $message .= "Добавьте товары из каталога!";

            $buttons = [
                ['callback:show_categories', '📱 Каталог товаров'],
                ['callback:show_main_menu', '🏠 Главное меню'],
            ];

            $this->sendSelfInline($message, $buttons);
            return;
        }

        $message = "🛒 **Ваша корзина:**\n\n";
        $total = 0;

        foreach ($cartItems as $item) {
            $product = $item->product;
            $itemTotal = $product->price * $item->quantity;
            $total += $itemTotal;

            $message .= "📦 {$product->name}\n";
            $message .= "💰 {$product->price} ₽ × {$item->quantity} = {$itemTotal} ₽\n\n";
        }

        $message .= "💳 **Итого: {$total} ₽**";

        $buttons = [
            ['callback:checkout', '✅ Оформить заказ'],
            ['callback:clear_cart', '🗑️ Очистить корзину'],
            ['callback:show_categories', '📱 Продолжить покупки'],
        ];

        $this->sendSelfInline($message, $buttons);
    }

    private function checkout(): void
    {
        $user = $this->getUser();
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            $this->sendSelf('❌ Корзина пуста');
            return;
        }

        // Проверяем наличие товаров
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                $this->sendSelf("❌ Товар '{$item->product->name}' недоступен в нужном количестве");
                return;
            }
        }

        // Создаем заказ
        $total = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        $order = Order::create([
            'user_id' => $user->id,
            'total' => $total,
            'status' => 'pending',
        ]);

        // Переносим товары из корзины в заказ
        foreach ($cartItems as $cartItem) {
            $order->items()->create([
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->product->price,
            ]);

            // Уменьшаем количество на складе
            $cartItem->product->decrement('stock', $cartItem->quantity);
        }

        // Очищаем корзину
        $user->cartItems()->delete();

        $message = "✅ **Заказ #{$order->id} успешно создан!**\n\n";
        $message .= "💰 Сумма: {$total} ₽\n";
        $message .= "📞 Мы свяжемся с вами для подтверждения\n\n";
        $message .= "Спасибо за покупку! 🎉";

        $buttons = [
            ["callback:order_{$order->id}", '📦 Детали заказа'],
            ['callback:show_main_menu', '🏠 Главное меню'],
        ];

        $this->sendSelfInline($message, $buttons);

        // Уведомляем админов о новом заказе
        $this->notifyAdminsNewOrder($order);
    }

    private function formatProduct(Product $product): string
    {
        $message = "📦 **{$product->name}**\n";
        $message .= "💰 {$product->price} ₽\n";
        
        if ($product->description) {
            $description = Str::limit($product->description, 100);
            $message .= "📝 {$description}\n";
        }
        
        if ($product->stock < 5) {
            $message .= "⚠️ Осталось: {$product->stock} шт.\n";
        }

        return $message;
    }

    private function handleCallbacks(): void
    {
        $callback = $this->getCallbackData();
        if (!$callback) return;

        $parts = explode('_', $callback);
        $action = $parts[0];

        switch ($action) {
            case 'category':
                $categoryId = (int)$parts[1];
                $page = isset($parts[3]) ? (int)$parts[3] : 1;
                $this->showCategoryProducts($categoryId, $page);
                break;

            case 'product':
                $productId = (int)$parts[1];
                $this->showProduct($productId);
                break;

            case 'add':
                if ($parts[1] === 'to' && $parts[2] === 'cart') {
                    $productId = (int)$parts[3];
                    $this->addToCart($productId);
                }
                break;

            case 'show':
                switch ($parts[1]) {
                    case 'categories':
                        $this->showCategories();
                        break;
                    case 'cart':
                        $this->showCart();
                        break;
                    case 'main':
                        $this->showMainMenu();
                        break;
                }
                break;

            case 'checkout':
                $this->checkout();
                break;
        }
    }
}
```

## 📰 Новостной бот

### Бот для новостных рассылок

```php
<?php

namespace App\Bots;

use Teg\Modules\UserModule;
use Teg\Modules\StateModule;
use App\Models\NewsCategory;
use App\Models\Article;
use App\Models\Subscription;

class NewsBot extends AdstractBot
{
    use StateModule, UserModule;

    public function main(): void
    {
        $this->globalMiddleware([
            'spam_protection',
            'activity_logging',
        ]);

        $this->registerCommands();

        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText);
        }
    }

    private function registerCommands(): void
    {
        $this->registerCommand('start', function () {
            $this->showWelcome();
        }, [
            'description' => 'Начать работу с ботом',
        ]);

        $this->registerCommand('news', function ($args) {
            $this->showNews($args);
        }, [
            'description' => 'Последние новости',
            'args' => ['category?'],
        ]);

        $this->registerCommand('subscribe', function ($args) {
            $this->manageSubscriptions($args);
        }, [
            'description' => 'Управление подписками',
            'args' => ['action?'],
        ]);

        $this->registerCommand('categories', function () {
            $this->showCategories();
        }, [
            'description' => 'Категории новостей',
        ]);

        // Админские команды
        $this->registerCommand('publish', function ($args) {
            $this->publishNews($args);
        }, [
            'description' => 'Опубликовать новость',
            'admin_only' => true,
            'args' => ['category', 'title', 'content...'],
        ]);

        $this->registerCommand('broadcast', function ($args) {
            $this->broadcastMessage($args);
        }, [
            'description' => 'Рассылка сообщения',
            'admin_only' => true,
            'args' => ['message...'],
        ]);
    }

    private function showWelcome(): void
    {
        $user = $this->getUser();
        
        $message = "📰 **Добро пожаловать в новостной бот!**\n\n";
        $message .= "🔔 Получайте актуальные новости прямо в Telegram\n";
        $message .= "⚡ Мгновенные уведомления о важных событиях\n";
        $message .= "🎯 Настраиваемые категории подписок\n\n";

        if ($user->subscriptions()->count() > 0) {
            $message .= "✅ У вас есть активные подписки\n";
        } else {
            $message .= "💡 Подпишитесь на интересующие категории\n";
        }

        $buttons = [
            ['callback:latest_news', '📰 Последние новости'],
            ['callback:categories', '📂 Категории'],
            ['callback:my_subscriptions', '🔔 Мои подписки'],
            ['callback:settings', '⚙️ Настройки'],
        ];

        $this->sendSelfInline($message, $buttons);
    }

    private function showNews(array $args = []): void
    {
        $categorySlug = $args[0] ?? null;
        $category = null;

        if ($categorySlug) {
            $category = NewsCategory::where('slug', $categorySlug)->first();
            if (!$category) {
                $this->sendSelf('❌ Категория не найдена');
                return;
            }
        }

        $query = Article::where('published', true)
            ->orderBy('created_at', 'desc')
            ->limit(5);

        if ($category) {
            $query->where('category_id', $category->id);
        }

        $articles = $query->get();

        if ($articles->isEmpty()) {
            $message = $category 
                ? "📰 В категории \"{$category->name}\" пока нет новостей"
                : "📰 Новостей пока нет";
            
            $this->sendSelf($message);
            return;
        }

        $message = $category 
            ? "📰 **Новости: {$category->name}**\n\n"
            : "📰 **Последние новости**\n\n";

        foreach ($articles as $article) {
            $message .= $this->formatArticle($article) . "\n\n";
        }

        $buttons = [];
        if ($category) {
            $buttons[] = ['callback:categories', '📂 Все категории'];
        }
        $buttons[] = ['callback:start', '🏠 Главное меню'];

        $this->sendSelfInline($message, $buttons);
    }

    private function showCategories(): void
    {
        $categories = NewsCategory::where('active', true)->get();

        $message = "📂 **Категории новостей:**\n\n";
        $buttons = [];

        foreach ($categories as $category) {
            $articlesCount = $category->articles()->where('published', true)->count();
            
            $message .= "{$category->emoji} **{$category->name}**\n";
            $message .= "📄 Статей: {$articlesCount}\n";
            $message .= "{$category->description}\n\n";

            $buttons[] = [
                "callback:category_news_{$category->id}",
                "{$category->emoji} {$category->name}"
            ];
        }

        $buttons[] = ['callback:start', '🏠 Главное меню'];

        $this->sendSelfInline($message, $buttons);
    }

    private function manageSubscriptions(array $args = []): void
    {
        $action = $args[0] ?? 'show';
        $user = $this->getUser();

        switch ($action) {
            case 'show':
                $this->showSubscriptions();
                break;

            case 'add':
                $this->showSubscriptionOptions();
                break;

            case 'remove':
                $this->showUnsubscribeOptions();
                break;

            default:
                $this->sendSelf('❌ Неизвестное действие. Используйте: show, add, remove');
        }
    }

    private function showSubscriptions(): void
    {
        $user = $this->getUser();
        $subscriptions = $user->subscriptions()->with('category')->get();

        if ($subscriptions->isEmpty()) {
            $message = "🔔 **У вас нет активных подписок**\n\n";
            $message .= "Подпишитесь на интересующие категории для получения уведомлений";

            $buttons = [
                ['callback:add_subscription', '➕ Добавить подписку'],
                ['callback:categories', '📂 Просмотреть категории'],
                ['callback:start', '🏠 Главное меню'],
            ];

            $this->sendSelfInline($message, $buttons);
            return;
        }

        $message = "🔔 **Ваши подписки:**\n\n";

        foreach ($subscriptions as $subscription) {
            $category = $subscription->category;
            $message .= "{$category->emoji} {$category->name}\n";
            
            if ($subscription->instant_notifications) {
                $message .= "⚡ Мгновенные уведомления\n";
            } else {
                $message .= "📅 Ежедневная сводка\n";
            }
            $message .= "\n";
        }

        $buttons = [
            ['callback:add_subscription', '➕ Добавить'],
            ['callback:remove_subscription', '➖ Удалить'],
            ['callback:notification_settings', '⚙️ Настройки'],
            ['callback:start', '🏠 Главное меню'],
        ];

        $this->sendSelfInline($message, $buttons);
    }

    private function publishNews(array $args): void
    {
        if (count($args) < 3) {
            $this->sendSelf('❌ Использование: /publish <категория> <заголовок> <содержание>');
            return;
        }

        $categorySlug = $args[0];
        $title = $args[1];
        $content = implode(' ', array_slice($args, 2));

        $category = NewsCategory::where('slug', $categorySlug)->first();
        if (!$category) {
            $this->sendSelf('❌ Категория не найдена');
            return;
        }

        // Создаем статью
        $article = Article::create([
            'category_id' => $category->id,
            'title' => $title,
            'content' => $content,
            'author_id' => $this->getUserId,
            'published' => true,
            'published_at' => now(),
        ]);

        $this->sendSelf("✅ Новость опубликована! ID: {$article->id}");

        // Отправляем уведомления подписчикам
        $this->notifySubscribers($article);
    }

    private function notifySubscribers(Article $article): void
    {
        $category = $article->category;
        
        // Получаем подписчиков с мгновенными уведомлениями
        $subscribers = Subscription::where('category_id', $category->id)
            ->where('instant_notifications', true)
            ->with('user')
            ->get();

        $message = "🔔 **Новая новость в категории {$category->name}**\n\n";
        $message .= $this->formatArticle($article);

        $buttons = [
            ["callback:article_{$article->id}", '📖 Читать полностью'],
            ['callback:latest_news', '📰 Все новости'],
        ];

        foreach ($subscribers as $subscription) {
            try {
                $this->sendMessage(
                    $subscription->user->telegram_id,
                    $message,
                    $buttons
                );

                // Логируем отправку
                $this->logActivity('notification_sent', [
                    'user_id' => $subscription->user->id,
                    'article_id' => $article->id,
                    'category_id' => $category->id,
                ]);

            } catch (Exception $e) {
                $this->logError('Failed to send notification', $e, [
                    'user_id' => $subscription->user->id,
                    'article_id' => $article->id,
                ]);
            }
        }

        $this->sendSelf("📨 Уведомления отправлены {$subscribers->count()} подписчикам");
    }

    private function formatArticle(Article $article): string
    {
        $message = "📰 **{$article->title}**\n";
        $message .= "📂 {$article->category->name}\n";
        $message .= "🕐 " . $article->created_at->format('d.m.Y H:i') . "\n\n";
        
        $preview = Str::limit($article->content, 200);
        $message .= $preview;

        if (strlen($article->content) > 200) {
            $message .= "\n\n👆 Читать далее...";
        }

        return $message;
    }
}
```

## 🎫 Служба поддержки

### Система тикетов и обращений

```php
<?php

namespace App\Bots;

use Teg\Modules\UserModule;
use Teg\Modules\StateModule;
use App\Models\SupportTicket;
use App\Models\TicketMessage;

class SupportBot extends AdstractBot
{
    use StateModule, UserModule;

    public function main(): void
    {
        $this->globalMiddleware([
            'spam_protection',
            'activity_logging',
            'business_hours_check',
        ]);

        $this->registerCommands();

        // Обработка медиа в тикетах
        $this->mediaWithCaption(function ($mediaInfo, $caption) {
            $this->handleTicketMedia($mediaInfo, $caption);
        });

        if ($this->hasMessageText()) {
            if ($this->isMessageCommand()) {
                $this->handleCommand($this->getMessageText);
            } else {
                $this->handleTicketMessage();
            }
        }
    }

    private function registerCommands(): void
    {
        $this->registerCommand('start', function () {
            $this->showSupportMenu();
        }, [
            'description' => 'Главное меню поддержки',
        ]);

        $this->registerCommand('ticket', function ($args) {
            $this->manageTickets($args);
        }, [
            'description' => 'Управление тикетами',
            'args' => ['action?', 'id?'],
        ]);

        $this->registerCommand('faq', function () {
            $this->showFAQ();
        }, [
            'description' => 'Часто задаваемые вопросы',
        ]);

        $this->registerCommand('contact', function () {
            $this->showContacts();
        }, [
            'description' => 'Контактная информация',
        ]);

        // Команды для операторов
        $this->registerCommand('queue', function () {
            $this->showTicketQueue();
        }, [
            'description' => 'Очередь тикетов',
            'middleware' => ['check_operator'],
        ]);

        $this->registerCommand('take', function ($args) {
            $this->takeTicket($args);
        }, [
            'description' => 'Взять тикет в работу',
            'args' => ['ticket_id'],
            'middleware' => ['check_operator'],
        ]);

        $this->registerCommand('close', function ($args) {
            $this->closeTicket($args);
        }, [
            'description' => 'Закрыть тикет',
            'args' => ['ticket_id'],
            'middleware' => ['check_operator'],
        ]);
    }

    private function showSupportMenu(): void
    {
        $user = $this->getUser();
        $activeTickets = $user->tickets()->where('status', '!=', 'closed')->count();

        $message = "🎫 **Служба поддержки**\n\n";
        $message .= "Мы готовы помочь вам решить любые вопросы!\n\n";
        
        if ($activeTickets > 0) {
            $message .= "📋 У вас есть {$activeTickets} активных обращений\n\n";
        }

        $message .= "Выберите действие:";

        $buttons = [
            ['callback:create_ticket', '📝 Создать обращение'],
            ['callback:my_tickets', '📋 Мои обращения'],
            ['callback:faq', '❓ FAQ'],
            ['callback:contacts', '📞 Контакты'],
        ];

        if ($this->isOperator()) {
            $buttons[] = ['callback:operator_panel', '👨‍💼 Панель оператора'];
        }

        $this->sendSelfInline($message, $buttons);
    }

    private function createTicket(): void
    {
        $this->setState('creating_ticket');
        
        $message = "📝 **Создание нового обращения**\n\n";
        $message .= "Опишите вашу проблему или вопрос максимально подробно.\n";
        $message .= "Вы можете прикрепить файлы, скриншоты или видео.\n\n";
        $message .= "💡 *Чем подробнее описание, тем быстрее мы сможем помочь!*";

        $buttons = [
            ['callback:cancel_ticket_creation', '❌ Отмена'],
        ];

        $this->sendSelfInline($message, $buttons);
    }

    private function handleTicketMessage(): void
    {
        $state = $this->getState();
        
        if ($state === 'creating_ticket') {
            $this->processNewTicket();
        } elseif (str_starts_with($state, 'ticket_')) {
            $ticketId = (int)str_replace('ticket_', '', $state);
            $this->addMessageToTicket($ticketId);
        }
    }

    private function processNewTicket(): void
    {
        $message = $this->getMessageText();
        $user = $this->getUser();

        // Создаем тикет
        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => Str::limit($message, 100),
            'priority' => $this->detectPriority($message),
            'status' => 'open',
            'category' => $this->detectCategory($message),
        ]);

        // Добавляем первое сообщение
        $ticket->messages()->create([
            'user_id' => $user->id,
            'message' => $message,
            'type' => 'user',
        ]);

        $this->clearState();

        $response = "✅ **Обращение #{$ticket->id} создано!**\n\n";
        $response .= "📋 Тема: " . $ticket->subject . "\n";
        $response .= "⚡ Приоритет: " . $this->formatPriority($ticket->priority) . "\n";
        $response .= "🕐 Ожидаемое время ответа: " . $this->getExpectedResponseTime($ticket->priority) . "\n\n";
        $response .= "Мы рассмотрим ваше обращение в ближайшее время.";

        $buttons = [
            ["callback:ticket_{$ticket->id}", "📋 Обращение #{$ticket->id}"],
            ['callback:my_tickets', '📋 Мои обращения'],
            ['callback:start', '🏠 Главное меню'],
        ];

        $this->sendSelfInline($response, $buttons);

        // Уведомляем операторов
        $this->notifyOperators($ticket);
    }

    private function showTicket(int $ticketId): void
    {
        $user = $this->getUser();
        $ticket = SupportTicket::with(['messages.user', 'assignedOperator'])
            ->where('id', $ticketId);

        if (!$this->isOperator()) {
            $ticket->where('user_id', $user->id);
        }

        $ticket = $ticket->first();

        if (!$ticket) {
            $this->sendSelf('❌ Обращение не найдено');
            return;
        }

        $message = $this->formatTicket($ticket);

        $buttons = [];

        if ($ticket->status !== 'closed') {
            if ($ticket->user_id === $user->id) {
                $buttons[] = ["callback:add_message_{$ticketId}", '💬 Добавить сообщение'];
                $buttons[] = ["callback:close_ticket_{$ticketId}", '✅ Закрыть обращение'];
            } elseif ($this->isOperator()) {
                if (!$ticket->assigned_to) {
                    $buttons[] = ["callback:take_ticket_{$ticketId}", '👨‍💼 Взять в работу'];
                }
                $buttons[] = ["callback:reply_ticket_{$ticketId}", '💬 Ответить'];
                $buttons[] = ["callback:escalate_ticket_{$ticketId}", '⬆️ Эскалация'];
            }
        }

        $buttons[] = ['callback:my_tickets', '📋 Обращения'];
        $buttons[] = ['callback:start', '🏠 Главное меню'];

        $this->sendSelfInline($message, $buttons);
    }

    private function formatTicket(SupportTicket $ticket): string
    {
        $message = "🎫 **Обращение #{$ticket->id}**\n\n";
        $message .= "👤 Пользователь: {$ticket->user->name}\n";
        $message .= "📋 Тема: {$ticket->subject}\n";
        $message .= "📊 Статус: " . $this->formatStatus($ticket->status) . "\n";
        $message .= "⚡ Приоритет: " . $this->formatPriority($ticket->priority) . "\n";
        $message .= "🗂️ Категория: {$ticket->category}\n";
        $message .= "🕐 Создано: " . $ticket->created_at->format('d.m.Y H:i') . "\n";

        if ($ticket->assigned_to) {
            $message .= "👨‍💼 Оператор: {$ticket->assignedOperator->name}\n";
        }

        $message .= "\n📝 **Сообщения:**\n\n";

        foreach ($ticket->messages()->orderBy('created_at')->get() as $msg) {
            $icon = $msg->type === 'user' ? '👤' : '👨‍💼';
            $time = $msg->created_at->format('d.m H:i');
            
            $message .= "{$icon} {$msg->user->name} ({$time}):\n";
            $message .= $msg->message . "\n\n";
        }

        return $message;
    }

    private function isOperator(): bool
    {
        $user = $this->getUser();
        return $user->hasRole('support_operator') || $user->hasRole('admin');
    }

    private function detectPriority(string $message): string
    {
        $highPriorityWords = ['срочно', 'критично', 'не работает', 'ошибка', 'сломано'];
        $lowPriorityWords = ['вопрос', 'как', 'подскажите', 'можно ли'];

        $messageLower = mb_strtolower($message);

        foreach ($highPriorityWords as $word) {
            if (str_contains($messageLower, $word)) {
                return 'high';
            }
        }

        foreach ($lowPriorityWords as $word) {
            if (str_contains($messageLower, $word)) {
                return 'low';
            }
        }

        return 'medium';
    }

    private function getExpectedResponseTime(string $priority): string
    {
        return match ($priority) {
            'high' => '30 минут',
            'medium' => '2 часа',
            'low' => '24 часа',
            default => '2 часа',
        };
    }
}
```

---

🚀 **Примеры TegBot** - готовые решения для любых задач! 