<?php

							/*----------------------------------------------*/
							/*	  класс вывода дополнительной информации		*/
							/*		по защищаемому сенсорами сегменту сети	*/
							/*												*/
							/* 	 					 v0.1 28.04.2015        */
							/*												*/
							/*----------------------------------------------*/

class GetSensorInformation
{
private static $PDO;
private $directiry;

function __construct()
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directory = $array_directory[1];	
	}

//ресурс БД
private static function linkDB()
	{
	if(empty(self::$PDO)){
		self::$PDO = new DBOlink;
		}
	return self::$PDO;
	}

//поиск данных по IP-адресу назначения в таблице `sensor_information_main_one`
private function searchInformation($ipDst)
	{
	if(ExactilyUserData::getIp($ipDst) === false) return false;
	$ip = ip2long($ipDst);
	$arrayResult = array();
	try{
		$query = self::linkDB()->connectionDB()->query("SELECT t0.sensor_id, `ip_start`, `ip_end`, `login`,
													   `add_date_sensor`, `ip_address_sensor`, `sensor_all_information` 
													    FROM `sensor_information_main_one` t0 INNER JOIN `sensor_information_main_two` t1 
													    ON t0.sensor_id=t1.sensor_id WHERE t0.sensor_id IN 
													   (SELECT `sensor_id` FROM `sensor_information_main_one` 
													    WHERE `ip_start`<='{$ip}' AND '{$ip}'<=`ip_end`) ORDER BY t0.sensor_id ASC");
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$arrayResult[$row->sensor_id]['ipStartEnd'][] = long2ip($row->ip_start).' - '.long2ip($row->ip_end);
			$arrayResult[$row->sensor_id]['userName'] = $row->login;
			$arrayResult[$row->sensor_id]['addDateSensor'] = date('d.m.Y в H:i', $row->add_date_sensor);
			$arrayResult[$row->sensor_id]['sensorIp'] = long2ip($row->ip_address_sensor);
			$arrayResult[$row->sensor_id]['sensorName'] = $row->sensor_all_information;	
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	return $arrayResult;
	}

//получить дополнительную информацию о найденном IP-адресе назначения
private function getAllInformationForIp()
	{

	}


//поиск названия защищаемого сегмента сети по ipDst
public function getInformationForIp($ip)
	{
	$arrayResult = $this->searchInformation($ip);
	if($arrayResult === false || count($arrayResult) == 0) return;
	?>
	<style type="text/css">
	.windowComics {
		display: none; 
/*		position: absolute;
		top: 25px;
/*		left: 60px; */
		margin: auto;
		width: 220px;
		height: auto;
		background: #fff;
		padding: 10px;
		-webkit-border-radius: 6px;
		-moz-border-radius: 6px;
		border-radius: 6px;
		-webkit-box-shadow: 0 0 7px #bbb;
		-moz-box-shadow: 0 0 7px #bbb;
		box-shadow: 0 0 7px #bbb;
		z-index: 250;
	}
	.windowComics:before {
		content: "";
		position: absolute; left: 45%; top: 10px; margin-top: -20px; z-index: 1;
		display: block;
		width: 0px;
		height: 0px;
		border-left: 10px solid transparent;
		border-right: 10px solid transparent;
		border-bottom: 11px solid #fff;
	}
	</style>
	<?php
	$ReadXMLSetup = new ReadXMLSetup;
	foreach($arrayResult as $sensorId=>$array){
		?>
		<div name="sensorName" style="position: relative; cursor: pointer;">
			<span style="color: #0000FF; text-decoration: underline;"><?= $array['sensorName']; ?></span>
			<div class="windowComics tableHeader" name="windowInform">
				<p style="font-weight: bold;">Сенсор №<?= $sensorId ?></p>
				<p><?= $array['sensorName'] ?></p>
				<p>IP-адрес сенсора <?= $array['sensorIp'] ?></p>	
				<p>Ответственный<br><?= $ReadXMLSetup->giveUserNameAndSurname($array['userName']) ?></p>
				<p>Сенсор добавлен/изменен<br><?= $array['addDateSensor'] ?></p>
				<p style="font-weight: bold;">Контролируемые IP-адреса</p>
				<?php
				foreach($array['ipStartEnd'] as $ipAddress){
					echo "{$ipAddress}<br>";
					}
				?>
			</div>
		</div>
		<?php
		}
	?>	
	<script type="text/javascript">
	function showMessage(elem){
		var idObj = elem.childNodes[3];
		if(idObj.style.display == 'block'){
			idObj.style.display = 'none';
		} else {
			idObj.style.display = 'block';
		}
	}
	function addHandler(){
		var divSensorName = document.getElementsByName('sensorName');
		countDiv = divSensorName.length;
		if(countDiv.length == 0){ 
			console.log('test'); 
			return;
		}
		for(var div in divSensorName){
		if(divSensorName[div].nodeName == 'DIV'){
			divSensorName[div].addEventListener('click', function(){showMessage(this)}, false);
			}
		}
		//получаем половину от ширины элемента
		var elemWidth = divSensorName[0].offsetWidth / 2 - 110;
		var nameWindowInfo = document.getElementsByName('windowInform');
		for(var winElem in nameWindowInfo){
			if(nameWindowInfo[winElem].nodeName == 'DIV'){
			nameWindowInfo[winElem].setAttribute('style', 'position: absolute; top: 25px; left:' + elemWidth + 'px;');
			}
		}
	}
	addHandler();
	</script>
	<?php
	}
}
?>
