<?php
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
					.'<p style="color:#999">Можно добавить ещё администраторов сайта — нажать кнопку формы «Применить»'
					.'<p><a href=/admin>Перейти в административную часть сайта</a>';	//href=/'.$lang.'/admin сайт может этого языка не содержать
				}else if($lang=='uk'){
					echo '<p>На сайт додано обліковий запис адміністратора сайту.'
					.'<p style="color:#999">Можна додати ще адміністраторів сайту — натиснути кнопку форми «Застосувати»'
					.'<p><a href=/admin>Перейти до адміністративної частини сайту</a>';
				}else{
					echo '<p>The site administrator account has been added to the site.'
					.'<p style="color:#999">You can add more site administrators — apply form'
					.'<p><a href=/admin>Go to the administrative part of the site</a>';
				}
				echo $finalComment; //'<div style="color: #777;text-align: right;">(if server ip not "127.0.0.1" — login and password "1" — to block search engines until completion of the site development: robots.txt filesize will become more than 30 bytes)</div>';
			}else{
				echo '<p class=err>'.DB::info();
			};

			exeTime();
			exit;