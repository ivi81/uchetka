<?php

							/*--------------------------------------------------------------*/
							/*  		класс поиска компьютерных воздействий				*/
							/* 		данный класс принимает параметры от формы searchForm,	*/
							/*		создаваемой классом SearchComputerImpactShowFrom		*/
							/*		выполняет их проверку, генерирует SQL-запрос и 			*/
							/*		возвращает полученную информацию	в виде массива			*/
							/*																*/
							/* 	 								 	v.0.1 25.03.2015   		*/
							/*--------------------------------------------------------------*/

class SearchComputerImpactProcess
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

//функция проверки цифровых значений
private function checkValueInteger()
	{
	$arrayTmp = array();
	$arrayFieldName = array('sensorId', 'typeKA', 'answerAnalyst', 
							'numMail', 'numSid', 'numImpact');
	foreach($arrayFieldName as $value){
		if(!empty($_POST[$value]) && preg_match("/^[0-9]+$/", (int) $_POST[$value], $integer)){
			$arrayTmp[$value] = $integer[0];
			}
		}
	return $arrayTmp;
	}

//функция проверки строк
private function checkValueString()
	{
	$arrayTmp = array();
	$arrayFieldName = array('sensorUser', 'listCountry');
	foreach($arrayFieldName as $value){
		if(!empty($_POST[$value]) && preg_match("/^[a-z]+$/i", addslashes($_POST[$value]), $string)){
			$arrayTmp[$value] = $string[0];
			}
		}
	return $arrayTmp;
	}

//функция проверки IP-адреса
private function checkValueIpAddress()
	{
	$arrayTmp = array();
	$arrayFieldName = array('srcIp', 'dstIp', 'dstIPName');
	$pattern = "/^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/";
	foreach ($arrayFieldName as $value) {
		if(!empty($_POST[$value]) && preg_match($pattern, $_POST[$value], $ip)){
			$arrayTmp[$value] = ip2long($ip[0]);
			}
		}
	//проверяем если в массиве $arrayTmp есть dstIPName и dstIp, то приоритет отдается
	//dstIPName, а dstIp из массива удаляется
	if(isset($arrayTmp['dstIPName'])){
		if(isset($arrayTmp['dstIp'])) unset($arrayTmp['dstIp']);
		}
	return $arrayTmp;
	}

//функция проверки даты и времени
private function checkValueDateTime()
	{
	$arrayTmp = array();
	//массив интервалов времени в сутках и формате UNIX
	$arraydateTimeRange = array(1 => '86400', 
								7 => '604800', 
								30 => '2592000', 
								120 => '10368000', 
								365 => '31536000');
	//проверяем заданный интервал времени
	if(!empty($_POST['dateTimeRange'])){
		if(preg_match("/^[0-9]+$/", (int) $_POST['dateTimeRange'], $integer)){
			$arrayTmp['dateTimeStart'] = date('Y-m-d H:i', (time() - $arraydateTimeRange[$integer[0]]));
			$arrayTmp['dateTimeEnd'] = date('Y-m-d H:i', time());
			}
		}
	//проверяем поля ввода точной даты и времени (приоритет перед списком выбора интервала времени)
	if(!empty($_POST['dateStart']) && !empty($_POST['dateEnd'])){
		preg_match("/^[0-9]{4}[\-][0-9]{2}[\-][0-9]{2}$/D", $_POST['dateStart'], $dateStart);
		preg_match("/^[0-9]{4}[\-][0-9]{2}[\-][0-9]{2}$/D", $_POST['dateEnd'], $dateEnd);
		preg_match("/^[0-9]{2}[\:][0-9]{2}$/D", $_POST['timeStart'], $timeStart);
		preg_match("/^[0-9]{2}[\:][0-9]{2}$/D", $_POST['timeEnd'], $timeEnd);
		if($dateStart[0] && $dateEnd[0] && $timeStart[0] && $timeEnd[0]){
			$dateTimeStart = $dateStart[0].' '.$timeStart[0];
			$dateTimeEnd = $dateEnd[0].' '.$timeEnd[0];
			if(strtotime($dateTimeStart) < strtotime($dateTimeEnd)){
				$arrayTmp['dateTimeStart'] = $dateTimeStart;
				$arrayTmp['dateTimeEnd'] = $dateTimeEnd;
				}
			}
		}
	return $arrayTmp;
	}

