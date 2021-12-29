<?php

						/*------------------------------------------*/
						/*  		управление списками IP-адресов 	*/
						/* 					 	 v.0.1 06.06.2014   */
						/*------------------------------------------*/

abstract class ControlIpList
{
//ресурс соединения с БД
protected static $DBO;

//получаем ресурс для доступа к БД
public static function getConnectionDB() 
	{
	if(empty(self::$DBO)){
		self::$DBO = new DBOlink;
		}
	return self::$DBO;
	}

//вывод общей информации о таблице БД содержащей списки IP-адресов
abstract function showBDInfo();
//создание таблицы БД
abstract protected function createIpList();
//обновление списков IP-адресов
abstract function updateIpList();
//отсоединение от БД
abstract function closeConnectionDB();
}
?>