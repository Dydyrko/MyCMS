<?php
if($_SERVER['SERVER_ADDR']!='127.0.0.1'){	//не локально
	if(empty($_SERVER["PHP_AUTH_USER"]) || $_SERVER["PHP_AUTH_USER"]!='2'
	 || empty($_SERVER["PHP_AUTH_PW"]) || $_SERVER["PHP_AUTH_PW"]!='2'){
		header('WWW-Authenticate: Basic realm="CMS setup"');
		header('HTTP/1.0 401 Unauthorized');
		exit('Ошибка авторизации!');
	}
}
$root=$_SERVER["DOCUMENT_ROOT"];
$L_apply=array('en'=>'Apply','uk'=>'Застосувати','ru'=>'Применить');
$L_step=array('en'=>'Step','uk'=>'Крок','ru'=>'Шаг');
session_start();

if(isset($_GET['lang']) && !in_array($_GET['lang'],array('uk','ru'))){$_GET['lang']='en';}

if(
	isset($_GET['lang']) && (empty($_SESSION['lang']) || $_SESSION['lang']!=$_GET['lang'])
){
	$_SESSION['lang']=$_GET['lang'];
	$lang=$_GET['lang'];
}

if(!empty($_SESSION['lang'])){$lang=$_SESSION['lang'];}

if(empty($lang) && isset($_GET['lang'])){$lang=$_GET['lang'];}

if(empty($lang)){$lang='en';}

//var_dump($_SESSION);
//echo $lang;

