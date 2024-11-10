<?php
if(empty($root)){require $_SERVER['DOCUMENT_ROOT'].'/1/core/404.php';};

require $root.'/setup/1/conf.php';
$Langs=$Conf['Langs'];

class DB{
	protected static $_instance;
	public static function getInstance() {
		if (self::$_instance === null) {self::$_instance = new self;}
		return self::$_instance;
	}  
	private $connect;
	private  function __construct() {
		global $Conf;
		$connect = mysqli_connect($Conf['HOST'],$Conf['USER'],$Conf['PASSWORD'],$Conf['DB']) or exit('<p>'.mysqli_connect_error());
		$this->connect = $connect;
		if(!mysqli_set_charset($this->connect, "utf8mb4")){exit(mysqli_error($this->connect));}
		mysqli_query($this->connect,'SET sql_mode = "NO_DIR_IN_CREATE"');
		mysqli_query($this->connect,'SET time_zone = "'.date_default_timezone_get().'"');
	}
	public static function q($sql) {
		global $root;
		if(empty($root)){$root=$_SERVER['DOCUMENT_ROOT'];}
		$obj=self::$_instance;       
		if(isset($obj->connect)){
			$tmp=$root.'/1/tmp';
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
        //public static function info(){return mysqli_info(self::$_instance->connect);}
	public static function info(){return @mysqli_info(self::$_instance->connect);}

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