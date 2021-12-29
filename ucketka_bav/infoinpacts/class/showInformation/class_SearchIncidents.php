<?php

							/*------------------------------------------------------*/
							/*  класс вывода формы поиска полностью заполненных 		*/
							/*	компьютерных атак и их поиск по заданным параметрам	*/
							/* 	 								 v.0.13 15.08.2014  */
							/*------------------------------------------------------*/

class SearchIncidents
{
//корневая директория сайта
private $directory;
//конструктор формы ввод данных для поиска информации по компьютерным атакам
public function __construct() 
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directory = $array_directory[1];	
	?>

<!-- проверка заполнения формы -->			
	<script type="text/javascript" >
	//функция проверяющая ввод даты
	function validateForm()
		{
		var dateStart = document.allForms.dateStart.value;
		var dateEnd = document.allForms.dateEnd.value;
		if(dateStart.length == 0 || dateEnd.length == 0){
			document.getElementById('messageDate').innerHTML = "необходимо выбрать дату";
			return false;
			}
		//проверяем чтобы начальная дата не была больше конечно
		var dStart = new Date(dateStart);
		var dEnd = new Date(dateEnd);		
		if(dStart > dEnd){
			document.getElementById('messageDate').innerHTML = "начальная дата больше конечной";
			return false;
			}
		}
	</script>	
	
	<div style="width: 960px; height: 100px;">	
	<form name="allForms" method="GET" onsubmit="return validateForm()" action="">
		<div style="width: 730px; height: 130px; float: left; display: inline-block; margin-left: 10px;">
<!-- начальная дата и время -->
			<span style="position: relative; top: 15px; left: 10px; font-size: 14px; font-family: 'Times New Roman', serif;">с</span>
			<div style="position: relative; top: -7px; left: 40px; width: 430px;">
				<input type="date" name="dateStart" style="width: 140px; height: 20px;"><input type="time" name="timeStart" value="00:00" style="width: 70px; height: 21px;"> 
			</div>
<!-- конечная дата и время -->
			<span style="position: relative; top: 7px; left: 10px; font-size: 14px; font-family: 'Times New Roman', serif;">по</span>
			<div style="position: relative; top: -15px; left: 40px; width: 550px;">
				<input type="date" name="dateEnd" style="width: 140px; height: 20px;"><input type="time" name="timeEnd" value="23:59" style="width: 70px; height: 21px;">
			</div>
<!-- выпадающий список стран -->
			<div style="position: relative; top: 0px; left: 10px; width: 170px;">
			<select name="listCountry" style="width: 170px; height: 23px;">
				<option value="">страна источник</option>
				<?php 
 				//объект БД GeoIP
				$GeoIP = new GeoIP();
				foreach($GeoIP::$codeCountry as $key=>$value){
					echo "<option value='$key'>$value</option>";
					}
				?>
			</select>				
			</div>
<!-- IP-адрес источника -->
			<span style="position: relative; top: -93px; left: 280px; font-size: 14px; font-family: 'Times New Roman', serif;">IP-src</span>	
			<div style="position: relative; top: -117px; left: 330px; width: 140px;">
				<input type="text" name="srcIP" style="width: 130px; height: 17px;"  placeholder="IP-адрес источника" title="введите IP-адрес источника компьютерной атаки">
			</div>
<!-- IP-адрес назначения -->
			<span style="position: relative; top: -100px; left: 280px; font-size: 14px; font-family: 'Times New Roman', serif;">IP-dst</span>	
			<div style="position: relative; top: -123px; left: 330px; width: 270px;">
				<input type="text" name="dstIP" style="width: 130px; height: 17px;" placeholder="IP-адрес назначения" title="введите IP-адрес назначения компьютерной атаки">
				<?php ListBox::listDomainName() ?>
			</div>
<!-- номер письма в ЦИБ -->
			<span style="position: relative; top: -178px; left: 510px; font-size: 14px; font-family: 'Times New Roman', serif;">номер письма</span>	
			<div style="position: relative; top: -200px; left: 610px; width: 120px;">
				<input type="text" name="numMail" style="width: 110px; height: 17px;" placeholder="№ письма" title="номер письма в 18 Центр ФСБ России">
			</div>
<!-- номер сигнатуры -->
			<div style="position: relative; top: -188px; left: 610px; width: 50px;">
				<input type="text" name="numSid" style="width: 110px; height: 17px;" placeholder="№ сигнатуры" title="номер сигнатуры">
			</div>
<!-- номер воздействия -->
<!--			<div style="position: relative; top: -176px; left: 610px; width: 120px;">
				<input type="text" name="numImpact" style="width: 110px; height: 17px;" placeholder="№ воздействия" title="номер компьютерного воздействия">
			</div>	-->
		</div>		
		<div style="position: relative; top: 0px; width: 190px; height: 130px; display: inline-block; margin-left: 10px;">
<!-- выпадающий список типов КА -->	
			<div style="position: relative; top: 10px; left: 20px; width: 170px;">
				<?php ListBox::listTypeKA(); ?>			
			</div>
<!-- решение аналитика -->	
			<div style="position: relative; top: 25px; left: 20px; width: 170px;">
				<select name="answerAnalyst" style="width: 170px; height: 23px;">
					<option value="">решение аналитика</option>
					<option value="no">ложное срабатывание</option>
					<option value="yes">компьютерная атака</option>
				</select>			
			</div>
<!-- количество выводимых на страницу строк -->
			<div style="position: relative; top: 40px; left: 20px; width: 50px;">
				<select name="stringCount" style="width: 50px; height: 23px;">
					<option value='20'>20</option>
					<option value='40'>40</option>
					<option value='60'>60</option>
					<option value='80'>80</option>
					<option value='100'>100</option>
				</select>
			</div>
<!-- кнопка "поиск" -->
			<div style="position: relative; top: 17px; left: 80px; width: 110px;">
				<input type="submit" name="search" value="поиск" style="width: 110px; height: 23px;">
			</div>
		</div>
<!-- информационное сообщение -->
		<span style="font-size: 16px; font-family: Verdana, serif; position: absolute; top: 150px; left: 410px; color: #000;" id="messageDate"></span>
	</form>
	</div>	
	<?php
	}

