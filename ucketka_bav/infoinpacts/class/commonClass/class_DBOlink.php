<?php

							/*--------------------------------------*/
							/*	класс соединения с БД методом PDO 	*/ 
							/*			(PHP Data Objects)			*/
							/*						v0.1 26.11.2013	*/
							/*--------------------------------------*/

class DBOlink
{
//данные для подключения к БД
private static $dbHost = "localhost";
private static $dbName = "data_on_KA";
private static $dbUser = "analyst_connect";	
private static $dbPassword = "BG5&*VCYi12_"; 

//соединение с БД
public function connectionDB() 
	{
	try{
		//Создание переменной $DBO (Database Handle)
		$DBO = new PDO("mysql:host=".self::$dbHost."; dbname=".self::$dbName."", self::$dbUser, self::$dbPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
		$DBO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
	catch (PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_DB_CONNECT, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	return $DBO;
	}

//отключение от БД
public function onConnectionDB() 
	{
	$this->DBO = null;
	}
}
?>