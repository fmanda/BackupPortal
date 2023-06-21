<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require '../vendor/autoload.php';
require '../src/classes/DB.php';


$app->get('/serverlist', function ($request, $response) {
  try{

    $json_object = file_get_contents('../src/databases.json');
    $data = json_decode($json_object);
  
    $result = array();

    foreach($data->databases as $database){
      if ($database->isactive){
        $obj = new stdClass();
        $obj->server = $database->server;
        $obj->servername = $database->servername;
        array_push($result, $obj);
      }
    }

    $json = json_encode($result);
    $response->getBody()->write($json);

		return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
	}catch(Exception $e){
    $msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});


$app->get('/databaselist', function ($request, $response) {
  try{
    $json_object = file_get_contents('../src/databases.json');
    $data = json_decode($json_object);
    $result = array();
    $id = 0;
    
    foreach($data->databases as $database){
      if ($database->isactive){
       
        $sql = "select ". $id . " + ROW_NUMBER() OVER (ORDER BY a.database_id)  as id, 
        '". $database->server . "' as server,  a.[name] as dbname
        FROM sys.databases a
        inner  join sys.sysusers b on a.owner_sid = b.sid
        where b.[name] = 'dbo' and a.database_id > 4";

        $data = DB::openQuery($sql, $database);
        $result = array_merge($result, $data);

        $id = $id + count($data);
      }
    }

    $json = json_encode($result);
    $response->getBody()->write($json);

		return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
	}catch(Exception $e){
    $msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});

$app->get('/lastbackupinfo[/{server}]', function ($request, $response) {
  try{
    $serverparam = $request->getAttribute('server');
    $json_object = file_get_contents('../src/databases.json');
    $data = json_decode($json_object);
    $result = array();

    foreach($data->databases as $database){
      if ($database->isactive == false) continue;
      
      if (isset($serverparam)){
        if ($serverparam != $database->server) continue;
      }

      $sql = "WITH MostRecentBackups
              AS(
                SELECT 
                  database_name AS [Database],
                  MAX(bus.backup_finish_date) AS LastBackupTime,
                  CASE bus.type
                  WHEN 'D' THEN 'Full'
                  WHEN 'I' THEN 'Differential'
                  WHEN 'L' THEN 'Transaction Log'
                  END AS Type
                FROM msdb.dbo.backupset bus
                WHERE bus.type <> 'F'
                GROUP BY bus.database_name,bus.type
              ),
              BackupsWithSize
              AS(
                SELECT mrb.*, (SELECT TOP 1 CONVERT(DECIMAL(10,4), b.backup_size/1024/1024/1024) AS backup_size FROM msdb.dbo.backupset b
                WHERE [Database] = b.database_name AND LastBackupTime = b.backup_finish_date) AS [Backup Size]
                FROM MostRecentBackups mrb
              )
              
              SELECT 
                  '". $database->server ."' as server,
                  SERVERPROPERTY('ServerName') AS instance, 
                  d.name AS [dbname],
                  d.state_desc AS state,
                  d.recovery_model_desc AS [recoverymodel],
                  bf.LastBackupTime AS [lastfull],
                  --DATEDIFF(DAY,bf.LastBackupTime,GETDATE()) AS [Time Since Last Full (in Days)],
                  --bf.[Backup Size] AS [Full Backup Size],
                  bd.LastBackupTime AS [lastdifferential]
                  --DATEDIFF(DAY,bd.LastBackupTime,GETDATE()) AS [Time Since Last Differential (in Days)],
                  --bd.[Backup Size] AS [Differential Backup Size],
                  --bt.LastBackupTime AS [Last Transaction Log],
                  --DATEDIFF(MINUTE,bt.LastBackupTime,GETDATE()) AS [Time Since Last Transaction Log (in Minutes)],
                  --bt.[Backup Size] AS [Transaction Log Backup Size]
              FROM sys.databases d
              LEFT JOIN BackupsWithSize bf ON (d.name = bf.[Database] AND (bf.Type = 'Full' OR bf.Type IS NULL))
              LEFT JOIN BackupsWithSize bd ON (d.name = bd.[Database] AND (bd.Type = 'Differential' OR bd.Type IS NULL))
              LEFT JOIN BackupsWithSize bt ON (d.name = bt.[Database] AND (bt.Type = 'Transaction Log' OR bt.Type IS NULL))
              WHERE d.name <> 'tempdb' AND d.source_database_id IS NULL
              order by d.name";

      $data = DB::openQuery($sql, $database);
      $result = array_merge($result, $data);
    }

    $json = json_encode($result);
    $response->getBody()->write($json);

		return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
	}catch(Exception $e){
    $msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});




$app->get('/backuphistory14/{server}[/{dbname}]', function ($request, $response) {
  try{
    $serverparam = $request->getAttribute('server');
    $dbparam = $request->getAttribute('dbname');

    if (!isset($dbparam)){
      $dbparam = '%%';
    }

    $json_object = file_get_contents('../src/databases.json');
    $data = json_decode($json_object);
    $result = array();

    foreach($data->databases as $database){
      if ($database->isactive == false) continue;
      
      if (isset($serverparam)){
        if ($serverparam != $database->server) continue;
      }

      $sql = "SELECT ROW_NUMBER() OVER (ORDER BY b.database_name,b.backup_finish_date  desc)  as id,
                  '". $database->server ."' as server,
                  b.database_name as dbname,
                  b.backup_start_date as backupstart,
                  b.backup_finish_date as backupfinish,    
                  b.backup_size as backupsize,    
                  a.physical_device_name as backuppath
              FROM
                  msdb.dbo.backupmediafamily a
                  INNER JOIN msdb.dbo.backupset b ON a.media_set_id = b.media_set_id
              WHERE
                  (CONVERT(datetime, b.backup_start_date, 102) >= GETDATE() - 14)
                and b.database_name like '". $dbparam ."'
              ORDER BY
                  b.database_name,
                  b.backup_finish_date  desc";

      $data = DB::openQuery($sql, $database);
      $result = array_merge($result, $data);
    }

    $json = json_encode($result);
    $response->getBody()->write($json);

		return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
	}catch(Exception $e){
    $msg = $e->getMessage();
    $response->getBody()->write($msg);
		return $response->withStatus(500)
			->withHeader('Content-Type', 'text/html');
	}
});