<?php

						/*------------------------------------------------------*/
						/*			класс создания таблиц БД "data_on_KA"		*/
						/*							v.0.2 28.01.2014 	   		*/
						/*------------------------------------------------------*/

class CreateTables
{

public function __construct() 
	{
	try{
		//объект для подключения к БД
		$DBO = new DBOlink();
		
		//создание таблицы учета компьютерных инцидентов incident_chief_tables (основная) 
/*
id - порядковый номер инцидента
date_time_incident_start - дата и время инцидента, начало
date_time_incident_end - дата и время инцидента, конец
date_time_create - дата и время внесения инцидента в таблицу
ip_src - IP-адрес источника
count_impact - количество воздействий с каждого IP-адреса
ip_dst - IP-адрес назначения
type_attack - тип компьютерной атаки (в числовом виде, расшифровка в файле setup_site.xml)
country - код страны
*/
		$DBO->connectionDB()->query("CREATE TABLE IF NOT EXISTS `incident_chief_tables` (
								     id INT(9) UNSIGNED NOT NULL,
									 date_time_incident_start DATETIME NOT NULL,
									 date_time_incident_end DATETIME NOT NULL,
									 date_time_create INT NOT NULL,
						  			 ip_src INT(10) UNSIGNED NOT NULL,
						  			 count_impact INT(10),
									 ip_dst INT(10) UNSIGNED NOT NULL,
									 type_attack TINYINT(3) NOT NULL,
									 country VARCHAR(2),
									 INDEX index_for_id(id),
									 INDEX index_for_date_time_incident(date_time_incident_start),
									 INDEX index_for_ip_src(ip_src),
									 INDEX index_for_ip_dst(ip_dst),
									 INDEX index_for_type_attack(type_attack),
									 INDEX index_for_country(country)) ENGINE=MyISAM DEFAULT CHARSET=utf8");

		//создание таблицы учета компьютерных инцидентов incident_additional_tables (вся дополнительная информация)
/*
id - порядковый номер инцидента
login_name - логин дежурного
availability_host - доступность информационного ресурса
direction_attack - направление компьютерной атаки
solution - решение дежурного
number_mail_in_CIB - номер письма в ЦИБ
number_mail_in_organization - номер письма в стороннюю организацию
space_safe - место хранения трафика
explanation - пояснение дежурного
*/
		$DBO->connectionDB()->query("CREATE TABLE IF NOT EXISTS `incident_additional_tables` (
								     id INT(9) UNSIGNED NOT NULL,
									 login_name VARCHAR(20) NOT NULL,						  					  
									 availability_host	TINYINT(2) UNSIGNED,
									 direction_attack TINYINT(2) UNSIGNED NOT NULL,
									 solution TEXT,
									 number_mail_in_CIB VARCHAR(35),
									 number_mail_in_organization VARCHAR(35),
									 space_safe TEXT NOT NULL,
									 explanation TEXT,
									 PRIMARY KEY(id),
									 INDEX index_for_login_name(login_name),
									 INDEX index_for_mail_CIB(number_mail_in_CIB(4))) ENGINE=MyISAM DEFAULT CHARSET=utf8");

		//создание таблицы учета компьютерных инцидентов incident_number_signature_tables (вся о сработавших сигнатурах и их количестве)
/*
id - порядковый номер инцидента
sid - номер сигнатуры
count_alert - количество срабатываний
*/
		$DBO->connectionDB()->query("CREATE TABLE IF NOT EXISTS `incident_number_signature_tables` (
									 id INT(9) UNSIGNED NOT NULL,
									 sid INT(10) NOT NULL,
									 count_alert INT(10),
									 INDEX index_for_id(id),
									 INDEX index_for_sid(sid)) ENGINE=MyISAM DEFAULT CHARSET=utf8");

		//создание таблицы учета компьютерных инцидентов incident_analyst_tables (информация аналитика)
/*
id - порядковый номер инцидента
login_name - логин аналитика
true_false - компьютерная атака или нет и пометка - нет трафика
			
			Выпадающий список:
				0 - ложное срабатывание (false),
 				1 - компьютерная атака (true), 
				2 - сетевого трафика по указанному пути не обнаружено
 				
count_alert_analyst - количество срабатываний
information_analyst - информация аналитика
date_time_analyst - время анализа компьютерного воздействия
*/
		$DBO->connectionDB()->query("CREATE TABLE IF NOT EXISTS `incident_analyst_tables` (
									 id INT(9) UNSIGNED NOT NULL,
									 login_name VARCHAR(20) NOT NULL,						  					  
									 true_false TINYINT(2) NOT NULL,
									 count_alert_analyst INT,
									 information_analyst TEXT,
									 date_time_analyst INT,
									 PRIMARY KEY(id),
									 INDEX index_for_login_name(login_name),
									 INDEX index_for_true_false(true_false)) ENGINE=MyISAM DEFAULT CHARSET=utf8");

		//создание таблицы простейшего чата simply_chat
/*
date_time - дата и время размещения сообщения											  								  
login_source - логин разместившего сообщение
login_addressee - логин или логины получателей сообщения
message TEXT - текст сообщения
who_reade TEXT - логин человека просмотревшего сообщение которое ему было адресовано 	
*/			
		$DBO->connectionDB()->query("CREATE TABLE IF NOT EXISTS `simply_chat` (
									 date_time DATETIME NOT NULL,											  								  
									 login_source VARCHAR(15) NOT NULL,
									 login_addressee TEXT,
									 message TEXT,
									 who_reade TEXT, 								  
									 INDEX index_for_date_time(date_time)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
											  
//создание таблиц problem_table_basic и problem_table_additional используемых для хранения задач
	//problem_table_basic
/*
task_id - порядковый номер задачи
task_date_time - дата и время постановки задачи											  								  
task_criticality - критичность задачи
task_show - задача
*/			
		$DBO->connectionDB()->query("CREATE TABLE IF NOT EXISTS `problem_table_basic` (
									 task_id INT(5) UNSIGNED AUTO_INCREMENT NOT NULL,
									 task_date_time DATETIME NOT NULL,
									 login_source VARCHAR(15) NOT NULL,											  								  
									 task_criticality VARCHAR(15) NOT NULL,
									 task_show TEXT,
									 INDEX index_for_task_date_time(task_date_time),
									 PRIMARY KEY(task_id)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	//problem_table_additional
/*
task_id - порядковый номер задачи
task_date_time_change - время модификации хода выполнения задачи
task_login_addressee - логин исполнителя
task_progress - ход выполнения задачи
task_message_addressee - пояснение исполнителя
*/			
		$DBO->connectionDB()->query("CREATE TABLE IF NOT EXISTS `problem_table_additional` (
									 task_id INT(5) UNSIGNED NOT NULL,
									 task_date_time_change DATETIME NOT NULL,
									 task_login_addressee VARCHAR(15) NOT NULL,
									 task_progress INT(4),
									 task_message_addressee TEXT,
									 INDEX index_for_task_login_addressee(task_login_addressee)) ENGINE=MyISAM DEFAULT CHARSET=utf8");

	//sensor_information_main_one
/*
sensor_id - номер сенсора
ip_start - начальный IP-адрес защищаемого сегмента
ip_end - конечный IP-адрес защищаемого сегмента
*/

		$DBO->connectionDB()->query("CREATE TABLE IF NOT EXISTS `sensor_information_main_one` (
									 sensor_id INT(5) UNSIGNED NOT NULL, 
									 ip_start INT(10) UNSIGNED NOT NULL, 
									 ip_end INT(10) UNSIGNED NOT NULL, 
									 INDEX index_for_ip_start(ip_start), 
									 INDEX index_for_ip_end(ip_end)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	
	//sensor_information_main_two
/*
sensor_id - номер сенсора
add_date_sensor - дата добавления или изменения информации 
login - логин ответственного дежурного 
ip_address_sensor - IP-адрес сенсора 
sensor_chort_name - краткое описание защищаемого сегмента
sensor_all_information - подробное описание защищаемого сегмента
sensor_contacts - контактные данные организации где установлен сенсор
*/		

		$DBO->connectionDB()->query("CREATE TABLE IF NOT EXISTS `sensor_information_main_two` (
									 sensor_id INT(5) UNSIGNED NOT NULL, 
									 add_date_sensor INT(10) UNSIGNED NOT NULL, 
									 login VARCHAR(10) NOT NULL, 
									 ip_address_sensor INT(10) UNSIGNED NOT NULL, 
									 sensor_chort_name TEXT, 
									 sensor_all_information TEXT, 
									 sensor_contacts TEXT,
									 INDEX index_for_sensor_id(sensor_id), 
									 INDEX index_for_add_date_sensor(add_date_sensor), 
									 INDEX index_for_ip_address_sensor(ip_address_sensor)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
		
		//sensor_information_analyst
/*
sensor_id - номер сенсора
ip_address - IP-адрес информация по которому добавляется
server_type - тип сервера
domain_name - доменное имя сервера (если есть)
open_network_ports - открытые сетевые порты
network_protocols - используемые сетевые протоколы
addition_information_analyst - дополнительная информация
*/

		$DBO->connectionDB()->query("CREATE TABLE IF NOT EXISTS `sensor_information_analyst` (
									 sensor_id INT(5) UNSIGNED NOT NULL,
									 ip_address INT(10) UNSIGNED NOT NULL,
									 server_type VARCHAR(255) NOT NULL,
									 domain_name VARCHAR(100),
									 open_network_ports TEXT,
									 network_protocols TEXT,
									 addition_information_analyst TEXT,
									 INDEX index_for_sensor_id(sensor_id),
									 INDEX index_for_ip_address(ip_address)) ENGINE=MyISAM DEFAULT CHARSET=utf8");

		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	//закрываем соединения с БД
	$DBO->onConnectionDB();	
	}
}

?>