<?php

namespace Zobirofkir\ServiceGenerator\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name}';

    protected $description = 'Create Service and Facade and Constructor Automatically';

    public function handle()
    {
        $name = $this->argument('name');

        $servicePath = app_path("Services/Services/{$name}Service.php");
        $constructorPath = app_path("Services/Constructors/{$name}Constructor.php");
        $facadePath = app_path("Services/Facades/{$name}Facade.php");
        $serviceProviderPath = app_path("App/Providers/{$name}ServiceProvider.php");

        (new Filesystem)->ensureDirectoryExists(app_path('Services/Services'));
        (new Filesystem)->ensureDirectoryExists(app_path('Services/Constructors'));
        (new Filesystem)->ensureDirectoryExists(app_path('Services/Facades'));
        (new Filesystem)->ensureDirectoryExists(app_path('App/Providers'));

        $this->createService($servicePath, $name);
        $this->createConstructor($constructorPath, $name);
        $this->createFacade($facadePath, $name);
        $this->createServiceProvider($name);

        $this->info("Created service, Constructor, and facade: {$name}Service, {$name}Constructor, and {$name}Facade, and service provider: {$name}ServiceProvider");
    }

    private function createService($path, $name)
    {
        $content = "<?php

namespace App\Services\Services;

use App\Services\Constructors\\{$name}Constructor;

class {$name}Service implements {$name}Constructor
{
    //
}";
        file_put_contents($path, $content);
    }

    private function createConstructor($path, $name)
    {
        $content = "<?php

namespace App\Services\Constructors;

interface {$name}Constructor
{
    //
}";
        file_put_contents($path, $content);
    }

    private function createFacade($path, $name)
    {
        $content = "<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class {$name}Facade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return '{$name}Service';
    }
}";
        file_put_contents($path, $content);
    }

    private function createServiceProvider($name)
    {
        exec("php artisan make:provider {$name}ServiceProvider");
    }
}