if(isset($_GET['ajx'])){	//выполнение аякс-запросов
	if(!empty($_SESSION['lang'])){$lang=$_SESSION['lang'];}
	if(
		isset($_POST['HOST'])		//выполнение формы конфигурации
		 || isset($_POST['CMS'])	//другие запросы
	){
		if(isset($_POST['HOST'])){	//выполнение формы конфигурации: в форме после изменения хоть одного значения
						// — иначе выполняется аякс с пустым POST['CMS'] (выполнение нажатием на "Применить" или [Enter])
			if(empty($_POST['geoLoc'])){$_POST['geoLoc']=0;}
			$A=array();
			foreach($_POST as $i=>$v){
				if($i=='Langs'){
					$B=explode(',',$v);
					foreach($B as $i1=>$v1){$B[$i1]=trim($v1);}
					$A[]='\''.$i.'\'=>array(\''.implode('\',\'',$B).'\')';
				}else{
					$A[]='\''.$i.'\'=>\''.$v.'\'';
				}

			}
			$A=array(
				'<?php'
				."\n".'if(empty($root)){exit;};'
				."\n".'$Conf=array('."\n",
				implode(','."\n",$A),
				"\n".');'
			);

			$s=implode('',$A);
			$s=file_put_contents($_SERVER["DOCUMENT_ROOT"].'/setup/1/conf.php',$s);
			if($s){
				$L=array('en'=>'Configuration written, file size',
					'uk'=>'Конфігурація записана, розмір файлу',
					'ru'=>'Конфигурация записана, размер файла');
				echo $L[$lang].': '.$s;
			}else{
				$E=error_get_last();
				exit('<p class=err>'.$E['message']);
			}
		}

		if(isset($_POST['CMS']) && $_POST['CMS']=='delTables'){	//удаление таблиц из БД (перед установкой)
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
		}else if(isset($_POST['CMS']) && $_POST['CMS']=='addTableKeys'){	//добавляем таблицам индексы
			$start=microtime(true);
			echo '<div class="log" title="Log">';
				require $root.'/setup/1/class.db.php';
				DB::getInstance();
				require $root.'/setup/1/addTableKeys.php';
				echo showTables();
			echo '</div>';
			$L=array('en'=>'Step',
				'uk'=>'Крок',
				'ru'=>'Шаг');
			echo
			'<h2>'.$L[$lang].' 3';
			$L=array('en'=>'expand file archive',
				'uk'=>'розгорнути архів файлів',
				'ru'=>'развернуть архив файлов');
			echo ': <a onclick="'
				.'window.lang=`setup`;return ajx(event,`CMS`,`zipExtract`,document.forms[\'setup\'].nextSibling)'
				.'">'.$L[$lang].'</a></h2>';
			exeTime();
			exit;
		}else if(isset($_POST['CMS']) && $_POST['CMS']=='zipExtract'){
			$start=microtime(true);
			$file=$root.'/setup/cms.zip';
			if(!file_exists($file)){exit($file.' not exists');}
			$zip=new ZipArchive;
			if($zip->open($file)===TRUE){
				$zip->extractTo($root.'/');
				for($i=0;$i<$zip->numFiles;$i++){	//сохранить даты файлов
					touch($root.'/'.$zip->statIndex($i)['name'], $zip->statIndex($i)['mtime']);
				}
				echo '<p>cms.zip unpacked';
				$zip->close();
				$zip2=new ZipArchive;
				$file=$root.'/setup/help.zip';
				if($zip2->open($file)===TRUE){
					$zip2->extractTo($root.'/help/1/');
					for($i=0;$i<$zip2->numFiles;$i++){	//сохранить даты файлов
						touch($root.'/help/1/'.$zip2->statIndex($i)['name'], $zip2->statIndex($i)['mtime']);
					}
					$zip2->close();
					echo '<p>help.zip unpacked';
				}

				if(copy($root.'/setup/1/conf.php',$root.'/1/conf.php')){
					$L=array('en'=>'Configuration file copied',
						'uk'=>'Файл конфігурації скопійовано',
						'ru'=>'Файл конфигурации скопирован');
					echo '<p>'.$L[$lang];
					exeTime();

					$L=array('en'=>'Step 4: Add a site administrator',
						'uk'=>'Крок 4: додати адміністратора сайту',
						'ru'=>'Шаг 4: добавить администратора сайта');
					echo
					'<h2>'.$L[$lang]
						.(empty($_SESSION['persons'])?''
							:' <small style="font-weight:normal">(persons now: '.$_SESSION['persons'].'. <a href=/>Site</a>)</small>')
					.'</h2>';
					require $root.'/setup/1/addPerson.php';
				}else{
					$L=array('en'=>'Error copying configuration file',
						'uk'=>'Помилка при копіюванні конфігураційного файлу',
						'ru'=>'Ошибка при копировании файла конфигурации');
					$E=error_get_last();
					echo '<p class=err>'.$L[$lang].': '.$E['message'];
				}
			}else{
				$L=array('en'=>'File unpacking error',
					'uk'=>'Помилка розпакування файлу',
					'ru'=>'Ошибка распаковки файла');
				echo '<p class=err>'.$L[$lang].': '.$file;
			}
			exit;
		}else if(isset($_POST['CMS']) && $_POST['CMS']=='addPerson'){
			$start=microtime(true);

			$A=explode('@',$_POST['mail']);$t=-1;
			if(count($A)==2){$t=getmxrr($A[1],$M);}
			if(!$t){exit('<p class=err>Mail server not found');}

			require $root.'/setup/1/class.db.php';
			DB::getInstance();
			$_POST['mail']=DB::esc($_POST['mail']);
			$_POST['psw']=DB::esc($_POST['psw']);

			$q='select id from person where mail="'.$_POST['mail'].'"';
			$r=DB::q($q);
			if(DB::num_rows($r)){
				exit('<p class=err>Exists person with "'.$_POST['mail'].'"');
			}

			$q='insert into cat set parent=-9,'	//persons
				.(empty($_SESSION['persons'])?'id=-201,':'')
				.'final=-120,'			//admin
				.'name="'.$_POST['mail'].'"';
			DB::q($q);
			if(empty($_SESSION['persons'])){
				if(DB::affected_rows()!=1){
					$E=error_get_last();
					exit('<p class=err>Error add person: '.$E['message'].', q='.$q);
				}
				$id=-201;
			}else{
				$id=DB::insert_id();
				if(!$id){
					$E=error_get_last();
					exit('<p class=err>Error add person: '.$E['message']);
				}
			}
			$q='insert into person set id='.$id.',mail="'.$_POST['mail'].'",psw="'.password_hash($_POST['psw'],PASSWORD_BCRYPT).'"';
			DB::q($q);
			
			if(DB::affected_rows()==1){
				$q='update cat set c=c+1 where id=-9';DB::q($q);
				if($lang=='ru'){
					echo '<p>На сайт добавлена учётная запись администратора сайта.'
					.'<p style="color:#999">Можно добавить ещё администраторов сайта'
					.'<p><a href=/'.$lang.'/admin>Перейти в административную часть сайта</a>';
				}else if($lang=='uk'){
					echo '<p>На сайт додано обліковий запис адміністратора сайту.'
					.'<p style="color:#999">Можна додати ще адміністраторів сайту'
					.'<p><a href=/'.$lang.'/admin>Перейти до адміністративної частини сайту</a>';
				}else{
					echo '<p>The site administrator account has been added to the site.'
					.'<p style="color:#999">You can add more site administrators'
					.'<p><a href=/'.$lang.'/admin>Go to the administrative part of the site</a>';
				}
				echo '<div style="color: #777;text-align: right;">(login and password "1" — to block search engines until completion of the site development)</div>';
			}else{
				echo '<p class=err>'.DB::info();
			};

			exeTime();
			exit;
		}

		$start=microtime(true);
		require $root.'/setup/1/class.db.php';	//получаем $Conf

		$dirSQL=$root.'/setup/1/sql.'.$Langs[0];
		if(!file_exists($dirSQL)){exit('SQL folder? First lang «'.$Langs[0].'» not in: ru, uk, en');}

							//класс DB понадобится после попытки создать базу данных её при отсутствии
		$conn=mysqli_init();			//подключимся к серверу MySQL без указания базы данных
		mysqli_real_connect($conn,$Conf['HOST'],$Conf['USER'],$Conf['PASSWORD']
			//,$Conf['NAME_BD']
		) or exit('<p class=err>'.mysqli_connect_error());	//отличие от mysqli_connect — что можно указать опции

		$sql = 'CREATE DATABASE IF NOT EXISTS '.$Conf['NAME_BD'];	//пытаемся создать базу данных при её отсутствии
		if($conn->query($sql) === TRUE){
			if($lang=='ru'){				//если достаточно прав у пользователя
				echo '<p>База данных "'.$Conf['NAME_BD'].'" создана или существовала';
			}else if($lang=='uk'){
				echo '<p>База даних "'.$Conf['NAME_BD'].'" створена або існувала';
			}else{
				echo '<p>Database "'.$Conf['NAME_BD'].'" is created or existed';
			}
		}
		else{
			exit('<p class=err>Error: '. $conn->error);
		}			
		$conn->close();

		DB::getInstance();	//Подключаемся к базе данных
		$s=showTables();
		if($s){
			echo $s;

			if(empty($tableKeys)){
				$L=array('en'=>'create table indexes',
					'uk'=>'створити індекси таблиць',
					'ru'=>'создать индексы таблиц');
				echo
				'<h2>'.$L_step[$lang].' 2: <a onclick="'
					.'window.lang=\'setup\';return ajx(event,\'CMS\',\'addTableKeys\',document.forms[\'setup\'].nextSibling)'
					.'">'.$L[$lang].'</a></h2>';
			}else{
				if(file_exists($root.'/1/conf.php')){
					$L=array('en'=>'add site administrator',
						'uk'=>'додати адміністратора сайту',
						'ru'=>'добавить администратора сайта');
					echo
					'<h2>'.$L_step[$lang].' 4: '.$L[$lang].'</h2>';
					require $root.'/setup/1/addPerson.php';

					$q='select count(*) as c from cat where parent=-9 and final=-120';
					$row=DB::f(DB::q($q));
					if($row['c']>0){
						$L=array('en'=>'Administrators',
							'uk'=>'Адміністраторів',
							'ru'=>'Администраторов');
						echo
						'<div><p>'.$L[$lang];
						$L=array('en'=>'Go to the administrative part of the site',
							'uk'=>'Перейти до адміністративної частини сайту',
							'ru'=>'Перейти в административную часть сайта');
						echo
						': '.$row['c'].'<div><a href="/'.$lang.'/admin">'.$L[$lang].'</a></div>'
						.'<div style="color: #777;text-align: right;">(login and password "1" — to block search engines until completion of the site development)</div>'
						.'</div>';
					}
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

		$L=array('en'=>'Create Table Indexes',
			'uk'=>'Створити індекси таблиць',
			'ru'=>'Создать индексы таблиц');
		echo '<h2>'.$L_step[$lang].' 2: <a onclick="'
				.'window.lang=`setup`;return ajx(event,\'CMS\',\'addTableKeys\',document.forms[\'setup\'].nextSibling)'
			.'">'.$L[$lang].'</a></h2>';

		exeTime();
	}
	exit;
}

require $root.'/setup/1/p.php';	//page

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