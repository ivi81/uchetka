<?php

										/*----------------------------------------------------------*/
										/*	класс формировани текста письма о компьютерной атаки		*/
										/*										v0.12 16.03.2015	*/
										/*----------------------------------------------------------*/

class FormingMail
{

private $array_mails, $ReadXMLSetup;
public function __construct(array $array)
	{	
	$this->array_mails = $array;
	//объект для чтения файла setup_site.xml
	$this->ReadXMLSetup = new ReadXMLSetup;
	}

//получаем начальное и конечное время
private function getStartEndTime($time) 
	{
	//поиск начальной даты
	foreach($this->array_mails as $value){
		$startTimestamp[] = strtotime($value['date_start'][0]);	
		}
		sort($startTimestamp);
	//поиск конечной даты
	foreach($this->array_mails as $value){
		$endTimestamp[] = strtotime($value['date_end'][0]);	
		}
		sort($endTimestamp);		
	if($time){
		//начальное время
		return date('Y-m-d H:i:s', $startTimestamp[0]);		
		} else {
		//конечное время
		return date('Y-m-d H:i:s', array_pop($endTimestamp));
		}
	}
	
//получаем все атакуемые IP-адреса 
private function getAllIpdst() 
	{
	$array_ip_dst = array();
	foreach($this->array_mails as $ip){
		$array_ip_dst[] = long2ip($ip['ip_dst'][0]);		
		}
	array_unique($array_ip_dst);
	foreach($array_ip_dst as $ip){
		if(!$array_tmp[] = $this->ReadXMLSetup->obtainFullInfoForIP($ip)){
			$array_tmp[][$ip] = "IP-адреса {$ip}";
			}
		}
	//готовим одномерный массив
	$array_site_name = array();
	foreach($array_tmp as $value){
		foreach($value as $domain => $name){
			$array_site_name[$domain] = $name;
			}
		}
	return $array_site_name;
	}

//подготовка ПЕРВОГО абзаца официального письма о компьютерных атаках (ниф. о КА за период)
public function getOneParagraph($dateStart = null, $dateEnd = null)
	{
	if($dateStart == null){
		$dateStart = $this->getStartEndTime(true);
		$dateEnd = $this->getStartEndTime(false);
		}
	$dayStart = (int) substr($dateStart, 8, 2);
	$dayEnd = (int) substr($dateEnd, 8, 2);
	//если начальное и конечное число совпадает
	if($dayStart == $dayEnd){
		return ConversionData::showDateConvertStr($dateStart);
		} else {
		//проверяем совпадает ли месяц
		if((substr($dateStart, 5, 2)) == (substr($dateEnd, 5, 2))){
			return "c {$dayStart} по ".ConversionData::showDateConvertStr($dateEnd);		
			} else {
			$yearStart = substr($dateStart, 0, 4);
			$yearEnd = substr($dateEnd, 0, 4);
			if($yearStart == $yearEnd){
				return "с {$dayStart} ".ConversionData::showMonth($dateStart)." по {$dayEnd} ".ConversionData::showMonth($dateEnd)." {$yearStart} года";
				} else {
				return "с {$dayStart} ".ConversionData::showMonth($dateStart)." {$yearStart} года по {$dayEnd} ".ConversionData::showMonth($dateEnd)." {$yearEnd} года";
				}
			}	
		}	
	}

//подготовка ВТОРОГО абзаца официального письма о компьютерных атаках (основная информация)
public function getTwoParagraph($DBO) 
	{
	$GeoIP = new GeoIP;
	$text = null;
	$a = 0;
	$numArray = count($this->array_mails);
	foreach($this->array_mails as $key => $val){
		sort($val['date_start']);
		sort($val['date_end']);
		$dateStart = $dateS = $val['date_start'][0];
		$dateEnd = $dateE = array_pop($val['date_end']);
		//дата в формате unix
		$dateStart = strtotime($dateStart);
		$dateEnd = strtotime($dateEnd);
		
		//---блок даты	
		if(($dateStart > ($dateEnd - 3600))){
			//по минутам
			$minutes = floor(($dateEnd - $dateStart) / 60);
			$text .= " - ".ConversionData::showDateConvertStr($dateS)." с ".substr($dateS, 11, 5)." в течение {$minutes} ".DeclansionWord::declansionNum($minutes, array('минуты', 'минут', 'минут'));		
			} else {
			if($dateStart > ($dateEnd - 86400)){
				$sec = $dateEnd - $dateStart;
				//по часам
				$hours = floor($sec / 3600);
				//по минутам
				$minutes = (($sec - $hours * 3600) / 60);
				$text .= " - ".ConversionData::showDateConvertStr($dateS)." с ".substr($dateS, 11, 5)." в течение {$hours} ".DeclansionWord::declansionNum($hours, array('часа', 'часов', 'час'))." {$minutes} ".DeclansionWord::declansionNum($minutes, array('минута', 'минуты', 'минут'));
				} else {
				$text .= " - ".$this->getOneParagraph($dateS, $dateE);
				}
			}
			
		//---IP-адрес источника компьютерной атаки
		$count_ip_src = count($val['ip_src']);
		if($count_ip_src == 1){
			$text .= " с IP-адреса ".long2ip($val['ip_src'][0])." (".$GeoIP->countryIP($DBO, $val['ip_src'][0]).") на";
			} else {
			for($i = 0; $i < $count_ip_src; $i++){
				if($i < $count_ip_src - 1){
					if($count_ip_src == 2){
						$text .= " с IP-адреса ".long2ip($val['ip_src'][$i])." (СТРАНА) и";
						} else {				
						$text .= " с IP-адреса ".long2ip($val['ip_src'][$i])." (СТРАНА),";
						}
					} else {
					$text .= " с IP-адреса ".long2ip($val['ip_src'][$i])." (СТРАНА)";
					}				
				}			
			}
		
		//---на кого организованна компьютерная атака
		if(!$array_ip_dst = $this->ReadXMLSetup->obtainFullInfoForIP(long2ip($val['ip_dst'][0]))){
			$text .= " IP-адрес ".long2ip($val['ip_dst'][0]);
			}
		$countArray = count($array_ip_dst);	
		//проверяем количество элементов в массиве
		if($countArray > 1){
			//у IP-адреса больше одного доменного имени
			$site = '';
			$i = 0;
			foreach($array_ip_dst as $domain => $name){
				$name = str_replace("\\", "", $name);
				if($i < ($countArray - 1)){
					if($i == ($countArray - 2)){
						$site .= "{$name} www.{$domain} и ";					
						} else {
						$site .= "{$name} www.{$domain}, ";
						}
					} else {
					$site .= "{$name} www.{$domain}";
					}
				$i++;
				}
			$text .= " официальные сайты {$site}";
			} else {
			foreach($array_ip_dst as $domain => $name){
				$name = str_replace("\\", "", $name);
				$text .= " официальный сайт {$name} www.{$domain}";
				}
			}
			
		//---количество воздействий
		if($val['count_impact'][0] != 0){
			$num_count_impact = count($val['count_impact']);
			if($num_count_impact > 1){
				//общее количество запросов с группы адресов
				$sum = 0;
				for($i = 0; $num_count_impact > $i; $i++){
					$sum += $val['count_impact'][$i];
					}
				$text .= " - {$sum} ".DeclansionWord::declansionNum($val['count_impact'][0], array('воздействие', 'воздействия', 'воздействий'));
				} else {
				//общее количество запросов с одного адреса
				$text .= " - ".$val['count_impact'][0]." ".DeclansionWord::declansionNum($val['count_impact'][0], array('воздействие', 'воздействия', 'воздействий'));		  
				}
			} else {
			//общее количество запросов для всех сработавших сигнатур
			$count_sid = AllInformationForIncident::showCountSignature($DBO, $key);
			if($count_sid != 0){
				$text .= " - {$count_sid} ".DeclansionWord::declansionNum($count_sid, array('воздействие', 'воздействия', 'воздействий'));
				}
			}
		//ставим ";" или "."
		if($a < $numArray - 1){
			$text .= ';';
			} else {
			$text .=	'.';		
			}
		$a++; 		
		}
	return $text;	
	}
	
//подготовка ТРЕТЬЕГО абзаца официального письма о компьютерных атаках (ниф. о доступности информационного ресурса)
public function getThreeParagraph() 
	{
	$array_site_name = $this->getAllIpdst();
	$countArray = count($array_site_name);	
	//проверяем количество элементов в массиве
	if($countArray > 1){
		//у IP-адреса больше одного доменного имени
		$text = '';
		$i = 0;
		foreach($array_site_name as $name){
			if(!strstr($name, 'IP-адрес')){
				$name = str_replace("\\", "", $name);
				if($i < ($countArray - 1)){
					if($i == ($countArray - 2)){
						$text .= "{$name} и ";					
						} else {
						$text .= "{$name}, ";
						}
					} else {
					$text .= "{$name}";
					}
				}
			$i++;
			}
		return "официальных сайтов {$text}";
		} else {
		foreach($array_site_name as $name){
			$name = str_replace("\\", "", $name);
			return "официального сайта {$name}";
			}
		}
	}
}

?>