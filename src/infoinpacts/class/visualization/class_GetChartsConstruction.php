<?php

							/*----------------------------------------------------------------------*/
							/*  класс формирования данных для построения графиков и диаграмм (ОБЩИЙ)	*/
							/* 	 											v0.1	01.10.2014		*/
							/*----------------------------------------------------------------------*/

class GetChartsConstruction
{
protected $GeoIP;
protected $ReadXMLSetup;
protected $directoryRoot;
protected $getChart;
protected static $DBO;

function __construct()
	{
	//получаем корневую директорию сайта
	$dir = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directoryRoot = $dir[1];
	//объект БД GeoIP
	$this->GeoIP = new GeoIP();
	//объект для чтения файла setup_site.xml
	$this->ReadXMLSetup = new ReadXMLSetup;
	//объект для посторения графиков
	$this->getChart = new ChartsConstruction;
	}

//устанавливаем соединение с БД
protected static function getConnectionDB()
	{
	if(empty(self::$DBO)){
		self::$DBO = new DBOlink;
		}
	return self::$DBO;
	}

}
?>