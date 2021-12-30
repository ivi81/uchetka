<?php

							/*------------------------------------------------------*/
							/*  класс поиска и редактирования компьютерных атакам	*/
							/*	 							(для Администратора)	*/
							/*			 						  v.0.1 05.06.2014  */
							/*------------------------------------------------------*/

class SearchIncidentsAdmin
{
//корневая директория сайта
private $directory;
//объект для подключения к БД
private static $DBO;
private $ReadXML, $GeoIP;

public function __construct() 
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directory = $array_directory[1];
	$this->ReadXML = new ReadXMLSetup;
	$this->GeoIP = new GeoIP;
	//выводим форму поиска компьютерных атак
	$this->showSearchForm();
	}
	
//получаем ресурс для доступа к БД
public static function getConnectionDB() 
	{
	if(empty(self::$DBO)){
		self::$DBO = new DBOlink;
		}
	return self::$DBO;
	}
	
//вывод формы для поиска информации по компьютерным атакам
private function showSearchForm() 
	{
	?>
	
<!-- проверка заполнения формы -->			
	<script type="text/javascript" >
	//функция проверки заполнения полей
	function validateForm(){
		var obj = {	numImpact: document.allForms.numImpact, 
					dateStart: document.allForms.dateStart,
					dateEnd: document.allForms.dateEnd,
					srcIP: document.allForms.srcIP,
					dstIP: document.allForms.dstIP };
		//убираем красные контуры на элементах форы
		for(var a in obj){
			obj[a].style['borderColor'] = '';
			}
		//проверяем наличия даты или номера компьютерного воздействия
		if(obj.numImpact.value.length == 0 && obj.dateStart.value.length == 0){
			obj.numImpact.style['borderColor'] = 'red';
			obj.dateStart.style['borderColor'] = 'red';
			obj.dateEnd.style['borderColor'] = 'red';
			document.getElementById('message').innerHTML = 'необходимо ввести дату или номер компьютерного воздействия';
			return false;
			}
		if(obj.numImpact.value.length == 0){
			return validateDateAndIP();
			} else {
			var numPattern = /^[0-9]{1,}$/;
			if(!numPattern.test(obj.numImpact.value)){
				obj.numImpact.style['borderColor'] = 'red';			
				document.getElementById('message').innerHTML = 'некорректный идентификационный номер воздействия';
				return false;
				}
			}
		}
	
	//функция проверяющая ввод даты
	function validateDateAndIP(){
		var obj = {	dateStart: document.allForms.dateStart,
					dateEnd: document.allForms.dateEnd,
					srcIP: document.allForms.srcIP,
					dstIP: document.allForms.dstIP };
		var i = b = 0;
		for(var a in obj){
			//проходим только два раза (начальное и конечное время)
			if(i == 2) break;
			if(obj[a].value.length == 0){
				document.getElementById('message').innerHTML = "необходимо выбрать дату";
				obj[a].style['borderColor'] = 'red';
				return false;
				}
			i++;
			}
		//проверяем чтобы начальная дата не была больше конечно
		var dStart = new Date(obj.dateStart.value);
		var dEnd = new Date(obj.dateEnd.value);		
		if(dStart > dEnd){
			document.getElementById('message').innerHTML = "начальная дата не может быть больше конечной";
			obj.dateStart.style['borderColor'] = 'red';
			obj.dateEnd.style['borderColor'] = 'red';
			return false;
			}
		//проверяем IP-адреса если они есть
		for(var a in obj){
			if(b >= 2 ){
				if(obj[a].value.length != 0){
					if(checkIpAddress(obj[a]) == false){
						return false;
						}					
					}
				}
			b++;
			}
		return true;
		}
	
	//проверка IP-адреса
	function checkIpAddress(ip){
		var ipPattern = /^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/;
		if(!ipPattern.test(ip.value)){
			ip.style['borderColor'] = 'red';			
			document.getElementById('message').innerHTML = 'некорректный IP-адрес';
			return false;
			}
		}
	</script>	
	
	<div style="width: 735px; height: 100px;">	
	<form name="allForms" method="GET" onsubmit="return validateForm()" action="">
<!-- начальная дата и время -->
		<span style="position: relative; top: 15px; left: 10px; font-size: 14px; font-family: 'Times New Roman', serif;">с</span>
		<div style="position: relative; top: -7px; left: 35px; width: 430px;">
			<input type="date" name="dateStart" style="width: 140px; height: 20px;"><input type="time" name="timeStart" value="00:00" style="width: 70px; height: 21px;"> 
		</div>
<!-- конечная дата и время -->
		<span style="position: relative; top: 7px; left: 10px; font-size: 14px; font-family: 'Times New Roman', serif;">по</span>
		<div style="position: relative; top: -15px; left: 35px; width: 550px;">
			<input type="date" name="dateEnd" style="width: 140px; height: 20px;"><input type="time" name="timeEnd" value="23:59" style="width: 70px; height: 21px;">
		</div>
<!-- IP-адрес источника -->
		<span style="position: relative; top: -73px; left: 270px; font-size: 14px; font-family: 'Times New Roman', serif;">IP-src</span>	
		<div style="position: relative; top: -95px; left: 310px; width: 140px;">
			<input type="text" name="srcIP" style="width: 130px; height: 17px;"  placeholder="IP-адрес источника" title="введите IP-адрес источника компьютерной атаки">
		</div>
<!-- IP-адрес назначения -->
		<span style="position: relative; top: -80px; left: 270px; font-size: 14px; font-family: 'Times New Roman', serif;">IP-dst</span>	
		<div style="position: relative; top: -100px; left: 310px; width: 270px;">
			<input type="text" name="dstIP" style="width: 130px; height: 17px;" placeholder="IP-адрес назначения" title="введите IP-адрес назначения компьютерной атаки"><?php ListBox::listDomainName() ?>
		</div>
<!-- номер письма в ЦИБ -->
		<div style="position: relative; top: -160px; left: 455px; width: 120px;">
			<input type="text" name="numMail" style="width: 85px; height: 17px;" placeholder="№ письма" title="номер письма в 18 Центр ФСБ России">
		</div>
<!-- номер воздействия -->
		<div style="position: relative; top: -183px; left: 555px; width: 120px;">
			<input type="text" name="numImpact" style="width: 100px; height: 17px;" placeholder="№ воздействия" title="номер компьютерного воздействия">
		</div>		
<!-- количество выводимых на страницу строк -->
		<div style="position: relative; top: -206px; left: 670px; width: 50px;">
			<select name="stringCount" style="width: 50px; height: 23px;">
				<option value='20'>20</option>
				<option value='40'>40</option>
				<option value='60'>60</option>
				<option value='80'>80</option>
				<option value='100'>100</option>
			</select>
		</div>
<!-- кнопка "поиск" -->
		<div style="position: relative; top: -192px; left: 630px; width: 90px;">
			<input type="submit" name="search" value="поиск" style="width: 90px; height: 23px;">
			<input type="hidden" name="id" value="1">
		</div>
<!-- информационное сообщение -->
		<div style="text-align:center; position: absolute; top: 78px; left: 0px; width: 735px; height: 10px;">
			<span style="font-size: 12px;	font-family: Verdana, serif; color: #CD0000;" id="message"></span>
		</div>
	</form>
	</div>	
	<?php
	}

