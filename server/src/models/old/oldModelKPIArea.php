<?php
	require_once '../src/models/BaseModel.php';

	class ModelKPIArea extends BaseModel{
		public static function getTableName(){
			return 'kpi_area';
		}

		public static function getFields(){
			return array(
				"kpi_department_id", "areacode", "areaname"
			);
		}
	}

	class ModelKPISubArea extends BaseModel{
		public static function getTableName(){
			return 'kpi_subarea';
		}
		public static function getFields(){
			return array(
				"kpi_area_id", "subcode",
				"subname", "subdesc", "weight",
				"level_1", "leveldetail_1",
				"level_2", "leveldetail_2",
				"level_3", "leveldetail_3",
				"level_4", "leveldetail_4",
				"level_5", "leveldetail_5",
			);
		}
	}
