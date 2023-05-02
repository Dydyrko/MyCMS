<?php

$L=array('en'=>'CMS setup','uk'=>'Встановлення CMS','ru'=>'Установка CMS');
echo
'<!doctype html>'
.'<title>'.$L[$lang].'</title>'	//Установка CMS
//.'<link type="image/png" rel="shortcut icon" href="/favicon.png" />'
.'<script src=js/ajx.js></script>'
.'<style>
	a{color:#00f;cursor:pointer}
	a:hover{color:#f30;}
	.log{display:inline-block;max-width: calc(100% - 15px);max-height:300px;overflow:auto;border:inset;padding:0 5px}
	.err{color:red}
</style>'
.'<body>'
.'<div style="max-width:800px;margin:auto">'
	.'<form style=float:right><select name=lang onchange="form.submit()">'
		.'<option'.($lang=='en'?' selected':'').'>en'
		.'<option'.($lang=='uk'?' selected':'').'>uk'
		.'<option'.($lang=='ru'?' selected':'').'>ru'
	.'</select></form>'
	.'<h1>'.$L[$lang].'</h1>'	//Установка CMS
	.'<h2>';
		$file=$_SERVER["DOCUMENT_ROOT"].'/setup/1/conf.php';
		if(file_exists($file)){
			require $file;
		}
		if(empty($Conf)){
			$Conf=[
				'TITLE'=>'Site_name',
				'Langs'=>['uk','ru','en'],
				//'adminLang'=>'en',
				'geoLoc'=>'',
				'HOST'=>'localhost',
				'NAME_BD'=>'',
				'USER'=>'root',
				'PASSWORD'=>'root'
			];
			$L=array('en'=>'Site Configuration','uk'=>'Конфігурація сайту','ru'=>'Конфигурация сайта');
			echo $L[$lang];	//'Конфигурация сайта';
		}else{
			$L=array('en'=>'Your site configuration','uk'=>'Ваша конфігурація сайту','ru'=>'Ваша конфигурация сайта');
			echo $L[$lang];	//'Ваша конфигурация сайта';
		}
	echo
	'</h2>'
	.'<form name=setup onsubmit="'
		.'window.lang=\'setup\';'		//корневая папка с index.php, обрабатывающим GET[ajx]
		.'var e=elements,b=0,i=0;
			for(i;i<e.length;i++){
				if(e[i].name){
					if(typeof(e[i].dataset.h)!=\'undefined\' && e[i].dataset.h!=e[i].value.hashCode()){
						b=1;
						if(e[i].type==\'checkbox\'){e[i].dataset.h=e[i].value.hashCode();}
						break
					}
				}
			}
			if(!b){return ajx(event,\'CMS\',0,nextSibling)}'
		.'return ajxFormData(event,this,0,nextSibling)'
		.'"'
		.' onclick="
			var e=event.target;
			if(typeof(e.value)==\'undefined\'){e.value=\'\';}
			if(e.type==\'checkbox\'){e.dataset.h=e.value.hashCode();e.value=e.checked;}else
			if(e.name){e.dataset.h=e.value.hashCode()}
		"'
	.'>'
		.'<table>';
		if($lang=='ru'){
			$T=array(
				'TITLE'=>'Имя сайта',
				'Langs'=>'Двух-буквенные коды языков через запятую, первый язык — основной',
				//'adminLang'=>'Код языка административной панели, если пусто — выбранный язык сайта',
				'geoLoc'=>'Использовать геолокацию',
				'HOST'=>'Сервер MySQL: «localhost» или как указано хостингом сервера',
				'USER'=>'Пользователь базы данных MySQL: можеть быть «root» для локального сервера (например, «OpenServer»)',
				'PASSWORD'=>'Пароль пользователя базы данных MySQL: можеть быть пустым для «root»',
				'NAME_BD'=>'Имя базы данных MySQL: можеть быть создана при достаточных правах пользователя базы данных, иначе — должна существовать.'
			);
		}else if($lang=='uk'){
			$T = array (
				'TITLE'=>'Ім\'я сайту',
				'Langs'=>'Двобуквенні коди мов через кому, перша мова - основна',
				//'adminLang'=>'Код мови адміністративної панелі, якщо порожньо — вибрана мова сайту',
				'geoLoc'=>'Використовувати геолокацію',
				'HOST'=>'Сервер MySQL: «localhost» або як зазначено хостингом сервера',
				'USER'=>'Користувач бази даних MySQL: може бути «root» для локального сервера (наприклад, «OpenServer»)',
				'PASSWORD'=>'Пароль користувача бази даних MySQL: може бути порожнім для «root»',
				'NAME_BD'=>'Ім\'я бази даних MySQL: може бути створена при достатніх правах користувача бази даних, інакше — має існувати.'
			);
		}else{
			$T=array(
				'TITLE'=>'Site name',
				'Langs'=>'Two-letter language codes separated by commas, first language is primary',
				//'adminLang'=>'The language code of the administrative panel, if empty — the selected language of the site',
				'geoLoc'=>'Use geolocation',
				'HOST'=>'MySQL Server: «localhost» or as specified by server host',
				'USER'=>'MySQL database user: can be «root» for local server (e.g. «OpenServer»)',
				'PASSWORD'=>'Mysql database user password: can be empty for «root»',
				'NAME_BD'=>'MySQL database name: can be created with sufficient database user rights, otherwise it must exist.'
			);

		}
		foreach($Conf as $i=>$v){
			if(!isset($T[$i])){continue;}	//'adminLang' возможно будет
			echo
			'<tr title="'.$T[$i].'"><td>'.$i.'<td><input name="'.$i.'" value="'.(is_array($v)?implode(', ',$v):$v).'"'
				.($i=='PASSWORD'?' type="password" placeholder="PASSWORD" autocomplete="off" ondblclick="(type==`password`?type=`text`:type=`password`)"':'')
				.($i=='geoLoc'?' type="checkbox"'.($v?' checked':''):'')
				.(in_array($i,array('HOST','NAME_BD','USER'))?' required placeholder="'.$i.'"':'')
			.'>';
		}
		echo '<tr><td><input type=reset style=width:100%>'
			.'<td><button style="width:100%">'.$L_apply[$lang].'</button>'	//Применить (index.php)
		.'</table>'
	.'</form>'
	.'<div></div>'
.'</div>'
.'</body>';
