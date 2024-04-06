<?php

namespace Controllers;

class RequestController
{
    public $useHttps;
    public $hostname;
    public $method;
    public $uri;
    public $get;
    public $post;
    public $body;
    public $headers;

    public function __construct()
    {
        $this->useHttps = $_SERVER['HTTPS'] ?? false;
        $this->hostname = $_SERVER['HTTP_HOST'];

        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->get = $_GET;
        $this->post = $_POST;
        $this->body = file_get_contents('php://input');
        $this->headers = getallheaders();

        if ($this->isJson($this->body)) $this->body = json_decode($this->body, true);

        $this->log();
    }

    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function log()
    {
        $log = "";
        $log .= "hostname: " . $this->hostname . " (" . ($this->useHttps ? "https" : "http") . ")\n";
        $log .= "Method: " . $this->method . "\n";
        $log .= "URI: " . $this->uri . "\n";
        $log .= "Body: " . print_r($this->body, true) . "\n";
        $log .= "Headers: " . print_r($this->headers, true) . "\n";
        // file_put_contents("log.txt", $log, FILE_APPEND);

        return $log;
    }

    public function getParams($key = null)
    {
        if ($key === null) return array_merge($this->get, $this->post);

        if (isset($this->get[$key])) return $this->get[$key];
        if (isset($this->post[$key])) return $this->post[$key];

        return null;
    }

    public function getBody($key = null)
    {
        if ($key === null) return $this->body;

        if (is_array($this->body) && isset($this->body[$key])) return $this->body[$key];

        return null;
    }
}
