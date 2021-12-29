<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		скрипт обработке данных вводимых аналитиком		*/
						/*								v.0.1 18.08.2014	 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));

//подключаем основную, для аналитика страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/analyst/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

?>
<!-- основная цветовая подложка -->	
<div class="majorArea">
<?php

//проверяем принял ли аналитик решение	
if(empty($_POST['solution'])){
	echo ShowMessage::showInformationError("необходимо принять решение");
	exit();
	}
if($_POST['solution'] == '1' && empty($_POST['typeKA'])){
	echo ShowMessage::showInformationError("выберите тип компьютерной атаки");
	exit();
	}


try{
			/*==================================*/	
			/*	+++ incident_analyst_tables +++ */
			/*==================================*/			

	//добавляем информацию в таблицу аналитика
	$information_analyst = $_POST['information'];

	//если аналитик задал тип компьютерной атаки, изменить его в таблице incident_chief_tables
	if($_POST['typeKA'] != ''){
		$DBO->connectionDB()->query("UPDATE `incident_chief_tables` SET `type_attack`='".intval($_POST['typeKA'])."'
								     WHERE `id`='".intval($_POST['numInt'])."'");	
		}
	$userName = $checkAuthorization->userLogin;
/*
id - порядковый номер инцидента
login_name - логин аналитика
true_false - компьютерная атака или нет и пометка - нет трафика
			
			Выпадающий список:
				1 - ложное срабатывание (false),
 				2 - компьютерная атака (true), 
				3 - сетевого трафика по указанному пути не обнаружено
 				4 - сетевой трафик утерян

count_alert_analyst - количество срабатываний
information_analyst - информация аналитика
date_time_analyst - время анализа компьютерного воздействия
*/		

	$query = $DBO->connectionDB()->prepare("INSERT IGNORE `incident_analyst_tables` 
										  (`id`, 
										   `login_name`, 
										   `true_false`, 
										   `count_alert_analyst`, 
										   `information_analyst`,
										   `date_time_analyst`)
										    VALUE 
										  ('".intval($_POST['numInt'])."', 
										   :userName, 
										   '".intval($_POST['solution'])."', 
										   '".intval($_POST['count_alert'])."', 
										   :information,
										   '".time()."')");
	$query->execute(array(':userName' => $userName, ':information' => $information_analyst));	
	}
catch(PDOException $e){
	echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
	}

//вывод сообщения об успешном выполнении и редирект на главную страницу
ShowMessage::messageOkRedirect("запись успешно добавлена");
?>		
</div>