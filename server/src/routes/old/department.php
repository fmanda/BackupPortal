<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require '../vendor/autoload.php';
require_once '../src/classes/DB.php';
require_once '../src/models/ModelDepartment.php';

// $app->get('/department_delete/{id}', function ($request, $response) {  //if hosting not allowed del
//   try{
//     $data = ModelDepartment::deleteFromDB($request->getAttribute('id'));
// 		return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
// 	}catch(Exception $e){
//     $msg = $e->getMessage();
//     $response->getBody()->write($msg);
// 		return $response->withStatus(500)
// 			->withHeader('Content-Type', 'text/html');
// 	}
// });

$app->get('/department', function ($request, $response) {
  try{
    $data = ModelDepartment::retrieveList();
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

$app->get('/department/{id}', function ($request, $response, $args) {
	try{
    $id = $request->getAttribute('id');
    $data = ModelDepartment::retrieve($id);
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

// $app->get('/employee/{deptname}', function ($request, $response, $args) {
//   try{
//     $deptname = $request->getAttribute('deptname');
//     // $employeename = $request->getAttribute('employeename');
//
//     $data = ModelDepartment::retrieveEmployee($deptname,'');
//     $json = json_encode($data);
//     $response->getBody()->write($json);
//   	return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
//   }catch(Exception $e){
//     $msg = $e->getMessage();
//     $response->getBody()->write($msg);
//   	return $response->withStatus(500)
//   		->withHeader('Content-Type', 'text/html');
//   }
// });

$app->get('/employee/{deptid}[/{employeename}]', function ($request, $response, $args) {
	try{
    $deptid = $request->getAttribute('deptid');
    $employeename = $request->getAttribute('employeename');

    $data = ModelDepartment::retrieveEmployee($deptid,$employeename);
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

$app->get('/allemployee[/{employeename}]', function ($request, $response, $args) {
	try{
    $employeename = $request->getAttribute('employeename');
    $data = ModelDepartment::retrieveAllEmployee($employeename);
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
//
// $app->post('/department', function ($request, $response) {
// 	$json = $request->getBody();
// 	$obj = json_decode($json);
// 	try{
// 		ModelDepartment::saveToDB($obj);
//     $json = json_encode($obj);
//     $response->getBody()->write($json);
//     return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
// 	}catch(Exception $e){
// 		$msg = $e->getMessage();
//     $response->getBody()->write($msg);
// 		return $response->withStatus(500)
// 			->withHeader('Content-Type', 'text/html');
// 	}
