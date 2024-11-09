<?php

use Illuminate\Support\Facades\Route;


Route::post('/bot/{token}', function ($token) {

    $bots = config('tegbot');

    $botName = array_search($token, $bots);

    if ($botName === false) {
        return response()->json(['error' => 'Bot not found'], 404);
    }

    $class = 'App\\Bots\\' . ucfirst($botName) . "Bot";

    if (!class_exists($class)) {
        return response()->json(['error' => 'Bot class not found'], 404);
    }

    return (new $class())->main();
});
