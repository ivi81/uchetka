<?php

						/*------------------------------------------------------------------------------*/
						/*  	управление списком black IP-адресов (например сеть Tor, Bot-net и т.д.)	*/
						/* 					 	 									v.0.1 06.08.2014    */
						/*------------------------------------------------------------------------------*/

?>
<script type="text/javascript">

//проверка выбора типа списка IP-адресов и загружаемого файла	
function checkInput(){
	var form = document.loadFile;
	//убираем выделение вокруг элементов
	var num = form.length;
	for(var i = 0; i < num; i++){
		form[i].style['borderColor'] = '';
		showMessage('');		
	}

	//проверяем выбор типа списка
	if(form.typeIpList.value == 0){
		form.typeIpList.style['borderColor'] = 'red';
		showMessage('необходимо выбрать тип списка IP-адресов');
		return false;
	}
	//проверяем выбор файла
	if(form.loadFiles.value.length == 0){
		form.loadFiles.style['borderColor'] = 'red';
		showMessage('файл не выбран');
		return false;	
	}
}


//показать информационное сообщения
function showMessage(message){
	var showMessage = document.getElementById('message');
		showMessage.style['display'] = 'block';
		showMessage.style['position'] = 'relative';
		showMessage.style['top'] = '-60px';
		showMessage.innerHTML = message;		
}

//изменение изображения	
function chageImg(elem, flag, directory){
	switch(flag){
		//удаление
		case '1':
			elem.src = '/' + directory + '/img/delete_recording.png';
		break;
		//удаление			
		case '2':
			elem.src = '/' + directory + '/img/delete_recording_1.png';
		break;
		}
	}

//подтверждение удаления записи о доменном имени
function confirmTypeDel(type){
	var testOk = confirm('удалить список ' + type + '?');
	return (testOk) ? true : false;
	}

//вывод списка стран с количеством IP-адресов
function showListCounrty(elem, test){
	if(test == 'down'){
		var listCountry = elem.previousSibling.previousSibling;
		if(listCountry.getAttribute('style').split(' ')[1] != 'block;'){
			listCountry.setAttribute('style', 'display: block; margin-left: 5px; margin-right: 5px; border-radius: 3px; background: #F0FFFF; box-shadow: inset 0 0 8px 0px #B7DCF7;');
			var sumCountry = elem.firstChild.nextSibling.nextSibling.nextSibling.firstChild;
			var sumIp = elem.firstChild.nextSibling.nextSibling.nextSibling.nextSibling.nextSibling.nextSibling.nextSibling.firstChild;
			//создание нового элемента DIV
			setDiv(listCountry.previousSibling.previousSibling, sumCountry['data'], sumIp['data']);
			} else {
			//удаление элемента
			delDiv(elem);
			listCountry.setAttribute('style', 'display: none;');
			}
		} else {
		elem.parentNode.nextSibling.nextSibling.setAttribute('style', 'display: none;');
		//удаление элемента
		delDiv(elem);
		}
	}

//создание DIV
function setDiv(elem, sumCountry, sumIp){
	var elemDiv = document.createElement('div');
	elemDiv.innerHTML = '<span style="color: #3300FF; text-decoration: underline;">всего: стран</span> - <span style="color: #FF0000; font-size: 16px;">' + sumCountry + '</span>, <span style="color: #3300FF; text-decoration: underline;">IP-адресов</span> - <span style="color: #FF0000; font-size: 16px;">' + sumIp + '</span>';
	elemDiv.setAttribute('style', 'text-align: center; cursor: pointer;');
	elemDiv.setAttribute('onclick', "showListCounrty(this, 'up')");
	elemDiv.setAttribute('id', "up");
	elem.appendChild(elemDiv);
}

//удаление DIV
function delDiv(elem){
	var upElem = document.getElementById('up');
	if(upElem){
		upElem.parentNode.removeChild(upElem);
	}
}
</script>
<?php

