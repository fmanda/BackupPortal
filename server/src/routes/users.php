<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT;

require '../vendor/autoload.php';
require_once '../src/classes/DB.php';
require_once '../src/models/ModelUsers.php';

$app->get('/users', function ($request, $response) {
  try{
    $data = ModelUsers::retrieveList();
    // $str = 'select * from users a
    //         left join department b on a.department_id = b.id ';

    // $data = DB::openQuery($str);
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

$app->get('/users/{id}', function ($request, $response, $args) {
	try{
    $id = $request->getAttribute('id');
    $data = ModelUsers::retrieve($id);
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

$app->post('/users', function ($request, $response) {
	$json = $request->getBody();
	$obj = json_decode($json);
	try{
		ModelUsers::saveToDB($obj);
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

$app->get('/users_delete/{id}', function ($request, $response) {  //if hosting not allowed del
  try{
    $data = ModelUsers::deleteFromDB($request->getAttribute('id'));
		return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
	}catch(Exception $e){
    $msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});

$app->post('/login', function (Request $request, Response $response, array $args) {
  $config = parse_ini_file("../src/config.ini");

  $json = $request->getBody();
	$obj = json_decode($json);
  $user = ModelUsers::retrieveLogin($obj->username, $obj->password);
  if(!$user) {
    $response->getBody()->write('These credentials do not match our records username');
    return $response->withStatus(401)
			->withHeader('Content-Type', 'text/html');
  }
  $token = array(
      'id' =>  $user->id,
      'username' => $user->username
  );
  $token = JWT::encode($token,  $config["secret"], "HS256");

  $result = new stdClass();
  $result->token = $token;
  $result->user = $user;
  $result = json_encode($result);

  $response->getBody()->write($result);
  return $response->withHeader('Content-Type', 'multipart/form-data');
  // return $this->response->withJson(['status' => 'success','data'=>$user, 'token' => $token]);
});
