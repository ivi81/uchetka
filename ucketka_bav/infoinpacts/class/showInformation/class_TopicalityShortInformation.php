<?php

										/*------------------------------------------*/
										/*	класс краткой, актуальной информации		*/
										/*							v0.2 31.01.2014	*/
										/*------------------------------------------*/

class TopicalityShortInformation
{

//общее количество инцидентов (АКТУАЛЬНО ДЛЯ ВСЕХ)
public static function showAllIncident($DBO) 
	{
	try{
		$query = $DBO->connectionDB()->query("SELECT COUNT(*) AS NUMBER FROM `incident_additional_tables`");
		return $query->fetch(PDO::FETCH_OBJ)->NUMBER;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
	
//общее количество ЛОЖНЫХ инцидентов (АКТУАЛЬНО ДЛЯ ВСЕХ)
public static function showAllFalseIncident($DBO) 
	{
	try{
		$query = $DBO->connectionDB()->query("SELECT COUNT(*) AS NUMBER FROM `incident_analyst_tables` WHERE `true_false`='2'");
		return $query->fetch(PDO::FETCH_OBJ)->NUMBER;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}	
	
//количество инцидентов сетевой трафик которых не был найден (АКТУАЛЬНО ДЛЯ ВСЕХ)
public static function showNoShearchNetTraffic($DBO) 
	{
	try{
		$query = $DBO->connectionDB()->query("SELECT COUNT(*) AS NUMBER FROM `incident_analyst_tables` WHERE `true_false`='3'");
		return $query->fetch(PDO::FETCH_OBJ)->NUMBER;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//количество не проанализированных инцидентов (АКТУАЛЬНО ДЛЯ ВСЕХ)
public static function showNoSeeAnalyst($DBO) 
	{
	try{
		$query = $DBO->connectionDB()->query("SELECT COUNT(t0.id) AS NUMBER FROM `incident_additional_tables` t0 LEFT JOIN `incident_analyst_tables` t1 
 											  ON t0.id = t1.id WHERE ((t1.id) is Null)");
		return $query->fetch(PDO::FETCH_OBJ)->NUMBER;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
	
//количество не полностью заполненных но проанализированных инцидентов (писать письмо) (АКТУАЛЬНО ДЛЯ ВСЕХ)
public static function showWriteMail($DBO)
	{
	try{
		$query = $DBO->connectionDB()->query("SELECT COUNT(t0.id) AS NUMBER FROM `incident_additional_tables` t0 LEFT JOIN `incident_analyst_tables` t1 
 											  ON t0.id = t1.id WHERE t1.true_false='1' AND (t0.number_mail_in_CIB is Null)");
		return $query->fetch(PDO::FETCH_OBJ)->NUMBER;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
	
//количество задач ожидающих выполнения
public static function showAllTask($DBO) 
	{
	try{
		$query = $DBO->connectionDB()->query("SELECT COUNT(DISTINCT(task_id)) AS NUMBER FROM `problem_table_additional` 
											  WHERE `task_progress`='10' ORDER BY `task_id`");
		return $query->fetch(PDO::FETCH_OBJ)->NUMBER;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//количество задач в процессе выполнения
public static function showProcessTask($DBO) 
	{
	try{
		$query = $DBO->connectionDB()->query("SELECT COUNT(DISTINCT(task_id)) AS NUMBER FROM `problem_table_additional` 
											  WHERE `task_progress`='11' ORDER BY `task_id`");
		return $query->fetch(PDO::FETCH_OBJ)->NUMBER;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
	
//авторизованные пользователи (возвращает массив Ф.И.О.)
public static function userOnLine($DBO, $ReadXMLSetup) 
	{
	$array = array();
	try{
		$query = $DBO->connectionDB()->query("SELECT `user_login` FROM `user_session`	WHERE `authorization`='yes'");
		while($row = $query->fetch(PDO::FETCH_ASSOC)){
			if($row['user_login'] != 'admin'){
				$array[] = $ReadXMLSetup->usernameFIO($row['user_login']);
				}
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	return $array;
	}
}
?>