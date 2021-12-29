<?php

						/*--------------------------------------------------------------*/
						/* класс работы администратора с информацией по новому сенсору	*/
						/*	- создание сенсора											*/
						/*	- удаление сенсора											*/
						/*	- редактирования информации									*/
						/*											v0.1 18.02.2015		*/
						/*--------------------------------------------------------------*/

class TotalInformationForSensorAdmin extends TotalInformationForSensor
{
protected $directoryRoot;
protected $fieldForm;
function __construct()
	{
	parent::__construct();
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directoryRoot = $array_directory[1];
	$this->setFieldForm(array('sensorId' => 'идентификатор сенсора',
							  'sensorIp' => 'IP-адрес сенсора',
							  'userName' => 'имя ответственного за сенсор',
							  'ipStart' => 'начальный IP-адрес защищаемой сети',
							  'ipEnd' => 'конечный IP-адрес защищаемой сети',
							  'segmentName' => 'название защищаемого сегмента сети',
							  'segmentInfo' => 'информацию по защищаемому сегменту сети',
							  'segmentContacts' => 'контактные данные'));
	}

//функция проверяющая данные, кроме диапазона IP-адресов, необходимые при создании нового сенсора
protected function checkData()
	{
	$array_check = parent::checkData();
	$array_check['userName'][] = ExactilyUserData::takeString($_POST['userName']); 
	return $array_check;
	}

//функция добавляющая новый сенсор и информацию к нему
public function addInformation()
	{
	?>
	<script type="text/javascript">
	function pageReload(){
		//обновляем страницу
		setTimeout(function reload(){ window.location.href = window.location.pathname; }, '3000');
	}
	</script>
	<?php
	//получаем массив проверенных данных кроме диапазона IP-адресов
	$array_data = $this->checkData();
	//получаем массив защищаемого диапазона IP-адресов
	$array_range_ip_address = $this->checkRangeInputIpAddress();

	try {
		//проверяем существует ли запись в таблице `sensor_information_main_one`
		if($this->checkDBRecord('sensor_information_main_one', $array_data['sensorId'][0]) !== false){
			?>
			<script type="text/javascript">
				pageReload();
			</script>
			<?php
			$this->showErrorMessage("сенсор с id = ".$array_data['sensorId'][0]." уже существует");
			}
		//проверяем существует ли запись в таблице `sensor_information_main_two`
		if($this->checkDBRecord('sensor_information_main_two', $array_data['sensorId'][0]) !== false){
			?>
			<script type="text/javascript">
				pageReload();
			</script>
			<?php
			$this->showErrorMessage("сенсор с id = ".$array_data['sensorId'][0]." уже существует");
			}
		
		//вставка данных в таблицу `sensor_information_main_one`
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
			$query_main_one->execute(array('sensor_id' => $array_data['sensorId'][0],
										   'ip_start' => $ipStart,
										   'ip_end' => $ipEnd));
			}

		//вставка данных в таблицу `sensor_information_main_two`
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
									   ':login' => $array_data['userName'][0],
									   ':ip_address_sensor' => ip2long($array_data['sensorIp'][0]),
									   ':sensor_chort_name' => $array_data['segmentName'][0],
									   'sensor_all_information' => $array_data['segmentInfo'][0],
									   'sensor_contacts' => $array_data['segmentContacts'][0])); 
		?>
		<script type="text/javascript">
		//обновляем страницу
		window.location.href = window.location.pathname;
		</script>
		<?php
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//удалить сенсор
public function deleteAllInformation()
	{
	$sensorId = ExactilyUserData::takeIntager($_POST['delete']);
	try{
		self::getConnectionDB()->connectionDB()->query("DELETE `sensor_information_main_one`, `sensor_information_main_two` FROM `sensor_information_main_one`, `sensor_information_main_two`
														WHERE sensor_information_main_one.sensor_id=sensor_information_main_two.sensor_id 
														AND sensor_information_main_one.sensor_id='".$sensorId."'");
	?>
	<script type="text/javascript">
	//обновляем страницу
		window.location.href = window.location.pathname;
	</script>
	<?php
	}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//изменить информацию о сенсоре (администратор)
public function changeInformation()
	{
	$this->changeSensorId(ExactilyUserData::takeIntager($_POST['sensorIdOld']));
	
	//сообщение об удачном выполнении изменения
	ShowMessage::messageOkRedirect("изменение выполненно успешно", 50);
	}

//показать информацию о сенсоре для ее последующего изменения
public function showInformationChange()
	{
	$sensorId = ExactilyUserData::takeIntager($_POST['changeSensorId']);
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
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	?>
	<div style="margin: 10px; background: #FFFAF0; width: 725px;">
<!-- форма для изменения информации о сенсоре -->
	<form name="formAddNewSensor" method="POST" action="" onsubmit="return checkFormData(this)">
		<div style="overflow: hidden; mardin: 10px; width: 725px;">
			<div class="textHead" style="margin-top: 5px; margin-left: 12px; width: 230px; float: left;">
				id сенсора
			</div>
			<div class="textHead" style="margin-top: 5px; margin-left: 5px; width: 230px; float: left;">
				IP-адрес сенсора
			</div>
			<div class="textHead" style="margin-top: 5px; margin-left: 5px; width: 230px; float: left;">
				Ф.И.О. ответственного
			</div>
<!-- id-сенсора (поле ввода) -->
			<div class="textHead" style="margin-top: 5px; margin-left: 12px; width: 230px; float: left;">
				<input type="text" name="sensorId" value="<?= $array_tmp[0]['sensor_id'] ?>" style="border-color: #C6E2FF;">
				<input type="hidden" name="sensorIdOld" value="<?= $array_tmp[0]['sensor_id'] ?>">
			</div>
<!-- ip-сенсора (поле ввода) -->
			<div class="textHead" style="margin-top: 5px; margin-left: 5px; width: 230px; float: left;">
				<input type="text" name="sensorIp" value="<?php echo long2ip($array_tmp[0]['ip_address_sensor']) ?>" style="border-color: #C6E2FF;">
			</div>
<!-- Ф.И.О. ответственного (поле ввода) -->
			<div class="textHead" style="margin-top: 5px; margin-left: 5px; width: 230px; float: left;">
				<?php ListBox::listUserNameLoginName(20, $array_tmp[0]['login']) ?>
			</div>
		</div>
<!-- диапазон защищаемых сетей (заголовок) -->
		<div class="textHead" class="textHead" style="padding-top: 10px; padding-bottom: 5px; width: 725px;">
			<span style="font-weight: bold;">диапазон защищаемого сегмента сети или группы сетей</span>
		</div>
		<div style="overflow: hidden; width: 725px;">
			<div class="textHead" style="float: left; width: 360px; padding-bottom: 5px;">
				<img id="delIp" src='<?php echo "/{$this->directoryRoot}/img/button_minus.png"; ?>' style="cursor: pointer;" title="удалить IP-адрес"/>
				начальный IP-адрес
			</div>
			<div class="textHead" style="float: left; width: 360px; padding-bottom: 5px;">
				конечный IP-адрес
				<img id="addIp" src='<?php echo "/{$this->directoryRoot}/img/button_plus.png"; ?>' style="cursor: pointer;" title="добавить IP-адрес"/>
			</div>
<!-- поле ввода начального и конечного IP-адресов -->
			<div id="inputIp">
			<?php 
			for($i = 0; $i < count($array_ip['ipStart']); $i++){
			$attribute = ($i === 0) ? 'main="mainIp"': '';
			?>
				<div class="textHead" <?= $attribute ?> style="float: left; width: 360px;">
					<input type="text" name="ipStart[]" value="<?php echo long2ip($array_ip['ipStart'][$i]) ?>" style="width: 140px; height: 15px; border-color: #C6E2FF;">
				</div><div class="textHead" <?= $attribute ?> style="float: left; width: 360px;">
					<input type="text" name="ipEnd[]" value="<?php echo long2ip($array_ip['ipEnd'][$i]) ?>" style="width: 140px; height: 15px; border-color: #C6E2FF;">
				</div>
			<?php
			}
			?>
			</div>
			<div class="textHead" style="float: left; width: 230px; padding-top: 10px; padding-bottom: 5px; margin-left: 10px;">
				название защищаемого сегмента
			</div>
			<div class="textHead" style="float: left; width: 230px; padding-top: 10px; padding-bottom: 5px; margin-left: 8px;">
				информация о защищаемом сегменте
			</div>
			<div class="textHead" style="float: left; width: 230px; padding-top: 10px; padding-bottom: 5px; margin-left: 8px;">
				контактные данные
			</div>
<!-- краткое название защищаемого сегмента -->
			<div class="textHead" style="float: left; width: 230px; padding-top: 5px; padding-bottom: 5px; margin-left: 10px;">
				<input type="text" name="segmentName" value="<?= $array_tmp[0]['sensor_chort_name'] ?>" style="width: 180px; height: 15px; border-color: #C6E2FF;">
			</div>
<!-- полное название защищаемого сегмента -->
			<div class="textHead" style="float: left; width: 230px; padding-top: 5px; padding-bottom: 5px; margin-left: 8px;">
				<textarea type="text" name="segmentInfo" style="width: 200px; height: 45px; border-color: #C6E2FF;"><?= $array_tmp[0]['sensor_all_information'] ?></textarea>
			</div>
<!-- контактные данные ответственного -->
			<div class="textHead" style="float: left; width: 230px; padding-top: 5px; padding-bottom: 5px; margin-left: 8px;">
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
		</div>
	</form>
	</div>
	<?php
	}

//показать лраткую информацию о сенсоре
public function showInformation()
	{
	try {
		//проверяем имеется ли в БД информация
		$query = self::getConnectionDB()->connectionDB()->query("SELECT COUNT(`sensor_id`) AS COLUMNS_NUM
																 FROM `sensor_information_main_two`");
		if($query->fetch(PDO::FETCH_OBJ)->COLUMNS_NUM == 0){
			echo ShowMessage::informationNotFound('информация не найдена', 10);
			}

		//выполняем запрос к БД
		$query_db = self::getConnectionDB()->connectionDB()->query("SELECT t0.sensor_id, `add_date_sensor`, 
																   `login`, `ip_address_sensor`, `ip_start`, 
																   `ip_end`, `sensor_chort_name` FROM `sensor_information_main_one` t0 
																    JOIN  `sensor_information_main_two` t1 ON t0.sensor_id=t1.sensor_id 
																    ORDER BY `sensor_id`");
		//временный массив под IP-адреса		
		$array_tmp = $array_ip = array();
		while($row = $array_tmp[] = $query_db->fetch(PDO::FETCH_ASSOC)){
			$array_ip[$row['sensor_id']]['ipStart'][] = $row['ip_start'];
			$array_ip[$row['sensor_id']]['ipEnd'][] = $row['ip_end'];
			}
		?>
		<script type="text/javascript">
		//функция изменеия изображения
		function changePicturesButton(picture, elem){
			elem.src = "/<?= $this->directoryRoot ?>/img/" + picture;
		}

		//подтверждение действия
		function confirmAction(sensorId){
			var test = confirm("Вы действительно хотите удалить сенсор №" + sensorId);
			if(test) return true;
			return false;
		}
		</script>

		<div style="border-width: 1px; border-style: solid; width: 725px; border-color: #B7DCF7;  margin-bottom: 20px;">
		<table id="tableShowInformation" border="0" style="width: 725px;">
			<?php echo "<tr ".COLOR_HEADER.">"; ?>
				<th colspan="2" class="tableHeader" style="width: 80px;">идентификатор сенсора</th>
				<th class="tableHeader" style="width: 100px;">дата добавления</th>
				<th class="tableHeader" style="width: 175px;">Ф.И.О. ответственного</th>
				<th class="tableHeader" style="width: 100px;">IP-адрес сенсора</th>
				<th class="tableHeader" style="width: 150px;">диапазон защищаемых<br>IP-адресов</th>
				<th class="tableHeader" style="width: 100px;">краткая информация</th>
			</tr>
			<?php
			$countArrayTmp = count($array_tmp);
			$sensorId = '';
			for($i = 0; $i < $countArrayTmp - 1; $i++){
				if($sensorId == $array_tmp[$i]['sensor_id']) continue;
				$sensorId = $array_tmp[$i]['sensor_id'];			
				echo "<tr bgcolor=".color().">";
				?>
<!-- идентификатор сенсора -->
					<td style="width: 30px; text-align: center;">
<!-- удаление -->		
						<form name="formDelete" method="POST" action="" onsubmit="return confirmAction(<?= $array_tmp[$i]['sensor_id'] ?>)">			
							<input type="image" src='<?php echo "/".$this->directoryRoot."/img/delete.png" ?>' onmouseout="changePicturesButton('delete.png', this)" onmouseover="changePicturesButton('delete_1.png', this)" style="cursor: pointer;">
							<input type="hidden" name="delete" value="<?= $array_tmp[$i]['sensor_id'] ?>">
						</form>
<!-- редактирование -->
						<form name="formChange" method="POST" action="">
							<input type="image" src='<?php echo "/".$this->directoryRoot."/img/pencil.png" ?>' onmouseout="changePicturesButton('pencil.png', this)" onmouseover="changePicturesButton('pencil_1.png', this)" style="cursor: pointer;">
							<input type="hidden" name="changeSensorId" value="<?= $array_tmp[$i]['sensor_id'] ?>">
						</form>
					</td>
					<td style="width: 40px; text-align: center; font-size: 11px; font-family: 'Times New Roman', serif;">
						<?= $array_tmp[$i]['sensor_id'] ?>
					</td>	
<!-- дата добавления -->
					<td style="text-align: center; font-size: 10px; font-family: 'Times New Roman', serif;">
						<?php echo ConversionData::showDateConvert($array_tmp[$i]['add_date_sensor']) ?>
					</td>
<!-- Ф.И.О. ответственного -->
					<td style="text-align: center; font-size: 10px; font-family: 'Times New Roman', serif;">
						<?php echo $this->readXMLSetup->usernameFIO($array_tmp[$i]['login']) ?>
					</td>
<!-- IP-адрес сенсора -->
					<td style="text-align: center; font-size: 10px; font-family: 'Times New Roman', serif;">
						<?php echo long2ip($array_tmp[$i]['ip_address_sensor']) ?>
					</td>
<!-- диапазон защищаемых IP-адресов -->
					<td style="text-align: center; font-size: 10px; font-family: 'Times New Roman', serif;">
						<?php
							for($j = 0; $j < count($array_ip[$array_tmp[$i]['sensor_id']]['ipStart']); $j++){
							echo "<table>";
							echo "<tr>";
							echo "<td style='text-align: center; width: 70px;'>".long2ip($array_ip[$array_tmp[$i]['sensor_id']]['ipStart'][$j])."</td>";
							echo "<td>-</td>";
							echo "<td style='text-align: center; width: 70px;'>".long2ip($array_ip[$array_tmp[$i]['sensor_id']]['ipEnd'][$j])."</td>";
							echo "</tr>";
							echo "</table>";
							}
						?>
					</td>
<!-- краткая информация -->
					<td style="text-align: center; font-size: 10px; font-family: 'Times New Roman', serif;">
						<?= $array_tmp[$i]['sensor_chort_name'] ?>
					</td>
				</tr>
				<?php
				}
			?>
		</table>
		</div>
		<?php
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//проверка существования в БД записи
private function checkDBRecord($tableName, $id)
	{
	try {
		$query = self::getConnectionDB()->connectionDB()->prepare("SELECT COUNT(`sensor_id`) AS COUNT_ID 
																   FROM `".$tableName."` WHERE `sensor_id`=:id");
		$query->bindParam(':id', $id);
		$query->execute();
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			if($row->COUNT_ID == 0){ 
				return false;
				} else {
				return $row->COUNT_ID;
				}
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
}
?>