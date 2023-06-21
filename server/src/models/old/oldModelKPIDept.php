<?php
	require_once '../src/models/BaseModel.php';
	require_once '../src/models/ModelDepartment.php';
	require_once '../src/models/ModelMLArea.php';
	require_once '../src/models/ModelKPIArea.php';

	class ModelKPIDept extends BaseModel{

		public static function getTableName(){
			return 'kpi_department';
		}

		public static function getFields(){
			return array(
				"department_id", "period"
			);
		}

		public static function retrieve($id){
			$obj = parent::retrieve($id);
			if (isset($obj)) {
				$obj->mlitems = ModelMLArea::retrieveList('kpi_department_id = '. $obj->id);
				foreach($obj->mlitems as $mlarea){
					$mlarea->items = ModelMLSubArea::retrieveList('ml_area_id = '. $mlarea->id);
				}
				$obj->kpiitems = ModelKPIArea::retrieveList('kpi_department_id = '. $obj->id);
				foreach($obj->kpiitems as $kpiarea){
					$kpiarea->items = ModelKPISubArea::retrieveList('kpi_area_id = '. $kpiarea->id);
				}
			}
			return $obj;
		}

		public static function retrieveBy($dept_id, $period){
			$row = DB::openQuery("select * from ".static::getTableName()
				." where department_id = " . $dept_id ." and period = " . $period);

			if (isset($row[0])) $obj = $row[0];

			if (isset($obj)) {
				$obj->mlitems = ModelMLArea::retrieveList('kpi_department_id = '. $obj->id);
				foreach($obj->mlitems as $mlarea){
					$mlarea->items = ModelMLSubArea::retrieveList('ml_area_id = '. $mlarea->id);
				}
				$obj->kpiitems = ModelKPIArea::retrieveList('kpi_department_id = '. $obj->id);
				foreach($obj->kpiitems as $kpiarea){
					$kpiarea->items = ModelKPISubArea::retrieveList('kpi_area_id = '. $kpiarea->id);
				}

				return $obj;
			}else{
				return null;
			}

		}

		public static function deleteFromDB($id){
			try{
				$str = static::generateSQLDelete("id=". $id);
				$str = $str . "delete a, b from ml_area a
							left join ml_subarea b on a.id = b.ml_area_id where a.kpi_department_id = " . $id . "; ";
				$str = $str . "delete a, b from kpi_area a
							left join kpi_subarea b on a.id = b.kpi_area_id where a.kpi_department_id = " . $id . "; ";
				DB::executeSQL($str);
			} catch (Exception $e) {
				$db->rollback();
				throw $e;
			}
		}

		public static function saveToDB($obj){
			$db = new DB();
			$db = $db->connect();
			$db->beginTransaction();
			try {
				if (!static::isNewTransaction($obj)){
					$str = "delete a, b from ml_area a
								left join ml_subarea b on a.id = b.ml_area_id where a.kpi_department_id = " . $obj->id . "; ";
					$str = $str . "delete a, b from kpi_area a
								left join kpi_subarea b on a.id = b.kpi_area_id where a.kpi_department_id = " . $obj->id . "; ";
					$db->prepare($str)->execute();
				}

				static::saveObjToDB($obj, $db);

				foreach($obj->mlitems as $mlarea){
					$mlarea->kpi_department_id = $obj->id;
					$mlarea->id = 0; //force insert
					ModelMLArea::saveObjToDB($mlarea, $db);
					foreach($mlarea->items as $mlsubarea){
						$mlsubarea->id = 0; //force insert
						$mlsubarea->ml_area_id = $mlarea->id;
					  ModelMLSubArea::saveObjToDB($mlsubarea, $db);
					}
				}

				foreach($obj->kpiitems as $kpiarea){
					$kpiarea->kpi_department_id = $obj->id;
					$kpiarea->id = 0; //force insert
					ModelKPIArea::saveObjToDB($kpiarea, $db);
					foreach($kpiarea->items as $kpisubarea){
						$kpisubarea->id = 0; //force insert
						$kpisubarea->kpi_area_id = $kpiarea->id;
					  ModelKPISubArea::saveObjToDB($kpisubarea, $db);
					}
				}

				$db->commit();
				$db = null;

			} catch (Exception $e) {
				$db->rollback();
				throw $e;
			}
		}

		public static function generate($dept_id, $period){
			$obj = new stdClass();
			$obj->id = 0;
			$obj->period = $period;

			$dept = static::retrieveBy($dept_id, $period);
			$department = ModelDepartment::retrieve($dept_id);

			if (isset($dept)) {
				$obj->dept_id = $dept->id;
				$obj->deptcode = $department->deptcode;
				$obj->deptname = $department->deptname;
				$obj->mlitems = array();
				$obj->kpiitems = array();

				foreach($dept->mlitems as $area){
					foreach($area->items as $subarea){
						ModelKPIDept::setObjItem($obj->mlitems, $area, $subarea);
					}
				}

				foreach($dept->kpiitems as $area){
					foreach($area->items as $subarea){
						ModelKPIDept::setObjItem($obj->kpiitems, $area, $subarea);
					}
				}
			}
			return $obj;
		}

		private static function setObjItem(&$items, $area, $subarea){
			for ($i = 1; $i <= 5; $i++) {
				if ($i == 2 && $subarea->level_2 == '') continue;
				if ($i == 3 && $subarea->level_3 == '') continue;
				if ($i == 4 && $subarea->level_4 == '') continue;
				if ($i == 5 && $subarea->level_5 == '') continue;

				$newitem = new stdClass();

				$newitem->areacode = $area->areacode;
				$newitem->areaname = $area->areaname;
				$newitem->subcode = $subarea->subcode;
				$newitem->subname = $subarea->subname;

				// $newitem->subdesc = $subarea->subname.  "\n\n" . $subarea->subdesc;
				if ($i == 1) {
					$newitem->subdesc = $subarea->subname;
				}else{
					$newitem->subdesc = $subarea->subdesc;
				}

				$newitem->weight = $subarea->weight;

				if ($i == 1){
					$newitem->levelcode = $subarea->level_1;
					$newitem->leveldetail = $subarea->leveldetail_1;
				}
				else if ($i == 2){
					$newitem->levelcode = $subarea->level_2;
					$newitem->leveldetail = $subarea->leveldetail_2;
				}
				else if ($i == 3) {
					$newitem->levelcode = $subarea->level_3;
					$newitem->leveldetail = $subarea->leveldetail_3;
				}
				else if ($i == 4){
					$newitem->levelcode = $subarea->level_4;
					$newitem->leveldetail = $subarea->leveldetail_4;
				}
				else if ($i == 5){
					$newitem->levelcode = $subarea->level_5;
					$newitem->leveldetail = $subarea->leveldetail_5;
				}
				$newitem->level = $i;

				$newitem->key = $newitem->subcode . '_' . $i;
				array_push($items, $newitem);
			}
		}


		public static function retrievePrev($dept_id, $period){
			$currentperiod = $period;
			$obj = static::retrieveBy($dept_id, $period);
			if (isset($obj)) {
				$currentid = $obj->id;
			}else{
				$currentid = 0;
			}

			$period = $period - 1;
			if (substr($period, -1) == 0) {
				$period =  ( substr($period,0,4)-1 ) . '02';
			}


			$obj = static::retrieveBy($dept_id, $period);

			if (isset($obj)) {
				$obj->id = $currentid;
				$obj->period = $currentperiod;

				return $obj;
			}else{
				return null;
			}

		}



	}
