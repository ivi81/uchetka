<?php

						/*---------------------------------------------------------------*/
						/* 		основной конфигурационный файл + include классов	 	 	 */
						/*										 			09.04.2015	 */
						/*---------------------------------------------------------------*/

class ClassInclude
{
private	$array = array('authorization' => array('Authorization', 'CheckAuthorization'),
			       	   'commonClass' => array('AdminEditingTableDB', 'AnalystEditingTableDB',
											  'CreateTables', 'DBOlink', 'TotalInformationForSensor', 
											  'TotalInformationForSensorAdmin',                
											  'MessageErrors', 'ReadSnortRules',         
											  'ReadXMLSetup', 'ReviewMessage',
											  'ShowMessage', 'SearchComputerImpactProcess', 
											  'SearchComputerImpactShowForm', 'SimplyChat',
											  'TaskProcessMajor', 'TaskProcessWorker',
											  'UserEditingTableDB', 'WriteXMLSetupDomainName',
											  'WriteXMLSetup', 'WriteXMLSetupTypeIpList',
											  'WriteXMLSetupUser'),
			       	   'showInformation' => array('AllInformationForIncident',
												  'DisplayAnalyticalInformationAnalyst',
												  'DisplayAnalyticalInformation',
												  'SearchIncidentsAdmin',
												  'SearchIncidents',
												  'ViewSearchComputerImpact',
												  'ShowAllInformationIpAnalyst',
												  'ShowInformationPreparedMail',
												  'ShowStatisticsForUsers',
												  'ShowStatisticsGeoIpDateCountry',
												  'ShowStatisticsGeoIpDstIpCountry',
												  'ShowStatisticsGeoIpDstIp',
												  'ShowStatisticsGeoIpKAIsFalse',
												  'ShowStatisticsGeoIP',
												  'ShowStatisticsTimeReact',
												  'ShowStatisticsWorkerAndTypeKa',
												  'StatisticsGeoIp',
												  'SearchComputerImpactViewInformation',
												  'TablesIndexInformation',
												  'TopicalityShortInformation',
												  'BlockShortInformation',
												  'BlockShortInformationTable',
												  'GetSensorInformation'),
			   		   'tools' => array('AdministrationUsers', 'ControlBlackIpList',
										'ControlIpListGeoIP', 'ControlIpList',
										'ConversionData', 'CreateBinaryDBIpAddress',
										'CreateDocxMail', 'CreateDocx', 'CreateDocxStatement',
										'DeclansionWord', 'ExactilyUserData',
										'FactoryObjectForAiaxResponse', 'FormingMail',
										'GeoIP', 'InfoPanel', 'InfoPanelUsers', 'FormattingText',
										'ReadBinaryDBBlackList', 'ReadBinaryDB', 'SeverityRatingData'),
			   	   	   'visualization' => array('ChartsConstruction', 'GetChartsConstructionMajor',  
												'GetChartsConstruction', 'GetMenu', 'GetNewColor',
												'ListBox', 'PageOutput', 'ShowUserDate'));
public function getDirectory($className)
	{
	foreach($this->array as $key => $value){
		$test = array_search($className, $value);
		if($test === 0 || $test == true){
			return $key.'/';
			}
		}
	return '';
	}
}

//автозагрузка классов
function __autoload($className) 
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	if(($className == "Authorization") ||($className == "CheckAuthorization") || ($className == "ReadXMLSetup") || ($className == "DBOlink")){
		require_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/class/".(new ClassInclude)->getDirectory($className)."class_".$className.".php");
		} else {
		include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/class/".(new ClassInclude)->getDirectory($className)."class_".$className.".php");
		}
	}

