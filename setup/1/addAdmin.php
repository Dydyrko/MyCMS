<?php
$L=array('en'=>'add site administrator',
	'uk'=>'додати адміністратора сайту',
	'ru'=>'добавить администратора сайта');
echo
'<h2>'.$L_step[$lang].' 4: '.$L[$lang].'</h2>';
require $root.'/setup/1/addPersonForm.php';

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
	': '.$row['c'].'<div><a href="/admin">'.$L[$lang].'</a></div>'	//href="/'.$lang.'/admin сайт может этого языка не содержать
	.'<div style="color: #777;text-align: right;">(login and password "1" — to block search engines until completion of the site development)</div>'
	.'</div>';
}