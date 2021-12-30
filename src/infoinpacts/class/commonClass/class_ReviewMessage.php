<?php

							/*-------------------------------------------------------------------------------------------*/
							/*  класс информирования пользователей SimplyChat о необходимости просмотра нового сообщения	*/
							/*	 и учет просмотренных пользователем сообщений															*/
							/* 	 																							v.0.1 21.01.2014  */
							/*-------------------------------------------------------------------------------------------*/
							
class ReviewMessage
{

public static function showReviewMessage() 
	{
	//создаем объект доступа к БД
	$DBO = new DBOlink();
	//получаем логин авторизованного пользователя
	$login_user_authorization = $_SESSION['userSessid']['userLogin'];
	//получаем индекс группы авторизованного пользователя
	$id_user_authorization = $_SESSION['userSessid']['userId'];
	try{
		$query_reade = $DBO->connectionDB()->query("SELECT `login_addressee`, `who_reade` FROM `simply_chat`");
		//массив для определения группы пользователя
		$array_user_id_group = array('20' => 'worker', '30' => 'analyst');		
		$count = 0;
		while($row_reade = $query_reade->fetch(PDO::FETCH_ASSOC)){
			//определяем кому предназначено данное сообщение
			//считать сообщения только если они были адресованы ВСЕМ, группе к которой относится пользователь или авторизованному пользователю		
			if(($row_reade['login_addressee'] == 'all') || ($row_reade['login_addressee'] == $array_user_id_group[$id_user_authorization]) || ($row_reade['login_addressee'] == $login_user_authorization)){
				if(!$row_reade['who_reade']){
					$count++;
					} else {
					$array_who_reade = explode(" ", $row_reade['who_reade']);
					(in_array($login_user_authorization, $array_who_reade)) ? true: $count++;
					}	
				}
			}
		unset($array_who_reade);
		//закрываем соединения с БД
		$DBO->onConnectionDB();
		return $count;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
}

?>