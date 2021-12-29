<?php

										/*--------------------------------------*/
										/*		класс конвертирования данных	*/
										/*					 v0.1 09.01.2014	*/
										/*--------------------------------------*/

class ConversionData
{

//функция конвертирования заданной даты в письменный вид (ВХОДНАЯ ДАТА В ФОРМАТЕ UNIX)
public static function showDateConvert($unix_date) 
	{
	$a = array("-01-","-02-","-03-","-04-","-05-","-06-","-07-","-08-","-09-","-10-","-11-","-12-");
	$b = array(" января "," февраля "," марта "," апреля "," мая "," июня "," июля "," августа "," сентября "," октября "," ноября "," декабря ");
	if(substr(date("d-m-Y", $unix_date), 0, 1) == 0){
		$day = substr(date("d-m-Y", $unix_date), 1, 1);			
		} else {
		$day = substr(date("d-m-Y", $unix_date), 0, 2);	
		}	
	$month = str_replace($a, $b, substr(date("d-m-Y", $unix_date), 2, 4));
	$year = substr(date("d-m-Y", $unix_date), 6, 4);
	$newdate = $day.$month.$year." года";
	return $newdate;
	}
	
//функция конвертирования заданной даты в письменный вид (ВХОДНАЯ ДАТА в виде 'год-месяц-день часы:мин:сек')
public static function showDateConvertStr($string_date)
	{
	$a = array("-01-","-02-","-03-","-04-","-05-","-06-","-07-","-08-","-09-","-10-","-11-","-12-");
	$b = array(" января "," февраля "," марта "," апреля "," мая "," июня "," июля "," августа "," сентября "," октября "," ноября "," декабря ");
	if(substr($string_date, 8, 1) == 0){
		$day = substr($string_date, 9, 1);			
		} else {
		$day = substr($string_date, 8, 2);	
		}	
	$month = str_replace($a, $b, substr($string_date, 4, 4));
	$year = substr($string_date, 0, 4);
	$newdate = $day.$month.$year." года";
	return $newdate;
	}
	
//функция конвертирования заданной даты в название месяца (ВХОДНАЯ ДАТА в виде 'год-месяц-день часы:мин:сек')
public static function showMonth($string_date)
	{
	$array = explode(' ', self::showDateConvertStr($string_date));
	return $array[1];	
	}
}
?>