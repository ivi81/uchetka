<?php

						/*+++++++++++++++++++++++++++++++*/
						/*		получаем документ	docx		*/
						/*				v.0.1 26.02.2014	 	*/
						/*+++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 

//подключаем основную, для дежурного страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/analyst/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

//объект класса редактирования конфигурационного XML файла
$writeXMLSetupUser = new WriteXMLSetupUser();
?>
<!-- поле вывода формы -->
	<div style="position: relative; top: 5px; left: 0px; z-index: 10; width: 960px; display: table; vertical-align: middle; border-radius: 6px; background: #F0FFFF; box-shadow: inset 0 0 10px 0px #B7DCF7;">	
	<?php
	$text = 'тестовый текст о пользе чая и других напитков, особенно напитка, получаемого в результате недельного брожения чайного гриба. Для начала настой чайного гриба надо слить.';
	$CreateDocx = new CreateDocx;
	//запись текста
	$CreateDocx->writeText($text);



//	$directory = $_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/docx/";
//$CreateDocx->extractZip($directory);
/*	function getDirectory($directory)
		{
		chdir($directory);
		echo $directory;
		if(!$dir = opendir($directory)){
			echo "ошибка, невозможно открыть директорию";
			}
		while($files = readdir($dir)){
			if($files != '.' && $files != '..'){
				if(is_file($files)){
					echo '<ul>'.$files.'</ul>';
					}
				if(is_dir($files)){
					echo '<li><strong>'.$files.'</strong></li>';
					getDirectory($directory.'/'.$files); 					
					}
				chdir($directory);
				}
			}
		closedir($dir);
		}*/
//	getDirectory('/Disk/www/new_project/docx');
	?>	
	</div>
