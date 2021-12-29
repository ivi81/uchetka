<?php

						/*----------------------------------------------------------*/
						/*  		класс редактирования таблиц БД (пользователь)	*/
						/*       - incident_chief_tables							*/
						/*		 - incident_additional_tables						*/
						/*		 - incident_number_signature_tables					*/
						/*															*/
						/* 					  					v.0.1 04.04.2014	*/
						/*----------------------------------------------------------*/

class UserEditingTableDB
{
	
//объект для подключения к БД
protected $DBO;
//ассоциативный массив содержащий редактируемую информацию
protected $array_info_impact;	

public function __construct(array $array)
	{
	$this->DBO = new DBOlink();
	$this->array_info_impact = $array;
	}

//проверка существования ключей массива
protected function checkKeysArray(array $array_keys) 
	{
	try{
		$num = count($array_keys);
		for($i = 0; $i < $num; $i++ ){
			if(!array_key_exists($array_keys[$i], $this->array_info_impact)){
				throw new Exception();
				}
			}
		}
	catch(Exception $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_NOIDENTIF,"\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: отсутствуют некоторые элементы массива");
		}			
	}

//редактирование таблицы incident_chief_tables	
public function editIncidentChiefTables()
	{
/*
id - порядковый номер инцидента
date_time_incident_start - дата и время инцидента, начало
date_time_incident_end - дата и время инцидента, конец
date_time_create - дата и время внесения инцидента в таблицу
ip_src - IP-адрес источника
count_impact - количество воздействий с каждого IP-адреса
ip_dst - IP-адрес назначения
type_attack - тип компьютерной атаки (в числовом виде, расшифровка в файле setup_site.xml)
*/		
	//проверяем переданные параметры
	$this->checkKeysArray(array('id', 'dateTimeStart', 'dateTimeEnd', 'arraySrcIp', 'arrayCountImpact', 'typeAttack', 'ipDst'));														
	try{
		$this->deleteIncidentChiefTables();
		$query_db = $this->DBO->connectionDB()->prepare("INSERT `incident_chief_tables` 
														(`id`, 
														 `date_time_incident_start`, 
														 `date_time_incident_end`, 
														 `date_time_create`, 
														 `ip_src`,
														 `count_impact`, 
														 `ip_dst`, 
														 `type_attack`)
														VALUE 
														('".$this->array_info_impact['id']."', 
														 :date_start, 
														 :date_end, 
														 '".time()."', 
														 :ip_src,
														 :count_impact, 
														 :ip_dst, 
														 '".$this->array_info_impact['typeAttack']."')");
		$query_db->bindParam(':date_start', $this->array_info_impact['dateTimeStart']);
		$query_db->bindParam(':date_end', $this->array_info_impact['dateTimeEnd']);
		$query_db->bindParam(':ip_dst', $this->array_info_impact['ipDst']);
		$countSrc = count($this->array_info_impact['arraySrcIp']);		
		for($i = 0; $i < $countSrc; $i++){
			//список IP-адресов источников 
			$ipSrc = ip2long($this->array_info_impact['arraySrcIp'][$i]);
			//количество срабатываний
			if(isset($this->array_info_impact['arrayCountImpact'][$i])){
				$numberImpact = $this->array_info_impact['arrayCountImpact'][$i];
				} else {
				$numberImpact = 0;
				}
			$query_db->bindParam(':ip_src', $ipSrc);
			$query_db->bindParam(':count_impact', $numberImpact); 
			$query_db->execute();
			}
		}
	catch(PDOException $e){
			echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//редактирование таблицы incident_additional_tables	
public function editIncidentAdditionalTables()
	{
	//проверяем переданные параметры
	$this->checkKeysArray(array('id', 'spaceSafeTraff'));	
	try{
		$this->DBO->connectionDB()->query("UPDATE `incident_additional_tables` 
										   SET `space_safe`='".$this->array_info_impact['spaceSafeTraff']."', 
										  `explanation`='".$this->array_info_impact['explanation']."' 
										   WHERE `id`='".$this->array_info_impact['id']."'");
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//редактирование таблицы incident_number_signature_tables	
public function editIncidentNumberSignatureTables()
	{
/*
id - порядковый номер инцидента
sid - номер сигнатуры
count_alert - количество срабатываний
*/
	//проверяем переданные параметры
	$this->checkKeysArray(array('id', 'arraySid', 'arrayNumberActiveSid'));
	try{
		$this->deleteIncidentNumberSignatureTables();
		$countSid = count($this->array_info_impact['arraySid']);
		for($i = 0; $i < $countSid; $i++){
			//номер сигнатуры
			$sid = $this->array_info_impact['arraySid'][$i];	
			if(isset($this->array_info_impact['arrayNumberActiveSid'][$i])){
				//количество срабатываний
				$numberActiveSid = $this->array_info_impact['arrayNumberActiveSid'][$i];
				} else {
				//количество срабатываний
				$numberActiveSid = 0;
				}
			$query_db = $this->DBO->connectionDB()->query("INSERT `incident_number_signature_tables` 
									 					  (`id`, 
														   `sid`, 
														   `count_alert`)
														   VALUE 
														  ('".$this->array_info_impact['id']."', 
														   '".$sid."', 
														   '".$numberActiveSid."')");
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}	

//удаляем записи из таблицы `incident_chief_tables`
public function deleteIncidentChiefTables() 
	{
	$this->DBO->connectionDB()->query("DELETE FROM `incident_chief_tables` WHERE `id`='".$this->array_info_impact['id']."'");	
	}

//удаляем записи из таблицы `incident_number_signature_tables`
public function deleteIncidentNumberSignatureTables() 
	{
	$this->DBO->connectionDB()->query("DELETE FROM `incident_number_signature_tables` WHERE `id`='".$this->array_info_impact['id']."'");
	}

}
?>