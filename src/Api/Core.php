<?php

namespace Teg\Api;

use Teg\Support\Facades\Services;
use Illuminate\Support\Facades\Http;
use Teg\Types\DynamicData;

class Core
{

    /**
     * @var string|null $bot Идентификатор бота.
     */
    public ?string $bot = null;

    /**
     * @var string|null $token Токен бота.
     */
    public ?string $token;

    /**
     * @var string|null $hostname host, связанный с ботом.
     */
    public ?string $hostname = null;

    /**
     * Отправляет все данные запроса от Telegram и возвращает их в виде массива.
     *
     * Данные запроса от Telegram в виде обьекта.
     */
    public function method($method, $query = [])
    {
        $maxRetries = 3;
        $baseDelay = 1;
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $url = "https://api.telegram.org/bot" . (new Services)->getToken($this->bot) . "/" . $method . ($query ? '?' . http_build_query($query) : '');
                
                $response = Http::withoutVerifying()
                    ->timeout(30)
                    ->retry(2, 100) // 2 попытки с задержкой 100мс
                    ->get($url);
                
                // Проверяем rate limit
                if ($response->status() === 429) {
                    $retryAfter = $response->header('Retry-After', $baseDelay * $attempt);
                    \Log::warning('Telegram API rate limit hit', [
                        'bot' => $this->bot,
                        'method' => $method,
                        'retry_after' => $retryAfter,
                        'attempt' => $attempt
                    ]);
                    
                    if ($attempt < $maxRetries) {
                        sleep($retryAfter);
                        continue;
                    }
                }
                
                $result = $response->json();
                
                // Логируем неуспешные ответы
                if (isset($result['ok']) && !$result['ok']) {
                    \Log::warning('Telegram API error', [
                        'bot' => $this->bot,
                        'method' => $method,
                        'error' => $result['description'] ?? 'Unknown error',
                        'error_code' => $result['error_code'] ?? 'Unknown'
                    ]);
                }
                
                return $result;
                
            } catch (\Exception $e) {
                \Log::error('Telegram API request failed', [
                    'bot' => $this->bot,
                    'method' => $method,
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempt === $maxRetries) {
                    // Возвращаем ошибку в том же формате что и Telegram API
                    return [
                        'ok' => false,
                        'error_code' => 500,
                        'description' => 'Request failed: ' . $e->getMessage()
                    ];
                }
                
                sleep($baseDelay * $attempt);
            }
        }
        
        return ['ok' => false, 'error_code' => 500, 'description' => 'Max retries exceeded'];
    }

    public function file($file_path)
    {
        $url = "https://api.telegram.org/file/bot" . (new Services)->getToken($this->bot) . "/" . $file_path;
        return $url;
    }

    /**
     * Получает все данные запроса от Telegram и возвращает их в виде массива.
     *
     * Данные запроса от Telegram в виде обьекта.
     */
    public function request()
    {
        return new DynamicData(request()->all());
    }
}