//функция заполнения массива $arrayDataFieldForm проверенными данными
private function determineFullFieldForm()
	{
	//формируем массив проверенных данных
	$arrayDataFieldForm = array_merge($this->checkValueDateTime(),
									  $this->checkValueIpAddress(),
									  $this->checkValueInteger(), 
									  $this->checkValueString());
	//проверяем заполненность массива $this->arrayDataFieldForm
	if(count($arrayDataFieldForm) == 0){
		echo ShowMessage::showInformationError("видимо Вы ввели некорректные значения :)");
		}
	return $arrayDataFieldForm;
	}

//функция проверки параметра в тестовом массиве
/*
 - функция принимает имя параметра $inName и его значение $inValue
 - выполняет поиск данного имени в массиве $arrayName
 - при совпадении имен возвращает значение массива $arrayName с подставленным в него значением $inValue
*/
private function checkValueInArray($inName, $inValue)
	{
	$arrayName = array("dateTimeStart" => "`date_time_incident_start` BETWEEN STR_TO_DATE('{$inValue}', '%Y-%m-%d %H:%i:%s')",
					   "dateTimeEnd" => "STR_TO_DATE('{$inValue}', '%Y-%m-%d %H:%i:%s')",
					   "srcIp" => "`ip_src`='{$inValue}'",
					   "dstIp" => "`ip_dst`='{$inValue}'",
					   "dstIPName" => "`ip_dst`='{$inValue}'",
					   "answerAnalyst" => "`true_false`='{$inValue}'",
					   "numMail" => "`number_mail_in_CIB`='{$inValue}'",
					   "numSid" => "`sid`='{$inValue}'",
					   "numImpact" => "t1.id='{$inValue}'",
					   "typeKA" => "`type_attack`='{$inValue}'",
					   "listCountry" => "`country`='{$inValue}'");
	if(array_key_exists($inName, $arrayName)){
		return $arrayName[$inName];
		}
	return false;
	}

//функция проверки массива с данными
/*
функция возвращает false если в массиве есть только элементы с 
именами ключей sensorId или sensorUser и false в обратном случае
*/
private function wordWhere(array $array)
	{
	$arrayTmp = $array;
	if(array_key_exists('sensorId', $arrayTmp)) unset($arrayTmp['sensorId']);
	if(array_key_exists('sensorUser', $arrayTmp)) unset($arrayTmp['sensorUser']);
	return (count($arrayTmp) == 0) ? false : true;
	}

//функция подготовки основного запроса
/*
результат выполнения запрос к следующим таблицам:
	- incident_chief_tables
	- incident_additional_tables
	- incident_analyst_tables
*/
private function prepareMainRequest(array $arrayData)
	{
	$string = $tableSid = '';
	foreach($arrayData as $key => $value){
		$findValueInArray = $this->checkValueInArray($key, $value);
		if($findValueInArray){
			$string .= $findValueInArray.' AND ';
			}
		}

	//проверяем выбор поиска по номеру сигнатуры
	if(array_key_exists('numSid', $arrayData)){
		$tableSid = 'INNER JOIN `incident_number_signature_tables` t3 ON t3.id = t1.id';
		}

	$where = ($this->wordWhere($arrayData))	? 'WHERE' : '';;
	//удаляем слово AND в конце
	$string = substr($string, 0, -5);
	$request = "SELECT t1.id, 
					  `date_time_incident_start`, 
					  t2.login_name,
					  `ip_src`, 
					  `country`, 
					  `ip_dst`, 
					  `true_false`, 
					  `number_mail_in_CIB`
				FROM `incident_chief_tables` t0 
				INNER JOIN `incident_additional_tables` t1 ON t0.id = t1.id 
				INNER JOIN `incident_analyst_tables` t2 ON t2.id = t1.id 
				{$tableSid} {$where} {$string} ORDER BY `date_time_incident_start` ASC";
	return $request;
	}

