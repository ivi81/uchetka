<?php

										/*----------------------------------------------*/
										/*	 абстрактный класс информационной панели	*/
										/*							 v0.1 01.04.2014	*/
										/*----------------------------------------------*/
										
abstract class InfoPanel 
{
protected $DBO, $ReadXMLSetup, $role, $userLogin, $dirSite;
private $array_IconsName = array();

/*
для конструктора необходимо логин пользователя и его роль (оперативный дежурный, аналитик и т.д.)
*/
public function __construct($role, $userLogin, $dirSite) 
	{
	//объект БД
	$this->DBO = new DBOlink();
	//объект доступа к файлу setup_site.xml
	$this->ReadXMLSetup = new ReadXMLSetup;
	$this->role = $role;
	$this->userLogin = $userLogin;
	$this->dirSite = $dirSite;
	}
	
public function setIconsName(array $iconsName) 
	{
	$this->array_IconsName = $iconsName;
	}
		
protected function getIconsName()
	{
	if(!$this->array_IconsName){
		return MessageErrors::userMessageError(MessageErrors::ERROR_PHP, "\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: какие то проблеммы с массивом");
		}
	return $this->array_IconsName;;	
	}

//вывод информационной панели
abstract public function showInfoPanel();
//получение имени пользователя
abstract protected function showUserName();
//получение даты последнего посещения
abstract protected function dateEndVisit();
//проверка наличия информационных иконок
abstract protected function checkInfoIcons();

}										
?>