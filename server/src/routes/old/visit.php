<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

require '../vendor/autoload.php';
require_once '../src/classes/DB.php';
require_once '../src/models/ModelVisit.php';

$app->get('/visit_delete/{id}', function ($request, $response) {  //if hosting not allowed del
  try{
    $data = ModelVisit::deleteFromDB($request->getAttribute('id'));
		return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
	}catch(Exception $e){
    $msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});

$app->get('/visit', function ($request, $response) {
  try{
    $data = ModelVisit::retrieveList();
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

$app->get('/ongoingvisit', function ($request, $response) {
  try{
    $data = ModelVisit::retrieveList('exitdate is null and coalesce(isdocument,0) = 0');
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

$app->get('/searchvisit/{dt1}/{dt2}[/{filtertxt}]', function ($request, $response) {
  try{
    $dt1 = $request->getAttribute('dt1');
    $dt2 = $request->getAttribute('dt2');
    $filtertxt = $request->getAttribute('filtertxt');

    $filter = " cast(entrydate as date) between '".  $dt1
              . "' and '". $dt2 . "' and (lower(visitorname) like '%"
              . strtolower($filtertxt) . "%' or lower(person_to_meet) like '%"
              . strtolower($filtertxt) . "%') and coalesce(isdocument,0) = 0";

    $data = ModelVisit::retrieveList($filter);
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

$app->get('/searchdocument/{dt1}/{dt2}[/{filtertxt}]', function ($request, $response) {
  try{
    $dt1 = $request->getAttribute('dt1');
    $dt2 = $request->getAttribute('dt2');
    $filtertxt = $request->getAttribute('filtertxt');

    $filter = " cast(entrydate as date) between '".  $dt1
              . "' and '". $dt2 . "' and (lower(visitorname) like '%"
              . strtolower($filtertxt) . "%' or lower(documentname) like '%"
              . strtolower($filtertxt) . "%') and coalesce(isdocument,0) = 1";

    $data = ModelVisit::retrieveList($filter);
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

$app->get('/visit/{id}', function ($request, $response, $args) {
	try{
    $id = $request->getAttribute('id');
    $data = ModelVisit::retrieve($id);
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


$app->get('/visitimage/{id}', function ($request, $response, $args) {
	try{
    $id = $request->getAttribute('id');
    $str = "SELECT encode(img1, 'base64')  as img1 FROM visitimage where visit_id = " . $id ;
    $obj =  DB::openQuery($str);
    $img = $obj[0]->img1;


    echo '<img crossorigin=""  src="data:image/jpeg;base64,'.$img.'"/>';

    return $response->withHeader("Content-Type", "image/jpeg");
		// return $response->withHeader('Content-Type', 'image/jpeg');
    // return $response->withHeader('Content-Type', 'text/html');
	}catch(Exception $e){
    $msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});



$app->get('/visitimageurl/{id}', function ($request, $response, $args) {
	try{
    $id = $request->getAttribute('id');
    $str = "SELECT imgpath1, imgpath2 FROM visitimage where visit_id = " . $id . "  order by imgpath1 desc";
    $obj =  DB::openQuery($str);

    // if($obj[0] == null) $response->withHeader("Content-Type", "text/html");

    $json = json_encode($obj[0]);

    //PR bikinkan config
    // $config = parse_ini_file("../src/config.ini");
  	// $directory =  $config["upload_directory"];
    // $directory = $directory . DIRECTORY_SEPARATOR; //. $year;

    $response->getBody()->write($json);

    return $response->withHeader("Content-Type", "text/html");
		// return $response->withHeader('Content-Type', 'image/jpeg');
    // return $response->withHeader('Content-Type', 'text/html');
	}catch(Exception $e){
    $msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});


$app->get('/endvisit/{id}', function ($request, $response, $args) {
	try{
    $id = $request->getAttribute('id');
    $date = ModelVisit::endvisit($id);
    $response->getBody()->write($date);

    return $response->withHeader("Content-Type", "text/html");
	}catch(Exception $e){
    $msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});


$app->get('/currentappointment', function ($request, $response) {
  try{
    // $data = ModelUsers::retrieveList();
    date_default_timezone_set('Asia/Jakarta');
    $date = new \DateTime();
    $filterdate = $date->format('Y-m-d');

    //current_date / now() not working
    $str = "select 0 as id, a.id as appointment_id,
            a.dept_id,
            a.planningdate as entrydate,
            a.reason,
            a.person_to_meet,
            b.visitorname,
            b.company,
            b.address,
            b.phone,
            c.name as deptname
            from appointment a
            left join visitor b on a.visitor_id = b.id
            left join jsection c on a.dept_id = c.section_id
            left join visit d on d.appointment_id = a.id
            where d.id is null and a.planningdate::date = '" . $filterdate . "'"  ;

    $data = DB::openQuery($str);
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

//multipart form data :
// $app->post('/visit', function ($request, $response) {
//   $json = $request->getParsedBody()['data'];
// 	$obj = json_decode($json);
// 	try{
// 		ModelVisit::saveToDB($obj);
//     $json = json_encode($obj);
//     executeUploadFile($request, $obj->id);
//     $response->getBody()->write($json);
//     return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
// 	}catch(Exception $e){
// 		$msg = $e->getMessage();
//     $response->getBody()->write($msg);
// 		return $response->withStatus(500)
// 			->withHeader('Content-Type', 'text/html');
// 	}
// });

//change this shit to json
$app->post('/visit', function ($request, $response) {
  $json = $request->getBody();
  $raw = json_decode($json);
	$obj = $raw->data;

	try{
		ModelVisit::saveToDB($obj);
    $json = json_encode($obj);
    executeUploadFile($raw, $obj->id);
    // $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'text/html');
	}catch(Exception $e){
		$msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});

//multipart form data :
// function executeUploadFile($request, $id){
//   $uploadedFile = $request->getParsedBody()['img1'];
//   uploadFromData($uploadedFile, $id, (int) 1 );
//
//   $uploadedFile = $request->getParsedBody()['img2'];
//   uploadFromData($uploadedFile, $id,  (int) 2 );
// }

function executeUploadFile($json, $id){
  $uploadedFile = $json->img1;
  uploadFromData($uploadedFile, $id, (int) 1 );

  $uploadedFile = $json->img2;
  uploadFromData($uploadedFile, $id, (int) 2 );
}


function moveUploadedFile($directory, UploadedFileInterface $uploadedFile)
{
	$filename = $uploadedFile->getClientFilename();
	$uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
	return $filename;
}


function uploadFromData($data, $id,  $imgidx) {
    $ext = null;
    if(strpos($data, 'data:image/jpeg;base64,') === 0) {
        $data = str_replace('data:image/jpeg;base64,', '', $data);
        $ext = '.jpg';
    } elseif (strpos($data, 'data:image/jpg;base64,') === 0) {
        $data = str_replace('data:image/jpg;base64,', '', $data);
        $ext = '.jpg';
    } elseif (strpos($data, 'data:image/png;base64,') === 0) {
        $data = str_replace('data:image/png;base64,', '', $data);
        $ext = '.png';
    } elseif (strpos($data, 'data:image/gif;base64,') === 0) {
        $data = str_replace('data:image/gif;base64,', '', $data);
        $ext = '.gif';
    }

    $config = parse_ini_file("../src/config.ini");
  	$directory =  $config["upload_directory"];

  	$directory = $directory . DIRECTORY_SEPARATOR; //. $year;

  	if (!file_exists($directory)) {
  		mkdir($directory, 0777, true);
  	}

    if($ext != null) {
        $image = base64_decode($data);
        // $filename = date('YmdHis') . '.' . createToken() . $ext;
        $filename = date('YmdHis') . '_' .$id .'_'.  $imgidx  . $ext;
        if(file_put_contents($directory . DIRECTORY_SEPARATOR . $filename, $image) !== FALSE) {
          $db = new DB();
          $db = $db->connect();
          $db->beginTransaction();
          try {
            //$sql = 'INSERT INTO visitimage (visit_id, imgpath' . $imgidx . ') VALUES(:visit_id, :imgpath' . $imgidx . ' )';

            $sql = 'update visitimage set  imgpath' . $imgidx . ' = :imgpath' . ' where visit_id = :visit_id';

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':visit_id' => $id,
                ':imgpath'  => $filename
            ]);
            $db->commit();
          } catch (Exception $e) {
            $db->rollback();
            throw $e;
          }
          return $filename;
        }
    }
    return null;
}

function createToken() {
    return bin2hex(openssl_random_pseudo_bytes(16));
}
