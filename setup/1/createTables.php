<?php

//url
		$q='CREATE TABLE IF NOT EXISTS `url` (
			  `id` int(11) NOT NULL,`t` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()';
			foreach($Langs as $i=>$v){
				$v=trim($v);
				if(strlen($v)!=2){exit('<p>Lang code «'.$v.'» must be two characters [a-z]');}
				if($i==0){
					$q.='
					,`text` text NOT NULL
					,`meta` text NOT NULL
					,`url` varchar(255) DEFAULT NULL';
				}else{
					$q.='
					,`text_'.$v.'` text NOT NULL
					,`meta_'.$v.'` text NOT NULL
					,`url_'.$v.'` varchar(255) DEFAULT NULL';
				}
			}
			
		$q.=') COMMENT="Description, META and friendly URL"';
		if(DB::q($q)){echo '<p>Table "url" created or exists';}else{exit('<p>Error creating table "url"');}
		$q='select count(*) as c from url';
		$row=DB::f(DB::q($q));
		if($row['c']){
			exit('<p class=err>Table "url" has records: '.$row['c']);
		}

//person
		$q='CREATE TABLE IF NOT EXISTS `person` (
			  `id` int(11) NOT NULL,
			  `mail` varchar(50) NOT NULL,
			  `psw` text NOT NULL,
			  `new` text NOT NULL,
			  `captcha` tinyint(1) NOT NULL
			)';
		if(DB::q($q)){echo '<p>Table "person" created or exists';}else{exit('<p>Error creating table "person"');}
		$q='select count(*) as c from person';
		$row=DB::f(DB::q($q));
		if($row['c']){
			exit('<p class=err>Table "person" has records: '.$row['c']);
		}

//r
		$q='CREATE TABLE IF NOT EXISTS `r` (
			  `r` int(11) NOT NULL COMMENT "object",
			  `a` int(11) NOT NULL COMMENT "library",
			  `b` int(11) NOT NULL COMMENT "library option",
			  `c` int(11) NOT NULL COMMENT "int value or library suboption"';
				foreach($Langs as $i=>$v){
					$v=trim($v);
					if($i==0){
						$q.=',`s`';
						$q.=' varchar(255) NOT NULL COMMENT "char value"';

					}else{
						$q.=',`s_'.$v.'`';
						$q.=' text NOT NULL COMMENT "text value"';
					}
				}
			  $q.='
			) COMMENT="Relations"';
		if(DB::q($q)){echo '<p>Table "r" created or exists';}else{exit('<p>Error creating table "r"');}
		$q='select count(*) as c from r';
		$row=DB::f(DB::q($q));
		if($row['c']){
			exit('<p class=err>Table "r" has records: '.$row['c']);
		}

//files
		$q='CREATE TABLE IF NOT EXISTS `files` (
			`id` int(11) NOT NULL,
			`cat` int(11) NOT NULL,
			`name` varchar(255) NOT NULL';
				foreach($Langs as $i=>$v){
					$v=trim($v);
					if($i==0){
						$q.=',`text` text NOT NULL';
					}else{
						$q.=',`text_'.$v.'` text NOT NULL';
					}
				}
			  $q.=',
			  `ord` int(11) NOT NULL DEFAULT "1",
			  `v` tinyint(1) NOT NULL DEFAULT "1",
			  `d` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  `note` text NOT NULL,
			  `mes` int(11) NOT NULL COMMENT "message id"
			) AUTO_INCREMENT=1';
		if(DB::q($q)){echo '<p>Table "files" created or exists';}else{exit('<p>Error creating table "files"');}
		$q='select count(*) as c from files';
		$row=DB::f(DB::q($q));
		if($row['c']){
			exit('<p class=err>Table "files" has records: '.$row['c']);
		}

