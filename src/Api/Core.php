<?php

namespace Teg\Api;

use Teg\Support\Facades\Services;
use Illuminate\Support\Facades\Http;

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
        $url = "https://api.telegram.org/bot" . (new Services)->getToken($this->bot) . "/" . $method . ($query ? '?' . http_build_query($query) : '');
        return Http::withoutVerifying()->get($url)->json();
    }
    
    
    /**
     * Получает все данные запроса от Telegram и возвращает их в виде массива.
     *
     * Данные запроса от Telegram в виде обьекта.
     */
    public function request()
    {
        return (object) request()->json()->all();
    }

}
