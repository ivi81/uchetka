<?php

							/*------------------------------------------*/
							/*  класс вывода аналитической информации	*/
							/* 	 				v0.1	27.08.2014		*/
							/*------------------------------------------*/

abstract class DisplayAnalyticalInformation
{
protected $directoryRoot;
protected static $DBO;

function __construct()
	{
	//получаем корневую директорию сайта
	$dir = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directoryRoot = $dir[1];
	}

//устанавливаем соединение с БД
protected static function getConnectionDB()
	{
	if(empty(self::$DBO)){
		self::$DBO = new DBOlink;
		}
	return self::$DBO;
	}

//получаем доменное имя
protected static function getDomainName($ip /* IP-адрес с точками */)
	{
	$ReadXMLSetup = new ReadXMLSetup();
	if($ReadXMLSetup->obtainDomainName($ip))
		return " (<span style='font-weight: bold;'>".$ReadXMLSetup->obtainDomainName($ip)."</span>)";
	}

//вывод аналитической информации по сигнатуре
abstract public function showInformationSid(array $sid);

//вывод аналитической информации по IP-адресу источника
abstract public function showInformationSrcIp(array $ipSrc);
}

?>