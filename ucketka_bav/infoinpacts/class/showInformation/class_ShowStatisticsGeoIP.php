<?php

							/*------------------------------------------*/
							/*  класс вывода статистической информации 	*/
							/*	 по разделу геопозиционирования GeoIP	*/
							/* 	 					v.0.2 12.11.2014    */
							/*------------------------------------------*/

class ShowStatisticsGeoIP
{
//директория сайта
public static $directory;
//объект подключения к БД
private static $DBO;
//объект для чтения файла XML
private static $XML;
const DST_IP = 1;
const COUNTRY_DST_IP = 2;
const DATE_COUNTRY_DST_IP = 3;
const FALSE_KA = 4;

function __construct()
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	self::$directory = $array_directory[1];
	?>
<!-- подключаем jQuery -->
	<script src="<?php echo '/'.self::$directory.'/js/jquery-2.1.1.min.js' ?>"></script>	
<!-- подключаем HighChart.js -->
	<script src="<?php echo '/'.self::$directory.'/js/highcharts.js' ?>"></script>
	<script src="<?php echo '/'.self::$directory.'/js/exporting.js' ?>"></script>
	<?php
	}

//формируем подключение к БД
private static function linkDataBase()
	{
	if(empty(self::$DBO)){
		//объект для подключения к БД
		self::$DBO = new DBOlink(); 
		}
	return self::$DBO;
	}

//получаем объект чтения XML
private static function getXmlRead()
	{
	if(empty(self::$XML)){
		self::$XML = new ReadXMLSetup();
		}
	return self::$XML;
	}

//получить статистическую информацию по GeoIP
public function getStatictics($num)
	{
	switch($num){
		//статистическая информация по IP-адресу назначения
		case self::DST_IP:
			(new ShowStatisticsGeoIpDstIp(self::linkDataBase(), self::getXmlRead()))->showInformation();
		break;
		//статистическая информация по странам для IP-адресов назначения
		case self::COUNTRY_DST_IP:
			(new ShowStatisticsGeoIpDstIpCountry(self::linkDataBase(), self::getXmlRead()))->showInformation();
		break;		
		//статистическая информация за отрезок времени по странам и IP-адресам назначения
		case self::DATE_COUNTRY_DST_IP:
			(new ShowStatisticsGeoIpDateCountry(self::linkDataBase(), self::getXmlRead()))->showInformation();
		break;
		//статистическая информация по ложным компьютерным атакам
		case self::FALSE_KA:
			(new ShowStatisticsGeoIpKAIsFalse(self::linkDataBase(), self::getXmlRead()))->showInformation();
		break;		
		} 
	}
}	
?>