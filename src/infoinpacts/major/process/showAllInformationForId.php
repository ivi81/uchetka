<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Информация по IP-адресу <?php echo $_GET['ipDst']; ?></title>
		<?php 
		//получаем корневую директорию сайта
		$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
		?>
		<link rel="stylesheet" type="text/css" href="/<?= $array_directory[1] ?>/css/style_index.css"></link>
			<style type="text/css">
				html, body { height: 100%; margin: 0; }
			</style>	
	</head>
	<body>
<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		скрипт вывода на страницу всех компьютерных воздействий в 			*/
						/*		которых фигурирует перечисленные номера компьютерных воздействий		*/
						/*													v.0.2 18.08.2014		*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

session_start();
//подключаем config
require_once ($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/config/config.php");

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

$ShowAllInformation = new ShowAllInformationIpAnalyst;

?>
<!-- основная цветовая подложка -->
		<div class="allInfoArea">
			<div style="position: relative; top: 10px; left: 10px; z-index: 10; width: 980px; min-height: 600px; display: table; vertical-align: middle; border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">	
			<br>
			<?php
			//вывод информации
			$ShowAllInformation->showInformation();
			?>
			</div><br>
		</div><br>
	</body>
</html>