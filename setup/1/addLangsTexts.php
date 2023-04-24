<?php
$c=count($Langs);
if($c>1){
	for($i=1;$i<$c;$i++){
		$dirSQL=$root.'/setup/1/sql.'.$Langs[$i];
		foreach(glob($dirSQL.'/*') as $file){		//цикл в алфавитном порядке (если не указан флаг GLOB_NOSORT)
			$t=substr($file,strrpos($file,'/')+1);	//например "cat.-10.adt"
			$A=explode('.',$t);
			$table=$A[0];	//имя таблицы
			if($table=='files'){continue;}

			$s=file_get_contents($file);
			if(!$s){exit('<li class=err>Error file get contents «'.$file.'»');}

			$id=$A[1];
			$col=$A[2];	//adt или text
			$n=mb_strpos($s,"\n");	//длина первой строки
			$t=mb_substr($s,0,$n);	//первая строка
			$s=mb_substr($s,$n);	//остальные строки
			$B=explode("\t",$t);	//[parent,v,ord,name,url,img] (по "name" обязательные значения — если нет файла "cat.")
	
			$q='update '.$table.' set '.$col.'_'.$Langs[$i].'="'.DB::esc($s).'"'
			.($table=='url' && !empty($B[4])?',url_'.$Langs[$i].'="'.DB::esc($B[4]).'"':'')
			.' where id='.$id;
			if(DB::q($q)){
				echo '<li>Updated: '.$col.' '.$table.'.id='.$id.' for '.$Langs[$i];
			}else{exit('<li class=err>Error update: '.$col.' '.$table.'.id='.$id.' for '.$Langs[$i].'</ul>');}

			if(!empty($B[3])){
				$q='update cat set name_'.$Langs[$i].'="'.$B[3].'" where id='.$id;DB::q($q);
			}
		}
	}
}