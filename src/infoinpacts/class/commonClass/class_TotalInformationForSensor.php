<?php

						/*------------------------------------------------------*/
						/* класс работы дежурного с информацией по сенсору		*/
						/*	- редактирования информации							*/
						/*									v0.11 05.03.2015	*/
						/*------------------------------------------------------*/

class TotalInformationForSensor
{
protected static $DBO;
protected $readXMLSetup;
protected $directoryRoot;
protected $fieldForm;
function __construct()
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directoryRoot = $array_directory[1];
	$this->readXMLSetup = new ReadXMLSetup();
	$this->setFieldForm(array('sensorId' => 'идентификатор сенсора',
							  'sensorIp' => 'IP-адрес сенсора',
							  'ipStart' => 'начальный IP-адрес защищаемой сети',
							  'ipEnd' => 'конечный IP-адрес защищаемой сети',
							  'segmentName' => 'название защищаемого сегмента сети',
							  'segmentInfo' => 'информацию по защищаемому сегменту сети',
							  'segmentContacts' => 'контактные данные'));
	}

//устанавливаем соединение с БД
protected static function getConnectionDB()
	{
	if(empty(self::$DBO)){
		self::$DBO = new DBOlink;
		}
	return self::$DBO;
	}

//вывод сообщения об ошибке
protected function showErrorMessage($text)
	{
	?>
	<script type="text/javascript">
	function pageReload(){
		//обновляем страницу
		setTimeout(function reload(){ window.location.href = window.location.pathname; }, '3000');
	}
	pageReload();
	</script>
	<?php
	echo "<div style='position: relative; top: 100px; margin-left: 20px;'>";
	echo ShowMessage::showInformationError($text);
	echo "</div>";
	return false;
	}

//функция проверяющая поля данных вводимых пользователем на пустоту	
protected function checkExistData(array $array)
	{
	foreach($array as $key => $value){
		if(!isset($_POST[$key]) || empty($_POST[$key]) || $_POST[$key] == 'null'){
			return "введите ".$value;
			}
		if(is_array($_POST[$key]) && strlen($_POST[$key][0]) == 0){
			return "введите ".$value;
			}			
		}
	}

//задаем поля формы
protected function setFieldForm(array $array_field)
	{
	$this->fieldForm = $array_field;
	}

//получаем поля формы
protected function getFieldForm()
	{
	return $this->fieldForm;
	}

//функция проверяющая данные, кроме диапазона IP-адресов, необходимые при создании нового сенсора
protected function checkData()
	{
	//проверяем заполненность формы
	if($this->checkExistData($this->getFieldForm())){ 
		$this->showErrorMessage($this->checkExistData($this->getFieldForm()));
		}
	
	$array_result = array();
	//идентификатор сенсора
	$array_result['sensorId'][] = ExactilyUserData::takeIntager($_POST['sensorId']);
	//IP-адрес сенсора
		if(!preg_match_all("/^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/", $_POST['sensorIp'])){
			$this->showErrorMessage("неверный IP-адрес");	
			} else {
			$array_result['sensorIp'][] = $_POST['sensorIp'];
 			}
	//имя защищаемого сегмента сети
	$array_result['segmentName'][] = ExactilyUserData::takeString($_POST['segmentName']);
	//информация по защищаемому сегменту сети
	$array_result['segmentInfo'][] = ExactilyUserData::takeString($_POST['segmentInfo']);
	//контактные данные
	$array_result['segmentContacts'][] = ExactilyUserData::takeString($_POST['segmentContacts']);

	return $array_result;
	}

//функция проверяющая диапазон IP-адресов ipStart и ipEnd
protected function checkRangeInputIpAddress()
	{
	//проверяем заполненность формы
	if($this->checkExistData($this->getFieldForm())){ 
		$this->showErrorMessage($this->checkExistData($this->getFieldForm()));
		}
	
	//получаем массив защищаемого диапазона IP-адресов
	$array = ExactilyUserData::giveArrayIpAddress($_POST['ipStart'], $_POST['ipEnd']);
	//проверяем массив на пустоту
	if(count($array) === 0) $this->showErrorMessage('неверный начальный или конечный IP-адрес');
	return $array;
	}

