<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Teg\LightBot;

class TegSet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teg:set {name?} {token?} {hostname?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setting and creating your bot Telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name') ?? $this->ask('How are we going to call it? Please choose a name for your bot.');
        $token = $this->argument('token') ?? $this->ask('Your bot Telegram token.');
        $hostname = $this->argument('hostname') ?? $this->ask('Name of the host. (exemple.com)');

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
            $this->info(PHP_EOL . $array['description'] . PHP_EOL);
        }

        $botNameCapitalized = ucfirst($name) . "Bot";
        $botDirectory = app_path("Bots");

        $startFilePath = "{$botDirectory}/{$botNameCapitalized}.php";

        if (!file_exists($startFilePath)) {
            $startFileContent = <<<PHP
          <?php
          
          namespace App\Bots;

          use Teg\Modules\UserModule;
          use Teg\Modules\StateModule;
          
          class {$botNameCapitalized} extends AdstractBot
          {
              use StateModule, UserModule; // optional 

              public function main(): void
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
