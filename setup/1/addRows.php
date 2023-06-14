<?php
echo
'<ol>';
	$Cat=array();	//id добавленных записей `cat`, $dirSQL в index.php — путь к папке sql для основного языка
	foreach(glob($dirSQL.'/*') as $file){		//цикл в алфавитном порядке (если не указан флаг GLOB_NOSORT)
		$s=file_get_contents($file);
		if(!$s){exit('<li class=err>Error file get contents «'.$file.'»');}

		$t=substr($file,strrpos($file,'/')+1);	//например "cat.-10.adt"
		$A=explode('.',$t);
		$table=$A[0];	//имя таблицы

		if($table!='files'){
			$id=$A[1];$col=$A[2];
			$n=mb_strpos($s,"\n");	//длина первой строки
			$t=mb_substr($s,0,$n);	//первая строка
			$s=mb_substr($s,$n);	//остальные строки
			$B=explode("\t",$t);	//[parent,v,ord,name,url,img] (по "name" обязательные значения — если нет файла "cat.")

		}
		if($table=='cat'){			//первые по алфавиту
			$q='insert into cat set '
			.'id='.$id.','
			.'parent='.$B[0]
			.',v='.$B[1]
			.',ord='.$B[2]
			.',name="'.DB::esc($B[3]).'",'
			.(empty($B[5])?'':'img="'.DB::esc($B[5]).'",')	//img
			.$col.'="'.DB::esc($s).'"';		//adt или text
			if(DB::q($q)){
				echo '<li>Added: '.$col.' cat.id='.$id;
				$Cat[]=$id;
			}else{exit('<li class=err>Error add: '.$col.' '.$table.'.id='.$id.'</ul>');}

			if(!empty($B[4])){	//url
				$q='insert into url set '
				.'id='.$id.','
				.'url="'.DB::esc($B[4]).'"';
				if(!DB::q($q)){exit('<li class=err>'.DB::info().'</ul>');}
			}
		}else if($table=='files'){
			$q='insert into files (id,cat,name,text,ord,v) VALUES ';
			$B=explode("\n",$s);$Q=array();
			foreach($B as $i=>$v){
				if($i==0 || trim($v)==''){continue;}
				$C=explode("\t",$v);
				foreach($C as $i1=>$v1){$C[$i1]=DB::esc($v1);}
				$Q[]='("'.$C[0].'","'.$C[1].'","'.$C[2].'","'.$C[3].'","'.$C[4].'","'.$C[5].'")';
			}
			$q.=implode(',',$Q);
			//echo $q;
			if(DB::q($q)){
				echo '<li>Added table files';
			}else{exit('<li class=err>Error add table files</ul>');}

		}else{		//таблица не `cat`
			if(!in_array($id,$Cat)){		//если ещё нет записи `cat`
				$q='insert into cat set '
				.'id='.$id.','
				.'parent='.$B[0]
				.',v='.$B[1]
				.',ord='.$B[2]
				.',name="'.DB::esc($B[3]).'"';
				if(!DB::q($q)){exit('<li class=err>'.DB::info().'</ul>');}
				$Cat[]=$id;
			}
			$q='select id from '.$table.' where id='.$id;
			$r=DB::q($q);
			if(DB::num_rows($r)){
				$q='update '.$table.' set '.$col.'="'.DB::esc($s).'"'
				.($table=='url' && !empty($B[4])?',url="'.DB::esc($B[4]).'"':'')
				.' where id='.$id;
				if(DB::q($q)){
					echo '<li>Updated: '.$col.' '.$table.'.id='.$id;
				}else{exit('<li class=err>Error update: '.$col.' '.$table.'.id='.$id.'</ul>');}
			}else{
				$q='insert into '.$table.' set id='.$id.','.$col.'="'.DB::esc($s).'"';
				if(!empty($B[4])){$q.=',url="'.DB::esc($B[4]).'"';}
				if(DB::q($q)){
					echo '<li>Added: '.$col.' '.$table.'.id='.$id;
				}else{exit('<li class=err>Error add: '.$col.' '.$table.'.id='.$id.'</ul>');}
			}
		}
	}

	require $root.'/setup/1/addLangsTexts.php';

	$L=array(
		'ru'=>array(
			-100=>'Внутренние страницы',
			-115=>'Посетитель',
			-120=>'Администратор',
			-90=>'Личный кабинет',
			-91=>'Общая информация',
			-92=>'Контакты',
			-93=>'Настройки',
			-113=>'Псевдоним',
			-114=>'Обо мне',
			-111=>'Телефон'
		),
		'uk'=>array(
			-100=>'Внутрішні сторінки',
			-115=>'Відвідувач',
			-120=>'Адміністратор',
			-90=>'Приватний кабінет',
			-91=>'Загальна інформація',
			-92=>'Контакти',
			-93=>'Лаштунки',
			-113=>'Псевдонім',
			-114=>'Про мене',
			-111=>'Телефон'
		),
		'en'=>array(
			-100=>'Inner pages',
			-115=>'User',
			-120=>'Admin',
			-90=>'Cabinet',
			-91=>'Common',
			-92=>'Contacts',
			-93=>'Set',
			-113=>'Nic',
			-114=>'About',
			-111=>'Phone'
		)
	);
	if(!isset($L[$Langs[0]])){$L[$Langs[0]]=$L['en'];}
	foreach($Langs as $i=>$v){if(!isset($L[$v])){$L[$v]=$L['en'];}}

	$q='INSERT INTO `cat` '		//страницы без текстов
		.'(`id`,	`parent`,	`v`,	`ord`,	`name`)
		VALUES '	
		.'(-100,	0,	0,	10,	"'.$L[$Langs[0]][-100].'")'	
		.',(-115,	-10,	0,	1,	"'.$L[$Langs[0]][-115].'")'		//id=cat.final персоны "Посетитель"
		.',(-120,	-10,	0,	2,	"'.$L[$Langs[0]][-120].'")'	//id=cat.final персоны "Администратор"
	
		.',(-90,	-100,	0,	130,	"'.$L[$Langs[0]][-90].'")'
		.',(-91,	-90,	0,	1,	"'.$L[$Langs[0]][-91].'")'
		.',(-92,	-90,	0,	2,	"'.$L[$Langs[0]][-92].'")'
		.',(-93,	-90,	0,	3,	"'.$L[$Langs[0]][-93].'")'

		.',(-113,	-91,	0,	1,	"'.$L[$Langs[0]][-113].'")'		//v!=1, ещё могут быть произвольные поля с v=1
		.',(-114,	-91,	0,	2,	"'.$L[$Langs[0]][-114].'")'

		.',(-111,	-92,	0,	1,	"'.$L[$Langs[0]][-111].'")'		//v!=1, ещё могут быть произвольные поля с v=1

		.',(-55,	-100,	0,	131,	"CSS")'			//Для указания в таблице `r` файлов CSS странице
										//r=-55,a=cat.id,s=(имя файла перед .css),
										//b=0 для страницы или -1 для вложенных страниц,
										//с=очерёдность со знаком минус.

		.',(-56,	-100,	0,	132,	"JS")'			//Аналогично для JS
	;
	if(DB::q($q)){
		echo '<li>To table `cat` added records without texts: '.DB::affected_rows();
	}else{exit('<li class=err>'.DB::info());}

	if(isset($Langs[1])){	//несколько языков: добавим имена страниц на этих языках к вставленным записям без текстов
		foreach($L['en'] as $i=>$v){
			$A=array();
			foreach($Langs as $i1=>$v1){
				if($i1==0){continue;}	//основной язык
				$A[]='name_'.$v1.'="'.$L[$v1][$i].'"';
			}
			$q='update cat set '.implode(',',$A).' where id='.$i;
			echo '<li>'.$q;
			DB::q($q);
		}
	}

	$q='INSERT INTO `r` (`r`, `a`, `b`, `c`, `s`)
		VALUES '	
		.'(-56, -30, 0, 1, "ajx.js")'
		.',(-56, -30, 0, 2, "f.js")'
		.',(-56, -30, 0, 4, "header.js")'	//авторизация…
		.',(-56, -9, 0, 1, "reg.js")'		//регистрация
		.',(-56, -9, -1, 1, "cabinet.js")'	//кабинет: внутри "регистрации"

		.',(-55, -30, 0, 1, "1.css")'
		.',(-55, -30, 0, 2, "ajx.css")'
		.',(-55, -30, 0, 4, "header.css")'	//шапка
		.',(-55, -30, 0, 5, "headerLang.css")'	//переключение языка в шапке
		.',(-55, -31, 0, 1, "footer.css")'

		.',(-55, -9, -1, 1, "cabinet.css")'	//кабинет: внутри "регистрации"

		.',(-55, 1, 0, 1, "pubs.css")'		//статьи
	;
	if(DB::q($q)){
		echo '<li>To table `r` added records (CSS and JS files connection): '.DB::affected_rows();
	}else{exit('<li class=err>'.DB::info());}
echo
'</ol>';

$q='select count(*) as c,parent from cat where parent!=0 group by parent';
$r=DB::q($q);
if(DB::num_rows($r)){
	echo '<ul>Update table `cat`:';
	while($row=DB::f($r)){
		$q='update cat set c='.$row['c'].' where id='.$row['parent'];
		if(DB::q($q)){
			echo '<li>id='.$row['parent'].': inner tables: '.$row['c'];
		}else{$E=error_get_last();echo '<p class=err>Error set count inner tables: '.$E['message'];}
	}
	echo '</ul>';
}