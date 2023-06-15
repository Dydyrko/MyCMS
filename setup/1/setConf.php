<?php	//выполнение формы конфигурации: в форме после изменения хоть одного значения
	// — иначе выполняется аякс с пустым POST['CMS'] (нажатием на "Применить" или [Enter])

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