<?php

										/*--------------------------------------------------------------*/
										/*		класс вывода всей доступной информации по воздействию	*/
										/*											v0.11 18.08.2014	*/
										/*--------------------------------------------------------------*/

class AllInformationForIncident
{

//логин добавившего информацию
static public function showUserNameAddInformation($DBO, $id_incident) 
	{
	try{
		$query = $DBO->connectionDB()->query("SELECT `date_time_create`, `login_name`, `space_safe` FROM `incident_chief_tables` t0 JOIN `incident_additional_tables` t1 ON t0.id = t1.id 
											  WHERE t0.id='".intval($id_incident)."' LIMIT 0,1");	
		$row = $query->fetch(PDO::FETCH_OBJ);		
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	$array = array('date_create' => $row->date_time_create,
				   'login_name' => $row->login_name,
				   'space_safe' => $row->space_safe,);
	
	return $array;	
	}

//дата и время начала и конца инцидента, IP-адрес источника и назначения и тип компьютерной атаки
static public function showDateStartAndEndAndIpSrcIpDst($DBO, $id_incident) 
	{
	//массив для результатов запросов
	$array = array();	
	try{
		$query = $DBO->connectionDB()->query("SELECT `date_time_incident_start`, `date_time_incident_end`, `date_time_create`, `ip_src`, `count_impact`, `ip_dst`, `type_attack` 
											  FROM `incident_chief_tables` WHERE `id`='".intval($id_incident)."'");	
		$i = 0;
		while($row = $query->fetch(PDO::FETCH_ASSOC)){
			$array['date_start'][] = $row['date_time_incident_start'];
			$array['date_end'][] = $row['date_time_incident_end'];
			$array['date_time_create'] = $row['date_time_create'];
			$array['ip_src'][] = $row['ip_src'];
			$array['count_impact'][] = $row['count_impact'];
			$array['ip_dst'][] = $row['ip_dst'];
			$array['type_attack'][] = $row['type_attack'];
			}	
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	return $array;
	}

//направление инцидента, доступность инф. ресурса и место расположения сетевого трафика
static public function showSpaceSafeNetTraffic($DBO, $id_incident) 
	{
	try{
		$query = $DBO->connectionDB()->query("SELECT `login_name`, `availability_host`, `direction_attack`, `solution`, `number_mail_in_CIB`, `number_mail_in_organization`, `space_safe`, `explanation` 
											  FROM `incident_additional_tables` WHERE id='".intval($id_incident)."'");	
		$row = $query->fetch(PDO::FETCH_OBJ);		
		}
	catch(PDOException $e){ 
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	$array = array('login_name' => $row->login_name,
				   'availability_host' => $row->availability_host,
				   'direction_attack' => $row->direction_attack,
				   'solution' => $row->solution,
				   'number_mail_in_CIB' => $row->number_mail_in_CIB,
				   'number_mail_in_organization' => $row->number_mail_in_organization,
				   'space_safe' => $row->space_safe,
				   'explanation' => $row->explanation);
	return $array;	
	}

//полная информация о сработавших сигнатурах
static public function showSignature($DBO, $id_incident) 
	{
	//массив для результатов запросов
	$array = array();	
	try{
		//выбираем сигнатуры и их количество
		$query = $DBO->connectionDB()->query("SELECT `sid`, `count_alert` FROM `incident_number_signature_tables` WHERE id='".intval($id_incident)."'");	
		while($row = $query->fetch(PDO::FETCH_ASSOC)){
			$array['sid'][] = $row['sid'];
			$array['count_alert'][] = $row['count_alert'];
			//проверяем наличие описания найденных сигнатур			
			$query_messag = $DBO->connectionDB()->query("SELECT `sid`, `short_message`, `snort_rules` FROM `signature_tables` WHERE sid='".$row['sid']."'");
			$row_message = $query_messag->fetch(PDO::FETCH_OBJ);			
			if(isset($row_message->short_message)){
				$array['short_message'][] = $row_message->short_message;
				$array['snort_rules'][] = $row_message->snort_rules;
				} else {	
				$array['short_message'][] = "описание не найдено";
				$array['snort_rules'][] = "правило не найдено";
				}
			}	
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	return $array;	
	}

//полная информация заполняемая аналитиком
static public function showAllInformationAnalyst($DBO, $id_incident) 
	{
	//массив для результатов запросов
	$array = array();	
	try{
		//получаем информацию заполняемую аналитиком
		$query = $DBO->connectionDB()->query("SELECT `login_name`, `true_false`, `count_alert_analyst`, `information_analyst`, `date_time_analyst` 
											  FROM `incident_analyst_tables` WHERE id='".intval($id_incident)."'");	
		while($row = $query->fetch(PDO::FETCH_ASSOC)){
			$array['login_name'][] = $row['login_name'];
			$array['true_false'][] = $row['true_false'];
			$array['count_alert_analyst'][] = $row['count_alert_analyst'];
			$array['information_analyst'][] = $row['information_analyst'];
			$array['date_time_analyst'][] = $row['date_time_analyst'];
			}	
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	return $array;	
	}	

//общее количество сработавших сигнатур
static public function showCountSignature($DBO, $id_incident) 
	{
	try{
		//выбираем сигнатуры и их количество
		$query = $DBO->connectionDB()->query("SELECT `sid`, `count_alert` FROM `incident_number_signature_tables` 
											  WHERE id='".intval($id_incident)."'");	
		$count = null;	
		while($row = $query->fetch(PDO::FETCH_ASSOC)){
			$count += $row['count_alert'];
			}	
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	return $count;	
	}
}
?>