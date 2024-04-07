<?php

use Controllers\RequestController as Request;
use Controllers\ResponseController as Response;
use Controllers\AppController as App;

require_once(__DIR__ . "/src/autoload.php");


$request = new Request();
$response = new Response();

$route_config = [];
$route_config["useJson"] = true;
$route_config["useCors"] = true;

if (strpos($request->hostname, "localhost") !== false) $route_config["baseUri"] = dirname($_SERVER["PHP_SELF"]);

$app = new App(new Request(), new Response(), $route_config ?? []);

function withToken($app)
{
    $token = $app->request->headers["Authorization"] ?? $app->request->getParams("token") ?? null;
    if ($token === null) $app->response->withError(401, "Token not found");
}

$app->get("/", function ($req, $res, $args) {
    $res->withJson(["message" => "Hello, World!"]);
});

$app->get("/authenticated", function ($req, $res, $args) {
    $token = $req->headers["Authorization"] ?? $req->getParams("token") ?? null;

    return $res->withJson(["message" => "Authenticated with {$token}"]);
}, ["withToken"]);

$app->run();
