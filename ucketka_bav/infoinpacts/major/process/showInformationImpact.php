<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>дополнительная информация</title>
		<?php
		//получаем корневую директорию сайта
		$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
		?>
		<link rel="stylesheet" type="text/css" href="/<?= $array_directory[1] ?>/css/style_index.css"></link>
			<style type="text/css">
				html, body { height: 100%; margin: 0; }
				.textHeadTable {
					font-size: 12px; 
					font-family: 'Times New Roman', serif;
				}
				.textTable {
					font-size: 11px; 
					font-family: 'Times New Roman', serif;					
					text-align: center;
				}
			</style>	
	</head>
	<body>
<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*	скрипт вывода на страницу информации о текущих компьютерных воздействиях:	*/
						/*	- неразобранных воздействиях												*/
						/*	- ненайденном сетевом трафике												*/
						/*	- неподготовленных письмах													*/
						/*	скрипт получает данные от класса BlocShortInformation						*/
						/*												v.0.1 11.02.2015	 			*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//подключаем config
require_once ($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/config/config.php");

session_start();

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

if($_GET['stringId']){
	$BlockShortInformationTable = new BlockShortInformationTable;
	$BlockShortInformationTable->showInformationTable($_GET['stringId']);
	}
?>
	</body>
</html>