<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Teg\LightBot;

class TegDelToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teg:deltoken {token?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete token Telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = new LightBot();
        $client->token = $this->argument('token') ?? $this->ask('Токен системы');

        if ($client !== null) {
            $array = $client->removeWebhook();
            $description = $array['description'];
            $this->info(PHP_EOL . $description . PHP_EOL);
          } else {
            $this->fail(PHP_EOL . 'Error: There is no such bot!' . PHP_EOL);
          }
    }
}
