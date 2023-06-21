<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;

use Tuupola\Middleware\CorsMiddleware;


require __DIR__ . '/../vendor/autoload.php';
$config = parse_ini_file("../src/config.ini");

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$app->setBasePath('');
// $app->setBasePath('/api');

// build https (release)
// $app->add(new Tuupola\Middleware\CorsMiddleware([
//     "origin" => ["*"],
//     "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE"],
//     "headers.allow" => ["Authorization", "If-Match", "If-Unmodified-Since"],
//     "headers.expose" => [],
//     "credentials" => true,
//     "cache" => 0,
// ]));


//
// $app->add(new Tuupola\Middleware\JwtAuthentication([
//     "regexp" => "/(.*)/", //default format Bearer <token>
//     "secret" => $config["secret"],
//     "algorithm" => ["HS256"],
//     "rules" => [
//         new Tuupola\Middleware\JwtAuthentication\RequestPathRule([
//             "ignore" => [
//               $app->getBasePath() . "/check",
//               $app->getBasePath() . "/login"
//             ]
//         ]),
//         new Tuupola\Middleware\JwtAuthentication\RequestMethodRule([
//             "ignore" => ["OPTIONS"]
//         ])
//     ]
// ]));


$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

// just for dev (http)
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH')
            ->withHeader("Access-Control-Allow-Credentials", "true");
            // ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});


$app->get('/check', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Server Ready !");
    return $response;
});

require '../src/routes/database.php';

// $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
//     $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
//     return $handler($req, $res);
// });

// $app->options('/{routes:.+}', function ($request, $response, $args) {
//     return $response;
// });

$app->run();


//notes upload img error : if dev cors activated and using ip 12.xx ??
//upload img success when using corsmiddleware and ip 0.xx