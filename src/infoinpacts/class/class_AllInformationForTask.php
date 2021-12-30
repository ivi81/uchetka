<?php

							/*----------------------------------------------------------------------------------*/
							/*  класс вывода всей имеющейся информации по поставленной руководством задаче		*/
							/* 	 															v.0.1 31.01.2014  	*/
							/*----------------------------------------------------------------------------------*/

class AllInformationForTask
{

//информация по поставленной задаче (основная таблица) возвращает array
static public function showInformationOnTableBasic($DBO, $task_id) 
	{
	//problem_table_basic
	/*
	task_id - порядковый номер задачи
	task_date_time - дата и время постановки задачи											  								  
	task_criticality - критичность задачи
	task_show - задача
	*/
	try
		{
		$query = $DBO->connectionDB()->query("SELECT `task_date_time`, `login_source`, `task_criticality`, `task_show` FROM `problem_table_basic` 
														  WHERE `task_id`=$task_id");	
		$row = $query->fetch(PDO::FETCH_OBJ); 
		}
	catch(PDOException $e)
		{
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	$array = array('task_date_time' => $row->task_date_time,
						'login_source' => $row->login_source,
						'task_criticality' => $row->task_criticality,
						'task_show' => $row->task_show);
	return $array;
	}

//информация по поставленной задаче (дополнительная информация) возвращает array	
static public function showInformationOnTableadditional($DBO, $task_id) 
	{
	//problem_table_additional
	/*	
	task_id - порядковый номер задачи
	task_date_time_change - время модификации хода выполнения задачи
	task_login_addressee - логин исполнителя
	task_progress - ход выполнения задачи 
		10 - задача поставлена но не просмотрена
		11 - задача просмотрена исполнителем и выполняется
		12 - задача выполнена
	task_message_addressee - пояснение исполнителя
	*/
	try
		{
		$query = $DBO->connectionDB()->query("SELECT `task_date_time_change`, `task_login_addressee`, `task_progress`, `task_message_addressee` 
														  FROM `problem_table_additional` WHERE `task_id`=$task_id");	
		while($row = $query->fetch(PDO::FETCH_ASSOC))
			{
			$array['task_date_time_change'][] = $row['task_date_time_change'];
			$array['task_login_addressee'][] = $row['task_login_addressee'];
			$array['task_progress'][] = $row['task_progress'];
			$array['task_message_addressee'][] = $row['task_message_addressee'];			
			} 
		}
	catch(PDOException $e)
		{
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	return $array;
	}
	
}
?>