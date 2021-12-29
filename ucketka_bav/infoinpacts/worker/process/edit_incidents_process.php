<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		скрипт выполнения изменений в компьютерном инциденте осуществляемых дежурными	*/
						/*																v.0.2 03.04.2014	 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));

//подключаем основную, для дежурного страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/worker/index.php"); 

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

			/*---------------------------------------------------*/
			/*													 */
			/*** для изменения местоположения сетевого трафика	**/
			/*													 */
			/*---------------------------------------------------*/
			
	if(isset($_POST['newSpaceNetTraffic']) && !empty($_POST['newSpaceNetTraffic'])){
		try{
			$new_space_net_traffik = ExactilyUserData::takeStringAll($_POST['newSpaceNetTraffic']);
			
			//изменяем местоположение сетевого трафика в таблице "incident_additional_tables"
			$query = $DBO->connectionDB()->prepare("UPDATE `incident_additional_tables` SET `space_safe`=:new_space_net_traffik
												    WHERE `id`='".intval($_POST['idInc'])."'");
			$query->execute(array(':new_space_net_traffik' => $new_space_net_traffik));

			//и удаляем запись об данном инциденте в таблице аналитика "incident_analyst_tables"
			$query = $DBO->connectionDB()->query("DELETE FROM `incident_analyst_tables` WHERE `id`='".intval($_POST['idInc'])."'");
			}
		catch(PDOException $e){
			echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
			}
		//вывод сообщения об успешном выполнении и редирект на главную страницу
		ShowMessage::messageOkRedirect("запись успешно изменена");
		}

			/*------------------------------------------------------------*/
			/*															  */
			/*** для дополнения (ввод номер письма) компьютерной атаки	***/
			/*															  */
			/*------------------------------------------------------------*/

	$arraySolution = array(1 => 'подготовлено письмо в 18 Центр ФСБ России',
						   2 => 'подготовлено письмо в региональное подразделение ФСБ России',
						   3 => 'информация о компьютерном воздействии не отправлялась');
	if(!empty($_POST['radioNumMail'])){
		$radioNumMail = intval($_POST['radioNumMail']);
		if($radioNumMail === 3){
			$numberMail = 99999999;
		} else {
			//проверяем основной номер письма
			if(isset($_POST['numberMail']) && preg_match("/^[0-9]+$/", $_POST['numberMail'])){
				$numberMail = intval($_POST['numberMail']);
			} else {
			echo MessageErrors::userMessageError(MessageErrors::ERROR_USER, '');
			}
		}
		//заполняем поле информации о подготовленных письмах
		$solution = $arraySolution[$radioNumMail];	
		//проверяем письмо в стороннюю организацию
		$numberMailOrganization = '';
		if(isset($_POST['numberMailOrganization']) && preg_match("/^[0-9]+$/", $_POST['numberMailOrganization'])){
			$numberMailOrganization = $_POST['numberMailOrganization'];
			$solution .= ', а также в сопутствующую организацию';
		}

		//проверяем содержимое поля заметок
		$fieldNodes = (!empty($_POST['fieldNodes'])) ? ExactilyUserData::takeStringAll($_POST['fieldNodes']): '';
		//убираем лишние ":"
		$num = strlen($_POST['idInc']) - 1;
		//получаем массив номеров инцидентов
		$arrayIncindentsId = explode(":", substr($_POST['idInc'], 0, $num));
		try {
			//дополняем таблицу "incident_additional_tables" номерами писем
			$query = $DBO->connectionDB()->prepare("UPDATE `incident_additional_tables` SET 
														   `number_mail_in_CIB`=:number_mail_in_CIB, 
														   `number_mail_in_organization`=:number_mail_in_organization, 
														   `solution`=:solution, 
														   `explanation`=:explanation, 
														   `time_forming_mail`='".time()."' WHERE `id`=:id");
			foreach($arrayIncindentsId as $id){
				$query->execute(array(':id' => $id,
							 		  ':number_mail_in_CIB' => $numberMail,
									  ':number_mail_in_organization' => $numberMailOrganization,
									  ':explanation' => $fieldNodes,
									  ':solution' => $solution));
			}

		}
		catch(PDOException $e){
			echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
		//вывод сообщения об успешном выполнении и редирект на главную страницу
		ShowMessage::messageOkRedirect("номер успешно добавлен");
	}

			/*----------------------------------------------------------------------------------*/
			/*																					*/
			/*** для изменения уже добавленной дежурным информации о компьютерном воздействии ***/
			/*																	  				*/
			/*----------------------------------------------------------------------------------*/


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
		$spaceSafeTraff = ExactilyUserData::takeString($_POST['spaceSafe']);
		//пояснение к компьютерному воздействию
		$explanation = ExactilyUserData::takeString($_POST['explanation']);
				
		//создаем объект для редактирования таблиц БД
		$UserEditingTableDB = new UserEditingTableDB(array('id' => $id,
														   'dateTimeStart' => $dateTimeStart,
														   'dateTimeEnd' => $dateTimeEnd,
														   'arraySrcIp' => $arraySrcIp,
														   'arrayCountImpact' => $arrayCountImpact,
														   'typeAttack' => $typeAttack,
														   'ipDst' => $ipDst,
														   'arraySid' => $arraySid,
														   'arrayNumberActiveSid' => $arrayNumberActiveSid,
														   'spaceSafeTraff' => $spaceSafeTraff,
														   'explanation' => $explanation));
		/*	+++ incident_chief_tables +++ */

		$UserEditingTableDB->editIncidentChiefTables();		
			
		/*	+++ incident_additional_tables +++ */

		$UserEditingTableDB->editIncidentAdditionalTables();
			
		/*	+++ incident_number_signature_tables +++ */

		$UserEditingTableDB->editIncidentNumberSignatureTables();

		//вывод сообщения об успешном выполнении и редирект на главную страницу
		ShowMessage::messageOkRedirect("запись успешно изменена");
		}

?>
	</div>