//поиск информации о компьютерных воздействиях в БД
public function searchIncidentsBD($DBO, $dateTimeStart, $dateTimeEnd) 
	{
	$tableSid = $IP_SRC = $IP_DST = $TYPE_KA = $COUNTRY_CODE = $ANSWER_ANALYST = $NUM_SID = $NUM_MAIL_STRING = "";
	//объект БД GeoIP
	$GeoIP = new GeoIP();

	//объект для чтения файла setup_site.xml
	$ReadXMLSetup = new ReadXMLSetup;

	//чтение бинарной БД со списками IP-адресов
	$ReadBinaryDBBlackList = new ReadBinaryDBBlackList;

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

	//тип КА (НЕОБЯЗАТЕЛЬНЫЙ ПАРАМЕТР)
	if(!empty($_GET['typeKA'])){
		$TYPE_KA = "AND `type_attack`='".ExactilyUserData::takeIntager($_GET['typeKA'])."'";
		}	
		
	//решение аналитика (НЕОБЯЗАТЕЛЬНЫЙ ПАРАМЕТР)
	// empty воспринимает 0 как пустое значение
	if(!empty($_GET['answerAnalyst'])){
		switch($_GET['answerAnalyst']){
			//если ложное срабатывание то 2
			case "no":
			$ANSWER_ANALYST = "AND `true_false`='2'";
			break;
			//если компьютерная атака то 1
			case "yes":
			$ANSWER_ANALYST = "AND `true_false`='1'";
			break;
			}
		}
	//номер письма (НЕОБЯЗАТЕЛЬНЫЙ ПАРАМЕТР)
	if(!empty($_GET['numMail'])){
		$NUM_MAIL_STRING = "AND `number_mail_in_CIB` LIKE ".$DBO->connectionDB()->quote("%".ExactilyUserData::takeStringAll($_GET['numMail'])."%");
		}

	//номер сигнатуры (НЕОБЯЗАТЕЛЬНЫЙ ПАРАМЕТР)
	if(!empty($_GET['numSid'])){
		$NUM_SID = "AND `sid`='".intval($_GET['numSid'])."'";
		$tableSid = "LEFT JOIN `incident_number_signature_tables` t3 ON t0.id = t3.id";
		}

	//страна источника компьютерной атаки (НЕОБЯЗАТЕЛЬНЫЙ ПАРАМЕТР)
	if(!empty($_GET['listCountry'])){
		if(strlen($_GET['listCountry']) == 2){
			$COUNTRY_CODE = "AND `country`='".ExactilyUserData::takeStringAll($_GET['listCountry'])."'";
			}
		}

			//ДЛЯ постраничных ССЫЛОК	
	//количество выводимых на страницу элементов	(строк)

	$string_limit = (int) $_GET['stringCount'];

	//текущее смещение
	$start = 0;
	if(isset($_GET["page"])) { 
		$start = $_GET["page"]; 
		}

	try{
		//основной запрос
		$query = $DBO->connectionDB()->query("SELECT t1.id, `date_time_incident_start`, `date_time_incident_end`, `ip_src`, `country`, `ip_dst`, `true_false`, `number_mail_in_CIB`
 											  FROM `incident_chief_tables` t0 LEFT JOIN `incident_additional_tables` t1 
 											  ON t0.id = t1.id LEFT JOIN `incident_analyst_tables` t2 ON t2.id = t0.id $tableSid WHERE `date_time_incident_start` BETWEEN STR_TO_DATE('$dateTimeStart', '%Y-%m-%d %H:%i:%s') 
				 							  AND STR_TO_DATE('$dateTimeEnd', '%Y-%m-%d %H:%i:%s') AND (`true_false`!='3') $IP_SRC $IP_DST $TYPE_KA $COUNTRY_CODE $ANSWER_ANALYST $NUM_SID $NUM_MAIL_STRING 
				 							  ORDER BY `date_time_incident_start` ASC LIMIT $start, ".$string_limit);

		//запрос для подсчета количества найденных строк где COUNT_ALL общее число найденных строк, COUNT_id количество инцидентов	
		$query_count = $DBO->connectionDB()->query("SELECT COUNT(*) AS COUNT_ALL, COUNT(DISTINCT(id)) AS COUNT_id FROM 
												   (SELECT t2.id FROM `incident_chief_tables` t0 LEFT JOIN `incident_additional_tables` t1 
 												  	ON t0.id = t1.id LEFT JOIN `incident_analyst_tables` t2 ON t2.id = t0.id $tableSid 
 												  	WHERE `date_time_incident_start` BETWEEN STR_TO_DATE('$dateTimeStart', '%Y-%m-%d %H:%i:%s') 
				 									AND STR_TO_DATE('$dateTimeEnd', '%Y-%m-%d %H:%i:%s') AND (`true_false`!='3') 
				 									$IP_SRC $IP_DST $TYPE_KA $COUNTRY_CODE $ANSWER_ANALYST $NUM_SID $NUM_MAIL_STRING ) tables");

		$num_string = $query_count->fetch(PDO::FETCH_OBJ);
		
		//для унификации (возможность использования пользователями разных групп), определяем путь до скрипта		
		$array_path = explode("/",$_SERVER['PHP_SELF']);
		$path = "";		
		for($i = 1; $i < count($array_path) - 1; $i++){
			$path .= "/".$array_path[$i];
			}

		//временный массив под IP-адреса источников		
		$array_tmp = $array_ip = array();
		while($row = $array_tmp[] = $query->fetch(PDO::FETCH_ASSOC)){
			$array_ip[$row['id']][] = $row['ip_src'];
			}

		//выводим сообщение если ничего не нашли и проверяем присутствие IP-адресов в массиве $array_ip
		if((count($array_ip) == 0) || ($num_string->COUNT_ALL == 0)){
			?>
			<br><br><br>
			<div style="text-align: center; font-size: 16px; font-family: 'Times New Roman', serif;">
				по вашему запросу ничего не найдено
			</div><br><br><br><br><br><br><br><br><br><br><br><br>
			<?php
			exit;
			}
		?><br>
<!-- для выделения строки в таблице -->
		<style>
		.tableHover tr:hover {
			background-color: #E0FFFF; }
		</style>
		<div style="border-width: 1px; border-style: solid; width: 940px; border-color: #B7DCF7;">			
	<form name="showDocx" method="POST" enctype="multipart/form-data" target="_blank" action="<?= $path.'/get_docx.php'; ?>">
		<table id="elTableSearch" border="0" width="940px" cellpadding="2" class="tableHover">
			<?php echo "<tr ".COLOR_HEADER.">"; ?>
				<th style="width: 50px; font-size: 12px; font-family: 'Times New Roman', serif;">№</th>
				<th style="width: 30px; font-size: 12px; font-family: 'Times New Roman', serif;">
					<input type="image" name="showDocumentWord" src="/<?= $this->directory ?>/img/doc_word.png" title="получить документ Word">
					<input type="checkbox" name="majorCheckBox" onclick="checkAll()" title="отметить всё">						
				</th>
				<th onclick="sortColumnSearch('dateStartSearch')" style="width: 95px; font-size: 12px; font-family: 'Times New Roman', serif;">
					начальное<br>дата/время<br>
					<img id="dateStartSearch" src="/<?= $this->directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="сортировать по начальному времени"/>
				</th>
				<th style="width: 95px; font-size: 12px; font-family: 'Times New Roman', serif;">
                    конечное<br>дата/время<br>
				</th>
				<th style="width: 115px; font-size: 12px; font-family: 'Times New Roman', serif;">
					IP-адрес(-а) источник(-ов)
				</th>
				<th onclick="sortColumnSearch('countrySearch')" style="width: 170px; font-size: 12px; font-family: 'Times New Roman', serif;">
					страна<br>
					<img id="countrySearch" src="/<?= $this->directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по стране источнику"/>
				</th>
				<th onclick="sortColumnSearch('dstIpSearch')" style="width: 125px; font-size: 12px; font-family: 'Times New Roman', serif;">
					IP-адрес назначения<br>
					<img id="dstIpSearch" src="/<?= $this->directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по IP-адресу назначения"/>
				</th>
				<th onclick="sortColumnSearch('solutionSearch')" style="width: 105px; font-size: 12px; font-family: 'Times New Roman', serif;">
					решение<br>аналитика<br>
					<img id="solutionSearch" src="/<?= $this->directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по принятому аналитиком решению"/>				
				</th>
				<th style="width: 120px; font-size: 12px; font-family: 'Times New Roman', serif;">№ письма в<br>18 Центр ФСБ России</th>
			</tr>
		<?php
		for($a = $i = 0; $a < count($array_tmp) - 1; $a++){
			if(($i != $array_tmp[$a]['id']) && array_key_exists($array_tmp[$a]['id'], $array_ip)){
				echo "<tr bgcolor=".color().">";
				?>
<!-- № воздействия -->
					<td style="width: 50px; text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;"><?php echo $array_tmp[$a]['id']; ?>.</td>
					<td style="width: 30px; text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<?php 
					if($array_tmp[$a]['true_false'] == "1"){
						echo "<input type='checkbox' name=showMailDocx[] value='".$array_tmp[$a]['id']."'>";
						} 
					?>
						<input type="image" name="showAllInformationImg" onclick="return shadowForm(this)" value="<?= $array_tmp[$a]['id'] ?>" src="/<?= $this->directory ?>/img/eye.png" style="cursor: pointer;" title="просмотреть полную информацию о компьютерном воздействии">
					</td>
<!-- дата/время начала -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;"><?php echo $array_tmp[$a]['date_time_incident_start']; ?></td>
<!-- Ф.И.О. -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;"><?php echo $array_tmp[$a]['date_time_incident_end']; ?></td>
<!-- IP-адреса источников -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<?php
					foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
						//устанавливаем IP-адрес для поиска в бинарной БД
						$ReadBinaryDBBlackList->setIp(long2ip($ip_src));
						//вывод найденной информации
						$ReadBinaryDBBlackList->showInfoSearchIp();
						} 
					?>
					</td>
<!-- страна -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<?php
					//для geoip
					foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
						echo $GeoIP->countryIP($DBO, $ip_src)." ";
						?> 
						<img src= <?php echo "/{$this->directory}/img/flags/".$GeoIP->flags(); ?> /><br>
						<?php
						}
					?>
					</td>
<!-- IP-адрес назначения (доменное имя) -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<?php
					echo long2ip($array_tmp[$a]['ip_dst'])."<br>"; 
					echo $ReadXMLSetup->obtainDomainName(long2ip($array_tmp[$a]['ip_dst']));
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
<!-- № письма в ЦИБ -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<?php
					if($array_tmp[$a]['number_mail_in_CIB'] != null){
						echo "№149/2/1/".$array_tmp[$a]['number_mail_in_CIB'];
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
<!-- скрытая форма -->
		<form name="showAllInfo" method="POST" enctype="multipart/form-data" target="_blank" action="<?= $path.'/showAllInformationForIncidents.php'; ?>">
			<input type="hidden" name="showAllInformation">
		</form>			
		</div><br>
		<script>
		//функция скрытой формы
		function shadowForm(elem){
			document.showAllInfo.showAllInformation.value = elem.value;
			document.showAllInfo.submit();
		return false;
		}

		//функция "выбрать все"
		function checkAll(){
			var itemForms = document.showDocx.elements;
			var flags = false;
			for(var i = 0; i < itemForms.length; i++){
				//ищем главный checkbox
				if(itemForms[i].name == 'majorCheckBox'){
					if(itemForms[i].checked == true){
					flags = true;
					itemForms[i].value = 1;
					} else {
					flags = false;
					itemForms[i].value = 0;					
					}
				}
				//изменяем остальные checkbox
				if(itemForms[i].type == 'checkbox' && flags == true && itemForms[i].name != 'majorCheckBox'){
				itemForms[i].checked = flags;
				} 
				if(itemForms[i].type == 'checkbox' && flags == false && itemForms[i].name != 'majorCheckBox') {
				itemForms[i].checked = flags;
				}
			}
		}
		
		var flag = true;
		//сортировка значений
		function sortColumnSearch(elName){
			//получаем числовой индекс по имени поля
			var id;
			switch(elName){
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
		
		//создаем объект постраничных ссылок
		// 1 число - кол-во элементов выводимых на страницу
		// 2 число - кол-во ссылок
		// 3 число - общее число найденных строк
		$PageOutput = new PageOutput($string_limit, 3, $num_string->COUNT_ALL);

		//вывод постраничных ссылок
		?>
<!-- выводим количество найденных элементов -->
		<div style="text-align: center; font-size: 14px; font-family: Palatino, 'Times New Roman', serif;">
		<?php 
		echo "всего компьютерных воздействий найдено - <span style='font-weight: bold;'>".$num_string->COUNT_id.'</span>'; 
		if(!empty($_GET['listCountry'])){
			if(strlen($_GET['listCountry']) == 2){
				echo '<br>IP-адресов принадлежащих стране '.$GeoIP->nameAndFlagsOfCode($_GET['listCountry']).' - <span style="font-weight: bold;">'
				.$num_string->COUNT_ALL.'</span>';
				}
			}
		?>
		</div>
			<style>
/*класс для ссылки на страницы которой мы находимся в данный момен */
			.page_active {
				font-weight: 500;
				background: #BF8851;
				color: #FFF;
				padding: 5px 10px;
				font-size: 14px; font-family: Arial, Helvetica, sans-serif;
				text-decoration: none;
				vertical-align: middle;
				-webkit-border-radius: 5px;
				-moz-border-radius: 5px;
				border-radius: 5px;	
				line-height: 30px; }			
/* класс для ссылок */			
			.pagination a {
				background: #FFEA6C;
				color: #000;
				padding: 5px 10px;
				font-size: 14px; font-family: Arial, Helvetica, sans-serif;
				text-decoration: none;
				vertical-align: middle;
				-webkit-border-radius: 5px;
				-moz-border-radius: 5px;
				border-radius: 5px;
				line-height: 30px; }
			.pagination a:hover {
				background: #BF8851;
				color: #000;
				padding: 5px 10px;
				font-size: 14px; font-family: Arial, Helvetica, sans-serif;
				text-decoration: none;
				vertical-align: middle;
				-webkit-border-radius: 5px;
				-moz-border-radius: 5px;
					border-radius: 5px; }
			.pagination b {
				font-weight: 500;
				background: #ff0033;<?php echo "<tr ".COLOR_HEADER.">"; ?>
				color: #000;
				padding: 5px 10px;
				font-size: 14px; font-family: Arial, Helvetica, sans-serif;
				text-decoration: none;
				vertical-align: middle;
				-webkit-border-radius: 5px;
				-moz-border-radius: 5px;
				border-radius: 5px;	
				line-height: 30px; }
			</style>
<!-- выводим постраничные ссылки -->
		<div class="pagination" style="text-align: center; font-size: 14px; font-family: Palatino, 'Times New Roman', serif;"> 		
		<?php 
		echo $PageOutput->giveLinks('page', $start); 
		?>
		</div><br>
		<?php
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}		
	}
}
?>