$q='SHOW FUNCTION status where db="'.$Conf['NAME_BD'].'" and name="transliterate_func"';
$r=DB::q($q);
$n=DB::num_rows($r);
//echo'<p>FUNCTION status = '.$n;
if($n>0){
	echo'<p>"transliterate_func" exists';
}else{
	$q='CREATE FUNCTION `transliterate_func`(`original` VARCHAR(512)) RETURNS varchar(512) CHARSET utf8
	BEGIN
	 
	DECLARE translit VARCHAR(512) DEFAULT "";
	DECLARE len INT(3) DEFAULT 0;
	DECLARE pos INT(3) DEFAULT 1;
	DECLARE letter CHAR(4);
	 
	SET original = TRIM(LOWER(original));
	SET len = CHAR_LENGTH(original);
	 
	WHILE (pos <= len) DO
	SET letter = SUBSTRING(original, pos, 1);
	 
	CASE TRUE
	 
	WHEN letter IN("á","à","â","ä","å","ā","ą","ă","а","а") THEN SET letter = "a";
	WHEN letter IN("č","ć","ç","ć") THEN SET letter = "c";
	WHEN letter IN("ď","đ","д","д") THEN SET letter = "d";
	WHEN letter IN("é","ě","ë","è","ê","ē","ę","е","е") THEN SET letter = "e";
	WHEN letter IN("ģ","ğ") THEN SET letter = "g";
	WHEN letter IN("í","î","ï","ī","î","и","і") THEN SET letter = "i";
	WHEN letter IN("ķ") THEN SET letter = "k";
	WHEN letter IN("ľ","ĺ","ļ","ł") THEN SET letter = "l";
	WHEN letter IN("ň","ņ","ń","ñ") THEN SET letter = "n";
	WHEN letter IN("ó","ö","ø","õ","ô","ő","ơ","о","о") THEN SET letter = "o";
	WHEN letter IN("ŕ","ř","р","р") THEN SET letter = "r";
	WHEN letter IN("š","ś","ș","ş","с","с") THEN SET letter = "s";
	WHEN letter IN("ť","ț") THEN SET letter = "t";
	WHEN letter IN("ú","ů","ü","ù","û","ū","ű","ư") THEN SET letter = "u";
	WHEN letter IN("ý","у","у") THEN SET letter = "y";
	WHEN letter IN("ž","ź","ż") THEN SET letter = "z";
	 
	WHEN letter = "б" THEN SET letter = "b";
	WHEN letter = "в" THEN SET letter = "v";
	WHEN letter = "г" THEN SET letter = "g";
	WHEN letter = "д" THEN SET letter = "d";
	WHEN letter = "ж" THEN SET letter = "zh";
	WHEN letter = "з" THEN SET letter = "z";
	WHEN letter = "и" THEN SET letter = "i";
	WHEN letter = "й" THEN SET letter = "i";
	WHEN letter = "к" THEN SET letter = "k";
	WHEN letter = "л" THEN SET letter = "l";
	WHEN letter = "м" THEN SET letter = "m";
	WHEN letter = "н" THEN SET letter = "n";
	WHEN letter = "п" THEN SET letter = "p";
	WHEN letter = "т" THEN SET letter = "t";
	WHEN letter = "ф" THEN SET letter = "f";
	WHEN letter = "х" THEN SET letter = "ch";
	WHEN letter = "ц" THEN SET letter = "c";
	WHEN letter = "ч" THEN SET letter = "ch";
	WHEN letter = "ш" THEN SET letter = "sh";
	WHEN letter = "щ" THEN SET letter = "shch";
	WHEN letter = "ъ" THEN SET letter = "";
	WHEN letter = "ь" THEN SET letter = "";
	WHEN letter = "ы" THEN SET letter = "y";
	WHEN letter = "э" THEN SET letter = "e";
	WHEN letter = "ю" THEN SET letter = "ju";
	WHEN letter = "я" THEN SET letter = "ja";
	 
	WHEN letter IN ("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","0","1","2","3","4","5","6","7","8","9")
	THEN SET letter = letter;
	 
	ELSE
	SET letter = "-";
	 
	END CASE;
	 
	SET translit = CONCAT(translit, letter);
	SET pos = pos + 1;
	END WHILE;
	 
	WHILE (translit REGEXP "-{2,}") DO
	SET translit = REPLACE(translit, "--", "-");
	END WHILE;
	 
	RETURN TRIM(BOTH "-" FROM translit);
	
	END;
	';
	if(DB::q($q)){echo '<p>Database function `transliterate_func` created';}else{echo '<p>Error creating function `transliterate_func`';}
}
