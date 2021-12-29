<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Авторизация</title>
<!-- основной фон страницы -->
			<style>
				body {
   					background: -webkit-linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent);
   					background: -moz-linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent);
   					background: -o-linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent);
   					background: -ms-linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent);
					background: linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent); 
					}
			</style>		
	</head>
	<body>
<?php
unset($userSessid);
session_start();

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 

//подключаем config
require ($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/config/config.php");

//объект класса авторизации
$newAuthorization = new Authorization();

//Кнопка выхода
if(isset($_GET['Quit'])){
	$newAuthorization->exitUserSession();	
	}

switch($_SESSION['userSessid']['userId']){
	case 10:
		require($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/major/login.php");
	break;
	case 20:
		require($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/worker/login.php");
	break;
	case 30:
		require($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/analyst/login.php");
	break;
	case 40:
		require($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/admin/login.php");
	break;
	}		
?>
	</body>
</html>