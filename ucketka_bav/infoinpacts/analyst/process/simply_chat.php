<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		страница простейшего чата между аналитиками и оперативными дежурными		*/
						/*																			v.0.1 14.01.2014		*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));

//подключаем основную, для дежурного страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/analyst/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

?>

<!-- поле вывода формы -->
		<div style="position: relative; top: 5px; left: 0px; z-index: 10; width: 960px; display: table; vertical-align: middle; border-radius: 6px; background: #F0FFFF; box-shadow: inset 0 0 10px 0px #B7DCF7;">	
		<?php
		//класс вывода формы поиска информации
		$SimplyChat = new SimplyChat($checkAuthorization->userLogin);
		?>	
		</div>
		<div style="position: relative; top: 40px;">
			<hr style="width: 60%">
		</div>
<!-- поле вывода основной информации -->
		<?php
		//если была нажата кнопка отправить		
		if(isset($_POST['send'])){
			$SimplyChat->sendUserMessage();
			}
		?>
		
<!-- вывод всей имеющейся в БД информации -->		
		<div style="position: relative; top: 70px; left: 20px; z-index: 10; width: 960px; display: table; vertical-align: middle;	border-radius: 3px; ">
		<?php
		$SimplyChat->showUserMessage($array_color_message_chat, $simply_chat_time_delete);
		?>		
		</div><br><br><br><br>

<?php
include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>	