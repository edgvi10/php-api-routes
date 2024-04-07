<?php

namespace Controllers;

class AppController
{
    public $uri;
    public $method;
    public $prefix = "";
    public $routes = [];
    public $middlewares = [];
    public $request;
    public $response;
    public $args = [];
    public $config;

    public function __construct($request, $response, $config = [])
    {
        $this->uri = $_SERVER["REQUEST_URI"];
        $this->request = $request;
        $this->response = $response;
        $this->config = $config;

        if (!isset($this->config["baseUri"])) $this->config["baseUri"] = "";
    }

    public function addMiddleware($middleware, $route = null)
    {
        // to use $app->addMiddleware($callback)->get(); or $app->addMiddleware($callback);
        if ($route === null) :
            $this->middlewares[] = $middleware;
            return $this;
        else :
            $this->middlewares[$route][] = $middleware;
            return $this;
        endif;
    }

    public function addRoute($method, $uri, $callback, $middleware = [])
    {
        $uri = rtrim($this->prefix . rtrim($uri, "/"), "/");
        $method = mb_strtoupper($method, "UTF-8");
        if (!in_array($method, ["GET", "POST", "PUT", "DELETE"])) throw new \Exception("Method not allowed", 405);

        if (!is_callable($callback) && !is_string($callback)) throw new \Exception("Callback must be a function or a controller", 500);

        $this->routes[] = [
            "method" => $method,
            "uri" => $uri,
            "callback" => $callback,
            "middlewares" => $middleware
        ];

        return $this;
    }

    public function group($prefix, $callback, $middleware = [])
    {
        $this->prefix = rtrim($this->prefix, "/") . "/" . ltrim($prefix, "/");
        if (is_callable($callback)) $callback($this);

        return $this;
    }

    public function all()
    {
        $params = func_get_args();
        $this->addRoute("GET", ...$params);
        $this->addRoute("POST", ...$params);
        $this->addRoute("PUT", ...$params);
        $this->addRoute("DELETE", ...$params);
    }

    public function map()
    {
        $params = func_get_args();
        $methods = array_shift($params);
        foreach ($methods as $method) :
            $this->addRoute($method, ...$params);
        endforeach;
    }

    public function get()
    {
        $params = func_get_args();
        $this->addRoute("GET", ...$params);
    }

    public function post()
    {
        $params = func_get_args();
        $this->addRoute("POST", ...$params);
    }

    public function put()
    {
        $params = func_get_args();
        $this->addRoute("PUT", ...$params);
    }

    public function delete()
    {
        $params = func_get_args();
        $this->addRoute("DELETE", ...$params);
    }

    public function run()
    {
        try {
            $uri = $this->uri;
            $method = mb_strtoupper($_SERVER["REQUEST_METHOD"], "UTF-8");

            if (isset($this->config["useCors"]) && $this->config["useCors"] === true) :
                if ($method === "OPTIONS") :
                    $this->response->addHeader("Access-Control-Allow-Origin", "*");
                    $this->response->addHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE");
                    $this->response->addHeader("Access-Control-Allow-Headers", "Content-Type");
                    $this->response->send();
                endif;
            endif;

            if (isset($this->config["baseUri"]))
                $uri = str_replace($this->config["baseUri"], "", $uri);

            $path = explode("?", $uri);
            $uri = rtrim($path[0], "/");
            $route = null;

            $args = [];
            foreach ($this->routes as $r) :
                $pattern = str_replace("/", "\/", $r["uri"]);
                $pattern = preg_replace("/{[a-zA-Z0-9_]+}/", "([a-zA-Z0-9-_]+)", $pattern);
                $pattern = "/^" . $pattern . "$/";

                if (preg_match($pattern, $uri, $matches) && $r["method"] === $method) :
                    $route = $r;
                    preg_match_all("/{[a-zA-Z0-9_]+}/", $r["uri"], $args_keys);
                    foreach ($args_keys[0] as $i => $key) :
                        $key = str_replace(["{", "}"], "", $key);
                        $args[$key] = $matches[$i + 1];
                    endforeach;
                endif;
            endforeach;

            $this->args = $args;

            if ($route === null) throw new \Exception("Route not found", 404);

            if (isset($this->config["useJson"]) && $this->config["useJson"] === true) $this->response->addHeader("Content-Type", "application/json");

            if (isset($this->config["useCors"]) && $this->config["useCors"] === true) :
                $this->response->addHeader("Access-Control-Allow-Origin", "*");
                $this->response->addHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE");
                $this->response->addHeader("Access-Control-Allow-Headers", "Content-Type");
            endif;

            foreach ($this->middlewares as $middleware) if (is_callable($middleware)) $middleware($this);

            if (isset($this->middlewares[$route["uri"]])) foreach ($this->middlewares[$route["uri"]] as $middleware) if (is_callable($middleware)) $middleware($this);
            if (isset($route["middlewares"])) foreach ($route["middlewares"] as $middleware) if (is_callable($middleware)) $middleware($this);

            $callback = $route["callback"];
            if (is_callable($callback)) :
                $callback($this->request, $this->response, $args);
            elseif (is_string($callback)) :
                $callback = explode("@", $callback);
                $controller = $callback[0];
                $controller_method = (isset($callback[1])) ? mb_strtolower($callback[1], "UTF-8") : $method;

                if (!class_exists($controller)) throw new \Exception("Controller not found", 404);
                if (!method_exists($controller, $controller_method)) :
                    if (!method_exists($controller, "handler")) throw new \Exception("Method not found", 404);
                    $controller_method = "handler";
                endif;

                $controller = new $controller();
                $controller->$controller_method($this->request, $this->response, $args);
            endif;
        } catch (\Exception $e) {
            $this->response->withError($e->getCode(), $e->getMessage());
        }
    }
}
