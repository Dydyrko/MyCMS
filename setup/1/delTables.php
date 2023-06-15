<?php
$start=microtime(true);
require $root.'/setup/1/class.db.php';
DB::getInstance();

$q='set sql_mode=""';DB::q($q);

$q='SHOW TABLES';	//получить имена таблиц
$r=DB::q($q);
$n=DB::num_rows($r);
if($n){
	$A=array();
	while($row=DB::fetch_array($r)){
		$A[]=$row[0];	//список таблиц
	}
	$q='drop table '.implode(',',$A);
	if(DB::q($q)){
		$L=array('en'=>'Tables removed',
			'uk'=>'Таблиці видалені',
			'ru'=>'Таблицы удалены');
		echo '<p>'.$L[$lang].' ('.$n.').';
	}else{
		$L=array('en'=>'Error while dropping tables',
			'uk'=>'Помилка видалення таблиць',
			'ru'=>'Ошибка при удалении таблиц');
		$E=error_get_last();
		echo '<p class=err>'.$L[$lang].': '.$E['message'];
	}
}

function delFiles($dir){
	$s='/setup';
	foreach(glob($dir.'/*') as $f){	//кроме скрытых (.htaccess)
		if(strpos($f,$s)===false){
			if(is_file($f)){
				//echo '<li>'.$f;
				unlink($f);
	 		}else{
				$t=$f;
				delFiles($t);
				//rmdir($f);
			}
		}
	}
}
delFiles($root);
$L=array('en'=>'Removed site files',
	'uk'=>'Видалено файли сайту',
	'ru'=>'Удалены файлы сайта');
echo '<p>'.$L[$lang];
$L=array('en'=>'To start the installation, complete the configuration form - click',
	'uk'=>'Для початку встановлення виконайте форму конфігурації – натисніть',
	'ru'=>'Для начала установки выполните форму конфигурации — нажмите');
echo '<p style=font-size:150%>'.$L[$lang].' '
.'«<a onclick="document.forms[\'setup\'].onsubmit()">'.$L_apply[$lang].'</a>»';

exeTime();
exit;