//удаление выбранного воздействия	
private function checkDeleteImpact()
	{
	try{
		$query = self::getConnectionDB()->connectionDB()->query("DELETE `incident_chief_tables`, `incident_additional_tables`, 
																`incident_number_signature_tables` FROM `incident_chief_tables`, 
																`incident_additional_tables`, `incident_number_signature_tables` 
																 WHERE incident_additional_tables.id = incident_chief_tables.id 
																 AND incident_number_signature_tables.id = incident_chief_tables.id 
																 AND incident_chief_tables.id='".intval($_POST['deleteImpact'])."'");
		$query = self::getConnectionDB()->connectionDB()->query("DELETE `incident_analyst_tables` FROM `incident_analyst_tables`
																 WHERE incident_analyst_tables.id='".intval($_POST['deleteImpact'])."'");																		   
		//вывод сообщения об успешном выполнении и редирект на главную страницу
		ShowMessage::messageOkRedirect("запись удалена", 50);
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//подготовка данных для поиска
public function setSearchData() 
	{
	if(isset($_GET['numImpact']) && !empty($_GET['numImpact'])){
		return array('numImpact' => ((int) $_GET['numImpact']));
		}
	if(isset($_GET['dateStart']) && !empty($_GET['dateStart']) && isset($_GET['dateEnd']) && !empty($_GET['dateEnd'])
		&& !empty($_GET['timeStart']) && !empty($_GET['timeEnd'])){
		$dateTimeStart = ExactilyUserData::takeDate($_GET['dateStart'])."  ".ExactilyUserData::takeTime($_GET['timeStart']);
		$dateTimeEnd = ExactilyUserData::takeDate($_GET['dateEnd'])."  ".ExactilyUserData::takeTime($_GET['timeEnd']);
		return array('dateStart' => $dateTimeStart,
						 'dateEnd' => $dateTimeEnd);
		}
	}

//поиск информации в БД	по номеру компьютерного воздействия
private function searchIncidentsForNum($array) 
	{
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT t1.id, `date_time_incident_start`, `date_time_incident_end`, `ip_src`, `ip_dst`, `true_false`
													  			 FROM `incident_chief_tables` t0 LEFT JOIN `incident_additional_tables` t1 
													  			 ON t0.id = t1.id LEFT JOIN `incident_analyst_tables` t2 ON t2.id = t0.id 
													  			 WHERE t1.id='".$array['numImpact']."' ORDER BY `date_time_incident_start` ASC");
		//выводим найденную информацию		
		$this->showTable($query);		
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
	
//поиск информации в БД по дате и IP-адресам
private function searchIncidentForDate($array) {
	$IP_SRC = $IP_DST = $NUM_MAIL_STRING = "";
	//IP-адрес ИСТОЧНИКА (НЕОБЯЗАТЕЛЬНЫЙ ПАРАМЕТР)
	if(!empty($_GET['srcIP'])){
		foreach(ExactilyUserData::takeIP($_GET['srcIP']) as $ip){
			$IP_SRC = "AND `ip_src`='".ip2long($ip)."'";
			} 
		}			
	//IP-адрес НАЗНАЧЕНИЯ (НЕОБЯЗАТЕЛЬНЫЙ ПАРАМЕТР)
	if(!empty($_GET['dstIPName']) || !empty($_GET['dstIP'])){
		//IP-адрес назначения (доменное имя или IP-адрес)
		if(count(ExactilyUserData::takeIP($_GET['dstIPName']) ) != 0){
			foreach(ExactilyUserData::takeIP($_GET['dstIPName']) as $ip){
				$IP_DST = " AND `ip_dst`='".ip2long($ip)."'";
				}
			} else {
			foreach(ExactilyUserData::takeIP($_GET['dstIP']) as $ip){
				$IP_DST = "AND `ip_dst`='".ip2long($ip)."'";
				} 
			}
		}
	//номер письма (НЕОБЯЗАТЕЛЬНЫЙ ПАРАМЕТР)
	if(!empty($_GET['numMail'])){
		$NUM_MAIL_STRING = "AND `number_mail_in_CIB` LIKE ".self::getConnectionDB()->connectionDB()->quote("%".ExactilyUserData::takeStringAll($_GET['numMail'])."%");
		}
	try{
		//основной запрос
		$query = self::getConnectionDB()->connectionDB()->query("SELECT t1.id, `date_time_incident_start`, `date_time_incident_end`, `ip_src`, `ip_dst`, `true_false`
 														  		 FROM `incident_chief_tables` t0 LEFT JOIN `incident_additional_tables` t1 
 														  		 ON t0.id = t1.id LEFT JOIN `incident_analyst_tables` t2 ON t2.id = t0.id 
 														  		 WHERE `date_time_incident_start` BETWEEN STR_TO_DATE('".$array['dateStart']."', '%Y-%m-%d %H:%i:%s') 
				 										  		 AND STR_TO_DATE('".$array['dateEnd']."', '%Y-%m-%d %H:%i:%s') $IP_SRC $IP_DST $NUM_MAIL_STRING ORDER BY t1.id ASC");
		//выводим найденную информацию
		$this->showTable($query);
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}			
	}

//вывод таблицы с найденными компьютерными воздействиями
private function showTable($data) 
	{
	//временный массив под IP-адреса источников		
	$array_tmp = $array_ip = array();
	while($row = $array_tmp[] = $data->fetch(PDO::FETCH_ASSOC)){
		$array_ip[$row['id']][] = $row['ip_src'];
		}	
	//выводим сообщение если ничего не нашли и проверяем присутствие IP-адресов в массиве $array_ip
	if(count($array_ip) == 0){
		?>
		<br><br><br>
		<div style="text-align: center; font-size: 16px; font-family: 'Times New Roman', serif;">
			по вашему запросу ничего не найдено
		</div><br><br><br><br><br><br><br><br><br><br><br><br>
		<?php
		exit;
		}
	?>
	<br>
<!-- для выделения строки в таблице -->
		<style>
		.tableHover tr:hover {
			background-color: #E0FFFF; }
		</style>
		
		<script type="text/javascript" >
		//подтверждение удаления компьютерного воздействия
		function confirmDelete(id){
			var testOk = confirm('Подтвердите удаление компьютерного воздействия №' + id);
			return (testOk) ? true : false;
			}
			
		//изменение изображения	
		function chageImg(elem, flag){
			switch(flag){
				//удаление пользователя
				case '1':
					elem.src = '/<?= $this->directory ?>/img/eye.png';
				break;
				//удаление пользователя			
				case '2':
					elem.src = '/<?= $this->directory ?>/img/eye_1.png';
				break;
				//сохранение информации
				case '3':
					elem.src = '/<?= $this->directory ?>/img/delete.png';
				break;
				//сохранение информации
				case '4':
					elem.src = '/<?= $this->directory ?>/img/delete_1.png';
				break;
				}
			}
		</script>

		<div style="border-width: 1px; border-style: solid; width: 730px; border-color: #B7DCF7;">			
		<table id="elTableSearch" border="0" width="730px" cellpadding="2" class="tableHover">
			<?php echo "<tr ".COLOR_HEADER.">"; ?>
				<th style="width:  30px; font-size: 12px; font-family: 'Times New Roman', serif;"></th>
				<th onclick="sortColumnSearch('numSearch')" style="width:  50px; font-size: 12px; font-family: 'Times New Roman', serif;">
					№<br>
					<img id="numSearch" src="/<?= $this->directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="сортировать по номеру компьютерного воздействия"/>
				</th>
				<th onclick="sortColumnSearch('dateStartSearch')" style="width: 100px; font-size: 12px; font-family: 'Times New Roman', serif;">
					начальное<br>дата/время<br>
					<img id="dateStartSearch" src="/<?= $this->directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="сортировать по начальному времени"/>
				</th>
				<th style="width: 100px; font-size: 12px; font-family: 'Times New Roman', serif;">
					конечное<br>дата/время
				</th>
				<th style="width: 90px; font-size: 12px; font-family: 'Times New Roman', serif;">
					IP-адрес(-а) источник(-ов)
					</th>
				<th onclick="sortColumnSearch('countrySearch')" style="width: 170px; font-size: 12px; font-family: 'Times New Roman', serif;">
					страна<br>
					<img id="countrySearch" src="/<?= $this->directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по стране источнику"/>
				</th>
				<th onclick="sortColumnSearch('dstIpSearch')" style="width: 100px; font-size: 12px; font-family: 'Times New Roman', serif;">
					IP-адрес назначения<br>
					<img id="dstIpSearch" src="/<?= $this->directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по IP-адресу назначения"/>
				</th>
				<th onclick="sortColumnSearch('solutionSearch')" style="width: 90px; font-size: 12px; font-family: 'Times New Roman', serif;">
					решение<br>аналитика<br>
					<img id="solutionSearch" src="/<?= $this->directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по принятому аналитиком решению"/>				
				</th>
			</tr>
		<?php
		for($a = $i = 0; $a < count($array_tmp) - 1; $a++){
			if(($i != $array_tmp[$a]['id']) && array_key_exists($array_tmp[$a]['id'], $array_ip)){
				echo "<tr bgcolor=".color().">";
				$id = $array_tmp[$a]['id'];
				?>
<!-- № воздействия -->
					<td style="width: 50px; text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
<!-- просмотр и изменение информации -->
					<form name="editAllInfo" method="POST" enctype="multipart/form-data" target="_blank" action="/<?= $this->directory ?>/admin/process/edit_incidents.php">
						<input type="image" name="editAllInformation" onmouseout="chageImg(this,'1')" onmouseover="chageImg(this,'2')" src="/<?= $this->directory ?>/img/eye.png" title="просмотр и изменение информации о компьютерном воздействии">
						<input type="hidden" name="editAllInformation" value="<?= $id ?>">
					</form>
<!-- удаление компьютерного воздействия -->
					<form method="POST" enctype="multipart/form-data" onsubmit="return confirmDelete('<?= $id ?>')" action="">
						<input type="image" name="deleteImpact" onmouseout="chageImg(this,'3')" onmouseover="chageImg(this,'4')" src="/<?= $this->directory ?>/img/delete.png" title="удаление информации о компьютерном воздействии">
						<input type="hidden" name="deleteImpact" value="<?= $id ?>">
					</form>											
					</td>
					<td style="width: 30px; text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<?php echo $array_tmp[$a]['id']; ?>.</td>
<!-- дата/время начала -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;"><?php echo $array_tmp[$a]['date_time_incident_start']; ?></td>
<!-- дата/время конца -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;"><?php echo $array_tmp[$a]['date_time_incident_end']; ?></td>
<!-- IP-адреса источников -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<?php 
					foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
						echo long2ip($ip_src)."<br>";
						} 
					?>
					</td>
<!-- страна -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<?php
					//для geoip
					foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
						echo $this->GeoIP->countryIP(self::getConnectionDB(), $ip_src)." ";
						?> 
						<img src= <?php echo "/{$this->directory}/img/flags/".$this->GeoIP->flags(); ?> /><br>
						<?php
						}
					?>
					</td>
<!-- IP-адрес назначения (доменное имя) -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<?php
					echo long2ip($array_tmp[$a]['ip_dst'])."<br>"; 
					echo $this->ReadXML->obtainDomainName(long2ip($array_tmp[$a]['ip_dst']));
					?>
					</td>
<!-- решение аналитика -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<?php
					if($array_tmp[$a]['true_false'] == "1"){
						echo "<span style='font-weight: bold;'>компьютерная атака</span>";
						} 
					elseif($array_tmp[$a]['true_false'] == "2"){
						echo "ложное срабатывание";
						} 
					elseif($array_tmp[$a]['true_false'] == "3"){
						echo "отсутствует сетевой трафик";
						} 
					?>
					</td>
				</tr>
				<?php
				}
			$i = $array_tmp[$a]['id'];
			}
		?>
		</table>
		</form>
		</div><br>
	<script>
	var flag = true;
	
	//сортировка значений
	function sortColumnSearch(elName)
		{
		//получаем числовой индекс по имени поля
		var id;
		switch(elName){
			case 'numSearch':
				id = 1;
			break;
			case 'dateStartSearch':
				id = 2;
			break;
			case 'countrySearch':
				id = 5;
			break;
			case 'dstIpSearch':
				id = 6;
			break;
			case 'solutionSearch':
				id = 7;
			break;
			} 
		var elSort = document.getElementById(elName);
		//складываем путь к картинке находящийся в src в массив
		var arrayString = elSort.src.split('/');
		//изменяем изображение (направление стрелки)
		if(flag){
			elSort.src = '/' + arrayString[3] + '/img/buttonblue_up.png';
			} else {
			elSort.src = '/' + arrayString[3] + '/img/buttonblue_down.png';
			}
		//сортируем таблицу
		var elTable = document.getElementById('elTableSearch');
		var arraySort = new Array();
		for(var b = 1; b < elTable.rows.length; b++){
			arraySort[b-1] = new Array();
			if(elTable.rows[b].getElementsByTagName('TD').item(id) !== null){
				//получаем содержимое выбранного столбца (текст в теге td)
				arraySort[b-1][0] = elTable.rows[b].getElementsByTagName('TD').item(id).innerHTML;
				//получаем содержимое строки (всю информацию по каждому пользователю)
				arraySort[b-1][1] = elTable.rows[b];
				}
			}
		//сортируем значения
		arraySort.sort();	
		if(!flag){	
			arraySort.reverse();
			}
		//добавляем в таблицу отсортированные значения
		for(var j = 0; j < arraySort.length; j++){
			if(arraySort[j][1] !== undefined){
				//добавляем потомка
				elTable.appendChild(arraySort[j][1]);
				//проверяем на четность
				if(j % 2 === 0){
					arraySort[j][1].setAttribute('bgcolor', '#D1E7F7');
					} else {
					arraySort[j][1].setAttribute('bgcolor', '#B7DCF7');	
					}
				}
			}
		flag = !flag;
		}	
	</script>
	<?php
	}
	
//вывод найденной информации
public function showSearchIncidents() 
	{
	//проверяем необходимость удаления выбранного компьютерного воздействия
	if(isset($_POST['deleteImpact']) && !empty($_POST['deleteImpact'])){
		$this->checkDeleteImpact();
		} else {
		//выбираем вид запроса
		if(is_array($array = $this->setSearchData())){
			if(count($array) == 1){
				//поиск только по номеру воздействия
				$this->searchIncidentsForNum($array);
				} else {
				//поиск по дате и IP-адресам
				$this->searchIncidentForDate($array);
				}
			}
		}
	}
	
}
?>