//Подключаем классы
/*
				//основные классы по обработки информации
class_Authorization.php		//класс авторизации
class_CheckAuthorization.php		//класс проверки авторизации пользователя, наследник class_Authorization.php	
class_ReadXMLSetup.php		//класс чтения выбранных параметров из конфигурационного XML файла
class_WriteXMLSetup.php		//абстрактный класс чтения и записи в конфигурационный XML файл
class_WriteXMLSetupUser.php	//класс добавления и изменения записей о пользователе в конфигурационный XML файл
class_WriteXMLSetupDomainName.php 	//класс добавления и изменения записей о доменном имени в конфигурационный XML файл
class_WriteXMLSetupTypeIpList.php 	//класс удаление или добавление записи о типе списков IP-адресов
class_MessageErrors.php		//класс записи в лог-файл и вывод ошибок на экран
class_ShowMessage.php		//класс вывода сообщений пользователям 
class_DBOlink.php		//класс установления соединения с БД
class_CreateTables.php		//класс создания таблиц БД
class_ExactilyUserData.php		//класс проверки данных введенных пользователем
class_ReadSnortRules.php		//класс загрузки сигнатур СОА snort в БД
class_GeoIP.php		//класс геопривязки IP-адресов
class_AdminEditingTableDB.php 		//класс редактирования таблиц БД (администратор)
class_UserEditingTableDB.php		//класс редактирования таблиц БД (дежурный)
class_AnalystEditingTableDB.php		//класс редактирования таблиц БД (аналитик)
class_TotalInformationForSensor.php 	//класс изменения информации по сенсору (дежурный)
class_TotalInformationForSensorAdmin.php //класс создания и удаления нового сенора (администратор), наследник TotalInformationForSensor

				//визуализация
class_GetMenu.php		//класс вывода меню
class_ListBox.php		//класс вывода выпадающих списков
class_ShowUserDate.php		//класс календаря 
class_PageOutput.php		//класс вывода постраничных ссылок
class_GetNewColor.php 		//класс цветовой палитры
class_ChartsConstruction.php 	//класс построения диаграмм и графиков
class_GetChartsConstruction.php 	//класс получения информации для построения диаграмм (ОБЩИЙ)
class_GetChartsConstructionMajor.php 		//класс получения информации для построения диаграмм (Руководство)

				//информация по компьютерным воздействиям
class_TopicalityShortInformation.php		//класс вывода краткой актуальной информации
class_AllInformationForIncident.php		//вывод всей доступной информации по инцидентам
class_SearchComputerImpactShowFrom.php 		//класс вывода формы поиска компьютерного воздействия и полученной врезультате поиска информации		
class_SearchComputerImpactProcess.php 			//класс поиска компьютерных воздействий
class_SearchComputerImpactViewInformation.php 		//класс вывода формы поиска компьютерного воздействия и полученной врезультате поиска информации		
class_SearchIncidentsAdmin.php		//поиск и редактирование компьютерных атак (администратор)
class_TablesIndexInformation.php		//информация выводимая на страницу index.php
class_DisplayAnalyticalInformation.php 		//абстрактный класс вывода аналитической информации
class_DisplayAnalyticalInformationAnalyst.php 	//класс вывода аналитической информации (аналитик)
class_ShowAllInformationIpAnalyst.php 		//класс вывода полной информации по компьютерным инцидентам (аналитик)
class_ShowInformationPreparedMail.php 		//класс для вывода информации о подготовленных письмах (руководство)
class_BlockShortInformation.php 		//класс блока краткой информации о зафиксированным компьютерным воздействиям
class_BlockShortInformationTable.php 		//класс выводящий таблицу с информацией по ссылке получаемой с блока краткой информации
class_GetSensorInformation.php 		//класс вывода дополнительной информации по защищаемому сенсором сегменту сети
	//статистика по пользователям (дежурныи и аналитикам)
class_ShowStatisticsForUsers.php 		//класс вывода статистической информации по пользователям (дежурным и аналитикам)
class_ShowStatisticsWorkerAndTypeKa.php 		//класс вывода статистической информации в виде соотношения типов КА и дежурных
	//статистика по GeoIP
class_StatisticsGeoIp.php 		//абстрактный класс статистических данных по GeoIP 
class_ShowStatisticsGeoIP.php 		//класс вывода статистических данных по GeoIP
class_ShowStatisticsGeoIpDstIp.php 		//класс статистической информации по IP-адресу назначения (расширение класса class_ShowStatisticsGeoIP.php)
class_ShowStatisticsGeoIpDstIpCountry.php 		//класс статистической информации, статистическая информация по странам для IP-адресов назначения (расширение класса class_ShowStatisticsGeoIP.php)
class_ShowStatisticsGeoIpDateCountry.php 		//класс статистической информации, статистическая информация за отрезок времени по странам и IP-адресам назначения (расширение класса class_ShowStatisticsGeoIP.php)
class_ShowStatisticsGeoIpKAIsFalse.php 		//класс статистической информации, статистическая информация по ложным событиям информационной безопасности
	//статистика по времени реагирования
class_StatisticsTimeReact.php 		//абстрактный класс данных по времени реагирования
class_ShowStatisticsTimeReact.php 	//класс статистических данных по времени реагирования

				//информация по задачам поставленным руководством
class_AllInformationForTask.php		//вывод всей доступной информации по поставленной задаче

				//конвертирование
class_ConversionData.php		//конвертирование данных пользователя
class_DeclansionWord.php		//склонение слов

				//вспомогательные средства
class_SimplyChat.php		//простой чат, SimplyChat, между аналитиком и оперативными дежурными
class_ReviewMessage.php		//информирования пользователей SimplyChat о необходимости просмотра нового сообщения
class_TaskProcessMajor.php		//добавление задания оперативному дежурному (класс руководства)
class_TaskProcessWorker.php		//добавление задания оперативному дежурному (класс оперативного дежурного) наследник class_TaskProcessMajor
class_CreateDocx.php		//класс создания текстового документа в формате docx
class_CreateDocxMail.php		//класс создания официального письма в формате docx (наследник CreateDocx)
class_CreateDocxStatement.php		//класс создания отчета по компьютерным атакам в формате docx (наследник CreateDocx)
class_FormingMail.php		//класс формирования официального письма о компьютерных атаках
class_InfoPanel.php		//абстрактный класс формирования информационной панели
class_InfoPanelUsers.php		//класс формирования информационной панели для пользователя (дежурный и аналитик)
class_AdministrationUsers.php		//класс администрирования пользователей (администратор)
class_ControlIpList.php 		//абстрактный класс управления БД IP-адресов (администратор)
class_ControlIpListGeoIP.php 		//класс управления БД геопозиционирования (администратор)
class_ControlBlackIpList.php 		//класс управления таблицами IP-адресов принадлежащих сетям Tor, Bot-net и др. (администратор)
class_CreateBinaryDBIpAddress.php 		//класс создание бинарной базы данных списков IP-адресов
class_ReadBinaryDB.php 		//абстрактный класс чтения бинарной БД
class_ReadBinaryDBBlackList.php 	//класс чтения бинарной базы данных списка IP-адресов
class_SeverityRatingData.php 		//класс оценки критичности данных
class_FactoryObjectForAiaxResponse.php 		//класс-фабрика для получения объектов вызываемых Ajax запросами 
class_FormattingText.php 		//класс форматирования выводимого текста
*/

