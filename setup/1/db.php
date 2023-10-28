<?php
$conn=mysqli_init();			//подключимся к серверу MySQL без указания базы данных
mysqli_real_connect($conn,$Conf['HOST'],$Conf['USER'],$Conf['PASSWORD']) or exit('<p class=err>'.mysqli_connect_error());

$sql = 'CREATE DATABASE IF NOT EXISTS '.$Conf['DB'];	//пытаемся создать базу данных в случае отсутствии
if($conn->query($sql) === TRUE){				//если достаточно прав у пользователя
	if($lang=='ru'){
		echo '<p>База данных "'.$Conf['DB'].'" создана или существовала';
	}else if($lang=='uk'){
		echo '<p>База даних "'.$Conf['DB'].'" створена або існувала';
	}else{
		echo '<p>Database "'.$Conf['DB'].'" is created or existed';
	}
}else{
	exit('<p class=err>Error: '. $conn->error);
}			
$conn->close();