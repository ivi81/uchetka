<?php

						/*--------------------------------------*/
						/*  	поиск информации по бинарной БД	*/
						/* 					 v.0.1 29.07.2014   */
						/*--------------------------------------*/

abstract class ReadBinaryDB
{
protected static $DBO;

//устанавливаем соединение с БД
protected static function getConnectionDB()
	{
	if(empty(self::$DBO)){
		self::$DBO = new DBOlink;
		}
	return self::$DBO;
	}
//устанавливаем IP-адрес
abstract public function setIp($ipAddress);
//вывод информации о бинарной БД
abstract public function showInfoBinaryBD();
//вывод информации о найденом в бинарной БД IP-адресе
abstract public function showInfoSearchIp();
//поиск одного IP-адреса в бинарной БД
abstract public function searchIp();
}
?>