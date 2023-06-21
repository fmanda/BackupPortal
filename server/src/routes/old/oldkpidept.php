<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

require '../vendor/autoload.php';
require_once '../src/classes/DB.php';
require_once '../src/models/ModelKPIDept.php';
require_once '../src/models/ModelDepartment.php';
require_once '../src/models/ModelUploadLog.php';

// $container = new Container();
// $container->set('upload_directory', __DIR__ . '/uploads');

$app->get('/kpidept/{id}', function ($request, $response, $args) {
	try{
    $id = $request->getAttribute('id');
    $data = ModelKPIDept::retrieve($id);
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

$app->get('/kpidept/{dept_id}/{period}', function ($request, $response, $args) {
	try{
    $deptid = $request->getAttribute('dept_id');
    $period = $request->getAttribute('period');
    $data = ModelKPIDept::retrieveBy($deptid, $period);
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

$app->post('/kpidept', function ($request, $response) {
	$json = $request->getBody();
	$obj = json_decode($json);
	try{
		ModelKPIDept::saveToDB($obj);
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

$app->get('/genkpidept/{dept_id}/{period}', function ($request, $response, $args) {
	try{
    $deptid = $request->getAttribute('dept_id');
    $period = $request->getAttribute('period');
    $data = ModelKPIDept::generate($deptid, $period);
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

$app->post('/kpidept_upload_ml/{period}/{deptid}/{subcode}/{level}', function(Request $request, Response $response) {
	try{
		return executeUploadFile($request, $response, false);
	}catch(Exception $e){
		$msg = $e->getMessage();
		$response->getBody()->write($msg . ' directory ' . $directory);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});

$app->post('/kpidept_upload_kpi/{period}/{deptid}/{subcode}/{level}', function(Request $request, Response $response) {
	try{
		return executeUploadFile($request, $response, true);
	}catch(Exception $e){
		$msg = $e->getMessage();
		$response->getBody()->write($msg . ' directory ' . $directory);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});

function executeUploadFile($request, $response, $isKPI){
	$config = parse_ini_file("../src/config.ini");
	$directory =  $config["upload_directory"];

	$deptid = $request->getAttribute('deptid');
	$level = $request->getAttribute('level');
	$subcode = $request->getAttribute('subcode');
	$period = $request->getAttribute('period');

	$dept = ModelDepartment::retrieveHeader($deptid);
	$deptcode = $dept->deptcode;

	$directory = $directory . DIRECTORY_SEPARATOR . $period
		. DIRECTORY_SEPARATOR . $deptcode
		. DIRECTORY_SEPARATOR . $subcode;

	if (!file_exists($directory)) {
		mkdir($directory, 0777, true);
	}

	$uploadedFiles = $request->getUploadedFiles();
	foreach ($uploadedFiles as $uploadedFile) {
		if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
			$filename = moveUploadedFile($directory, $uploadedFile);
			$response->getBody()->write('uploaded ' . $directory . DIRECTORY_SEPARATOR . $filename . '; ');
			$obj = new stdClass();
			$obj->filename = $filename;
			$obj->directory = $directory;
			$obj->filepath = $directory . DIRECTORY_SEPARATOR . $filename;
			$obj->period = $period;
			if ($isKPI){
				$obj->ml_kpiarea = $subcode;
			}else{
				$obj->ml_subarea = $subcode;
			}
			$obj->level_id = $level;
			$obj->department_id = $dept->id;
			$obj->user_id = 1;
			ModelUploadLog::saveToDB($obj);
		}
	}

	return $response;
}



function moveUploadedFile($directory, UploadedFileInterface $uploadedFile)
{
	$filename = $uploadedFile->getClientFilename();
	$uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
	return $filename;
}


$app->get('/period', function ($request, $response, $args) {
	try{
    $ar = array();

		for ($i = 2020; $i <= 2030; $i++) {
			for ($j = 1; $j <= 2; $j++) {
				$obj = new stdClass();
				$obj->id = $i . '0' .$j;
				$obj->caption = $i . ' semester ' . $j;
				array_push($ar, $obj);
			}
		}

    $json = json_encode($ar);
    $response->getBody()->write($json);
		return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
	}catch(Exception $e){
    $msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});

$app->get('/kpideptprev/{dept_id}/{period}', function ($request, $response, $args) {
	try{
    $deptid = $request->getAttribute('dept_id');
    $period = $request->getAttribute('period');
    $data = ModelKPIDept::retrievePrev($deptid, $period);
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