//изменить информацию о сенсоре (дежурный)
public function changeInformation()
	{
	$this->changeSensorId(ExactilyUserData::takeIntager($_POST['sensorIdOld']));
	}

//изменить информацию о сенсоре (обработчик)
protected function changeSensorId($sensorIdOld)
	{
	//проверка существования значений
	if(empty($sensorIdOld)) return false;
	//изменяем информацию в таблицах
	try{
		$login = ExactilyUserData::takeString($_POST['userName']);
		$this->changeTableSensorInformationMainOne($sensorIdOld);
		$this->changeTableSensorInformationMainTwo($sensorIdOld, $login);
		?>
		<script type="text/javascript">
		var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', "/<?= $this->directoryRoot ?>/worker/process/ajax_process.php", '', "informationSensorId=<?= $sensorIdOld ?>");
		newObjectXMLHttpRequest.sendRequest();
		</script>
		<?php
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//изменить записи в таблице 'sensor_information_main_one'
// таблица содержит следующие поля информация по которым будет изменена
// - sensor_id
// - ip_start
// - ip_end
private function changeTableSensorInformationMainOne($sensorIdOld)
	{
	//получаем массив защищаемого диапазона IP-адресов
	$array_range_ip_address = $this->checkRangeInputIpAddress();
	$sensorIdNew = ExactilyUserData::takeIntager($_POST['sensorId']);
	//удаляем выбранные записи из таблицы
	$this->deleteChooseTable($sensorIdOld, 'sensor_information_main_one');
	//вставляем новые данные
	$query_main_one = self::getConnectionDB()->connectionDB()->prepare("INSERT `sensor_information_main_one` 
																	  (`sensor_id`,
																	   `ip_start`,
																	   `ip_end`)
																		VALUE
																	  (:sensor_id,
		 														 	   :ip_start,
																 	   :ip_end)");
	for($i = 0; $i < count($array_range_ip_address); $i++){
		$ipStart = ip2long($array_range_ip_address[$i]['ipStart']);
		$ipEnd = ip2long($array_range_ip_address[$i]['ipEnd']);
		$query_main_one->execute(array('sensor_id' => $sensorIdNew,
									   'ip_start' => $ipStart,
									   'ip_end' => $ipEnd));
		}
	}

//изменить записи в таблице 'sensor_information_main_two'
// таблица содержит следующие поля информация по которым будет изменена
// - sensor_id
// - add_date_sensor
// - login
// - ip_address_sensor
// - sensor_chort_name
// - sensor_all_information
// - sensor_contacts
private function changeTableSensorInformationMainTwo($sensorIdOld, $login = false)
	{
	//получаем массив проверенных данных кроме диапазона IP-адресов
	$array_data = $this->checkData();
	if(array_key_exists('userName', $array_data)){
		$userName =	$array_data['userName'][0];
		} else {
		if(!$login) return false;
		$userName = $login;
		}
	//удаляем выбранные записи из таблицы
	$this->deleteChooseTable($sensorIdOld, 'sensor_information_main_two');
	//вставляем новые данные
	$query_main_two = self::getConnectionDB()->connectionDB()->prepare("INSERT `sensor_information_main_two`
																	  (`sensor_id`, 
																	   `add_date_sensor`, 
																	   `login`, 
																	   `ip_address_sensor`, 
																	   `sensor_chort_name`, 
																	   `sensor_all_information`, 
																	   `sensor_contacts`)
																	  	VALUE 
																	  (:sensor_id,
																	   '".time()."',
																	   :login,
																	   :ip_address_sensor,
																	   :sensor_chort_name,
																	   :sensor_all_information,
																	   :sensor_contacts)");

	$query_main_two->execute(array(':sensor_id' => $array_data['sensorId'][0],
								   ':login' => $userName,
								   ':ip_address_sensor' => ip2long($array_data['sensorIp'][0]),
								   ':sensor_chort_name' => $array_data['segmentName'][0],
								   ':sensor_all_information' => $array_data['segmentInfo'][0],
								   ':sensor_contacts' => $array_data['segmentContacts'][0]));
	}

//удалить выбранный сенсор в определенной таблице
protected function deleteChooseTable($sensorIdOld, $tableName)
	{
	//проверка существования значения
	if(empty($sensorIdOld) || empty($tableName)) return false;
	try{
		self::getConnectionDB()->connectionDB()->query("DELETE `".$tableName."` FROM `".$tableName."` 
														WHERE `sensor_id`='".$sensorIdOld."'");
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//показать информацию о сенсоре в виде пунктов меню
public function showSensorInformationMenu()
	{
	?>
	<script type="text/javascript">
	function openList(login){
	var elemTagA = document.getElementsByTagName('A');
	for(var i = 0; i < elemTagA.length; i++){
		if(elemTagA[i].hasAttribute('username') && elemTagA[i].getAttribute('username') === login){
			if(elemTagA[i].style.display == 'none' || elemTagA[i].style.display == ''){
				elemTagA[i].style.display = 'block';
				} else {
				elemTagA[i].style.display = 'none';
				}
			}
		}
	}
	</script>
	<?php
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT `sensor_id`, `login`, `sensor_chort_name` 
																 FROM `sensor_information_main_two` ORDER BY `login`, `sensor_id`");
		$loginName = '';
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$sensorChortName = $row->sensor_chort_name;
			if(mb_strlen($sensorChortName, 'UTF-8') > 20){
				$sensorChortName = mb_substr($sensorChortName, 0, 20, 'UTF-8').'...';
				}
			if($loginName !== $row->login){ 
				?>
				<div class="menuSensorWorkerName" onclick="openList('<?= $row->login ?>')"><?= $this->readXMLSetup->giveUserNameAndSurname($row->login) ?></div>
				<?php
				}
			$loginName = $row->login;
			?>
				<a href="#" sensorId="<?= $row->sensor_id; ?>" username="<?= $row->login ?>" class="listSensors">
				<?= $row->sensor_id.' '.$sensorChortName ?>
				</a>
			<?php
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	}

//показать краткую информацию о сенсоре
public function showShortInformation()
	{
	try {
		$query = self::getConnectionDB()->connectionDB()->query("SELECT `login`, COUNT(`login`) AS COUNT_SENSOR FROM `sensor_information_main_two` 
																 GROUP BY `login` ORDER BY COUNT_SENSOR DESC");
		?>
		<style type="text/css">
		.textStyle {
			font-size: 12px;
			font-family: 'Times New Roman', serif;
		}
		</style>
		<div style="width: 320px; display: inline-block;">
			<div style="text-transform: uppercase; padding-bottom: 5px; padding-top: 30px;">ответственные за сенсоры</div>
		<?php
		$sensorSum = 0;
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			echo "<div style='text-align: left; padding-left: 65px;'>".$this->readXMLSetup->giveUserNameAndSurname($row->login)." - {$row->COUNT_SENSOR} шт.</div>";
			$sensorSum += $row->COUNT_SENSOR;
			}
		?>
			<div style="padding-top: 5px;">общее количество сенсоров: <?= $sensorSum ?> шт.</div>
		</div>
		<?php
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//показать информацию о сенсоре для ее последующего изменения
public function showInformationChange()
	{
	$sensorId = ExactilyUserData::takeIntager($_POST['informationSensorId']);
	try {
		//выполняем запрос к БД
		$query_db = self::getConnectionDB()->connectionDB()->query("SELECT t0.sensor_id, `login`, `ip_address_sensor`, `ip_start`, `ip_end`, 
																   `sensor_chort_name`, `sensor_all_information`, `sensor_contacts` 
																    FROM  `sensor_information_main_one` t0 JOIN `sensor_information_main_two` t1 
																    ON t0.sensor_id=t1.sensor_id WHERE t0.sensor_id='".$sensorId."' ORDER BY `sensor_id`");
		//временный массив под IP-адреса		
		$array_tmp = $array_ip = array();
		while($row = $array_tmp[] = $query_db->fetch(PDO::FETCH_ASSOC)){
			$array_ip['ipStart'][] = $row['ip_start'];
			$array_ip['ipEnd'][] = $row['ip_end'];
			}
		//проверяем найденна ли информация
		if(count($array_tmp) <= 1) throw new Exception("информация по сенсору №{$sensorId}<br>не найдена");
		?>
		<style type="text/css">
		.textHead {
		font-size: 12px; 
		font-family: 'Times New Roman', serif;	
		}
		</style>
		<div style="padding: 20px; width: 715px;">
<!-- форма для изменения информации о сенсоре -->
		<form name="formAddNewSensor" method="POST" action="" onsubmit="return checkFormData(this)">
			<div style="overflow: hidden; mardin: 10px; width: 715px; text-align: center;">
				<div class="textHead" style="margin-top: 5px; margin-left: 7px; width: 230px; background: #CCFFFF; float: left;">
					id сенсора
				</div>
				<div class="textHead" style="margin-top: 5px; margin-left: 5px; width: 230px; float: left;">
					IP-адрес сенсора
				</div>
				<div class="textHead" style="margin-top: 5px; margin-left: 5px; width: 230px; background: #CCFFFF; float: left;">
					Ф.И.О. ответственного
				</div>
<!-- id-сенсора -->
				<div style="margin-top: 5px; margin-left: 12px; width: 230px; float: left; font-size: 18px; font-family: 'Times New Roman', serif;">
					<?= $array_tmp[0]['sensor_id'] ?>
					<input type="hidden" name="sensorId" value="<?= $array_tmp[0]['sensor_id'] ?>">
					<input type="hidden" name="sensorIdOld" value="<?= $array_tmp[0]['sensor_id'] ?>">
				</div>
<!-- ip-сенсора (поле ввода) -->
				<div class="textHead" style="margin-top: 5px; margin-left: 5px; width: 230px; float: left;">
					<input type="text" name="sensorIp" value="<?php echo long2ip($array_tmp[0]['ip_address_sensor']) ?>" style="border-color: #C6E2FF;">
				</div>
<!-- Ф.И.О. ответственного -->
				<div class="textHead" style="margin-top: 5px; margin-left: 5px; width: 230px; float: left; font-size: 14px; font-family: 'Times New Roman', serif;">
					<?= $this->readXMLSetup->usernameFIO($array_tmp[0]['login']) ?>
					<input type="hidden" name="userName" value="<?= $array_tmp[0]['login'] ?>">
				</div>
			</div>
<!-- диапазон защищаемых сетей (заголовок) -->
			<div class="textHead" class="textHead" style="margin-top: 10px; margin-left: 7px; margin-bottom: 5px; width: 700px; text-align: center; background: #CCFFFF;">
				<span style="font-weight: bold;">диапазон защищаемого сегмента сети или группы сетей</span>
			</div>
			<div style="overflow: hidden; width: 715px; text-align: center;">
				<div class="textHead" style="float: left; width: 357px; padding-bottom: 5px;">
					<img id="delIp" src='<?php echo "/{$this->directoryRoot}/img/button_minus.png"; ?>' style="cursor: pointer;" title="удалить IP-адрес"/>
					начальный IP-адрес
				</div>
				<div class="textHead" style="float: left; width: 357px; padding-bottom: 5px;">
					конечный IP-адрес
					<img id="addIp" src='<?php echo "/{$this->directoryRoot}/img/button_plus.png"; ?>' style="cursor: pointer;" title="добавить IP-адрес"/>
				</div>
<!-- поле ввода начального и конечного IP-адресов -->
				<div id="inputIp">
				<?php 
				for($i = 0; $i < count($array_ip['ipStart']); $i++){
				$attribute = ($i === 0) ? 'main="mainIp"': '';
				?>
					<div class="textHead" <?= $attribute ?> style="float: left; width: 357px;">
						<input type="text" name="ipStart[]" value="<?php echo long2ip($array_ip['ipStart'][$i]) ?>" style="width: 140px; height: 15px; border-color: #C6E2FF;">
					</div><div class="textHead" <?= $attribute ?> style="float: left; width: 357px;">
						<input type="text" name="ipEnd[]" value="<?php echo long2ip($array_ip['ipEnd'][$i]) ?>" style="width: 140px; height: 15px; border-color: #C6E2FF;">
					</div>
				<?php
				}
				?>
				</div>
				<div class="textHead" style="background: #CCFFFF; float: left; width: 230px; margin-top: 10px; margin-bottom: 5px; margin-left: 8px;">
					название защищаемого сегмента
				</div>
				<div class="textHead" style="background: #CCFFFF; float: left; width: 230px; margin-top: 10px; margin-bottom: 5px; margin-left: 5px;">
					информация о защищаемом сегменте
				</div>
				<div class="textHead" style="background: #CCFFFF; float: left; width: 230px; margin-top: 10px; margin-bottom: 5px; margin-left: 5px;">
					контактные данные
				</div>
<!-- краткое название защищаемого сегмента -->
				<div class="textHead" style="float: left; width: 230px; padding-top: 5px; padding-bottom: 5px; margin-left: 8px;">
					<input type="text" name="segmentName" value="<?= $array_tmp[0]['sensor_chort_name'] ?>" style="width: 180px; height: 15px; border-color: #C6E2FF;">
				</div>
<!-- полное название защищаемого сегмента -->
				<div class="textHead" style="float: left; width: 230px; padding-top: 5px; padding-bottom: 5px; margin-left: 5px;">
					<textarea type="text" name="segmentInfo" style="width: 200px; height: 45px; border-color: #C6E2FF;"><?= $array_tmp[0]['sensor_all_information'] ?></textarea>
				</div>
<!-- контактные данные ответственного -->
				<div class="textHead" style="float: left; width: 230px; padding-top: 5px; padding-bottom: 5px; margin-left: 5px;">
					<textarea type="text" name="segmentContacts" style="width: 200px; height: 45px; border-color: #C6E2FF;"><?= $array_tmp[0]['sensor_contacts'] ?></textarea>
				</div>
			</div>
			<div style="width: 725px; overflow: auto;">
<!-- кнопка "отмена" -->
				<div style="float: left; margin: 10px; width: 342px; text-align: left;">
					<input type="submit" name="back" style="border-color: #87CEEB; background: #E0EEEE; width: 100px; height: 20px;" value="отмена">
				</div>
<!-- кнопка "изменить" -->
				<div style="float: left; margin: 10px; width: 342px; text-align: right;">
					<input type="submit" name="changeInformation" style="border-color: #87CEEB; background: #E0EEEE; width: 100px; height: 20px;" value="изменить">
				</div>
<!-- поле javascript -->
				<input type="hidden" id="areaJScript" value="
				//функция проверки формы
				function checkFormData(elemForm){
					function showBorderColor(elem){
						elem.style.borderColor = 'red';
						return false;
					}
					function checkInteger(integer){
						var numPattern = /^[0-9]+$/;			
						if(!numPattern.test(integer.value)) return showBorderColor(integer);
						return true;
					}
					function checkIpAddress(ip){
						var ipPattern = /^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/;
						if(!ipPattern.test(ip.value)) return showBorderColor(ip);
						return true;
					}
					//убираем рамку вокруг элемента
					for(var j = 0; j < elemForm.length; j++){
						elemForm[j].style.borderColor = '';
					}
					for(var i = 0; i < elemForm.length; i++){
						//пропускаем кнопку
						if(elemForm[i].name == 'save') continue;
						//проверяем на пустоту
						if(elemForm[i].value.length == 0) return showBorderColor(elemForm[i]); 
 						//если это поле ввода чисел
 						if(elemForm[i].name == 'sensorId' && checkInteger(elemForm[i]) !== true) return checkInteger(elemForm[i]);
 						//если это поле ввода IP-адреса
 						if((elemForm[i].name == 'sensorIp' || elemForm[i].name == 'ipStart[]' || elemForm[i].name == 'ipEnd[]') 
 							&& checkIpAddress(elemForm[i]) !== true) return checkIpAddress(elemForm[i]);
 						//проверяем выбор ответственного 
 						if(elemForm[i].name == 'userName' && elemForm[i].value == 'null') return showBorderColor(elemForm[i]);
					}
				return true;
				}
				//функция изменение картинок
				function chageImg(num, elem){
					switch(num){
						case 1:
							elem.src = '/<?= $this->directoryRoot ?>/img/button_minus.png';
						break;
						case 2:
							elem.src = '/<?= $this->directoryRoot ?>/img/button_minus_1.png';
						break;
						case 3:
							elem.src = '/<?= $this->directoryRoot ?>/img/button_plus.png';			
						break;
						case 4:
							elem.src = '/<?= $this->directoryRoot ?>/img/button_plus_1.png';			
						break;
					}
				}
				//функция добавления полей IP-адресов
				function addElemIp(){
					//создаем универсальный div
					createDiv = function(){
						var div = document.createElement('DIV');
						div.setAttribute('class', 'textHead');
						div.setAttribute('style', 'float: left; width: 357px; margin-top: 5px;');
						return div;
					};
					//создаем универсальный input
					createInput = function(){
						var input = document.createElement('INPUT');
						input.setAttribute('type', 'text');
						input.setAttribute('style', 'width: 140px; height: 15px; border-color: #C6E2FF;');
						return input;
					};
					var inputIp = document.getElementById('inputIp');
					//создаем поле ipStart
					var inputIpStart = createInput();
					var divStart = createDiv();
					inputIpStart.setAttribute('name', 'ipStart[]');
					divStart.appendChild(inputIpStart);
					inputIp.appendChild(divStart);
					//создаем поле ipEnd
					var inputIpEnd = createInput();
					var divEnd = createDiv();
					inputIpEnd.setAttribute('name', 'ipEnd[]');
					divEnd.appendChild(inputIpEnd);
					inputIp.appendChild(divEnd);
				}
				//функция удаления полей IP-адресов
				function delElemIp(){
					var inputIp = document.getElementById('inputIp');
					var inputIpChild = inputIp.childNodes;
					for(var i = inputIpChild.length; i > 1; i--){
						if(inputIpChild[i] == null || inputIpChild[i].nodeName != 'DIV') continue;
						if(!inputIpChild[i].hasAttribute('main')){
							inputIp.removeChild(inputIpChild[i]);
							inputIp.removeChild(inputIpChild[i - 1]);
							break;
						}
					}
				}
				//функция добавления обработчика
				function pageOnload(){
					//обработчик для изображения удаления IP-адресов
					var delIp = document.getElementById('delIp');
					if(delIp){
						//обработчик удаляющий поле IP-адресов
						delIp.addEventListener('click', delElemIp, false);		
						delIp.addEventListener('mouseout', function (){ chageImg(1, this) }, false);
						delIp.addEventListener('mouseover', function (){ chageImg(2, this) }, false);
					}
					//обработчик для изображения добавления IP-адресов
					var addIp = document.getElementById('addIp');
					if(addIp){
						//обработчик добавляющий поле IP-адресов
						addIp.addEventListener('click', addElemIp, false);
						addIp.addEventListener('mouseout', function (){ chageImg(3, this) }, false);
						addIp.addEventListener('mouseover', function (){ chageImg(4, this) }, false);
					}
				}
				">
			</div>
		</form>
		</div>
		<?php
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	catch(Exception $e){
		(new ShowMessage)->informationNotFound($e->getMessage(), 50);
		}
	}
}
?>