<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Script extends Facade
{
    protected static array $variables = [];
    protected static array $defines = [];
    protected static array $rawScripts = [];

    protected static function getFacadeAccessor()
    { 
        return 'Script';
    }

    protected static function addVariable(string $key, mixed $value): void
    {
        self::$variables[$key] = $value;
    }

    protected static function define(string $name, mixed $value): void
    {
        self::$defines[$name] = $value;
    }


    protected static function meta(): string
    {
        return '<meta name="csrf-token" content="' . csrf_token() . '">';
    }

    protected static function globals(): string
    {
        $globals = collect(self::$defines)->map(function ($value, $name) {
            $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            return "<script>var {$name} = {$json};</script>";
        })->implode("\n\r");

        if(get_option("embed_code_status", 0)){
            $globals .= "\r".get_option("embed_code", "");
        }
        
        return $globals;
    }

    protected static function fromView(string $view, array $data = []): void
    {
        self::raw(view($view, $data)->render());
    }

    protected static function raw(string $js): void
    {
        if (stripos($js, '<script') !== false) {
            $cleaned = trim(strip_tags($js));
            if ($cleaned !== '') {
                self::$rawScripts[] = $js;
            }
        } else {
            self::$rawScripts[] = "<script>\n{$js}\n</script>";
        }
    }


    protected static function renderRaw(): string
    {
        return implode("\n", self::$rawScripts);
    }

    protected static function renderCss(): string
    {
        return collect(Core::loadModuleAssets()['css'])->map(fn($css) => "<link rel=\"stylesheet\" href=\"{$css}\">")->implode("\n");
    }

    protected static function renderJs(): string
    {
        return collect(Core::loadModuleAssets()['js'])->map(fn($js) => "<script src=\"{$js}\"></script>")->implode("\n");
    }
}
