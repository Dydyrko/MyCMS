<?php
if(empty($root)){require $_SERVER['DOCUMENT_ROOT'].'/1/core/404.php';};	//если прямой вызов: альтернатива "Deny from all" в .htaccess папки "/1"

require $root.'/1/conf.php';
$Langs=$Conf['Langs'];

$DbLog=array();
class DB{
	protected static $_instance;
	public static function getInstance() {
		if (self::$_instance === null) {self::$_instance = new self;}
		return self::$_instance;
	}  
	private  function __construct() {
		global $Conf;
		$this->connect = mysqli_connect($Conf['HOST'],$Conf['USER'],$Conf['PASSWORD'],$Conf['NAME_BD']) or exit('<p>'.mysqli_connect_error());
		//if(!mysqli_set_charset($this->connect, "utf8mb4")){exit(mysqli_error($this->connect));}
		mysqli_query($this->connect,'SET sql_mode = "NO_DIR_IN_CREATE"');
		mysqli_query($this->connect,'SET time_zone = "'.date_default_timezone_get().'"');
	}
	public static function q($sql) {
		global $root,$DbLog;
		if(empty($root)){$root=$_SERVER['DOCUMENT_ROOT'];}
		$obj=self::$_instance;       
		if(isset($obj->connect)){
			$tmp=$root.'/1/tmp';
			if(isset($_SESSION['debug'])){
				if(isset($obj->count_sql)){$obj->count_sql++;}else{$obj->count_sql=1;}
				$start_time_sql = microtime(true);
			}
			$result=mysqli_query($obj->connect,$sql) or $err=(mysqli_error($obj->connect));
			if(isset($err)){
				$A=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1);
				$B=array();
				foreach($A[0] as $i=>$v){$B[]=$i.'='.str_replace($root,'',$v);}
				if(!file_exists($tmp)){mkdir($tmp);}
				file_put_contents(
					$tmp.'/err_db.txt',
					date("Y.m.d H:i:s")."\t".$err."\n\t".$sql."\n\t".implode("\n\t",$B)."\n",
					FILE_APPEND);
				exit($err);
			}
			if(isset($_SESSION['debug'])){
				$time_sql = round((microtime(true) - $start_time_sql)*1000,3);
				$A=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1);
				$B=array();
				foreach($A[0] as $i=>$v){
					$n=strpos($v,$root);
					if($n!==false){$v=substr($v,$n);}
					$B[]=$i.'='.str_replace($root,'',$v);
				}
				$s=($obj->count_sql==1?date("Y.m.d H:i:s").' '.$_SERVER['REQUEST_URI']."\n":'').$obj->count_sql."\t".$time_sql."\t".str_replace(array("\n","\t"),' ',$sql)."\t".implode(', ',$B);
				//if(!file_exists($tmp)){mkdir($tmp);}file_put_contents($tmp.'/db.txt',$s."\n",FILE_APPEND);
				$DbLog[]=$s;
			}               
			return $result;
		}
		return false;
        }
        public static function f($object){return mysqli_fetch_assoc($object);}
        public static function fetch_object($object){return mysqli_fetch_object($object);}
        public static function fetch_array($object){return mysqli_fetch_array($object);}
        public static function fetch_row($object){return mysqli_fetch_row($object);}
        public static function num_rows($object){return mysqli_num_rows($object);}
        public static function data_seek($object,$pos){return mysqli_data_seek($object,$pos);}

        public static function free_result($object){return mysqli_free_result($object);}

        public static function insert_id(){return mysqli_insert_id(self::$_instance->connect);}
        public static function affected_rows(){return mysqli_affected_rows(self::$_instance->connect);}
        public static function info(){return mysqli_info(self::$_instance->connect);}
	//public static function info(){return @mysqli_info(self::$_instance->connect);}

        public static function esc($str){
		$obj=self::$_instance;       
		if(isset($obj->connect)){return mysqli_real_escape_string($obj->connect, $str);}
	}

        public static function qL($a,$as=''){	//основной язык как есть, остальные типа "name_en"
		global $Langs,$lang;
		$A=explode('.',$a);
		if(count($A)==2){$tbl=$A[0].'.';$name=$A[1];}else{$tbl='';$name=$a;}
		if($lang==$Langs[0]){
			return $tbl.$name.' as '
			.($as?$as:$name);
		}else{
			return 'IF('.$tbl.$name.'_'.$lang.'="",'.$tbl.$name.','.$tbl.$name.'_'.$lang.') as '
			.($as?$as:$name);
		}
	}
}