<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

require '../vendor/autoload.php';
require_once '../src/classes/DB.php';

// $container = new Container();
// $container->set('upload_directory', __DIR__ . '/uploads');

$app->get('/directory', function ($request, $response, $args) {
	try{
		$config = parse_ini_file("../src/config.ini");
		$directory =  $config["upload_directory"];
		$data = new stdClass();
    listFolderFiles($directory, $data);
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

function listFolderFiles($dir, &$obj){
		if (!isset($obj)) {
			$obj = new stdClass();
		}
		if (!isset($obj->fileName)){
			$obj->fileName 	= '<root>';
			$obj->filepath 	= $dir;
			$obj->id 				= 0;
			// $obj->fileName = str_replace('\\\\','\\',$obj->fileName );
		}
		$obj->items 		= array();

		if (is_dir($dir)) {
	    $ffs = scandir($dir);
	    unset($ffs[array_search('.', $ffs, true)]);
	    unset($ffs[array_search('..', $ffs, true)]);

	    // prevent empty ordered elements
	    if (count($ffs) < 1)
	    	return;

	    foreach($ffs as $ff){
				$item = new stdClass();
				$item->fileName = $ff;
				$item->filepath = $dir.'/'.$ff;
				$item->id = $obj->id +1;
				array_push($obj->items, $item);
				if (is_dir($dir.'/'.$ff)){
					listFolderFiles($dir.'/'.$ff,  $item);
				}
	    }
		}
}
