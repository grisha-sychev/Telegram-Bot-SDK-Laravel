<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Teg\LightBot;

class TegNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teg:new {name?} {token?} {hostname?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating bot Telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name') ?? $this->ask('Как вы назовете телеграм бота?');
        $token = $this->argument('token') ?? $this->ask('Токен телеграм бота');
        $hostname = $this->argument('hostname') ?? $this->ask('Наименование хоста (exemple.com)');

        $configPath = config_path('tegbot.php');
        $config = include $configPath;
        $config = is_array($config) ? $config : [];
        $config[$name] = $token;

        $client = new LightBot();
        $client->bot = $name;
        $client->hostname = $hostname;

        file_put_contents(
            $configPath,
            '<?php return ' . var_export($config, true) . ';'
        );

        Artisan::call('config:cache');

        if ($client !== null) {
            $array = $client->setWebhook();
            $description = $array['description'];

            if($description === "Webhook was set") {
                $this->info(PHP_EOL . $description . PHP_EOL);
            } else {
                $this->fail(PHP_EOL . $description . PHP_EOL);
                return;
            }
        }

        $botNameCapitalized = ucfirst($name) . "Bot";
        $botDirectory = app_path("Bots");

        $startFilePath = "{$botDirectory}/{$botNameCapitalized}.php";

        if (!file_exists($startFilePath)) {
            $startFileContent = <<<PHP
          <?php
          
          namespace App\Bots;
          
          class {$botNameCapitalized} extends AdstractBot
          {
              public function __construct()
              {
                  parent::__construct();
              }
          
              public function main()
              {
                  \$this->command("start", function () {
                      \$this->start();
                  });
              }
          
              private function start()
              {
                  \$this->sendSelf('Hello Word');
              }
          }
          PHP;

            file_put_contents($startFilePath, $startFileContent);
        }
    }
}
