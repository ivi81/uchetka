<?php

						/*----------------------------------------*/
						/*  класс для чтения файла setup_site.xml */
						/* 						v.0.1 04.02.2014  */
						/*----------------------------------------*/

class ReadXMLSetup
{
private $readXML_file;
//открытие и чтение файл setup_site.xml
public function __construct()
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 
	
	$readXML_file = simplexml_load_file($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/config/".XML_FILE);
	$this->readXML_file = $readXML_file;
	}
	
//получение учетных данных (login, password) необходимых для сравнения
public function accounts($login) 
	{
	$a = 0;
	foreach($this->readXML_file->accounts->users as $user){
		if($user['login'] == $login){
			return $user['login'].":".$user['idaccount'].":".$this->readXML_file->accounts->users[$a];
			}
		$a++;		
		}
	}

//получить Ф.И.О. пользователя по его логину
public function usernameFIO($login) 
	{
	$a = 0;
	foreach($this->readXML_file->accounts->users as $user){
		if($user['login'] == $login){
			return $user['name'];
			}
		$a++;		
		}	
	}
	
//получить ТОЛЬКО фамилию и имя пользователя по его логину	
public function giveUserNameAndSurname($login) 
	{
	list($surname, $name,) = explode(" ", $this->usernameFIO($login));
	return $surname." ".$name;
	}

//получение массива содержащего login пользователя и его Ф.И.О.
public function getArrayUserName() 
	{
	$array = array();
	foreach($this->readXML_file->accounts->users as $user){
		$array[(string) $user['login']] = (string) $user['name'];
		}
	return $array;
	}
	
//получение массива содержащего login пользователей принадлежащих определенной группе
public function getArrayUserNumGroup($id) 
	{
	$array = array();
	foreach($this->readXML_file->accounts->users as $user){
		if((string) $user['idaccount'] == $id)
			$array[] = (string) $user['login'];
		}
	return $array;
	}

//получение многомерного массива содержащего все информацию о пользователе, при этом группа пользователя является ключём массива
public function getArrayAllUsersInform() 
	{
	$a = 0;
	$array = array();
	foreach($this->readXML_file->accounts->users as $user){
		if($user['login'] != 'admin'){
			$array[(string) $user['idaccount']][(string) $user['login']]['name'] = (string) $user['name'];
			$array[(string) $user['idaccount']][(string) $user['login']]['password'] = (string) $this->readXML_file->accounts->users[$a];
			$a++;
			}
		}
	return $array;
	}
	
//формирование массива где ключом массива является IP-адрес, а элементом доменное имя
public function giveDomainName() 
	{
	$domain_name = array();
	foreach($this->readXML_file->table_ip_address->ip_address as $ip){
		$domain_name["$ip"][] = $ip['domname'];
		}
	return $domain_name;
	}

//формирование массива критичных IP-адресов назначения
public function giveCriticalDomainName() 
	{
	$array = array();
	foreach($this->readXML_file->critical_ip_dst->c_ip_dst as $ip){
		$array[] = $ip;
		}
	return $array;
	}	

//преобразование IP-адреса в его доменное имя
public function obtainDomainName($ip) 
	{
	foreach($this->readXML_file->table_ip_address->ip_address as $ip_address){
		if($ip == $ip_address){
			return $ip_address['domname'];
			}
		}
	}
	
//получение доменного имени и названия информационного ресурса по IP-адресу
public function obtainFullInfoForIP($ip) 
	{
	$array = array();
	foreach($this->readXML_file->table_ip_address->ip_address as $ip_address){
		if($ip == $ip_address){
			$domname = (string) $ip_address['domname'];
			$array[$domname] = (string) $ip_address['domfullname'];
			}
		}
	return $array;	
	}
	
//формирование многомерного массива содержащего IP-адрес, доменное имя и официальное название Web-сайта
public function getArrayAllInformWebSite() 
	{
	$array = array();
	foreach($this->readXML_file->table_ip_address->ip_address as $ip){
		$array[(string) $ip][(string) $ip['domname']] = (string) $ip['domfullname'];
		}
	return $array;
	}
	
//формирование массива где ключом массива является идентификатор КА, а элементом название КА
public function giveTypeKA() 
	{
	$type_KA = array();
	foreach($this->readXML_file->computer_attack->type_ka as $type){
		$type_KA["{$type['id_ka']}"] = $type;
		}
	return $type_KA;
	}	
	
//получаем название КА по ее идентификатору
public function giveTypeKAForId($id) 
	{
	foreach($this->readXML_file->computer_attack->type_ka as $key => $type){
		if($type['id_ka'] == $id){
			return $type;
			}
		}
	return false;
	}

//получаем список типов IP-адресов
public function giveListTypeIpAddress()
	{
	$array = array();
	foreach($this->readXML_file->list_ip->type_list_ip as $key => $value){
		$array[(string) "{$value['type_id']}"][0] = (string) $value;
		$array[(string) "{$value['type_id']}"][1] = (string) $value['type_info'];
		}
	return $array;
	}

//получаем название типа списка IP-адресов по коду
public function giveTypeIpList($code)
	{
	foreach($this->readXML_file->list_ip->type_list_ip as $value){
		if($value['type_id'] == $code){
			return $value;
			}
		}
	return "тип не определен";
	}
}
?>