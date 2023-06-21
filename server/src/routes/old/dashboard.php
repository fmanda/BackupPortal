<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

require '../vendor/autoload.php';
require_once '../src/classes/DB.php';

$app->get('/visitdashboardweek', function ($request, $response) {
  try{
    date_default_timezone_set('Asia/Jakarta');
    $date = new \DateTime();
    $date = "'". $date->format('Y-m-d H:i:s') ."'" ;
    //call current_date from here makes date shift ???

    $str = "select tanggal::date,
            to_char(tanggal, 'Dy') as dayname ,
            (select count(*) from visit where coalesce(isdocument,0) = 0 and entrydate::date = tanggal) as visit,
            (select count(*) from visit where isdocument=1 and entrydate::date = tanggal) as document,
            (select count(*) from appointment where planningdate::date = tanggal) as appointment
            from generate_series(". $date  ."::date-6, ". $date  ." ,'1 day') as tanggal";

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

$app->get('/visitdashboardmonth', function ($request, $response) {
  try{
    date_default_timezone_set('Asia/Jakarta');
    $date = new \DateTime();
    $date = "'". $date->format('Y-m-d H:i:s') ."'" ;
    //call current_date from here makes date shift ???

    $str = "select tanggal::date,
            to_char(tanggal, 'Dy') as dayname ,
            (select count(*) from visit where coalesce(isdocument,0) = 0 and entrydate::date = tanggal) as visit,
            (select count(*) from visit where isdocument=1 and entrydate::date = tanggal) as document,
            (select count(*) from appointment where planningdate::date = tanggal) as appointment
            from generate_series(". $date  ."::date-30, ". $date  ." ,'1 day') as tanggal";

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


$app->get('/visitdeptdashboard', function ($request, $response) {
  try{
    date_default_timezone_set('Asia/Jakarta');
    $date = new \DateTime();
    $date = "'". $date->format('Y-m-d H:i:s') ."'" ;
    //call current_date from here makes date shift ???

    $str = "select b.name as deptname, count(a.id) as visit
          from visit a
          inner join jsection b on a.dept_id = b.section_id
          where entrydate::date >= ( ". $date  ."::date - 30)
          group by b.name
          ";

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
