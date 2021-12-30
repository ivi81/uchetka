<?php

						/*----------------------------------------------------------*/
						/*  		класс проверки данных вводимых пользователем	*/
						/* 					 					v.0.1 28.04.2015    */
						/*----------------------------------------------------------*/

class ExactilyUserData
{
	
//ДАТА 
public static function takeDate($date) 
	{
	if(isset($date)){
		if(!preg_match("/^[0-9]{4}[\-][0-9]{2}[\-][0-9]{2}$/D", $date)){
			MessageErrors::showInformationError("не верная дата");			
			exit();
			}
		return $date;
		}
	}	

//ВРЕМЯ 
public static function takeTime($time) 
	{
	if(isset($time)){
		if(!preg_match("/^[0-9]{2}[\:][0-9]{2}$/D", $time)){
			MessageErrors::showInformationError("не верное время"); 
			exit();
			}
		return $time;
		}
	}	

//получить проверенный IP-адрес
public function getIp($ip)
	{
	$ip = trim($ip);
	if(preg_match("/^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/", $ip)){
		return $ip;
		}
	return false;
	}

//получить массив проверенных IP-адресов (входные данные массив или строка) 
public static function takeIP($ip) 
	{
	if(!isset($ip)){
		MessageErrors::showInformationError("IP-адрес не задан"); 
		exit(); 
		}
	$array_ip = array();
	if(is_array($ip)){
		$ip = array_unique($ip);
		foreach($ip as $value){
			if(preg_match("/^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/", $value))
			$array_ip[] = $value;
			}
		} else {
		//регулярное выражение поиска IP-адресов с учетом максимального значения каждого октета
		if(preg_match_all("/(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}/", $ip, $ip_address, PREG_PATTERN_ORDER)){
			$array_ip = $ip_address[0];
			}
		}
	return $array_ip;
	}

//получить массив состоящий из пар IP-адресов (входные данные два массива)
public static function giveArrayIpAddress(array $array_ip_start, array $array_ip_end)
	{
	$array_result = array();
	if(!is_array($array_ip_start) || !is_array($array_ip_end)){ 
		return ShowMessage::showInformationError('неверные входные данные');
		}

	$countArrayIpStart = count($array_ip_start);
	$countArrayIpEnd = count($array_ip_end);

	if($countArrayIpStart != $countArrayIpEnd){
		return ShowMessage::showInformationError('количество элементов массива не совпадает');
		}

	//создаем временный массив
	for($i = 0; $i < $countArrayIpStart; $i++){
		$array_tmp[$i][0] = $array_ip_start[$i];
		$array_tmp[$i][1] = $array_ip_end[$i];
		}

	//выполняем проверку IP-адресов
	$count_array_tmp = count($array_tmp);
	for($j = 0; $j < $count_array_tmp; $j++){
		$ipStart = $ipEnd = false;
		if(preg_match("/^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/", $array_tmp[$j][0])){
			$ipStart = $array_tmp[$j][0];
			}
		if(preg_match("/^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/", $array_tmp[$j][1])){
			$ipEnd = $array_tmp[$j][1];
			}
		if($ipStart == false || $ipEnd == false) continue;
		if(ip2long($ipStart) > ip2long($ipEnd)) continue;
		$array_result[$j]['ipStart'] = $ipStart;
		$array_result[$j]['ipEnd'] = $ipEnd;
		}
	return $array_result;
	}
	
//получить массив проверенных числовых значений например, номеров сигнатур (входные данные массив или строка где разделитель пробел или перенос строки)
public static function takeSid($num) 
	{
	if(!isset($num)){
		MessageErrors::showInformationError("не заданно числовое значение"); 
		exit(); 
		}
	$array_num = array();
	if(is_array($num)){
		$num = array_unique($num);
		foreach($num as $value){
			if(preg_match("/^[0-9]+$/", $value)){
				$array_num[] = $value;
				}
			}
		} else {
		//замена всех символов перевода строки на _
		$num = str_replace("\n", "_", $num);
		//замена всех символов пробела на _
		$num = str_replace(" ", "_", $num);
		//замена __ на _
		$num = str_replace("__", "_", $num);
		$array = explode("_", $num);
		foreach($array as $value){
			$array_num[] = intval($value);
			}
		}

	return $array_num;
	}

//любое числовое значение	
public static function takeIntager($number) 
	{
	if(isset($number)){
		if(!preg_match("/^[0-9]*$/", $number)){ 
			return MessageErrors::showInformationError("введённое выражение не является числом"); 
			}
		return intval($number);
		}
	}	
	
//строку которая может содержать необходимые специальные символы
public static function takeStringAll($string) 
	{
	if(isset($string)){
		return htmlspecialchars($string);
		}
	return MessageErrors::showInformationError("передана пустая строка");
	}

//строку в которые специальных символов быть не должно	
public static function takeString($string) 
	{
	if(isset($string)){
		return addslashes($string);
		}
	return MessageErrors::showInformationError("передана пустая строка");
	}	
}

?>