<?php
$conn=mysqli_init();			//подключимся к серверу MySQL без указания базы данных
mysqli_real_connect($conn,$Conf['HOST'],$Conf['USER'],$Conf['PASSWORD']
	//,$Conf['NAME_BD']
) or exit('<p class=err>'.mysqli_connect_error());	//отличие от mysqli_connect — что можно указать опции

$sql = 'CREATE DATABASE IF NOT EXISTS '.$Conf['NAME_BD'];	//пытаемся создать базу данных в случае отсутствии
if($conn->query($sql) === TRUE){				//если достаточно прав у пользователя
	if($lang=='ru'){
		echo '<p>База данных "'.$Conf['NAME_BD'].'" создана или существовала';
	}else if($lang=='uk'){
		echo '<p>База даних "'.$Conf['NAME_BD'].'" створена або існувала';
	}else{
		echo '<p>Database "'.$Conf['NAME_BD'].'" is created or existed';
	}
}else{
	exit('<p class=err>Error: '. $conn->error);
}			
$conn->close();