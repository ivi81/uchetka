<?php
						
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		скрипт обработки AJAX запросов (дежурный)			*/
						/*		поиск информации:									*/
						/* 		 - о количестве компьютерных воздействий за год		*/
						/*		 - о подготовленных письмах,						*/	
						/*		 - по номеру сигнатуры,								*/
						/*		 - по IP-адресу источника							*/
						/* 		 - для вывода статистической информации				*/
						/* 															*/
						/*									v.0.1 13.02.2015		*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

session_start();
//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
//подключаем config
require_once ($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/config/config.php");

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

//объект для подключения к БД
$DBO = new DBOlink();
$ReadXMLSetup = new ReadXMLSetup;

			/*----------------------------------------------------------------------/
									информация по номеру сенсора
			/----------------------------------------------------------------------*/

if(isset($_POST['informationSensorId']) && !empty($_POST['informationSensorId'])){
	//объект для работы с информацией о сенсорах
	$TotalInformationForSensor = new TotalInformationForSensor();
	//выводим информацию
	$TotalInformationForSensor->showInformationChange();
	}

			/*----------------------------------------------------------------------/
				Для вывода информации о количестве компьютерных воздействий за год
									для блока краткой информации
			/----------------------------------------------------------------------*/

if(isset($_POST['queryShortInformationYear']) && !empty($_POST['queryShortInformationYear'])){
	$choiceYear = $_POST['queryShortInformationYear'];
    echo $choiceYear.':'.BlockShortInformation::getCountImpactYear($choiceYear).':'.BlockShortInformation::getCountAnalyseInformationYear($choiceYear).':'.BlockShortInformation::getCountTraffikNotLook($choiceYear).':'.BlockShortInformation::getCountFalseImpactYear($choiceYear);
	}

			/*----------------------------------------------------------------------/
						Для поска информации по компьютерным воздействиям
			/----------------------------------------------------------------------*/

if(isset($_POST['searchStart'])){
	//выполняем поиск и выводим результат
	$SearchComputerImpactViewInformation = new SearchComputerImpactViewInformation();
	echo $SearchComputerImpactViewInformation->viewInformation();
	}

			/*-----------------------------------------/
				Для вывода статистической информации
			/-----------------------------------------*/

if(isset($_POST['queryStatistics']) && !empty($_POST['queryStatistics'])){
	switch($_POST['queryStatistics']){
		// GeoIP информация по IP-адресу назначения
		case 'GeoIp_1';
			FactoryObjectForAiaxResponse::getObjectForAjaxResponse('statisticsGeoIP')->getStatictics(ShowStatisticsGeoIP::DST_IP);
		break;
		// GeoIP информация по странам для IP-адресов назначения
		case 'GeoIp_2';
			FactoryObjectForAiaxResponse::getObjectForAjaxResponse('statisticsGeoIP')->getStatictics(ShowStatisticsGeoIP::COUNTRY_DST_IP);
		break;
		// GeoIP информация за отрезок времени по странам и IP-адресам назначения
		case 'GeoIp_3';
			FactoryObjectForAiaxResponse::getObjectForAjaxResponse('statisticsGeoIP')->getStatictics(ShowStatisticsGeoIP::DATE_COUNTRY_DST_IP);
		break;
		// GeoIP информация по ложным компьютерным атакам
		case 'GeoIp_4';
			FactoryObjectForAiaxResponse::getObjectForAjaxResponse('statisticsGeoIP')->getStatictics(ShowStatisticsGeoIP::FALSE_KA);
		break;
		}
	}

			/*----------------------------------------------/
				Для поиска информации по номеру сигнатуры
			/----------------------------------------------*/