//массив основного меню руководства
$menu_major = array('главная' => '/infoinpacts/major/index.php',
					'добавить задачу' => '/infoinpacts/major/process/add_problem.php',
					'статистика' => '/infoinpacts/major/process/statistics.php',
					'оценки' => '#',
					'поиск' => '/infoinpacts/major/process/search_incidents.php',
					'выход' => '/infoinpacts/index.php?Quit=quit');

//массив основного меню дежурного
$menu_worker = array('главная' => '/infoinpacts/worker/index.php',
					 'добавить информацию' => '/infoinpacts/worker/process/addComputerImpact.php',
//					 'сенсоры' => '',
					 'сенсоры' => '/infoinpacts/worker/process/sensorInformation.php',
					 'статистика' => '/infoinpacts/worker/process/statistics.php',
					 'задачи' => '/infoinpacts/worker/process/solve_problem.php',
					 'чат' => '/infoinpacts/worker/process/simply_chat.php',							 		
					 'поиск' => '/infoinpacts/worker/process/search_incidents.php',
					 'выход' => '/infoinpacts/index.php?Quit=quit');

//массив основного меню аналитика
$menu_analyst = array('главная' => '/infoinpacts/analyst/index.php',
					  'статистика' => '/infoinpacts/analyst/process/statistics.php',
					  'задачи' => '/infoinpacts/analyst/process/solve_problem.php',
					  'DDoS-атаки' => '',
					  'чат' => '/infoinpacts/analyst/process/simply_chat.php',
 					  'поиск' => '/infoinpacts/analyst/process/search_incidents.php',
					  'выход' => '/infoinpacts/index.php?Quit=quit');

//массив цветов для рамок сообщений в чате
$array_color_message_chat = array('trojan' => '#FFFF00',
								  'sergeev' => '#228B22',
								  'ippolitov' => '#6B8E23',
								  'chin' => '#0000FF',
								  'polykov' => '#CD0000',
								  'checking' => '#8B658B',
								  'artemiy' => '#32CD32',
								  'vitaliy' => '#2F4F4F');

//время старее которого их таблицы simply_chat будут удалены все записи
$simply_chat_time_delete = 2419200; //4 недели

//константа в которой храниться время после которой поставленные руководством задачи считаются не актуальными и подлежат удалению
define('OLD_TASK', '604800'); // 7 суток

//константа в которой хранится время после истечении которого включается информирование о невыполненной СРОЧНОЙ задаче
define('TIME_TASK_EXPRESS', '86400'); // 24 часа

//константа цвета заголовка таблицы
define('COLOR_HEADER', "bgcolor='#87CEEB'");

//константа пути к конфигурационному файлу
define('XML_FILE', 'setup_site.xml');

//функция чередования цветов таблицы
function color() 
	{
	static $a = 1; 
	((substr_count(($a/2),".")) == 0) ? $color = "#D1E7F7" : $color = "#B7DCF7"; 
	$a++;	
	return $color;			
	}

//функция изменения цвета (0 - синий, любое другое значение - красный)
function colorRedBlue($num) 
	{
	if($num != 0){
		//красный цвет
		return "<span style='color: #FF0000;'>".$num."</span>";
		} else {
		//синий цвет
		return "<span style='color: #3300FF;'>".$num."</span>";
		}
	}

?>