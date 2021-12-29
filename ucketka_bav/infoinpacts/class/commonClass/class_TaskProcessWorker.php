<?php

							/*----------------------------------------------------------------------------------------*/
							/*  класс постановки, вывода на экран и удаления из БД задач (для оперативного дежурного)	*/
							/* 	 																						v.0.1 30.01.2014  */
							/*----------------------------------------------------------------------------------------*/

class TaskProcessWorker extends TaskProcessMajor
{

//функция которая проверяет есть ли новая задача для конкретного пользователя
public function checkNewTask() 
	{
	try{
		$query = $this->DBO->connectionDB()->query("SELECT COUNT(*) AS NUM FROM `problem_table_additional` WHERE `task_progress`='10' AND `task_login_addressee`='".$this->userLogin."'");
		return $query->fetch(PDO::FETCH_OBJ)->NUM;		
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
	
//функция запрос на вывод из БД всей информации
public function demandsInformationTask() 
	{
	$array_task = array();
	try{
		$query = $this->DBO->connectionDB()->query("SELECT table_A.task_id, `task_date_time`, `login_source`, `task_criticality`, 
																 `task_show`, `task_date_time_change`, `task_login_addressee`, `task_progress`, 
																 `task_message_addressee` FROM `problem_table_basic` table_A 
																  INNER JOIN `problem_table_additional` table_B ON table_A.task_id=table_B.task_id 
																  WHERE `task_login_addressee`='".$this->userLogin."' ORDER BY `task_criticality` DESC, `task_date_time` DESC");
		$i = 0;		
		while($row = $query->fetch(PDO::FETCH_ASSOC)){
			$array_task[$i]['task_id'] = $row['task_id'];
			$array_task[$i]['task_date_time'] = $row['task_date_time'];
			$array_task[$i]['login_source'] = $row['login_source'];
			$array_task[$i]['task_criticality'] = $this->showCriticality($row['task_criticality']);
			$array_task[$i]['task_show'] = $row['task_show'];
			$array_task[$i]['task_date_time_change'] = $row['task_date_time_change'];
			$array_task[$i]['task_login_addressee'] = $row['task_login_addressee'];
			$array_task[$i]['task_progress'] = $row['task_progress'];
			$array_task[$i]['task_message_addressee'] = $row['task_message_addressee'];
			$i++;
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	return $array_task;
	}
	
//функция которая меняет статус выполнения задачи с "задача поставлена но не просмотрена" на "задача просмотрена исполнителем и выполняется"
public function autoChangeTaskProgress() 
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
	try{
		//все поставленные задачи переводим в разряд просмотренных		
		$this->DBO->connectionDB()->query("UPDATE `problem_table_additional` SET `task_progress`='11', `task_date_time_change`='".date('Y-m-d H:i:s', time())."' 
													  WHERE `task_progress`='10' AND `task_login_addressee`='".$this->userLogin."'");
		//все информационные задачи переводим в разряд выполненных
		$this->DBO->connectionDB()->query("UPDATE `problem_table_basic` AS table_A, `problem_table_additional` AS table_B 
													  SET `task_progress`='12', `task_date_time_change`='".date('Y-m-d H:i:s', time())."' 
													  WHERE table_A.task_id=table_B.task_id AND table_B.task_login_addressee='".$this->userLogin."' 
													  AND table_A.task_criticality='10'");
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}		
	}
	
//функция изменяющая статус выполнения задачи на "задача выполнена"
public function finallyChangeTaskProgress(array $array_task) 
	{
	try{
		$count_array = count($array_task);
		if($count_array > 0){
			$query = $this->DBO->connectionDB()->prepare("UPDATE `problem_table_additional` SET `task_progress`='12', `task_message_addressee`=:task_message, `task_date_time_change`='".date('Y-m-d H:i:s', time())."' 
																	    WHERE `task_id`=:task_id AND `task_login_addressee`='".$this->userLogin."'");
			for($i = 0; $i < $count_array; $i++){			
				foreach($array_task[$i] as $task_id => $message){
					$query->execute(array(':task_id' => $task_id, ':task_message' => $message));
					}				
				}
			}		
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	}
}

?>