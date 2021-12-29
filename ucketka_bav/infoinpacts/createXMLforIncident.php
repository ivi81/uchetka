<html>
	<head>
		<meta charset="utf-8">
		<title>Формирование XML файла</title>
	</head>
	<body>
<?php
/*
	Формирование файла в формате XML в котором содержится
	информация о зарегестрированных компьютерных воздействиях

	Версия 0.1, дата релиза 18.08.2015
*/

/*******************************************
			Класс подключение к БД
********************************************/
class LinkConnectionDB
{
//данные для подключения к БД
private static $dbHost = "localhost";
private static $dbName = "data_on_KA";
private static $dbUser = "analyst_connect";	
private static $dbPassword = "BG5&*VCYi12_"; 

//соединение с БД
public function connectionDB() 
	{
	try{
		//Создание переменной $DBO (Database Handle)
		$DBO = new PDO("mysql:host=".self::$dbHost."; dbname=".self::$dbName."", self::$dbUser, self::$dbPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
		$DBO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
	catch (PDOException $e){
		echo $e->getMessage();
//		echo MessageErrors::userMessageError(MessageErrors::ERROR_DB_CONNECT, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	return $DBO;
	}
}

/******************************************
			класс в котором выполняется 
		проверка IP-адреса и возврящается
		доменное имя если оно есть
*******************************************/
class GetDomainName
{
	private static $arrayDomainName = array(
			"91.222.157.5" => array("cikrf.ru"),
			"95.173.128.90" => array("gov.ru", "award.gov.ru", "munition.gov.ru", "svr.gov.ru"),
			"95.173.130.2" => array("duma.gov.ru"),
			"95.173.130.15" => array("asozr.duma.gov.ru"),
			"95.173.131.2" => array("scrf.gov.ru"),
			"95.173.131.101" => array("ach.gov.ru"),
			"95.173.131.170" => array("vsrf.ru"),
			"95.173.131.246" => array("fsvts.gov.ru"),
			"95.173.132.73" => array("council.gov.ru"),
			"95.173.135.62" => array("government.ru", "premier.gov.ru"),
			"213.24.76.23" => array("fsb.ru"),
			"213.24.76.20" => array("ps.fsb.ru"),
			"95.173.130.16" => array("ntc.duma.gov.ru"),
			"91.206.120.11" => array("economy.gov.ru"),
			"77.241.31.7" => array("adm.rkursk.ru", "web.rkursk.ru"),
			"91.206.121.102" => array("smb.gov.ru"),
			"91.206.121.106" => array("ais.economy.gov.ru"),
			"95.173.136.71" => array("kremlin.ru"),
			"91.206.121.124" => array("ved.gov.ru", "tha.ved.gov.ru", "svk.ved.gov.ru"),
			"91.206.121.141" => array("aisup.economy.gov.ru"),
			"91.206.120.206" => array("nko.economy.gov.ru")
			);
	//проверка IP-адреса
	private static function checkIp($ip){
		if(empty($ip)) return false;
		$ip = trim($ip);
		$pattern = "/^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/";
		if(!preg_match($pattern, $ip)) return false;
		return true;
	}

	//поиск доменного имени
	private static function searchDomainName($ip){
		$ipAddress = self::checkIp($ip);
		if($ipAddress == false) return false;
		if(!array_key_exists($ip, self::$arrayDomainName)) return false;
		$arrayName = self::$arrayDomainName[$ip];
		$arrayNameCount = count($arrayName);
		$stringDomainName = '';
		for($num = 0; $num < $arrayNameCount; $num++){
			$separator = ($num < $arrayNameCount - 1) ? ', ': ''; 
			$stringDomainName .= $arrayName[$num].$separator;
		}
		return $stringDomainName;
	}

	//получение доменного имени
	public static function domainName($ipAddress){
		$domainName = self::searchDomainName($ipAddress);
		if($domainName == false) return '-';
		return $domainName;
	}
}

var_dump(GetDomainName::domainName('95.173.128.90'));

/******************************************
			класс для получения ФИО 
			пользователя по логину
*******************************************/
class GetUserName
{
	private static $arrayUserName = array(
		"malygin" => "Малыгин Алексей Владимирович",
		"plyuxa" => "Плюха Сергей Александрович",
		"dunaev" => "Дунаев Валерий Николаевич",
		"ershov" => "Ершов Сергей Васильевич",
		"trojan" => "Троян Александр Викторович",
		"sergeev" => "Сергеев Дмитрий Сергеевич",
		"ippolitov" => "Ипполитов Илья Владимирович",
		"chin" => "Чинков Сергей Валерьевич",
		"polykov" => "Поляков Борис Михайлович",
		"checking" => "Сидоров Иван Петрович",
		"artemiy" => "Беляков Артемий Вячеславович",
		"vitaliy" => "Кожокарь Виталий Юрьевич"
		);

	public static function userName($login){
		if(empty($login)) return false;
		$login = trim($login);
		if(!preg_match("/^\w+$/", $login)) return false;
		return self::$arrayUserName[$login];
	}
}

/******************************************************

			основной класс который создает 
					файл в формате XML

*******************************************************/

class CreateXmlFile
{
	private static $linkDB;
	private $incidentNumber = '';
	private $action = array(
			'0' => 'none',
			'1' => 'R',
			'2' => 'C',
			'3' => 'N',
			'4' => 'T');
	private $arrayTegXml = array( 
		"Info" => array( 
			"Begin" => "date_time_incident_start",
			"End" => "date_time_incident_end",
			"TimeDetected" => "date_time_create",
			"UserDetected" => "login_name",
			"Explanation" => "explanation",
			"StatusIncidentAnalyst" => "true_false",
			"Type" => "type_attack",
			"UserAnalyst" => "login_name",
			"TimeAnalyst" => "date_time_analyst",
			"InformationAnalyst" => "information_analyst",
			"DstIp" => "ip_dst",
			"DstName" => "function_"),
		"Sensors" => array(			
			"IdControlObject" => "sensor_id",
			"NameControlObject" => "sensor_chort_name",
			"UserControlObject" => "login",
			"AddDateSensor" => "add_date_sensor",
			"IpAddressSensor" => "ip_address_sensor"
			),
		"Sources" => array(
			"SrcIp" => "ip_src",
			"CountryCode" => "country",
			"CountImpact" => "count_impact"
			),
		"Signatures" => array(
			"SignatureId" => "sid",
			"SignatureMessage" => "short_message"
			)
		);

	//на вход подается массив или ничего, тогда задается строка 'All'
	function __construct($incidentNumber = 'All'){
		$this->incidentNumber = $incidentNumber;
	}

	private static function getLinkDB(){
		if(empty(self::$linkDB)){
			$linkConnectionDB = new LinkConnectionDB;
			self::$linkDB = $linkConnectionDB->connectionDB(); 
		}
		return self::$linkDB;
	}

	//получаем название КА по ее номеру
	private function getNameIncidentForNum($num){
		$arrayTypeIncident = array( "", 
									"Shell-код", 
									"DoS-атака", 
									"DDoS-атака",
									"SQL-injection", 
									"Сканер портов", 
									"Сканер уязвимостей", 
									"Подбор пароля", 
									"Вирусное заражение", 
									"Exploit", 
									"Спам рассылка", 
									"URL-bruteforce",
									"Directory travelser",
									"CSRF", 
									"Компрометация сервера",
									"Remote File Include",
									"Local File Include", 
									"DoS Reflection attack",
									"XSS (Cross Site Scripting)");
		$num = (int) $num;
		if(!preg_match("/^[0-9]{1,}$/", $num)) return false;
		return $arrayTypeIncident[$num];
	}

	//получаем строку из названи полей таблицы БД
	/* на вход подается
		$arrayColumns - массив с перечнем полей 
		$column - поле к которому добавляется псевдоним таблицы, если нет то false
		$tableAlias - псевдоним	таблицы, если нет то false
	*/

	private function getStringColumns(array $arrayColumns, $column = false, $tableAlias = false){
		$stringColumns = '';
		$num = 0;
		$countSensor = count($arrayColumns);
		foreach($arrayColumns as $key => $value){
			$fix = ($num < $countSensor - 1) ? ', ': '';
			if($column == false){
				$stringColumns .= $value.$fix;
			} else {
				$stringColumns .= ($key == $column) ? $tableAlias.'.'.$value.$fix : $value.$fix;
			}
			$num++;
		}
		return $stringColumns;		
	}

	//получаем идентификатор сенсора и краткое описание
	//	- принимает IP-адрес в десятичном формате
	private function getInformationSensor($ipDst){
		$array = array();
		$stringColumns = $this->getStringColumns($this->arrayTegXml["Sensors"], 'IdControlObject', 't0');
		try{
			$query = self::getLinkDB()->query("SELECT {$stringColumns} 
											   FROM `sensor_information_main_one` t0 
											   INNER JOIN `sensor_information_main_two` t1 
									  		   ON t0.sensor_id=t1.sensor_id WHERE t0.sensor_id IN 
									  		   (SELECT `sensor_id` FROM `sensor_information_main_one` 
									  		   WHERE `ip_start`<='{$ipDst}' AND '{$ipDst}'<=`ip_end`) ORDER BY t0.sensor_id ASC");
			while($row = $query->fetch(PDO::FETCH_OBJ)){
				$array[$row->sensor_id]['UserControlObject'] = GetUserName::userName($row->login);
				$array[$row->sensor_id]['AddDateSensor'] = $row->add_date_sensor;
				$array[$row->sensor_id]['ipAddressSensor'] = long2ip($row->ip_address_sensor);
				$array[$row->sensor_id]['NameControlObject'] = $row->sensor_chort_name;
 			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		return $array;
	}

	//получаем sid сработавших сигнатур и их описание 
	//по номеру компьютерного воздействия ($id)

	private function getInformationSignature($id){
		$array = array();
		$id = (int) $id;
		$stringColumns = $this->getStringColumns($this->arrayTegXml["Signatures"], 'SignatureId', 't0');
		try{
			$query = self::getLinkDB()->query("SELECT {$stringColumns} FROM `incident_number_signature_tables` t0
											   INNER JOIN `signature_tables` t1 ON t0.sid=t1.sid
											   WHERE `id`='{$id}'");
			while($row = $query->fetch(PDO::FETCH_OBJ)){
				$array[$row->sid] = $row->short_message;
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		return $array;
	}

	//получаем информацию из таблицы аналитика
	/* если в таблице аналитика данных по компьютерному 
	   воздействию нет компьютерное воздействие считается не
	   проанализированным
		'R' - 'компьютерная атака',
		'C' - 'ложное срабатывание',
		'N' - 'отсутствует сетевой трафик',
		'T' - 'сетевой трафик утерян'
		'W' - 'если событие не обработанно'
	*/
	private function getInformationAnalyst($id){
		$array = array();
		$id = (int) $id;
		$arrayAnalyst = array();
		foreach($this->arrayTegXml["Info"] as $key => $value){
			if(strpos($key, 'Analyst') != false) $arrayAnalyst[$key] = $value;
		}
		$stringColumns = $this->getStringColumns($arrayAnalyst);
		try{
			$query = self::getLinkDB()->query("SELECT {$stringColumns}
											   FROM `incident_analyst_tables` WHERE `id`='{$id}'");
			$row = $query->fetch(PDO::FETCH_OBJ);
			if(isset($row->true_false)){
				$array['StatusIncidentAnalyst'] = $this->action[$row->true_false];
				$array['TimeAnalyst'] = $row->date_time_analyst;				
				$array['UserAnalyst'] = $row->login_name;
				$array['InformationAnalyst'] = $row->information_analyst;					
			} else {
				$array['StatusIncidentAnalyst'] = 'W';
				$array['TimeAnalyst'] = '-';				
				$array['UserAnalyst'] = '-';
				$array['InformationAnalyst'] = '-';					
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		return $array;
	}

	//формируем XML файл
	public function createFile(){
		$numComputerIncident = '';
		$arrayInfo = array();
		foreach($this->arrayTegXml["Info"] as $key => $value){
			if(strpos($key, 'Analyst') != false) continue;
			if(strpos($key, 'stName') != false) continue;
			$arrayInfo[$key] = $value;
		}
		//объект DomDocument
		$xmlDomDocument = new DomDocument('1.0', 'utf-8');
		
		$stringColumns = $this->getStringColumns($arrayInfo);
		$stringColumns .= ', '.$this->getStringColumns($this->arrayTegXml["Sources"]);
		if(is_array($this->incidentNumber)){
			$arrayCount = count($this->incidentNumber);
			for($num = 0; $num < $arrayCount; $num++){
				$separator = ($num < $arrayCount - 1) ? ' or ': '';
				$numComputerIncident .= " t1.id='".$this->incidentNumber[$num]."' ".$separator;
			}
		}
		try{
			$query = self::getLinkDB()->query("SELECT t1.id, {$stringColumns}
 											   FROM `incident_chief_tables` t0 LEFT JOIN `incident_additional_tables` t1 
 											   ON t0.id = t1.id WHERE {$numComputerIncident} ORDER BY t1.id ASC");
			$arrayTmp = $arrayIp = array();
			$num = $numId = 0;
			while($row = $arrayTmp[] = $query->fetch(PDO::FETCH_ASSOC)){
				if($numId != $row['id']) $num = 0;
				$arrayIp[$row['id']][$num]['SrcIp'] = $row['ip_src'];
				$arrayIp[$row['id']][$num]['CountryCode'] = $row['country'];
				$arrayIp[$row['id']][$num]['CountImpact'] = $row['count_impact'];
				$numId = $row['id'];
				$num++;
			} 
/*			
echo '<pre>';
var_dump($arrayIp);
echo '</pre>';
*/
			$countArrayTmp = count($arrayTmp);
			//создаем корневой тег root
			$root = $xmlDomDocument->appendChild($xmlDomDocument->createElement('root'));
			for($a = $i = 0; $a < $countArrayTmp - 1; $a++){
				if($i != $arrayTmp[$a]['id']){
					
					//получаем массив с информацией от аналитика
					$arrayInformationAnalyst = $this->getInformationAnalyst($arrayTmp[$a]['id']);
					//получаем массив с информацией от сенсоре
					$arrayInformationSensors = $this->getInformationSensor($arrayTmp[$a]['ip_dst']);					
					//получаем информацию о сигнатурах
					$arrayInformationSignatures = $this->getInformationSignature($arrayTmp[$a]['id']);

					$explanation = (!empty($arrayTmp[$a]['explanation'])) ? $arrayTmp[$a]['explanation'] : '-';
//тестовая выборка
//echo "<br>---------------------------------------<br>";
//IncidentId
					 $incidentId = $root->appendChild($xmlDomDocument->createElement('IncidentId'));
					 $incidentId->setAttributeNode(new DOMAttr('ID', $arrayTmp[$a]['id']));			
echo "<b>IncidentId - ".$arrayTmp[$a]['id'].'</b>';
//Information
	echo "<ul>Info";
					  $info = $incidentId->appendChild($xmlDomDocument->createElement('Info'));
		echo "<li>Begin - ".$arrayTmp[$a]['date_time_incident_start'];
					   $info->appendChild($xmlDomDocument->createElement('Begin', $arrayTmp[$a]['date_time_incident_start']));
		echo "<li>End - ".$arrayTmp[$a]['date_time_incident_end'];
					   $info->appendChild($xmlDomDocument->createElement('End', $arrayTmp[$a]['date_time_incident_end']));
		echo "<li>TimeDetected - ".$arrayTmp[$a]['date_time_create'];
					   $info->appendChild($xmlDomDocument->createElement('TimeDetected', $arrayTmp[$a]['date_time_create']));
		echo "<li>UserDetected - ".GetUserName::userName($arrayTmp[$a]['login_name']);
					   $info->appendChild($xmlDomDocument->createElement('UserDetected', GetUserName::userName($arrayTmp[$a]['login_name'])));
		echo "<li>Explanation - ".$explanation;
					   $info->appendChild($xmlDomDocument->createElement('Explanation', $explanation));
			echo "<li>[Analyst] StatusIncidentAnalyst - ".$arrayInformationAnalyst['StatusIncidentAnalyst'];
					   $info->appendChild($xmlDomDocument->createElement('StatusIncidentAnalyst', $arrayInformationAnalyst['StatusIncidentAnalyst']));
		echo "<li>Type - ".$this->getNameIncidentForNum($arrayTmp[$a]['type_attack']);
					   $info->appendChild($xmlDomDocument->createElement('Type', $this->getNameIncidentForNum($arrayTmp[$a]['type_attack'])));
			echo "<li>[Analyst] UserAnalyst - ".$arrayInformationAnalyst['UserAnalyst'];
					   $info->appendChild($xmlDomDocument->createElement('UserAnalyst', $arrayInformationAnalyst['UserAnalyst']));
			echo "<li>[Analyst] TimeAnalyst - ".$arrayInformationAnalyst['TimeAnalyst'];
					   $info->appendChild($xmlDomDocument->createElement('TimeAnalyst', $arrayInformationAnalyst['TimeAnalyst']));
			echo "<li>[Analyst] InformationAnalyst - ".$arrayInformationAnalyst['InformationAnalyst'];
					   $info->appendChild($xmlDomDocument->createElement('InformationAnalyst', $arrayInformationAnalyst['InformationAnalyst']));
		echo "<li>DstIp - ".$arrayTmp[$a]['ip_dst'];
					   $info->appendChild($xmlDomDocument->createElement('DstIp', $arrayTmp[$a]['ip_dst']));
		echo "<li>DstName - ".GetDomainName::domainName(long2ip($arrayTmp[$a]['ip_dst']));
					   $info->appendChild($xmlDomDocument->createElement('DstName', GetDomainName::domainName(long2ip($arrayTmp[$a]['ip_dst']))));
	echo "<br>/Info</ul>";
//Sources
	echo "<ul>Sources";
					  $sources = $incidentId->appendChild($xmlDomDocument->createElement('Sources'));
					foreach($arrayIp[$arrayTmp[$a]['id']] as $arrayValue){	
		echo "<li>SrcIp - ".$arrayValue['SrcIp'];
					   $sources->appendChild($xmlDomDocument->createElement('SrcIp', $arrayValue['SrcIp']));
		echo "<li>CountryCode - ".$arrayValue['CountryCode'];
					   $sources->appendChild($xmlDomDocument->createElement('CountryCode', $arrayValue['CountryCode']));		
		echo "<li>CountImpact - ".$arrayValue['CountImpact'];
					   $sources->appendChild($xmlDomDocument->createElement('CountImpact', $arrayValue['CountImpact']));		
					}
	echo "<br>/Source</ul>";
//Sensors
	echo "<ul>Sensors";
					  $sensors = $incidentId->appendChild($xmlDomDocument->createElement('Sensors'));
					foreach($arrayInformationSensors as $sensorId => $arrayName){
		echo "<li>IdControlObject - ".$sensorId;
					   $sensors->appendChild($xmlDomDocument->createElement('IdControlObject', $sensorId));		
						foreach($arrayName as $name => $value) {
				echo "<li>{$name} - ".$value;		
					   $sensors->appendChild($xmlDomDocument->createElement($name, $value));		
						}
					}
	echo "<br>/Sensors</ul>";
//Signatures
	echo "<ul>Signatures";
					  $signatures = $incidentId->appendChild($xmlDomDocument->createElement('Signatures'));
					foreach ($arrayInformationSignatures as $signatureId => $signatureMessage) {
		echo "<li>SignatureId - ".$signatureId;
					   $signatures->appendChild($xmlDomDocument->createElement('SignatureId', $signatureId));
		echo "<li>SignatureMessage - ".$signatureMessage;
					   $signatures->appendChild($xmlDomDocument->createElement('SignatureMessage', $signatureMessage));
					}
	echo "<br>Signatures</ul>";
echo "<br><b>IncidentId</b>";
				}			
				$i = $arrayTmp[$a]['id'];
			}
		$fileName = time().'_'.'unloadingFromTheDatabase.xml';
		$xmlDomDocument->save($fileName);
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
/*
		"Info" => array( 
			"Begin" => "date_time_incident_start",
			"End" => "date_time_incident_end",
			"TimeDetected" => "date_time_create",
			"UserDetected" => "login_name",
			"Explanation" => "explanation",
			"StatusIncidentAnalyst" => "true_false",
			"Type" => "type_attack",
			"UserAnalyst" => "login_name",
			"TimeAnalyst" => "date_time_analyst",
			"InformationAnalyst" => "information_analyst",
			"DstIp" => "ip_dst",
			"DstName" => "function_"),
		"Sensors" => array(			
			"IdControlObject" => "sensor_id",
			"NameControlObject" => "sensor_chort_name",
			"UserControlObject" => "login",
			"AddDateSensor" => "add_date_sensor",
			"IpAddressSensor" => "ip_address_sensor"
			),
		"Sources" => array(
			"SrcIp" => "ip_src",
			"CountryCode" => "country",
			"CountImpact" => "count_impact"
			),
		"Signatures" => array(
			"SignatureId" => "sid",
			"SignatureMessage" => "short_message"
			)
*/


/*		echo "<br>тип КА";
		echo $this->getNameIncidentForNum(7);
		echo "<br>id = ({$numComputerIncident})<br>";

		echo "<br>sensor information<br>";
		echo "<pre>";
		var_dump($this->getSensorId('1605208134'));
		echo "</pre>";
		echo "signature sid<br>";
		echo "<pre>";
		var_dump ($this->getSignatureInformation('2591'));
		echo "</pre>";
		echo "От аналитика<pre>";
		var_dump($this->getInformationAnalyst('3825'));
		echo "</pre>";
*/
	}

}
//$createXmlFile = new CreateXmlFile(array('3456', '3458', '3459'));
$createXmlFile = new CreateXmlFile();
echo "<br>";
echo $createXmlFile->createFile();
?>
	</body>
</html>