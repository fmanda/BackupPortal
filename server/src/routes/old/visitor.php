<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require '../vendor/autoload.php';
require_once '../src/classes/DB.php';
require_once '../src/models/ModelVisitor.php';

// $app->get('/visitor_delete/{id}', function ($request, $response) {  //if hosting not allowed del
//   try{
//     $data = ModelVisitor::deleteFromDB($request->getAttribute('id'));
// 		return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
// 	}catch(Exception $e){
//     $msg = $e->getMessage();
//     $response->getBody()->write($msg);
// 		return $response->withStatus(500)
// 			->withHeader('Content-Type', 'text/html');
// 	}
// });

$app->get('/visitor', function ($request, $response) {
  try{
    $data = ModelVisitor::retrieveList();
    $json = json_encode($data);
    $response->getBody()->write($json);

		return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
	}catch(Exception $e){
    $msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});

$app->get('/visitorbyname/{filter}', function ($request, $response) {
  try{
    $filter = $request->getAttribute('filter');
    $filter = " lower(visitorname) like '%".   strtolower($filter)  . "%'";
    $filter = $filter . " limit 300";
    $data = ModelVisitor::retrieveList($filter);
    $json = json_encode($data);
    $response->getBody()->write($json);

		return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
	}catch(Exception $e){
    $msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});

$app->get('/visitor/{id}', function ($request, $response, $args) {
	try{
    $id = $request->getAttribute('id');
    $data = ModelVisitor::retrieve($id);
    $json = json_encode($data);
    $response->getBody()->write($json);
		return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
	}catch(Exception $e){
    $msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});


$app->post('/visitor', function ($request, $response) {
	$json = $request->getBody();
	$obj = json_decode($json);
	try{
		ModelVisitor::saveToDB($obj);
    $json = json_encode($obj);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
	}catch(Exception $e){
		$msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}

});
