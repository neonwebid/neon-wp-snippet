<?php
declare(strict_types=1);

/**
 * Rest Api Helper
 *
 */

namespace NeonWebId\WP\Core\Base;

class RestApi
{

    private static string $namespace = 'neon/v1';

    private static array $routes = [];

    public static function namespace(string $namespace = '', ?callable $callback = null)
    {
        $oldNamespace    = self::$namespace;
        self::$namespace = $namespace;
        if (is_callable($callback)) {
            call_user_func($callback);
        }
        self::$namespace = $oldNamespace;

    }

    public static function get(string $route, callable $callback, bool $override = false)
    {
        self::$routes[] = [
            'route_namespace' => self::$namespace,
            'route'           => $route,
            'method'          => 'GET',
            'callback'        => $callback,
            'override'        => $override
        ];
    }

    public static function post(string $route, callable $callback, bool $override = false)
    {
        self::$routes[] = [
            'route_namespace' => self::$namespace,
            'route'           => $route,
            'method'          => 'POST',
            'callback'        => $callback,
            'override'        => $override
        ];
    }

    public static function register()
    {
        foreach (self::$routes as $route) {
            register_rest_route($route['route_namespace'], $route['route'], [
                'methods'  => $route['method'],
                'callback' => $route['callback'],
            ], $route['override']);
        }
    }

    public static function init()
    {
        add_action('rest_api_init', [__CLASS__, 'register']);
    }
}
