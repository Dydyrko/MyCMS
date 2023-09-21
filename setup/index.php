<?php
if($_SERVER['SERVER_ADDR']!='127.0.0.1'){	//не локально
	if(empty($_SERVER["PHP_AUTH_USER"]) || $_SERVER["PHP_AUTH_USER"]!='2'
	 || empty($_SERVER["PHP_AUTH_PW"]) || $_SERVER["PHP_AUTH_PW"]!='2'){
		header('WWW-Authenticate: Basic realm="CMS setup"');
		header('HTTP/1.0 401 Unauthorized');
		exit('Ошибка авторизации!');
	}
}

$Langs=array('uk','ru','en');	//языки интерфейса
$root=$_SERVER["DOCUMENT_ROOT"];
$L_apply=array('en'=>'Apply','uk'=>'Застосувати','ru'=>'Применить');
$L_step=array('en'=>'Step','uk'=>'Крок','ru'=>'Шаг');
session_start();

if(isset($_GET['lang']) && !in_array($_GET['lang'],$Langs)){$_GET['lang']='en';}
if(isset($_GET['lang'])){$lang=$_SESSION['lang']=$_GET['lang'];}
if(!empty($_SESSION['lang'])){$lang=$_SESSION['lang'];}
if(empty($lang)){$lang='en';}

$finalComment='<div style="color: #777;text-align: right;">(';
if($lang=='ru'){
	$finalComment.='если ip сервера не «127.0.0.1»,<br> то логин и пароль «1» — для гарантированной<br> блокировки поисковых систем — пока<br> размер файла "robots.txt" менее 30 байт';
}else if($lang=='uk'){
	$finalComment.='якщо ip сервера не «127.0.0.1»,<br> то логін і пароль «1» — для гарантованого<br> блокування пошукових систем — поки<br> розмір файлу "robots.txt" менш 30 байт';
}else{
	$finalComment.='if the server ip is not “127.0.0.1”, then<br> the login and password are “1” — for<br> guaranteed blocking of search engines<br> while the "robots.txt" file size is less than 30 bytes';
}
$finalComment.=')</div>';

if(isset($_GET['ajx'])	//выполнение аякс-запросов
 &&	(
	isset($_POST['HOST'])		//выполнение формы конфигурации
	|| isset($_POST['CMS'])		//другие запросы
	)
){
	if(isset($_POST['HOST'])){	//выполнение формы конфигурации
		require $root.'/setup/1/setConf.php';
	}
	if(!file_exists($_SERVER["DOCUMENT_ROOT"].'/setup/1/conf.php')){exit('config ?');}

	if(isset($_POST['CMS']) && $_POST['CMS']=='delTables'){	//удаление таблиц из БД (перед установкой)
		require $root.'/setup/1/delTables.php';
	}else if(isset($_POST['CMS']) && $_POST['CMS']=='addTableKeys'){	//добавляем таблицам индексы
		require $root.'/setup/1/addTableKeys.php';
	}else if(isset($_POST['CMS']) && $_POST['CMS']=='zipExtract'){
		require $root.'/setup/1/zipExtract.php';
	}else if(isset($_POST['CMS']) && $_POST['CMS']=='addPerson'){
		require $root.'/setup/1/addPerson.php';
	}

	$start=microtime(true);
	require $root.'/setup/1/class.db.php';	//получаем $Conf
	$dirSQL=$root.'/setup/1/sql.'.$Langs[0];
	if(!file_exists($dirSQL)){
		$dirSQL=$root.'/setup/1/sql.en';
	}

	require $root.'/setup/1/db.php';	//создать БД в случае отсутствия при наличии прав

	DB::getInstance();	//Подключаемся к базе данных
	$s=showTables();
	if($s){
		echo $s;
		if(empty($tableKeys)){
			addTableKeys();
		}else{
			if(file_exists($root.'/1/conf.php')){	//есть файл на сайте, а не только в /setup
				require $root.'/setup/1/addAdmin.php';
			}else{
				$L=array('en'=>'expand file archive',
					'uk'=>'розгорнути архів файлів',
					'ru'=>'развернуть архив файлов');
				echo
				'<h2>'.$L_step[$lang].' 3: <a onclick="'
					.'window.lang=`setup`;return ajx(event,\'CMS\',\'zipExtract\',document.forms[\'setup\'].nextSibling)'
					.'">'.$L[$lang].'</a></h2>';
			}
		}
		$L=array('en'=>'or clean and begin new installation',
			'uk'=>'або очистити та почати встановлення з нуля',
			'ru'=>'или очистить и начать установку с нуля');
		echo
		'<p>'.$L[$lang].': <a onclick="'
			.'window.lang=\'setup\';return ajx(event,\'CMS\',\'delTables\',document.forms[\'setup\'].nextSibling)'
			.'">';
		$L=array('en'=>'delete',
			'uk'=>'вилучити',
			'ru'=>'удалить');
		echo $L[$lang].'</a> ';
		$L=array('en'=>'all database tables and site files',
			'uk'=>'всі таблиці бази даних та файли сайту',
			'ru'=>'все таблицы базы данных и файлы сайта');
		echo $L[$lang];
		exeTime();
		exit;
	}

	echo '<div class="log" title="Log">';
		$start=microtime(true);
		require $root.'/setup/1/createTableCat.php';
		require $root.'/setup/1/createTables.php';
		require $root.'/setup/1/addRows.php';
		echo showTables();
	echo '</div>';
	addTableKeys();

	exeTime();
	exit;
	
}

require $root.'/setup/1/p.php';	//page

function addTableKeys(){
	global $L_step,$lang;
		$L=array('en'=>'Create Table Indexes',
			'uk'=>'Створити індекси таблиць',
			'ru'=>'Создать индексы таблиц');
		echo
		'<h2>'.$L_step[$lang].' 2: <a onclick="'
			.'window.lang=\'setup\';return ajx(event,\'CMS\',\'addTableKeys\',document.forms[\'setup\'].nextSibling)'
			.'">'.$L[$lang].'</a></h2>';
}

function showTables(){
	global $tableKeys;
	unset($_SESSION['persons']);
	$q='SHOW TABLES';
	$r=DB::q($q);
	$n=DB::num_rows($r);
	if($n){
		$A=array('<ol>Tables:');		// ('.$n.')
		while($row=DB::fetch_array($r)){
			$A[].='<li>'.$row[0];
			$q='select count(*) as c'
			.($row[0]=='cat'?',d0':'')
			.' from '.$row[0];
			$row1=DB::f(DB::q($q));
			if($row1['c']>0){
				if($row[0]=='person'){$_SESSION['persons']=$row1['c'];}
				$A[].=	' ('.$row1['c'].' records'
					.(isset($row1['d0'])?', first: '.$row1['d0']:'');
				$q='SHOW INDEX FROM '.$row[0].' WHERE Key_name = "PRIMARY"';
				$r2=DB::q($q);
				$n2=DB::num_rows($r2);
				if($n2){
					//while($row2=DB::fetch_array($r2)){print_r($row2);}
					$A[].=', indexes are given';
					$tableKeys=1;
				}
				$A[].=')';
			}
		}
		$A[].='</ol>';
	}
	return isset($A)?implode('',$A):false;
}

function exeTime(){
	global $start;
		echo '<p style="color:#999;cursor:help" title="Server Request Execution Report">'.number_format((microtime(true)-$start),3,',','`')
		.' сек, memory PHP usage: '.number_format(memory_get_usage()/1000,3,',','`')
		.' ('.number_format(memory_get_peak_usage(true)/1000,3,',','`').' peak)'
		.' Kb';	
}