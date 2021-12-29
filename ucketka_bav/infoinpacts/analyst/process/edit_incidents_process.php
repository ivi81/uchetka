<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		скрипт выполнения изменений в компьютерном инциденте	*/
						/*										v.0.1 11.08.2014	 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));

//подключаем основную, для дежурного страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/analyst/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

//функция конвертирования заданной даты в письменный вид
function showDateConvert($date) 
	{
	$a = array("-01-","-02-","-03-","-04-","-05-","-06-","-07-","-08-","-09-","-10-","-11-","-12-");
	$b = array(" января "," февраля "," марта "," апреля "," мая "," июня "," июля "," августа "," сентября "," октября "," ноября "," декабря ");
	$day = substr(date("d-m-Y", $date), 0, 2);	
	$month = str_replace($a, $b, substr(date("d-m-Y", $date), 2, 4));
	$year = substr(date("d-m-Y", $date), 6, 4);
	$newdate = $day." ".$month." ".$year." года";
	return $newdate;
	}

?>
<!-- основная цветовая подложка -->	
<div class="majorArea">
<?php

			/*-----------------------------------------------------------------*/
			/*																   */
			/*** для изменения проанализированного компьютерного воздействия ***/
			/*																   */
			/*-----------------------------------------------------------------*/

if(isset($_POST['editInpacts'])){
	//id воздействия
	$id = (int) $_POST['editInpacts'];
	//дата и время начала инцидента
	$dateTimeStart = ExactilyUserData::takeDate($_POST['dateStart'])."  ".ExactilyUserData::takeTime(substr($_POST['timeStart'], 0, 5));
	$dateTimeEnd = ExactilyUserData::takeDate($_POST['dateEnd'])."  ".ExactilyUserData::takeTime(substr($_POST['timeEnd'], 0 ,5));
	//проверяем что бы начальная дата была всегда меньше конечной 
	if(strtotime($dateTimeStart) > strtotime($dateTimeEnd)){
		echo "<div style='position: absolute; margin-left: 210px; top: 250px;'>".ShowMessage::showInformationError("начальная дата должна быть<br>меньше конечной")."</div>";
		exit();							
		}
			
	//так как метод ExactilyUserData::takeIP() ожидает входной параметр в виде строки делаем из массива строку
	$ipString = implode(" ", $_POST['ipSrc']);
	$ipStringNum = implode(" ", $_POST['ipNum']);
	$sidString = implode(" ", $_POST['sid']);
	$sidStringNum = implode(" ", $_POST['sidNum']);			
			
	//массив для IP-адресов источников
	$arraySrcIp = ExactilyUserData::takeIP($ipString); 
	//массив с количеством воздействий от IP-адресов источников
	$arrayCountImpact = ExactilyUserData::takeSid($ipStringNum);
	//тип компьютерной атаки
	$typeAttack = 0; //то есть не определен	
	if(!empty($_POST['typeKA'])){
		$typeAttack = ExactilyUserData::takeString($_POST['typeKA']);
		}
		
	foreach(ExactilyUserData::takeIP($_POST['ipDst']) as $ip){
		$ipDst = ip2long($ip);
		} 

	//массив сигнатур (sid)
	$arraySid = ExactilyUserData::takeSid($sidString);
	//массив количества срабатываний	сигнатур		
	$arrayNumberActiveSid = ExactilyUserData::takeSid($sidStringNum);
	//место расположения отфильтрованного сетевого трафика
	$spaceSafeTraff = ExactilyUserData::takeStringAll($_POST['spaceSafe']);
	//мнение аналитика
	$solveAnalyst = (int) $_POST['solution'];
	//количество пакетов информационной безопасности по мнению аналитика						
	$analystCount = (int) $_POST['analystCount'];
	//информация аналитика
	if(empty($_POST['analystInfo'])){
		$analystInfo = ' ';
		} else {
		$analystInfo = htmlspecialchars($_POST['analystInfo']);
		}
	//создаем объект для редактирования таблиц БД
	$AnalystEditingTableDB = new AnalystEditingTableDB(array('id' => $id,
															 'dateTimeStart' => $dateTimeStart,
															 'dateTimeEnd' => $dateTimeEnd,
															 'arraySrcIp' => $arraySrcIp,
															 'arrayCountImpact' => $arrayCountImpact,
															 'typeAttack' => $typeAttack,
															 'ipDst' => $ipDst,
															 'arraySid' => $arraySid,
															 'arrayNumberActiveSid' => $arrayNumberActiveSid,
															 'spaceSafeTraff' => $spaceSafeTraff,
															 'solution' => $solveAnalyst,
															 'analystCount' => $analystCount,
															 'analystInfo' => $analystInfo));
	/*	+++ incident_chief_tables +++ */
	$AnalystEditingTableDB->editIncidentChiefTables();		
			
	/*	+++ incident_additional_tables +++ */
	$AnalystEditingTableDB->editIncidentAdditionalTables();
			
	/*	+++ incident_number_signature_tables +++ */
	$AnalystEditingTableDB->editIncidentNumberSignatureTables();

	/* +++ incident_analyst_tables +++ */
	$AnalystEditingTableDB->editIncidentAnalystTables();

	//вывод сообщения об успешном выполнении и редирект на главную страницу
	ShowMessage::messageOkRedirect("запись успешно изменена");
	}

?>
</div>