<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Поиск и замена is Null в названии страны</title>
</head>
	<body>

<?php

/*
	Скрипт выполняет поиск пустых полей с атрибутом country
	и осуществляет запись в них найденного кода страны или кода "10"
	если значение не найденно
	
	для работы необходм класс DBOlink
*/

require "class/commonClass/class_DBOlink.php";
//include "class/commonClass/class_MessageErrors.php";

class SearchAreaIsNull
{
private static $PDO;
function __construct()
	{
	}

//ресурс БД
private static function linkDB()
	{
	if(empty(self::$PDO)){
		self::$PDO = new DBOlink;
		}
	return self::$PDO;
	}

//поиск is Null
private function searchCountryIsNull()
	{
	$arrayTmp = array();
	try{
		$query = self::linkDB()->connectionDB()->query("SELECT `ip_src` FROM `incident_chief_tables` WHERE `country` is Null");
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$arrayTmp[] = $row->ip_src;
			}
		return $arrayTmp;
		}
	catch(PDOException $e){
		echo "файл: ".$e->getFile()." линия: ".$e->getLine()." ошибка: ".$e->getMessage();
		}
	}

//замена is Null
private function replaceIsNull(array $arrayIsNull)
	{
	$sumReplaceCountry = 0;
	try{
		foreach($arrayIsNull as $ip){
			$query = self::linkDB()->connectionDB()->query("SELECT `code` FROM `geoip_data` WHERE `start`<{$ip} && {$ip}<`end`");
			$row = $query->fetch(PDO::FETCH_OBJ);
			if(!empty($row->code)){
				self::linkDB()->connectionDB()->query("UPDATE `incident_chief_tables` SET `country`='".$row->code."' WHERE `ip_src`={$ip}");
				$sumReplaceCountry++;
				}
			}
	return $sumReplaceCountry;
	}
	catch(PDOException $e){
		echo "файл: ".$e->getFile()." линия: ".$e->getLine()." ошибка: ".$e->getMessage();
		}
	}

//вывод результата
public function showResultSearchCountryIsNull()
	{
	//получаем массив ip_src с country is Null
	$arrayCountryIsNull = $this->searchCountryIsNull();
	echo '<p>Всего найденно пустых полей = '.count($arrayCountryIsNull).'</p>';	
	echo '<p>Всего найденно стран = '.$this->replaceIsNull($arrayCountryIsNull).'</p>';
	}
}

$SearchAreaIsNull = new SearchAreaIsNull;
$SearchAreaIsNull->showResultSearchCountryIsNull();

?>

	</body>
</html>
