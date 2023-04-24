<?php

			//Индексы
			$q='ALTER TABLE `cat`
			  ADD PRIMARY KEY (`id`),
			  ADD KEY `parent` (`parent`,`final`,`vimg`,`ord`,`v`)';
			if(DB::q($q)){echo '<p>Indexes table "cat" created';}else{exit('<p>Error creating table indexes "cat"');}

			$q='ALTER TABLE `cat`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1';
			if(DB::q($q)){echo '<p>AUTO_INCREMENT table "cat"';}else{exit('<p>Error AUTO_INCREMENT table "cat"');}
	
			$q='ALTER TABLE `url`
			  ADD PRIMARY KEY (`id`)';
					foreach($Langs as $i=>$v){
						$v=trim($v);
						if($i==0){
							$q.=',ADD UNIQUE KEY `url` (`url`)';
						}else{
							$q.=',ADD UNIQUE KEY `url_'.$v.'` (`url_'.$v.'`)';
						}
					}
			if(DB::q($q)){echo '<p>Indexes table "url" created';}else{exit('<p>Error creating table indexes "url"');}
	
			$q='ALTER TABLE `person`
			  ADD PRIMARY KEY (`id`),
			  ADD UNIQUE KEY `mail` (`mail`)';
			if(DB::q($q)){echo '<p>Indexes table "person" created';}else{exit('<p>Error creating table indexes "person"');}
	
			$q='ALTER TABLE `r`
			  ADD PRIMARY KEY (`r`,`a`,`b`,`c`),
			  ADD KEY `s` (`s`)';
			if(DB::q($q)){echo '<p>Indexes table "r" created';}else{exit('<p>Error creating table indexes "r"');}
	
			$q='ALTER TABLE `files`
			  ADD PRIMARY KEY (`id`),
			  ADD KEY `cat` (`cat`)';
			if(DB::q($q)){echo '<p>Indexes table "files" created';}else{exit('<p>Error creating table indexes "files"');}

			$q='ALTER TABLE `files` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1';
			if(DB::q($q)){echo '<p>AUTO_INCREMENT table "files"';}else{exit('<p>Error AUTO_INCREMENT table "files"');}