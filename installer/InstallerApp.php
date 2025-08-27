<?php

class InstallerContainer extends \Illuminate\Container\Container {
    public function storagePath($path = '') {
        $base = realpath(__DIR__ . '/../storage');
        return $base . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
    public function basePath($path = '') {
        $base = realpath(__DIR__ . '/..');
        return $base . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
    public function runningUnitTests()
    {
        return false;
    }
    public function getCachedConfigPath()
    {
        return $this->basePath('bootstrap/cache/config.php');
    }
    public function environment()
    {
        return 'production';
    }
    public function isBooted()
    {
        return true;
    }
    public function bootstrapWith(array $bootstrappers)
    {
        return $this;
    }
}

$container = InstallerContainer::getInstance();
\Illuminate\Support\Facades\Facade::setFacadeApplication($container);

if (!function_exists('storage_path')) {
    function storage_path($path = '') {
        global $container;
        return $container->storagePath($path);
    }
}
if (!function_exists('base_path')) {
    function base_path($path = '') {
        global $container;
        return $container->basePath($path);
    }
}

if (!function_exists('get_all_timezones_with_offset')) {
    function get_all_timezones_with_offset() {
        $timezones = [];
        $now = new DateTime('now', new DateTimeZone('UTC'));

        foreach (DateTimeZone::listIdentifiers() as $tz) {
            $zone = new DateTimeZone($tz);
            $now->setTimezone($zone);
            $offset = $now->getOffset() / 3600;
            $sign = $offset < 0 ? '-' : '+';
            $offset_abs = abs($offset);
            $offset_hours = floor($offset_abs);
            $offset_mins = ($offset_abs - $offset_hours) * 60;
            $label = sprintf(
                '%s (GMT%s%02d:%02d)',
                $tz,
                $sign,
                $offset_hours,
                $offset_mins
            );
            $timezones[$tz] = $label;
        }

        return $timezones;
    }
}

if (!function_exists('updateEnvVars')) {
    function updateEnvVars($file, $newVars) {
        if (!file_exists($file)) {
            $lines = [];
        } else {
            $lines = file($file, FILE_IGNORE_NEW_LINES);
        }
        $keys = array_keys($newVars);
        $replaced = [];

        foreach ($lines as &$line) {
            if (preg_match('/^\s*#/', $line) || trim($line) === '') continue;

            if (preg_match('/^([A-Z0-9_]+)\s*=\s*(.*)$/i', $line, $m)) {
                $key = $m[1];
                if (in_array($key, $keys)) {
                    $val = $newVars[$key];
                    if (preg_match('/\s/', $val) && $val[0] !== '"' && $val[0] !== "'") {
                        $val = '"' . addslashes($val) . '"';
                    }
                    $line = $key . '=' . $val;
                    $replaced[$key] = true;
                }
            }
        }

        foreach ($newVars as $key => $val) {
            if (!isset($replaced[$key])) {
                if (preg_match('/\s/', $val) && $val[0] !== '"' && $val[0] !== "'") {
                    $val = '"' . addslashes($val) . '"';
                }
                $lines[] = $key . '=' . $val;
            }
        }

        file_put_contents($file, implode("\n", $lines) . "\n");
    }
}

if (!function_exists('createAdminUser')) {
    function createAdminUser($pdo, $data) {
        $now = time();
        $id_secure = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10);
        $secret_key = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 15);
        $avatar = "https://ui-avatars.com/api/?name=".urlencode($data['fullname'])."&background=675dff&color=fff&font-size=0.5&rounded=false&format=png";
        $adminPassword = password_hash($data['admin_password'], PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("
            INSERT INTO users (
                id_secure, role, fullname, username, email, password, avatar, plan_id, expiration_date, timezone, language, data, secret_key, last_login, status, remember_token, changed, created
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");

        $stmt->execute([
            $id_secure,
            2,
            $data['fullname'],
            $data['admin_username'],
            $data['admin_email'],
            $adminPassword,
            $avatar,
            1,
            -1,
            $data['timezone'],
            NULL,
            NULL,
            $secret_key,
            time(),
            2,
            NULL,
            $now,
            $now
        ]);

        return ['success' => true];
    }
}

if (!function_exists('insertPurchaseAddon')) {
    function insertPurchaseAddon($pdo, $data) {
        $now = time();
        $id_secure = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 12);
        $stmt = $pdo->prepare("
            INSERT INTO addons (
                id_secure, source, product_id, module_name, purchase_code, is_main, version, install_path, relative_path, status, changed, created
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");

        $stmt->execute([
            $id_secure,
            1,
            $data['product_id'],
            null,
            $data['purchase_code'],
            1,
            $data['version'],
            $data['install_path'] ?? '',
            '',
            1,
            $now,
            $now
        ]);
    }
}

use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Http\Request;
use Illuminate\View\FileViewFinder;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Contracts\CallableDispatcher as CallableDispatcherContract;
use Illuminate\Routing\CallableDispatcher as DefaultCallableDispatcher;
use Illuminate\Session\SessionServiceProvider;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Facade;
use Illuminate\Config\Repository;
use Illuminate\Contracts\View\Factory as ViewFactoryContract;
use Illuminate\Routing\Redirector;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Console\Application as ArtisanConsole;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Builder;

class InstallerApp
{
    protected $router;
    protected $view;

    public function __construct()
    {
        global $container;

        $container->singleton('files', fn () => new Filesystem);

        $sessionConfig = [
            'driver' => 'file',
            'files' => storage_path('framework/sessions'),
            'cookie' => 'installer_session',
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'http_only' => true,
            'same_site' => null,
        ];
        $config = new Repository(['session' => $sessionConfig]);
        $container->instance('config', $config);

        (new SessionServiceProvider($container))->register();
        $sessionManager = $container->make('session');
        $container->instance('session', $sessionManager->driver());

        $events = new Dispatcher($container);
        $container->instance('events', $events);

        $filesystem = new Filesystem;
        $compiler = new BladeCompiler($filesystem, __DIR__.'/cache');
        $resolver = new EngineResolver;
        $resolver->register('blade', fn () => new CompilerEngine($compiler));
        $finder = new FileViewFinder($filesystem, [__DIR__.'/views']);
        $this->view = new Factory($resolver, $finder, $events);

        $container->instance('view', $this->view);
        $container->instance(ViewFactoryContract::class, $this->view);

        $container->singleton(CallableDispatcherContract::class, fn($c) => new DefaultCallableDispatcher($c));
        $this->router = new Router($events, $container);
        $this->router->middleware([StartSession::class]);

        $request = Request::capture();
        $urlGenerator = new UrlGenerator($this->router->getRoutes(), $request);
        $container->instance('url', $urlGenerator);
        $container->instance(\Illuminate\Contracts\Routing\UrlGenerator::class, $urlGenerator);

        $container->singleton('redirect', fn ($c) => new Redirector($c['url']));

        $container->singleton('db.factory', function ($app) {
            return new \Illuminate\Database\Connectors\ConnectionFactory($app);
        });

        $container->singleton('db', function ($app) {
            return new DatabaseManager($app, $app['db.factory']);
        });

        $container->singleton(ConnectionResolverInterface::class, function ($app) {
            return $app['db'];
        });

        $container->singleton('db.schema', function ($app) {
            return $app['db']->connection()->getSchemaBuilder();
        });
        $container->singleton(Builder::class, function ($app) {
            return $app['db']->connection()->getSchemaBuilder();
        });

        $container->singleton('migrator', function ($app) {
            $repository = new \Illuminate\Database\Migrations\DatabaseMigrationRepository($app['db'], 'migrations');
            return new Migrator($repository, $app['db'], $app['files'], $app['events']);
        });

        $container->singleton(ConsoleKernelContract::class, function ($app) use ($container) {
            $artisan = new ArtisanConsole($container, $container['events'], '1.0');

            $configClearCommand = new \Illuminate\Foundation\Console\ConfigClearCommand(
                $container->make('files'),
                $container->make('config')
            );
            $configClearCommand->setName('config:clear');
            $artisan->add($configClearCommand);

            $migrateCommand = new \Illuminate\Database\Console\Migrations\MigrateCommand(
                $container->make('migrator'),
                $container->make('events')
            );
            $migrateCommand->setName('migrate');
            $artisan->add($migrateCommand);

            return $artisan;
        });

        class_alias('Illuminate\Support\Facades\Artisan', 'Artisan');

        require __DIR__.'/routes.php';
    }

    public function run()
    {
        $request = Request::capture();
        $response = $this->router->dispatch($request);
        $response->send();
    }
}