class ControlBlackIpList extends ControlIpList
{
//директория сайта
private $directory;
//имя загруженного файла
private $uploadFileName;
private static $readXml;

public function __construct()
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directory = $array_directory[1];
	//проверяем доступность таблицы black IP
	try{
		//создаем таблицу если ее нет
		$this->createIpList();
		}
	catch (PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//чтение XML файла setup_site.xml
private static function readXML()
	{
	if(empty(self::$readXml)){
		self::$readXml = new ReadXMLSetup();
		}
	return self::$readXml;
	}

//вывод общей информации о таблице black_ip_list
public function showBDInfo()
	{
	try{
		?>
		<div style="">
			<lu style="margin-left: 20px;">
<!-- кол-во записей -->
				<li style="list-style-type: none; margin-left: 20px; padding-left: 20px;">
				<span style="font-size: 14px; font-family: 'Times New Roman', serif; color: #0000">
				Всего записей: <span style="text-decoration: underline;"><?php echo $this->showIpListInfo(); ?></span>
				</span>
				</li>
<!-- детально -->
				<li style="list-style-type: none; margin-left: 20px; padding-left: 20px;">
				<span style="font-size: 14px; font-family: 'Times New Roman', serif; color: #0000">
				Из них: 
				</span>
<!-- информация по каждому типу списка IP-адресов -->
				<?php $this->showCountTypeIp(); ?>
				</li>
<!-- дата последнего обновления -->
				<li style="list-style-type: none; margin-left: 20px; padding-left: 20px;">
				<span style="font-size: 14px; font-family: 'Times New Roman', serif; color: #0000;">
				Дата последнего обновления таблицы:<br> 
				<span style="text-decoration: underline;"><?php echo ConversionData::showDateConvertStr($this->showTableIpListInfo()) ?></span>
				</span>
				</li>
			</lu>
		</div>
		<?php
		}
	catch (PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//вывод формы загрузки файла со списком IP-адресов
public function loadFile()
	{
	?>
	<form name="loadFile" method="POST" enctype="multipart/form-data" onsubmit="return checkInput()" action="">
<!-- выпадающий список типов списков IP-адресов -->
		<div style="position: relative; top: 10px;"><?php ListBox::listTypeIpList(); ?></div>
		<div style="position: relative; top: 20px;"><input type="file" name="loadFiles" value="обзор" class="formProtocol" style="height: 20px;" title="загрузка файла"></div>
		<div style="position: relative; top: 30px;"><input type="text" name="infoIpList" style="width: 260px; height: 20px;" placeholder="дополнительная информация" title="дополнительная информация"></div>
		<div style="position: relative; top: 40px;">
			<input type="submit" name="send" value="загрузить" title="загрузить данные" style="width: 100px; height: 23px;">
		</div>
	</form>
	<?php
	}

//вывод общей информации о списке IP-адресов
private function showIpListInfo()
	{
	$query = self::getConnectionDB()->connectionDB()->query("SELECT COUNT(*) AS `num` FROM `black_ip_list`");
	return $query->fetch(PDO::FETCH_OBJ)->num;
	}

//вывод информации о количестве IP-адресов каждого типа
private function showCountTypeIp()
	{
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT `type`, COUNT(`type`) AS `count_type` 
																 FROM `black_ip_list` GROUP BY `type` ORDER BY `count_type` DESC");
		$a = 1;
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			?><li style="list-style-type: none; margin-left: 30px; padding-left: 20px;"><?php 
			echo $a++.'. <span style="font-weight: bold;">'.self::readXML()->giveTypeIpList($row->type).
				 '</span> - <span style="text-decoration: underline;">'.
				 $row->count_type.'</span></li>';
			}
		}
	catch (PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}		
	}

//вывод подробной информации о списке IP-адресов
public function showIpListInfoDetails()
	{
	$GeoIP = new GeoIP;
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT `type`, `information`, `date_time_create`, COUNT(`type`) AS COUNT_IP 
																 FROM `black_ip_list` GROUP BY `type` ORDER BY `COUNT_IP` DESC");
		?>
		<div style="position: relative; top: 0px; left: 5px;">
		<table border="0" style="width: 735px;">
			<?php echo "<tr ".COLOR_HEADER.">"; ?>
				<th style="width: 50px; font-size: 10px; font-family: 'Times New Roman', serif;"></th>
				<th style="width: 150px; font-size: 10px; font-family: 'Times New Roman', serif;">тип списка</th>
				<th style="width: 250px; font-size: 10px; font-family: 'Times New Roman', serif;">количество стран и IP-адресов</th>
				<th style="width: 185px; font-size: 10px; font-family: 'Times New Roman', serif;">дополнительная информация</th>
				<th style="width: 100px; font-size: 10px; font-family: 'Times New Roman', serif;">дата последнего обновления</th>
			<tr>
		<?php			
		while ($row = $query->fetch(PDO::FETCH_OBJ)){
			$query_country = self::getConnectionDB()->connectionDB()->query("SELECT `country_code`, COUNT(`country_code`) AS NUM_CODE 
																			 FROM `black_ip_list` WHERE `type`='".$row->type."'  GROUP BY `country_code` ORDER BY NUM_CODE DESC");
			echo "<tr bgcolor=".color().">"; ?>
				<td style="font-size: 12px; font-family: 'Times New Roman', serif; text-align: center;">
<!-- иконка об удалении -->				
				<form name="delTypeIp" method="POST" onsubmit="return confirmTypeDel('<?= self::readXML()->giveTypeIpList($row->type); ?>')" action="">
					<input onmouseout="chageImg(this, '1', '<?= $this->directory ?>')" onmouseover="chageImg(this, '2', '<?= $this->directory ?>')" type="image" src="/<?= $this->directory ?>/img/delete_recording.png" name="deleteTypeIP" title="удалить выбранный тип списка IP-дресов">
					<input type="hidden" name="deleteType" value="<?= $row->type ?>">
				</form>
				</td>
<!-- тип списка -->
				<td style="font-size: 13px; font-family: 'Times New Roman', serif; text-align: center;">
					<?= self::readXML()->giveTypeIpList($row->type) ?>
				</td>
<!-- количество стран и IP-адресов -->
				<td style="font-size: 12px; font-family: 'Times New Roman', serif;">
					<div value="point"></div> 
					<div id="listCountry" style="display: none;">
						<?php
						$num = 0;
						?><div style="margin-left: 10px; width: 210px;"><br><?php
						while($row_country = $query_country->fetch(PDO::FETCH_OBJ)){
							if($row_country->country_code != ''){
								echo '&nbsp;'.$GeoIP::$codeCountry[$row_country->country_code].' - '.$row_country->NUM_CODE.'<br>';
								$num++;
								}
							}
						?>
						<br>
						</div>
					</div>
					<div style="text-align: center; cursor: pointer;" onclick="showListCounrty(this, 'down')">
						<?php 
						echo '<span style="color: #3300FF; text-decoration: underline;">всего: стран</span> - <span style="color: #FF0000; font-size: 16px;">'.$num.'</span>'; 
						echo ', <span style="color: #3300FF; text-decoration: underline;">IP-адресов</span> - <span style="color: #FF0000; font-size: 16px;">'.$row->COUNT_IP.'</span>';
						?>
					</div>
				</td>
<!-- дополнительная информация -->
				<td style="font-size: 12px; font-family: 'Times New Roman', serif; text-align: center;"><?= $row->information ?></td>
<!-- дата последнего обновления -->
				<td style="font-size: 12px; font-family: 'Times New Roman', serif; text-align: center;">
					<?php 
					$year = substr($row->date_time_create, 0, 4);
					$month = substr($row->date_time_create, 5, 2); 
					$day = substr($row->date_time_create, 8, 2);
					echo $day.'.'.$month.'.'.$year;
					?>
				</td>				
			</tr>
			<?php
			}
		echo "</table></div>";
		}
	catch (PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//вывод информации об обновлении таблицы black_ip_list 
private function showTableIpListInfo()
	{
	$query = self::getConnectionDB()->connectionDB()->query("SHOW TABLE STATUS FROM `data_on_KA` WHERE `Name`='black_ip_list'");
	return $query->fetch(PDO::FETCH_OBJ)->Update_time;	
	}

//создание таблицы БД
protected function createIpList()
	{
	self::getConnectionDB()->connectionDB()->query("CREATE TABLE IF NOT EXISTS `black_ip_list` 
												   (type INT(5) UNSIGNED NOT NULL,
								     				ip_address INT(10) UNSIGNED NOT NULL,
								     				country_code varchar(2),
								     				country varchar(255),
									 				information TEXT,
									 				date_time_create DATETIME NOT NULL,
									 				INDEX index_ip(ip_address),
									 				INDEX index_type(type),
									 				INDEX index_code(country_code)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	}

//удаление выбранного типа списка IP-адресов
private function deleteTypeIpList($code)
	{
	try{
		$query = self::getConnectionDB()->connectionDB()->prepare("DELETE FROM `black_ip_list` WHERE `type`=:type");
		$query->execute(array(':type' => $code));
		?>
<!-- скрипт для предотвращении постоянного добавления одной и той же информации -->		
		<script type="text/javascript">
			window.location.href = window.location.pathname + '?id=1';
		</script> 
		<?php
		}
	catch (PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	}

//обновление списков IP-адресов
public function updateIpList()
	{
	//если нажата кнопка загрузить файл
	if(isset($_POST['send'])){
		//проверяем  выбор типа списка IP-адресов
		if((!isset($_POST['typeIpList'])) || (empty($_POST['typeIpList'])) || ($_POST['typeIpList'] == 0)){
			echo ShowMessage::showInformationError("Необходимо выбрать тип списка IP-адресов");
			}
		//проверяем выбор загружаемого файла
		if((!isset($_FILES['loadFiles'])) || (empty($_FILES['loadFiles']['tmp_name']))){
			echo ShowMessage::showInformationError("Необходимо выбрать файл со списком IP-адресов");
			}
		//загружаем файл
		$this->uploadFile();
		//читаем файл и записываем данные в БД
		$this->readFile();
		//удаляем загруженный файл
		$this->deleteUploadFile();
		//получаем массив списков IP-адресов и создаем из них бинарную базу данных
		$this->createFileBinaryDB($this->getArrayIP());
		}
	//удаляем выбранный список
	if(isset($_POST['deleteType']) && ($_POST['deleteType']) > 0){
		$this->deleteTypeIpList(intval($_POST['deleteType']));
		//получаем массив списков IP-адресов и пересоздаем из них бинарную базу данных
		$this->createFileBinaryDB($this->getArrayIP());
		}
	}

//создание бинарной БД
private function createFileBinaryDB($array_ip)
	{
	$CreateBinaryDBIpAddress = new CreateBinaryDBIpAddress;
	$CreateBinaryDBIpAddress->createDB($array_ip);
	}

//получаем из БД массив IP-адресов
private function getArrayIP()
	{
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT `ip_address` FROM `black_ip_list` GROUP BY `ip_address`");
		$array = array();
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$array[] = long2ip($row->ip_address);
			}
		return $array;		
		}
	catch (PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	} 

//чтение загруженного файла
private function readFile()
	{
	if(!$array_file = file($this->uploadFileName)){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_FILE, "\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: невозможно прочитать файл ".$this->uploadFileName);
		}

	//массив для полученных IP-адресов
	$array_ip = array();
	//получаем IP-адреса
	foreach($array_file as $string){
	//Глобальный поиск шаблона в строке
		if(preg_match_all("/[0-9]{1,3}[\.][0-9]{1,3}[\.][0-9]{1,3}[\.][0-9]{1,3}/", $string, $ip_address, PREG_SET_ORDER)){
			for($i = 0; $i < (count($ip_address)); $i++){
				$ip_address = ip2long($ip_address[$i][0]);
				if(isset($ip_address)) {
					$array_ip[] = $ip_address;
					}
				}
			}	
		}
	//проверяем код типа списка IP-адресов
	$codeTypeIp = ExactilyUserData::takeIntager($_POST['typeIpList']);
	try{
		//создаем новую таблицу БД если ее нет
		$this->createIpList();
		//получаем массив IP-адресов относящихся к выбранному типу списка IP-адресов
		$array_ip_db = $this->checkIpListIp($codeTypeIp);
		//загружаем в БД
		$this->loadIpInDB($array_ip, $array_ip_db, $codeTypeIp);
		}
	catch (PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	?>
	<!-- скрипт для предотвращении постоянного добавления одной и той же информации -->		
		<script type="text/javascript" >
			window.location.href = window.location.pathname + '?id=1';
		</script>
	<?php
	}

//получаем массив IP-адресов относящихся к выбранному типу списка IP-адресов		
private function checkIpListIp($code)
	{
	$query = self::getConnectionDB()->connectionDB()->prepare("SELECT `ip_address` FROM `black_ip_list` WHERE `type`=:code");
	$query->execute(array(':code' => $code));
	$array_ip = array();
	while($row = $query->fetch(PDO::FETCH_ASSOC)){
		$array_ip[] = $row['ip_address'];
		}
	return $array_ip;
	}

//загружаем данные в таблицу БД
private function loadIpInDB(array $array_ip, array $array_ip_db, $code)
	{
	//получаем доп. информацию
	if((!isset($_POST['infoIpList'])) || (empty($_POST['infoIpList']))){
		$information = '';
		} else {
		$information = ExactilyUserData::takeStringAll($_POST['infoIpList']);	
		}
	$GeoIP = new GeoIP;
	foreach ($array_ip as $ip) {
		//если IP-адреса из выбранного списка есть, проверяем наличие IP-адреса в полученном из БД списке 
		if(!in_array($ip, $array_ip_db)){
			$query_ip = self::getConnectionDB()->connectionDB()->query("SELECT `code` FROM `geoip_data` WHERE start<='".intval($ip)."' AND end>='".intval($ip)."'");
			$row_ip = $query_ip->fetch(PDO::FETCH_ASSOC);
			if($row_ip['code']){
				$code = $row_ip['code'];
				$country = $GeoIP::$codeCountry[$row_ip['code']];
				} else {
				$code = 'NO';
				$country = 'Страна не найдена';	
				}
			$type = (int) $_POST['typeIpList'];
			$query = self::getConnectionDB()->connectionDB()->prepare("INSERT INTO `black_ip_list` 
																	 			  (`type`, 
																	 			   `ip_address`, 
																	 			   `country_code`, 
																	 			   `country`, 
																	 			   `information`,
																	 			   `date_time_create`)
																	   			  VALUES 
																	   			  ('".$type."', 
																	   			   :ip, 
																	   			   '".$code."',
																	   			   :country,
																	   			   :information,
																	   			   '".date('Y-m-d H:i:s', time())."')");
			$query->execute(array(':ip' => $ip, ':country' => $country, ':information' => $information));	
			}
		}
	}

//загрузка файла
private function uploadFile()
	{
	//директория для загруженных файлов
	$dirUpload = $_SERVER['DOCUMENT_ROOT']."/{$this->directory}/tmp/";
	if(empty($_FILES['loadFiles']['tmp_name'])){
		exit();
		}
	//расположение и имя загруженного файла
	$uploadFile = $dirUpload.basename($_FILES['loadFiles']['name']);
	if(copy($_FILES['loadFiles']['tmp_name'], $uploadFile)){
		chdir($dirUpload);
		if (!opendir($dirUpload)){
			echo MessageErrors::userMessageError(MessageErrors::ERROR_FILE, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
			}
		$fileName = $dirUpload.$_FILES['loadFiles']['name'];
		if(!fopen($fileName, "r")){
			echo MessageErrors::userMessageError(MessageErrors::ERROR_FILE, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
			}
		}
	return $this->uploadFileName = $fileName;
	}

//удаление загруженного файла
private function deleteUploadFile()
	{
	if(!unlink($this->uploadFileName)){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_FILE, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//отключаемся от БД
function closeConnectionDB()
	{

	}
}
?>