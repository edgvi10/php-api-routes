<?php

namespace Controllers;

class ResponseController
{
    public $status;
    public $body;
    public $headers;

    public function __construct()
    {
        $this->status = 200;
        $this->body = "";
        $this->headers = [];
    }

    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function withJson($data, $status = 200)
    {
        $this->addHeader("Content-Type", "application/json");
        $this->body = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS);
        $this->status = $status;

        http_response_code($this->status);
        foreach ($this->headers as $key => $value) header($key . ": " . $value);

        echo $this->body;
        exit;
    }

    public function send()
    {
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header($key . ": " . $value);
        }

        echo $this->body;
        exit;
    }

    public function redirect($url, $status = 302)
    {
        http_response_code($status);
        header("Location: " . $url);
        exit;
    }

    public function withError($status, $message)
    {
        $this->status = $status;

        return $this->withJson(["error" => true, "message" => $message], $status);
    }
}
