<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Teg\LightBot;

class TegDel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teg:del {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deleting bot telegrams';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        $configPath = config_path('tegbot.php');
        $config = include $configPath;
      
        if (isset($config[$name])) {
          unset($config[$name]);
          file_put_contents($configPath, '<?php return ' . var_export($config, true) . ';');
      
          $client = new LightBot();
          $client->bot = $name;
      
          if ($client !== null) {
            $array = $client->removeWebhook();
            $description = $array['description'];
            $this->info(PHP_EOL . $description . PHP_EOL);
          } else {
            $this->fail(PHP_EOL . 'Error: There is no such bot!' . PHP_EOL);
          }
        } else {
          $this->fail(PHP_EOL . 'Error: Bot name not found in config!' . PHP_EOL);
        }
    }
}
