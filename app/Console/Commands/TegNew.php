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
    protected $signature = 'teg:new {name} {token} {hostname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating bot telegrams';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $token = $this->argument('token');
        $hostname = $this->argument('hostname');

        $configPath = config_path('tegbot.php');
        $config = include $configPath;
        $config = is_array($config) ? $config : [];
        $config[$name] = $token;

        file_put_contents(
            $configPath,
            '<?php return ' . var_export($config, true) . ';'
        );

        Artisan::call('config:cache');

        $client = new LightBot();
        $client->bot = $name;
        $client->hostname = $hostname ? $hostname : $client->hostname;

        if ($client !== null) {
            $array = $client->setWebhook();
            $description = $array['description'];

            if ($description === "Webhook is already set") {
                $this->fail(PHP_EOL . $description . PHP_EOL);
                return;
            }

            $this->info(PHP_EOL . $description . PHP_EOL);
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
