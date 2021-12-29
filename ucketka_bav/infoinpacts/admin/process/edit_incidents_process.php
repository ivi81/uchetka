<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		скрипт выполнения изменений в компьютерном воздействии	*/
						/*											v.0.1 02.08.2014 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));

//подключаем основную, для дежурного страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/admin/index.php"); 

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

			/*----------------------------------------------------------------------------------*/
			/*																					*/
			/*** для изменения уже добавленной дежурным информации о компьютерном воздействии ***/
			/*																	  				*/
			/*----------------------------------------------------------------------------------*/

/*
editInpacts +
dateStart +
timeStart +
dateEnd +
timeEnd +
ipSrc +
ipNum  +
ipDst +
analystCount + 
solution (решение аналитика) +
typeKA (аналитик) +
analystInfo +
ping +
direction (направление КА) +
number_mail_in_CIB +
number_mail_in_organization +
explanation (пояснение дежурного) +
sid (№ сигнатуры) +
sidNum (count) +
spaceSafe +
*/

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
	
		//МНЕНИЕ АНАЛИТИКА
		//если 0 удаляем запись в таблице аналитика по этому воздействию
		if(isset($_POST['solution']) && !empty($_POST['solution'])){
			$solution = (int) $_POST['solution'];
		} else {
			$solution = 0;
		}
		//количество срабатываний по мнению аналитика
		if(isset($_POST['analystCount']) && !empty($_POST['analystCount'])){
			$analystCount = (int) $_POST['analystCount'];
		} else {
			$analystCount = 0;
		}
		//информация аналитика
		if(isset($_POST['analystInfo']) && !empty($_POST['analystInfo'])){
			$analystInfo = ExactilyUserData::takeString($_POST['analystInfo']);
		} else {
			$analystInfo = '';
		}

		//доступность информационного ресурса
		if(isset($_POST['ping']) && !empty($_POST['ping'])){
			$ping = (int) $_POST['ping'];
		} else {
			$ping = 1;
		}
		//тип компьютерной атаки
		if(isset($_POST['typeKA']) && !empty($_POST['typeKA'])){
			$typeAttack = ExactilyUserData::takeString($_POST['typeKA']);
		} else {
			$typeAttack = 0;
		}
		//направление компьютерной атаки
		if(isset($_POST['direction']) && !empty($_POST['direction']) || $_POST['direction'] == 0){
			$direction = (int) $_POST['direction'];
		} else {
			$direction = 1;
		}

		//IP-адрес назначения
		foreach(ExactilyUserData::takeIP($_POST['ipDst']) as $ip){
			$ipDst = ip2long($ip);
		}
		//номер письма в ЦИБ
		$number_mail_in_CIB = '';
		if(!empty($_POST['number_mail_in_CIB'])){
			$number_mail_in_CIB = ExactilyUserData::takeString($_POST['number_mail_in_CIB']);
		}
		//номер письма в стороннюю организацию
		$number_mail_in_organization = '';
		if(!empty($_POST['number_mail_in_organization'])){
			$number_mail_in_organization = ExactilyUserData::takeString($_POST['number_mail_in_organization']);
		}

		//массив сигнатур (sid)
		$arraySid = ExactilyUserData::takeSid($sidString);
		//массив количества срабатываний сигнатур		
		$arrayNumberActiveSid = ExactilyUserData::takeSid($sidStringNum);
		//место расположения отфильтрованного сетевого трафика
		$spaceSafeTraff = ExactilyUserData::takeString($_POST['spaceSafe']);
		//пояснение к компьютерному воздействию
		$explanation = ExactilyUserData::takeString($_POST['explanation']);
				
		//создаем объект для редактирования таблиц БД
		$UserEditingTableDB = new AdminEditingTableDB(array('id' => $id,
															'dateTimeStart' => $dateTimeStart,
															'dateTimeEnd' => $dateTimeEnd,
															'arraySrcIp' => $arraySrcIp,
															'arrayCountImpact' => $arrayCountImpact,
															'ipDst' => $ipDst,
															'analystCount' => $analystCount,
															'solution' => $solution,
															'typeAttack' => $typeAttack,
															'analystInfo' => $analystInfo,
															'ping' => $ping,
															'direction' => $direction,
															'number_mail_in_CIB' => $number_mail_in_CIB,
															'number_mail_in_organization' => $number_mail_in_organization,
															'explanation' => $explanation,
															'arraySid' => $arraySid,
															'arrayNumberActiveSid' => $arrayNumberActiveSid,
															'spaceSafeTraff' => $spaceSafeTraff));
		/*	+++ incident_chief_tables +++ */

		$UserEditingTableDB->editIncidentChiefTables();		
			
		/*	+++ incident_additional_tables +++ */

		$UserEditingTableDB->editIncidentAdditionalTables();
			
		/*	+++ incident_number_signature_tables +++ */

		$UserEditingTableDB->editIncidentNumberSignatureTables();

		/*  +++ incident_analyst_tables +++  */
		$UserEditingTableDB->editIncidentAnalystTables();
		}

?>
		<script type="text/javascript" >
			//перенаправляем на другую страницу
			window.location.href = "/<?= $array_directory[1] ?>/admin/process/edit_incidents.php?editAllInformation=<?= $id ?>";
		</script>
	</div>