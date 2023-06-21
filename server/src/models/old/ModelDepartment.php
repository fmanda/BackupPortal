<?php
	require_once '../src/models/BaseModel.php';

	class ModelDepartment extends BaseModel{
		public static function getFields(){
			return array(
				"deptname"
			);
		}

		public static function retrieve($id){
			$sql = 'select section_id as id, "name" as deptname, "code" as deptcode
							from jsection where section_id = department_id
							and section_id='.$id;

			$obj = DB::openQuery($sql);
			if (isset($obj[0])) return $obj[0];
		}

		public static function retrieveList($filter=''){
			$sql = 'select section_id as id, "name" as deptname, "code" as deptcode
							from jsection where section_id = department_id';
			if ($filter<>''){
				$sql = $sql .' and '. $filter;
			}
			$sql = $sql .' order by id';

			$obj = DB::openQuery($sql);
			return $obj;
		}

		public static function retrieveEmployee($deptid, $employeename){
			$sql = "with t as(
							select trim(regexp_replace(
								firstname|| ' ' || middlename || ' ' || lastname, '\s+', ' ', 'g')) as employeename , b.department_id
							from jemployee a
							inner join jsection b on a.section_id = b.section_id
							)";

			$sql = 	$sql . " select * from t where department_id = ". $deptid
							. " and lower(employeename) like '%". strtolower($employeename) ."%'";

			$sql = $sql .' order by employeename';

			$obj = DB::openQuery($sql);
			return $obj;
		}

		public static function retrieveAllEmployee($employeename){
			$sql = "with t as (select trim(regexp_replace(	firstname|| ' ' || middlename || ' ' || lastname, '\s+', ' ', 'g')) as employeename ,
							c.name as deptname
							from jemployee a
							inner join jsection b on a.section_id = b.section_id
							inner join jsection c on b.department_id = c.section_id
							) select * from t where lower(employeename) like '%" . strtolower($employeename) . "%'
							order by 1";

			$obj = DB::openQuery($sql);
			return $obj;
		}

	}

	//override