if(isset($_POST['querySid']) && !empty($_POST['querySid'])){
	$sid = ExactilyUserData::takeIntager($_POST['querySid']);
	$response = '';
	try{
		//получаем список ложных компьютерных воздействий в которых встречается sid данной сигнатуры  
		$query = $DBO->connectionDB()->query("SELECT (SELECT COUNT(t1.id) FROM (SELECT * FROM `incident_number_signature_tables` 
											  WHERE `sid`='".$sid."') AS t0 LEFT JOIN `incident_analyst_tables` AS t1 ON t0.id=t1.id 
											  WHERE `true_false`='1') AS true_false_1, (SELECT COUNT(t1.id) FROM (SELECT * FROM `incident_number_signature_tables` 
											  WHERE `sid`='".$sid."') AS t0 LEFT JOIN `incident_analyst_tables` AS t1 ON t0.id=t1.id 
											  WHERE `true_false`='2') AS true_false_2");
		$row = $query->fetch(PDO::FETCH_OBJ);
		//проверяем есть ли сигнатуры у которых возможность ложного срабатывания равна 100%
		if($row->true_false_1 == 0 && $row->true_false_2 != 0){
			$response = 100;
			} else {
			$response = ceil(($row->true_false_1 / 100) * $row->true_false_2);
			}
		if($response == ''){
			?>
			<span style="font-size: 24px; color: #0000CD;">0 %</span>
			<?php
			} else {
			$query_ip_dst = $DBO->connectionDB()->query("SELECT DISTINCT `id`, `ip_dst` FROM `incident_chief_tables` 
												  		 WHERE `id` IN (SELECT t1.id FROM (SELECT * FROM `incident_number_signature_tables` 
												  		 WHERE `sid`='".$sid."') AS t0 LEFT JOIN `incident_analyst_tables` AS t1 ON t0.id=t1.id 
												  		 WHERE `true_false`='2') ORDER BY `id` ASC");
			$table = "<div style='position: relative; left: 70px; width: 300px; display: none;'>";
			$table .= "<table border='0' style='width: 330px; border: 1px solid #87CEEB; -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px;'>";
			$table .= "<tr ".COLOR_HEADER."><th style='width: 120px; font-size: 10px;'>номер компьютерного воздействия</th><th style='width: 210px; font-size: 10px;'>IP-адрес назначения</th></tr>";
			while($row_ip_dst = $query_ip_dst->fetch(PDO::FETCH_ASSOC)){
				$domain = '';
				if($ReadXMLSetup->obtainDomainName(long2ip($row_ip_dst['ip_dst']))){
					$domain = " (<span style='font-weight: bold;'>".$ReadXMLSetup->obtainDomainName(long2ip($row_ip_dst['ip_dst'])).")</span>";
					}
				$table .= "<tr bgcolor=".color().">";
				$table .= "<td style='text-align: center; font-size: 11px;'>{$row_ip_dst['id']}</td>";
				$table .= "<td style='text-align: center; font-size: 11px;'>".long2ip($row_ip_dst['ip_dst'])."{$domain}</td></tr>";
				}
			$table .= "</table>";
			if($response > 80) $color = '#FF0000';
			elseif($response < 80 && $response > 60) $color = '#FFA500';
			elseif($response < 60 && $response > 40) $color = '#FFFF00';
			elseif($response < 40 && $response > 20) $color = '#ADFF2F';
			else $color = '#00FF00';
			$response = "<span style='font-size: 24px; color: ".$color."; text-shadow: #696969 1px 0 0px, #696969 0 1px 0px, #696969 -1px 0 0px, #696969 0 -1px 0px;'>{$response} %</span>";	
			?>
			<div onclick="(function(elem){ var div = elem.firstChild.nextSibling; if(div.style.display == 'none') div.style.display = 'block'; else div.style.display = 'none'; })(this)" style="cursor: pointer;"><?= $response.$table ?></div>
			<?php
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

			/*-------------------------------------------------/
				Для поиска информации по IP-адресу источника
			/-------------------------------------------------*/

if(isset($_POST['queryIpSrc']) && !empty($_POST['queryIpSrc'])){
	$ipSrc = ExactilyUserData::takeIP($_POST['queryIpSrc']);
	$response = '';
	try{
		//получаем количество положительных и ложных компьютерных воздействий в которых встречается IP-адрес источника  
		$query_count = $DBO->connectionDB()->prepare("SELECT (SELECT COUNT(t0.id) FROM `incident_chief_tables` t0 
											    	  LEFT JOIN `incident_analyst_tables` t1 ON t0.id=t1.id 
											    	  WHERE `ip_src`=:ipSrc AND `true_false`='1') AS `TRUE`, 
											   		 (SELECT COUNT(t0.id) FROM `incident_chief_tables` t0 
													  LEFT JOIN `incident_analyst_tables` t1 ON t0.id=t1.id 
													  WHERE `ip_src`=:ipSrc AND `true_false`='2') AS `FALSE`");
		$ipSrc = ip2long($ipSrc[0]);
		$query_count->execute(array(':ipSrc' => $ipSrc));
		$row_count = $query_count->fetch(PDO::FETCH_OBJ);
		$sum = $row_count->TRUE + $row_count->FALSE;
		$arraySum = array(array($sum, '#0000CD'), 
						  array($row_count->TRUE, '#00FF00'), 
						  array($row_count->FALSE, '#FF0000'));

		$num = count($arraySum);
		for($i = 0; $num > $i; $i++){
			$response .= "<span style='font-size: 24px; color: ".$arraySum[$i][1].";";
			$response .= "text-shadow: #696969 1px 0 0px, #696969 0 1px 0px, #696969 -1px 0 0px, #696969 0 -1px 0px;'>{$arraySum[$i][0]}</span>";
			if(($num - 1) > $i) $response .= "<span style='font-size: 24px; color: #000;'>/</span>";
			}
		if($sum == 0){
			echo $response;		
			} else {
			//получаем список компьютерных воздействий в которых встречается указанный IP-адрес источника
			$query = $DBO->connectionDB()->prepare("SELECT t0.id, `ip_dst`, `true_false` FROM `incident_chief_tables` t0
													LEFT JOIN `incident_analyst_tables` t1 ON t0.id=t1.id WHERE `ip_src`=:ipSrc
													AND `true_false`!='3' ORDER BY `true_false`, t0.id ASC");
			$query->execute(array(':ipSrc' => $ipSrc));
			$table = "<div style='position: relative; left: 40px; width: 300px; display: none;'>";
			$table .= "<table border='0' style='width: 400px; border: 1px solid #87CEEB; -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px;'>";
			$table .= "<tr ".COLOR_HEADER."><th style='width: 100px; font-size: 10px;'>номер компьютерного воздействия</th>";
			$table .= "<th style='width: 200px; font-size: 10px;'>IP-адрес назначения</th>";
			$table .= "<th style='width: 100px; font-size: 10px;'>компьютерная<br>атака (да/нет)</th></tr>";
			while($row = $query->fetch(PDO::FETCH_ASSOC)){
				$domain = '';
				if($ReadXMLSetup->obtainDomainName(long2ip($row['ip_dst']))){
					$domain = " (<span style='font-weight: bold;'>".$ReadXMLSetup->obtainDomainName(long2ip($row['ip_dst'])).")</span>";
					}
				($row['true_false'] == 1) ? $trueFalse = 'да' : $trueFalse = 'нет';
				$table .= "<tr bgcolor=".color().">";
				$table .= "<td style='text-align: center; font-size: 11px;'>{$row['id']}</td>";
				$table .= "<td style='text-align: center; font-size: 11px;'>".long2ip($row['ip_dst'])."{$domain}</td>";
				$table .= "<td style='text-align: center; font-size: 11px;'>$trueFalse</td></tr>";				
				}
			$table .= "</table>";
			?>
			<div onclick="(function(elem){ for(var i = 0; elem.childNodes.length > i; i++){ if(elem.childNodes[i].nodeName == 'DIV'){ if(elem.childNodes[i].style.display == 'none') elem.childNodes[i].style.display = 'block'; else elem.childNodes[i].style.display = 'none'; }}})(this)" style="cursor: pointer;"><?= $response.$table ?></div>
			<?php
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
?>