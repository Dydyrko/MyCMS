<?php
$q='CREATE TABLE IF NOT EXISTS `cat` (
	`id` int(11) NOT NULL,';

	foreach($Langs as $i=>$v){
		$v=trim($v);
		if(strlen($v)!=2){exit('<p>Lang code «'.$v.'» must be two characters [a-z]');}
		if($i==0){
			$q.='
			`name` text NOT NULL,
			`adt` text NOT NULL,';
		}else{
			$q.='
			`name_'.$v.'` text NOT NULL,
			`adt_'.$v.'` text NOT NULL,';
		}
	}

	$q.='
	  `note` text NOT NULL,
	  `parent` int(11) NOT NULL,
	  `final` tinyint(1) NOT NULL,
	  `c` int(11) NOT NULL,
	  `img` text NOT NULL,
	  `vimg` tinyint(1) NOT NULL DEFAULT "0" COMMENT "Show img not in list only",
	  `ord` int(11) NOT NULL,
	  `v` smallint(6) NOT NULL DEFAULT "1" COMMENT "Public (visible on site): 0=no, 1=yes, 2=special status",'

	.'`d0` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT "Create date",
	  `d` datetime NULL COMMENT "Page date",
	  `d1` datetime NULL COMMENT "event date",
	  `owner` int(11) NOT NULL COMMENT ""
	) AUTO_INCREMENT=1';
if(DB::q($q)){
	echo '<p>Table "cat" created or exists';}else{exit('<p>Error creating table "cat"');
}

$q='select count(*) as c from cat';
$row=DB::f(DB::q($q));
if($row['c']){
	exit('Table "cat" has records: '.$row['c']);
}
