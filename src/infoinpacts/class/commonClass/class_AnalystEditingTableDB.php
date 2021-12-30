<?php

						/*-------------------------------------------------------*/
						/*  		класс редактирования таблиц БД (аналитик)	 */
						/*       - incident_chief_tables						 */
						/*			- incident_analyst_tables					 */
						/*			- incident_additional_tables				 */
						/*			- incident_number_signature_tables			 */
						/*														 */
						/* 					  				v.0.1 11.08.2014     */
						/*-------------------------------------------------------*/

class AnalystEditingTableDB extends UserEditingTableDB
{

//редактирование таблицы `incident_analyst_tables`	
public function editIncidentAnalystTables()
	{
/*
id
login_name
true_false
count_alert_analyst
information_analyst
*/
	//проверяем переданные параметры
	$this->checkKeysArray(array('solution', 'analystCount', 'analystInfo'));
	try{
		$query = $this->DBO->connectionDB()->prepare("UPDATE `incident_analyst_tables` SET `true_false`='".intval($this->array_info_impact['solution'])."',
													 `count_alert_analyst`='".intval($this->array_info_impact['analystCount'])."', 
													 `information_analyst`=:infoAnalyst WHERE `id`='".$this->array_info_impact['id']."'");
		$info = $this->array_info_impact['analystInfo'];
		$query->execute(array(':infoAnalyst' => $info));
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
										   SET `space_safe`='".$this->array_info_impact['spaceSafeTraff']."' 
										   WHERE `id`='".$this->array_info_impact['id']."'");
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
}
?>