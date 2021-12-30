<?php
						
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		скрипт обработки AJAX запросов (аналитик)			*/
						/*		поиск информации:  									*/
						/*		 - о количестве компьютерных воздействий за год		*/
						/* 		 - для вывода статистической информации				*/
						/* 															*/
						/*									v.0.1 13.02.2015		*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

session_start();
//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
//подключаем config
require_once ($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/config/config.php");

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

			/*----------------------------------------------------------------------/
				Для вывода информации о количестве компьютерных воздействий за год
									для блока краткой информации
			/----------------------------------------------------------------------*/

if(isset($_POST['queryShortInformationYear']) && !empty($_POST['queryShortInformationYear'])){
	$choiceYear = $_POST['queryShortInformationYear'];
    echo $choiceYear.':'.BlockShortInformation::getCountImpactYear($choiceYear).':'.BlockShortInformation::getCountAnalyseInformationYear($choiceYear).':'.BlockShortInformation::getCountTraffikNotLook($choiceYear).':'.BlockShortInformation::getCountFalseImpactYear($choiceYear);
	}

			/*----------------------------------------------------------------------/
						Для поска информации по компьютерным воздействиям
			/----------------------------------------------------------------------*/

if(isset($_POST['searchStart'])){
	//выполняем поиск и выводим результат
	$SearchComputerImpactViewInformation = new SearchComputerImpactViewInformation();
	echo $SearchComputerImpactViewInformation->viewInformation();
	}

			/*---------------------------------------------------------------------/
							    Для вывода статистической информации
			/---------------------------------------------------------------------*/

if(isset($_POST['queryStatistics']) && !empty($_POST['queryStatistics'])){
	switch($_POST['queryStatistics']){
		// GeoIP информация по IP-адресу назначения
		case 'GeoIp_1';
			FactoryObjectForAiaxResponse::getObjectForAjaxResponse('statisticsGeoIP')->getStatictics(ShowStatisticsGeoIP::DST_IP);
		break;
		// GeoIP информация по странам для IP-адресов назначения
		case 'GeoIp_2';
			FactoryObjectForAiaxResponse::getObjectForAjaxResponse('statisticsGeoIP')->getStatictics(ShowStatisticsGeoIP::COUNTRY_DST_IP);
		break;
		// GeoIP информация за отрезок времени по странам и IP-адресам назначения
		case 'GeoIp_3';
			FactoryObjectForAiaxResponse::getObjectForAjaxResponse('statisticsGeoIP')->getStatictics(ShowStatisticsGeoIP::DATE_COUNTRY_DST_IP);
		break;
		// GeoIP информация по ложным компьютерным атакам
		case 'GeoIp_4';
			FactoryObjectForAiaxResponse::getObjectForAjaxResponse('statisticsGeoIP')->getStatictics(ShowStatisticsGeoIP::FALSE_KA);
		break;
		}
	}
?>