//функция формирующая строку запроса для поиска id сенсора в таблицах sensor_information_main_one и two
//функция формирующая массив из диапазона IP-адресов контролирующихся сенсорами
private function requestSensorId(array $arrayData)
	{
	$sensorId = array_key_exists('sensorId', $arrayData);
	$sensorUser = array_key_exists('sensorUser', $arrayData);
	if(!$sensorId && !$sensorUser) return false;
	$array = array();
	if($sensorUser){
		$searchField = '`login`=:login';
		$arraySearchField = array(':login' => $arrayData['sensorUser']);
		} else {
		$searchField = 't0.sensor_id=:sensorId';
		$arraySearchField = array(':sensorId' => $arrayData['sensorId']);
		}
	
	try{
		$query = self::linkDB()->connectionDB()->prepare("SELECT `ip_start`, `ip_end` FROM `sensor_information_main_one` t0 
														  INNER JOIN `sensor_information_main_two` t1 
														  ON t0.sensor_id=t1.sensor_id WHERE ".$searchField);
		$query->execute($arraySearchField);
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	$arrayNumber = 0;
	while($row = $query->fetch(PDO::FETCH_OBJ)){
		$array[$arrayNumber]['ipStart'] = $row->ip_start;
		$array[$arrayNumber]['ipEnd'] = $row->ip_end;
		$arrayNumber++;
		}
	return $array;
	}

//функция выполняющая поиск значения в интервале значений массива получаемого от функции equestSensorId() 
private function searchInArraySensorId(array $arraySensorId, $searchIpDst)
	{
	foreach($arraySensorId as $value){
		if(($value['ipStart'] <= $searchIpDst) && ($searchIpDst <= $value['ipEnd'])) return true; 
		}
	return false;
	}

//функция выполняющая поиск
public function executeSearch()
	{
	$arrayResult = array();
	//массив с проверенными данными
	$arrayDataFieldForm = $this->determineFullFieldForm();
//var_dump($this->prepareMainRequest($arrayDataFieldForm));
	//проверяем выбор поиска по sensorId и sensorUser
	$sensorIdOrUserTrue = (!empty($arrayDataFieldForm['sensorId']) || !empty($arrayDataFieldForm['sensorUser']));
	//создаем массив из диапазонов IP-адресов защищаемых сенсорами сегментов 
	if($sensorIdOrUserTrue){
		$arrayRange = $this->requestSensorId($arrayDataFieldForm);
		}
	try{
		$query = self::linkDB()->connectionDB()->query($this->prepareMainRequest($arrayDataFieldForm));
		$idSearchResultMain = $idSearchResultRange = '';
		$searchResultSensor = false;
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$ipSrc = long2ip($row->ip_src);
			
			if($idSearchResultRange != $row->id){
				//проверяем наличие sensorId или sensorUser и searchInArraySensorId() равного true
				$searchResultSensor = $sensorIdOrUserTrue && ($this->searchInArraySensorId($arrayRange, $row->ip_dst));
				}
			$idSearchResultRange = $row->id;

			//выполняем поиск значения в интервале массива $arrayRange
			if(($searchResultSensor == true) || ($sensorIdOrUserTrue == false)){
				if($idSearchResultMain == $row->id){
					$arrayResult[$row->id]['ipSrc'][] = $ipSrc;
					$arrayResult[$row->id]['country'][] = $row->country;
					} else {
					$arrayResult[$row->id]['dateTimeStart'] = $row->date_time_incident_start;
					$arrayResult[$row->id]['loginName'] = $row->login_name;
					$arrayResult[$row->id]['ipSrc'][] = $ipSrc;
					$arrayResult[$row->id]['country'][] = $row->country;
					$arrayResult[$row->id]['ipDst'] = long2ip($row->ip_dst);
					$arrayResult[$row->id]['solution'] = $row->true_false;
					$arrayResult[$row->id]['mailNumber'] = $row->number_mail_in_CIB;
					}
				$idSearchResultMain = $row->id;
				}
			}
		return $arrayResult;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
}

?>