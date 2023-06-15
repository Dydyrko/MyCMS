<?php
			$start=microtime(true);
			$file=$root.'/setup/cms.zip';
			if(!file_exists($file)){exit($file.' not exists');}
			$zip=new ZipArchive;
			if($zip->open($file)===TRUE){
				$zip->extractTo($root.'/');
				for($i=0;$i<$zip->numFiles;$i++){	//сохранить даты файлов
					touch($root.'/'.$zip->statIndex($i)['name'], $zip->statIndex($i)['mtime']);
				}
				echo '<p>cms.zip unpacked</p>';
				$zip->close();
				$zip2=new ZipArchive;
				$file=$root.'/setup/help.zip';
				if(!file_exists($file)){exit($file.' not exists');}
				if($zip2->open($file)===TRUE){
					$zip2->extractTo($root.'/help/');
					for($i=0;$i<$zip2->numFiles;$i++){	//сохранить даты файлов
						touch($root.'/help/'.$zip2->statIndex($i)['name'], $zip2->statIndex($i)['mtime']);
					}
					$zip2->close();
					echo '<p>help.zip unpacked</p>';
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
					require $root.'/setup/1/addPersonForm.php';
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