<?php

							/*----------------------------------------------*/
							/*  класс таблиц выводящих основную информацию	*/ 
							/*			на страницу index.php 				*/
							/*												*/
							/* 	 					v.0.11 14.04.2014		*/
							/*----------------------------------------------*/

class TablesIndexInformation
{
//директория сайта
public static $directory;
//объект подключения к БД
private static $DBO;

//формируем подключение к БД
private static function linkDataBase()
	{
	if(empty(self::$DBO)){
		//объект для подключения к БД
		self::$DBO = new DBOlink(); 
		}
	return self::$DBO;
	}

//компьютерные инциденты ожидающие анализа (дежурный)	
static public function showTableWaitInformation($GeoIP) 
	{
	$ReadBinaryDBBlackList = new ReadBinaryDBBlackList;
	try{
		$query = self::linkDataBase()->connectionDB()->query("SELECT t1.id, `date_time_incident_start`, `date_time_incident_end`, `ip_src`, `ip_dst`, `space_safe`, t1.login_name 
 															  FROM `incident_chief_tables` t0 LEFT JOIN `incident_additional_tables` t1 
 														  	  ON t0.id = t1.id LEFT JOIN `incident_analyst_tables` t2 ON t0.id = t2.id 
 														  	  WHERE ((t2.id) is Null) ORDER BY `date_time_incident_start` ASC");
		//временный массив под IP-адреса источников		
		$array_tmp = $array_ip = array();
		while($row = $array_tmp[] = $query->fetch(PDO::FETCH_ASSOC)){
			$array_ip[$row['id']][] = $row['ip_src'];
			} 
		for($a = $i = $j = 0; $a < count($array_tmp) - 1; $a++){
			if($i != $array_tmp[$a]['id']){
				$j++;		
				echo "<tr bgcolor=".color().">";				
				?>
				<td class="tableHeader" style="text-align: center;">
<!-- возможность исправлять добавленный инцидент -->				
				<?php
					if(isset($_SESSION['userSessid']['userLogin']) && $_SESSION['userSessid']['userLogin'] == $array_tmp[$a]['login_name']){
					?>
					<form name="editInpacts" method="POST" enctype="multipart/form-data" action="/<?= self::$directory ?>/worker/process/edit_incidents.php">					
						<input type="image" class="" name="editInpacts" src="/<?= self::$directory ?>/img/pencil.png" title="редактировать информацию">
						<input type="hidden" name="editInpacts" value="<?= $array_tmp[$a]['id'] ?>">					
					</form>					
					<?php
						}
					?>					
				</td>
<!-- начальная дата и время -->
				<td class="tableHeader" style="text-align: center;">
					<?php
					$array_date_start_tmp = explode(" ", $array_tmp[$a]['date_time_incident_start']);
					echo ConversionData::showDateConvertStr($array_date_start_tmp[0]).'<br>';
					echo $array_date_start_tmp[1];
					?>
				</td>
<!-- конечная дата и время -->
				<td class="tableHeader" style="text-align: center;">
					<?php
					$array_date_start_tmp = explode(" ", $array_tmp[$a]['date_time_incident_end']);
					echo ConversionData::showDateConvertStr($array_date_start_tmp[0]).'<br>';
					echo $array_date_start_tmp[1];
					?>
				</td>
<!-- IP-адрес источника -->
				<td class="tableHeader" style="text-align: center;">
				<?php
				foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
					//устанавливаем IP-адрес для поиска в бинарной БД
					$ReadBinaryDBBlackList->setIp(long2ip($ip_src));
					//вывод найденной информации
					$ReadBinaryDBBlackList->showInfoSearchIp(); 
					}
				echo '</td>';
				?>
<!-- GeoIP -->
				<td class="tableHeader" style="text-align: center;">
				<?php
				foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
					echo $GeoIP->countryIP(self::linkDataBase(), $ip_src)." ";
					?> 
					<img src= <?php echo "/".self::$directory."/img/flags/".$GeoIP->flags(); ?> /><br>
					<?php
					}
				echo '</td>';
				?>
<!-- IP-адрес назначения -->
				<td class="tableHeader" style="text-align: center;">
				<?php
				$ReadXMLSetup = new ReadXMLSetup(); 
				echo long2ip($array_tmp[$a]['ip_dst'])."<br>"; 
				echo $ReadXMLSetup->obtainDomainName(long2ip($array_tmp[$a]['ip_dst']));
				?>
				</td>
				</tr>
				<?php
				}
			$i = $array_tmp[$a]['id'];
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}		
	}
	
//компьютерные атаки требующие полного заполнения (разобранные компьютерные атаки) /* дежурные и руководство */
static public function showTableEmptySpaceIncident($GeoIP, $userId) 
	{
	$ReadXMLSetup = new ReadXMLSetup();
	$ReadBinaryDBBlackList = new ReadBinaryDBBlackList;
	//определение критичности данных
	$SeverityRatingData = new SeverityRatingData; 
	try{
		$query = self::linkDataBase()->connectionDB()->query("SELECT t1.id, `date_time_incident_start`, `date_time_incident_end`, `ip_src`, `ip_dst`, t1.login_name, date_time_analyst
															  FROM `incident_chief_tables` t0 JOIN `incident_additional_tables` t1
														 	  ON t0.id = t1.id LEFT JOIN `incident_analyst_tables` t2 ON t0.id = t2.id 
														  	  WHERE (t2.true_false='1' OR t2.true_false='5') AND (t1.number_mail_in_CIB is Null) ORDER BY `date_time_incident_start` ASC");
		//временный массив под IP-адреса источников		
		$array_tmp = $array_ip = array();
		while($row = $array_tmp[] = $query->fetch(PDO::FETCH_ASSOC)){
			$array_ip[$row['id']][] = $row['ip_src'];
			} 

		//вывод таблицы только при наличие в ней данных
		if(count($array_tmp) > 1){
			if($userId != 10){
				$tableWidth = '735px';
				$widthColum_1 = '40px';
				$display = '';
				$rowspan = 'rowspan="2"';
				?>
<!-- заголовок таблицы -->			
				<span style="position: relative; top: 0px; left: 80px; font-size: 12px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #990000;">
					необходимо подготовить информационное сообщение по следующим компьютерным атакам *			
				</span>
			<form name="forms" method="POST" enctype="multipart/form-data" action="/<?= self::$directory ?>/worker/process/edit_incidents.php">
				<?php 
				} else {
				$tableWidth = '725px';
				$widthColum_1 = '30px';
				$display = 'display: none';
				$rowspan = '';
				?>
<!-- название таблицы -->
				<div onclick="(function(elem) { var div = getElementById('tableNotMailKA'); if(div.nodeType == 1){ if(div.style.display == 'none'){ div.style.display = 'block'; elem.style.textDecoration = ''; } else { div.style.display = 'none'; elem.style.textDecoration = 'underline'; }}})(this)" 
					  style="position: relative; top: 0px; left: 10px; font-size: 14px; font-family: 'Times New Roman', serif; color: #000080; cursor: pointer; text-decoration: underline;">
					необходимо подготовить письма по следующим компьютерным атакам
				</div>
				<?php } ?>
				<div id="tableNotMailKA" style="border-width: 1px; border-style: solid; width: <?= $tableWidth ?>; border-color: #B7DCF7; <?= $display ?>">				
				<table id="elTableShortAdd" border="0" width="<?= $tableWidth ?>" cellpadding="2">
					<?php echo "<tr ".COLOR_HEADER.">"; ?>
						<th class="tableHeader" style="width: <?= $widthColum_1 ?>;">
						<?php if($userId != 10){ ?><input type="image" name="editor" src="/<?= self::$directory ?>/img/pencil.png" value="" title="дополнить инцидент"><?php } ?>
						</th>
						<th class="tableHeader" onclick="sortColumnShortAdd('dateStartShortAdd')" style="width: 90px;" <?= $rowspan ?>>
						начальное<br>дата/время<br>
						<img id="dateStartShortAdd" src="/<?= self::$directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="сортировать по начальному времени"/>							
						</th>
						<th class="tableHeader" style="width: 90px;" <?= $rowspan ?>>конечное<br>дата/время</th>
						<th class="tableHeader" style="width: 100px;" <?= $rowspan ?>>IP-адреса источники</th>
						<th class="tableHeader" onclick="sortColumnShortAdd('countryShortAdd')" style="width: 160px;" <?= $rowspan ?>>
						Страна<br>
						<img id="countryShortAdd" src="/<?= self::$directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по стране источнику"/>						
						</th>	
						<th class="tableHeader" onclick="sortColumnShortAdd('dstIpShortAdd')" style="width: 100px;" <?= $rowspan ?>>
						IP-адрес назначения<br>
						<img id="dstIpShortAdd" src="/<?= self::$directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по IP-адресу назначения"/>						
						</th>
						<th class="tableHeader" onclick="sortColumnShortAdd('addNameShortAdd')" style="width: 155px;" <?= $rowspan ?>>
						разобран<br>
						<img id="addNameShortAdd" src="/<?= self::$directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по Ф.И.О. добавившего"/>						
						</th>
					</tr>
					<?php echo "<tr ".COLOR_HEADER.">";
					if($userId != 10){
						?>
						<th class="tableHeader" style="width: 40px;">
						<input type="checkbox" name="majorCheckBox" onclick="checkAll()" title="отметить всё">						
						</th>
					<?php 
						}
					echo '</tr>';  
			sort($array_tmp);			 			
			for($a = $i = 0; $a < count($array_tmp); $a++){
				if($i != $array_tmp[$a]['id']){
					echo "<tr bgcolor=".color().">";				
					?>
					<td class="tableHeader" style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
<!-- номера воздействий -->				
					<?php 
					if($userId != 10){ 
						echo "<input type='checkbox' name=editIncident[] value='".$array_tmp[$a]['id']."'>"; 
						} else {
					 	?>
					 	<form name="showAllInfo" method="POST" enctype="multipart/form-data" target="_blank" action="/<?= self::$directory?>/major/process/showAllInformationForIncidents.php">
							<input type="image" name="showAllInformation" value="<?= $array_tmp[$a]['id'] ?>" src="/<?= self::$directory ?>/img/eye.png" style="cursor: pointer;" title="просмотреть полную информацию о компьютерном воздействии">
						</form>
						<?php
						if($SeverityRatingData->getSeverityRatingIpDst(long2ip($array_tmp[$a]['ip_dst']))){
					 		?>
					 		<img src="/<?= self::$directory ?>/img/warning_16.png" title="наиболее критичные IP-адреса назначения">
					 		<?php
							} 
						}
					?>
					</td>
<!-- дата/время начала -->
					<td class="tableHeader" style="text-align: center;">
						<?php
						$array_date_start_tmp = explode(" ", $array_tmp[$a]['date_time_incident_start']);
						echo ConversionData::showDateConvertStr($array_date_start_tmp[0]).'<br>';
						echo $array_date_start_tmp[1];
						?>
					</td>
<!-- дата/время конца -->
					<td class="tableHeader" style="text-align: center;">
						<?php
						$array_date_start_tmp = explode(" ", $array_tmp[$a]['date_time_incident_end']);
						echo ConversionData::showDateConvertStr($array_date_start_tmp[0]).'<br>';
						echo $array_date_start_tmp[1];
						?>
					</td>
<!-- IP-адреса источников -->
					<td class="tableHeader" style="text-align: center;">
					<?php
					//для ip_src
					foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
						//устанавливаем IP-адрес для поиска в бинарной БД
						$ReadBinaryDBBlackList->setIp(long2ip($ip_src));
						//вывод найденной информации
						$ReadBinaryDBBlackList->showInfoSearchIp(); 
						}
					?>
					</td>
<!-- страна принадлежности -->
					<td class="tableHeader" style="text-align: center;">
					<?php
					//для geoip
					foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
						echo $GeoIP->countryIP(self::linkDataBase(), $ip_src)." ";
						?> 
						<img src= <?php echo "/".self::$directory."/img/flags/".$GeoIP->flags(); ?> /><br>
						<?php
						}
					?>
					</td>
<!-- IP-адрес назначения (доменное имя) -->
					<td class="tableHeader" style="text-align: center;">
					<?php
					//IP-адрес назначения и доменное имя
					echo long2ip($array_tmp[$a]['ip_dst'])."<br>"; 
					echo $ReadXMLSetup->obtainDomainName(long2ip($array_tmp[$a]['ip_dst']));
					?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?php
					//време анализа инцидента
echo ConversionData::showDateConvert( $array_tmp[$a]['date_time_analyst'])
//					echo $ReadXMLSetup->usernameFIO($array_tmp[$a]['login_name']);
					?>
					</td>
					</tr>
					<?php
					}
				$i = $array_tmp[$a]['id'];
				}
			?>
			</table>
			</div>
			<?php 
			if($userId != 10){ 
				?></form>
				<span style="position: relative; top: 0px; left: 0px; font-size: 10px; font-family: 'Times New Roman', serif; color: #000;">
				* для того чтобы компьютерные атаки в данной таблице больше не отображались необходимо заполнить пустые поля
				</span>
		<?php } ?>
		<script>
		//функция "выбрать все"
		function checkAll() {
			var itemForms = document.forms.elements;
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
		//функция сортировки
		function sortColumnShortAdd(elName)
			{
			//получаем числовой индекс по имени поля
			var id;
			switch(elName) {
				case 'dateStartShortAdd':
					id = 1;
				break;
				case 'countryShortAdd':
					id = 4;
				break;
				case 'dstIpShortAdd':
					id = 5;
				break;
				case 'addNameShortAdd':
					id = 6;
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
			var elTable = document.getElementById('elTableShortAdd');
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
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
	
//компьютерные атаки сетевой трафик по которым отсутствует
static function showTableNoNetTraffic($GeoIP, $userId) 
	{
	//определение критичности данных
	$SeverityRatingData = new SeverityRatingData; 
	try{
		$query = self::linkDataBase()->connectionDB()->query("SELECT t1.id, `date_time_incident_start`, `date_time_incident_end`, `ip_src`, `ip_dst`, t1.login_name, `space_safe` 
									 						  FROM `incident_chief_tables` t0 JOIN `incident_additional_tables` t1 ON t0.id = t1.id 
														  	  INNER JOIN `incident_analyst_tables` t2 ON t0.id = t2.id WHERE t2.true_false='3'
														  	  ORDER BY `date_time_incident_start` ASC");
		//временный массив под IP-адреса источников		
		$array_tmp = $array_ip = array();
		while($row = $array_tmp[] = $query->fetch(PDO::FETCH_ASSOC)){
			$array_ip[$row['id']][] = $row['ip_src'];
			}

		//учет пользователей должников
		$num_user_debt = 0;
		//вывод таблицы только при наличие в ней данных
		if(count($array_tmp) > 1){
			if($userId != 10){
				$tableWidth = '735px';
				$widthColum_1 = '40px';
				$display = '';
				?>
				<!-- текст -->			
				<span style="position: relative; top: 0px; left: 155px; font-size: 12px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #990000;">
				отсутствует сетевой трафик по следующим компьютерным воздействиям *			
				</span>
				<?php 
				} else {
				$tableWidth = '725px';
				$widthColum_1 = '30px';
				$display = 'display: none';
				?>
				<div onclick="(function(elem) { var div = getElementById('tableLossTraff'); if(div.nodeType == 1){ if(div.style.display == 'none'){ div.style.display = 'block'; elem.style.textDecoration = ''; } else { div.style.display = 'none'; elem.style.textDecoration = 'underline'; }}})(this)" 
					  style="position: relative; top: 0px; left: 10px; font-size: 14px; font-family: 'Times New Roman', serif; color: #000080; cursor: pointer; text-decoration: underline;">
					отсутствует сетевой трафик по следующим компьютерным воздействиям
				</div>
				<?php } ?>
			<div id="tableLossTraff" style="border-width: 1px; border-style: solid; width: <?= $tableWidth ?>; border-color: #B7DCF7; <?= $display ?>">				
			<table id="elTableLoss" border="0" width="<?= $tableWidth ?>" cellpadding="2">
			<?php echo "<tr ".COLOR_HEADER.">"; ?>
					<th class="tableHeader" style="width: <?= $widthColum_1 ?>;"></th>
					<th class="tableHeader" style="width: 100px;">
					начальное<br>дата/время
					</th>
					<th class="tableHeader" style="width: 100px;">
					конечное<br>дата/время
					</th>
					<th class="tableHeader" style="width: 110px;">
					IP-адреса источники
					</th>
					<th class="tableHeader" style="width: 110px;">
					IP-адрес назначения
					</th>
					<th class="tableHeader" onclick="sortColumnLoss()" style="width: 120px;">
					добавлен
					<img id="addNameLoss" src="/<?= self::$directory ?>/img/button_blue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по Ф.И.О. добавившего"/>					
					</th>
					<th class="tableHeader" style="width: 145px;">
					расположение сетевого трафика
					</th>
				</tr>
			<?php
			for($a = $i = 0; $a < count($array_tmp) - 1; $a++){
				if($i != $array_tmp[$a]['id']){		
					echo "<tr bgcolor=".color().">";				
					?>
<!-- номера воздействий для редактирования -->
					<td class="tableHeader" style="text-align: center;">
					<?php
					if($userId != 10){
						//для всех пользователей кроме руководства
						?>
						<form name="editFormAnalyst" method="POST" enctype="multipart/form-data" action="/<?= self::$directory ?>/worker/process/edit_incidents.php">
						<input type="image" name="editIncidentTraffic" src="/<?= self::$directory ?>/img/pencil--plus.png" title="изменить местоположение сетевого трафика">
						<input type="hidden" name="editIncidentTraffic" value="<?= $array_tmp[$a]['id'] ?>">					
						</form>					
						<?php
						} else {
						//для руководства
						if($SeverityRatingData->getSeverityRatingIpDst(long2ip($array_tmp[$a]['ip_dst']))){
					 		?><img src="/<?= self::$directory ?>/img/warning_16.png" title="наиболее критичные IP-адреса назначения"><?php
							}
						}
					?>
					</td>
					<td class="tableHeader" style="text-align: center;">
						<?php
						$array_date_start_tmp = explode(" ", $array_tmp[$a]['date_time_incident_start']);
						echo ConversionData::showDateConvertStr($array_date_start_tmp[0]).'<br>';
						echo $array_date_start_tmp[1];
						?>
					</td>
					<td class="tableHeader" style="text-align: center;">
						<?php
						$array_date_start_tmp = explode(" ", $array_tmp[$a]['date_time_incident_end']);
						echo ConversionData::showDateConvertStr($array_date_start_tmp[0]).'<br>';
						echo $array_date_start_tmp[1];
						?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?php
					//для ip_src
					foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
						echo long2ip($ip_src)."<br>";
						}
					?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?php
					//IP-адрес назначения
					$ReadXMLSetup = new ReadXMLSetup(); 
					echo long2ip($array_tmp[$a]['ip_dst'])."<br>"; 
					//доменное имя назначения
					echo $ReadXMLSetup->obtainDomainName(long2ip($array_tmp[$a]['ip_dst']));
					?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?php
					//Ф.И.О. добавившего воздействие
					echo $ReadXMLSetup->usernameFIO($array_tmp[$a]['login_name']);
					if(isset($_SESSION['userSessid']['userLogin']) && !empty($_SESSION['userSessid']['userLogin'])){
						$array_tmp[$a]['login_name'] == $_SESSION['userSessid']['userLogin'] ? $num_user_debt++: false;
						}
					?>				
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?php
					//место нахождения сетевого трафика
					echo FormattingText::formattingTextLength($array_tmp[$a]['space_safe'], 20);
					?>				
					</td>
				</tr>
				<?php
					}
				$i = $array_tmp[$a]['id'];
				}
			if($num_user_debt > 0){
				?>
				<script type="text/javascript">
				alert('У Вас есть компьютерные воздействия сетевой трафик по которым отсутствует. Всего воздействий - <?php echo $num_user_debt; ?>');
				</script>
				<?php
				}	
			?>
			</table>
			</div>
			<?php if($userId != 10){ ?>
			<span style="position: relative; top: 0px; left: 0px; font-size: 10px; font-family: 'Times New Roman', serif; color: #000;">
				* для того чтобы компьютерные атаки в данной таблице больше не отображались необходимо изменить местонахождение сетевого трафика
			</span>
			<?php } ?>
			<script type="text/javascript" >
			var flag = true;
			function sortColumnLoss()
				{
				var elSort = document.getElementById('addNameLoss');
				//складываем путь к картинке находящийся в src в массив
				var arrayString = elSort.src.split('/');
				//изменяем изображение (направление стрелки)
				if(flag){
					elSort.src = '/' + arrayString[3] + '/img/button_blue_up.png';
					} else {
					elSort.src = '/' + arrayString[3] + '/img/button_blue_down.png';
					}
				//сортируем таблицу
				var elTable = document.getElementById('elTableLoss');
				var arraySort = new Array();
				for(var b = 1; b < elTable.rows.length; b++){
					arraySort[b-1] = new Array();
					if(elTable.rows[b].getElementsByTagName('TD').item(5) !== null){
						//получаем содержимое выбранного столбца (текст в теге td)
						arraySort[b-1][0] = elTable.rows[b].getElementsByTagName('TD').item(5).innerHTML;
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
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	}	
	
//компьютерные воздействия признанные ЛОЖНЫМИ
static public function showTableFalseIncident($GeoIP) 
	{
	$ReadXMLSetup = new ReadXMLSetup();
	try{
		$query = self::linkDataBase()->connectionDB()->query("SELECT t1.id, `date_time_incident_start`, `date_time_incident_end`, `ip_src`, `ip_dst`, t3.login_name 
														  	  FROM `incident_chief_tables` t1 JOIN `incident_analyst_tables` t2 
														  	  ON t1.id = t2.id JOIN `incident_additional_tables` t3 ON t1.id = t3.id
														  	  WHERE t2.true_false='2' ORDER BY t1.date_time_incident_start DESC LIMIT 0,10");
		//временный массив под IP-адреса источников		
		$array_tmp = $array_ip = array();
		while($row = $array_tmp[] = $query->fetch(PDO::FETCH_ASSOC)){
			$array_ip[$row['id']][] = $row['ip_src'];
			} 
		for($a = $i = $j = 0; $a < count($array_tmp) - 1; $a++){
			if($i != $array_tmp[$a]['id']){
				$j++;		
				echo "<tr bgcolor=".color().">";				
				?>
				<td class="tableHeader" style="text-align: center;">
				<?php echo $j; ?>.
				</td>
				<td class="tableHeader" style="text-align: center;">
				<?php echo $array_tmp[$a]['date_time_incident_start']; ?>
				</td>
				<td class="tableHeader" style="text-align: center;">
				<?php echo $array_tmp[$a]['date_time_incident_end']; ?>
				</td>
				<td class="tableHeader" style="text-align: center;">
				<?php
				//для ip_src
				foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
					echo long2ip($ip_src)."<br>";
					}
				?>
				</td><td class="tableHeader" style="text-align: center;">
				<?php
				//для geoip
				foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
					echo $GeoIP->countryIP(self::linkDataBase(), $ip_src)." ";
					?> 
					<img src= <?php echo "/".self::$directory."/img/flags/".$GeoIP->flags(); ?> /><br>
					<?php
					}
				?>
				</td>
				<td class="tableHeader" style="text-align: center;">
				<?php
				//IP-адрес назначения
				$ReadXMLSetup = new ReadXMLSetup(); 
				echo long2ip($array_tmp[$a]['ip_dst'])."<br>"; 
				//доменное имя назначения
				echo $ReadXMLSetup->obtainDomainName(long2ip($array_tmp[$a]['ip_dst']));
				?>
				</td>
				<td class="tableHeader" style="text-align: center;">
				<?php
				//номера сигнатур
				$query_sid = self::linkDataBase()->connectionDB()->query("SELECT `sid` FROM `incident_number_signature_tables` WHERE `id`='".$array_tmp[$a]['id']."'");
				while($row_sid = $query_sid->fetch(PDO::FETCH_ASSOC)){
					echo $row_sid['sid']."<br>";
					}
				?>
				</td>
				<td class="tableHeader" style="text-align: center;">
					<?php echo $ReadXMLSetup->usernameFIO($array_tmp[$a]['login_name']); ?>
				</td>
				</tr>
				<?php
				}
			$i = $array_tmp[$a]['id'];
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//компьютерные воздействия ожидающие анализа (аналитик и руководство)
static public function showTableWaitAnalysis($GeoIP, $userId) 
	{
	$ReadBinaryDBBlackList = new ReadBinaryDBBlackList;
	//определение критичности данных
	$SeverityRatingData = new SeverityRatingData;
	try{
 		$query = self::linkDataBase()->connectionDB()->query("SELECT t1.id, `date_time_incident_start`, `date_time_incident_end`, `date_time_create`, `ip_src`, `ip_dst`, `space_safe` FROM `incident_chief_tables` t0 INNER JOIN `incident_additional_tables` t1 ON t0.id = t1.id LEFT JOIN `incident_analyst_tables` t2 ON t1.id = t2.id WHERE ((t2.id) is Null) GROUP BY `date_time_incident_start`, `date_time_incident_end`, `date_time_create`,`ip_src` , `ip_dst`, t1.id ORDER BY `date_time_incident_start`");
		//временный массив под IP-адреса источников		
		$array_tmp = $array_ip = array();
		while($row = $array_tmp[] = $query->fetch(PDO::FETCH_ASSOC)){
			$array_ip[$row['id']][] = $row['ip_src'];
			} 
		
		//проверяем наличие необработанных воздействий
		if(count($array_tmp) > 1){
			if($userId != 10){
				$display = '';
				?>
				<span style="position: relative; top: 0px; left: 110px; font-size: 12px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #990000;">
					необходимо выполнить анализ следующих событий информационной безопасности<br>
				</span>
				<?php 
				} else {
				$display = 'display: none';
				?>
				<div onclick="(function(elem) { var div = getElementById('tableNotAnalysis'); if(div.nodeType == 1){ if(div.style.display == 'none'){ div.style.display = 'block'; elem.style.textDecoration = ''; } else { div.style.display = 'none'; elem.style.textDecoration = 'underline'; }}})(this)" 
					  style="position: relative; top: 0px; left: 11px; font-size: 14px; font-family: 'Times New Roman', serif; color: #000080; cursor: pointer; text-decoration: underline;">
					невыполнен анализ следующих событий информационной безопасности
				</div>
				<?php } ?>	
			<div id="tableNotAnalysis" style="border-width: 1px; border-style: solid; width: 725px; border-color: #B7DCF7; <?= $display ?>">				
			<table id="elTableWaitTest" border="0" width="725px" cellpadding="2">
				<?php echo "<tr ".COLOR_HEADER.">"; ?>
					<th class="tableHeader" colspan="2" style="width: 60px;">№</th>
                    <th class="tableHeader" style="width: 80px;">
                    id
                    </th>
                    <th class="tableHeader" onclick="sortColumnWaitTest('dateStartWaitTest')" style="width: 85px;">
					начальное<br>дата/время<br>
					<img id="dateStartWaitTest" src="/<?= self::$directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="сортировать по начальному времени"/>
					</th>
					<th class="tableHeader" style="width: 90px;">
					IP-адреса источников
					</th>
					<th class="tableHeader" onclick="sortColumnWaitTest('countryWaitTest')" style="width: 180px;">
					страна<br>
					<img id="countryWaitTest" src="/<?= self::$directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по стране источнику"/>					
					</th>
					<th class="tableHeader" onclick="sortColumnWaitTest('dstIpWaitTest')" style="width: 95px;">
					IP-адрес назначения<br>
					<img id="dstIpWaitTest" src="/<?= self::$directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по IP-адресу назначения"/>					
					</th>
					<th class="tableHeader" onclick="sortColumnWaitTest('saveWaitTest')" style="width: 135px;">
					расположения сет. трафика<br>
					<img id="saveWaitTest" src="/<?= self::$directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по местоположению сетевого трафика"/>					
					</th>
				</tr>
			<?php
			$a_count = count($array_tmp) - 1;
            function dateIsNow($unixDate) {
                if(substr(date("d-m-Y", $unixDate), 0, 1) == 0){
                    $dayCreate = substr(date("d-m-Y", $unixDate), 1, 1);
                } else {
                    $dayCreate = substr(date("d-m-Y", $unixDate), 0, 2);
                }
                $monthCreate = substr(date("d-m-Y", $unixDate), 2, 4);

                $dateString = date("d-m-Y", time());
                if(substr($dateString, 0, 1) == 0){
                    $dayNow = substr($dateString, 1, 1);
                } else {
                    $dayNow = substr($dateString, 0, 2);
                }
                $monthNow = substr($dateString, 2, 4);

                return (($dayNow == $dayCreate) && ($monthNow == $monthCreate));
            }

			for($a = $i = $j = 0; $a < $a_count; $a++){
				if($i != $array_tmp[$a]['id']){
					$j++;		
					echo "<tr bgcolor=".color().">";				
					?>
					<td class="tableHeader" style="width: 30px; text-align: center;"><?php echo $j; ?>.</td>
					<?php
					if($userId != 10){
					?>
					<td class="tableHeader" style="width: 30px; text-align: center;">
						<form name="editFormAnalyst" method="POST" enctype="multipart/form-data" action="process/analysis_incident.php">
							<input type="image" name="editAnalyst" src="/<?= self::$directory ?>/img/eye--pencil.png" title="анализ компьютерного воздействия">
							<input type="hidden" name="editAnalyst" value="<?= $array_tmp[$a]['id'] ?>">
						</form>					
					</td>
					<?php } else {
							?><td class="tableHeader" style="width: 30px; text-align: center;"><?php
							if($SeverityRatingData->getSeverityRatingIpDst(long2ip($array_tmp[$a]['ip_dst']))){
					 		?><img src="/<?= self::$directory ?>/img/warning_16.png" title="наиболее критичные IP-адреса назначения"><?php
								} 
							?></td><?php 
					 		} ?>
					<td class="tableHeader" style="text-align: center;">
					<?php echo $array_tmp[$a]['id']; ?>
					</td>
<!-- дата и время -->
					<td class="tableHeader" style="text-align: center;">
					<?php
                    echo dateIsNow($array_tmp[$a]['date_time_create']) ? '<strong>'.$array_tmp[$a]['date_time_incident_start'].'</strong>': $array_tmp[$a]['date_time_incident_start'];
                    ?>
					</td>
					<td class="tableHeader" style="text-align: center;">
<!-- ip_src -->
					<?php
					//для ip_src
					foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
						//устанавливаем IP-адрес для поиска в бинарной БД
						$ReadBinaryDBBlackList->setIp(long2ip($ip_src));
						//вывод найденной информации
						$ReadBinaryDBBlackList->showInfoSearchIp(); 
						}
					?>
					</td>
<!-- страна принадлежности и информация о найденном в бинарной БД IP-адресе -->
					<td class="tableHeader" style="text-align: center;">
					<?php
					//для geoip
					foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
						echo $GeoIP->countryIP(self::linkDataBase(), $ip_src)." ";
						?><img src= <?php echo "/".self::$directory."/img/flags/".$GeoIP->flags(); ?> /><br><?php
						}
					?>
					</td>
<!-- ip_dst и доменное имя -->
					<td class="tableHeader" style="text-align: center;">
					<?php
					$ReadXMLSetup = new ReadXMLSetup(); 
					echo long2ip($array_tmp[$a]['ip_dst'])."<br>"; 
					echo $ReadXMLSetup->obtainDomainName(long2ip($array_tmp[$a]['ip_dst']));
					?>
					</td>
<!-- расположение сетевого трафика -->
					<td class="tableHeader" style="text-align: center;">
					<?php
					echo FormattingText::formattingTextLength($array_tmp[$a]['space_safe'], 20);
					?>
					</td>
					</tr>
					<?php
					}
				$i = $array_tmp[$a]['id'];
				}
			?>
			</table>
			</div>
			<script type="text/javascript" >
			var flag = true;
			function sortColumnWaitTest(elName)
				{
				//получаем числовой индекс по имени поля
				var id;
				switch(elName) {
					case 'dateStartWaitTest':
						id = 3;
					break;
					case 'countryWaitTest':
						id = 5;
					break;
					case 'dstIpWaitTest':
						id = 6;
					break;
					case 'saveWaitTest':
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
				var elTable = document.getElementById('elTableWaitTest');
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
			if($userId != 10){
			?>
			<span style="position: relative; top: 0px; left: 0px; font-size: 10px; font-family: 'Times New Roman', serif; color: #000;">
			* для того чтобы компьютерные атаки в данной таблице больше не отображались необходимо заполнить пустые поля
			</span>
			<?php
				}	
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}		
	}

//последние проанализированные аналитиком воздействия
public static function showTableAnalysisIncident($GeoIP)
	{
	$ReadXMLSetup = new ReadXMLSetup(); 
	try{
	$query = self::linkDataBase()->connectionDB()->query("SELECT t1.id, `date_time_incident_start`, `date_time_incident_end`, `ip_src`, `ip_dst`, `true_false` 
 													  	  FROM `incident_chief_tables` t0 JOIN `incident_analyst_tables` t1 ON t0.id = t1.id 
 													  	  WHERE `login_name`='".$_SESSION['userSessid']['userLogin']."'
 													  	  ORDER BY `date_time_analyst` DESC LIMIT 0, 20");
	//временный массив под IP-адреса источников		
	$array_tmp = $array_ip = array();
	while($row = $array_tmp[] = $query->fetch(PDO::FETCH_ASSOC)){
		$array_ip[$row['id']][] = $row['ip_src'];
		}
		//проверяем наличие необработанных воздействий
		if(count($array_tmp) > 1){
			?>	
<!-- текст -->
			<span style="position: relative; top: 0px; left: 180px; font-size: 12px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #000099;">
				последние проанализированные компьютерные воздействия<br>
			</span>
			<div style="border-width: 1px; border-style: solid; width: 725px; border-color: #B7DCF7;">				
			<table id="elTableLostImpact" border="0" width="725px" cellpadding="2">
				<?php echo "<tr ".COLOR_HEADER.">"; ?>
					<th class="tableHeader" style="width: 40px;"></th>
					<th class="tableHeader" onclick="sortColumnLostImpact('dateStartLostImpact')" style="width: 90px;">
					начальное<br>дата/время<br>
					<img id="dateStartLostImpact" src="/<?= self::$directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="сортировать по начальному времени"/>
					</th>
					<th class="tableHeader" style="width: 85px;">конечное<br>дата/время</th>
					<th class="tableHeader" style="width: 85px;">IP-адреса источников</th>
					<th class="tableHeader" onclick="sortColumnLostImpact('countryLostImpact')" style="width: 185px;">
					страна<br>
					<img id="countryLostImpact" src="/<?= self::$directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по стране источнику"/>
					</th>
					<th class="tableHeader" onclick="sortColumnLostImpact('dstIpLostImpact')" style="width: 95px;">
					IP-адрес назначения<br>
					<img id="dstIpLostImpact" src="/<?= self::$directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по IP-адресу назначения"/>
					</th>
					<th class="tableHeader" onclick="sortColumnLostImpact('solutionLostImpact')" style="width: 145px;">
					решение аналитика<br>
					<img id="solutionLostImpact" src="/<?= self::$directory ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по решению аналитика"/>					
					</th>
				</tr>
			<?php
			sort($array_tmp);
			$a_count = count($array_tmp);

			for($a = $i = 0; $a < $a_count; $a++){
				if($i != $array_tmp[$a]['id']){
					echo "<tr bgcolor=".color().">";				
					?>
					<td class="tableHeader" style="width: 30px; text-align: center;">
						<form name="editFormAnalyst" method="POST" enctype="multipart/form-data" action="process/edit_incidents.php">
							<input type="image" name="editInpacts" src="/<?= self::$directory ?>/img/pencil.png" title="редактирование компьютерного воздействия">
							<input type="hidden" name="editInpacts" value="<?= $array_tmp[$a]['id'] ?>">
						</form>					
					</td>
					<td class="tableHeader" style="text-align: center;">
						<?php echo $array_tmp[$a]['date_time_incident_start']; ?>
					</td>
					<td class="tableHeader" style="text-align: center;">
						<?php echo $array_tmp[$a]['date_time_incident_end']; ?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?php
					//для ip_src
					foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
						echo long2ip($ip_src)."<br>";
						}
					?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?php
					//для geoip
					foreach($array_ip[$array_tmp[$a]['id']] as $ip_src){	
						echo $GeoIP->countryIP(self::linkDataBase(), $ip_src)." ";
						?> 
						<img src= <?php echo "/".self::$directory."/img/flags/".$GeoIP->flags(); ?> /><br>
						<?php
						}
					?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?php
					echo long2ip($array_tmp[$a]['ip_dst'])."<br>"; 
					echo $ReadXMLSetup->obtainDomainName(long2ip($array_tmp[$a]['ip_dst']));
					?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?php
					if($array_tmp[$a]['true_false'] == "1"){
						echo "компьютерная атака";
						}
					elseif($array_tmp[$a]['true_false'] == "2"){
						echo "ложное срабатывание";
						}
					elseif($array_tmp[$a]['true_false'] == "3"){
						echo "отсутствует сетевой трафик";								
						}
					elseif($array_tmp[$a]['true_false'] == "4"){
						echo "сетевой трафик утерян";	
						}
                    elseif($array_tmp[$a]['true_false'] == "5"){
                        echo "сетевой трафик не рассматривался";
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
			</div>
		<script type="text/javascript" >
		var flag = true;
		function sortColumnLostImpact(elName)
			{
			//получаем числовой индекс по имени поля
			var id;
			switch(elName) {
				case 'dateStartLostImpact':
					id = 1;
				break;
				case 'countryLostImpact':
					id = 4;
				break;
				case 'dstIpLostImpact':
					id = 5;
				break;
				case 'solutionLostImpact':
					id = 6;
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
			var elTable = document.getElementById('elTableLostImpact');
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
	}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}			
	}

//количество воздействий добавленных и проанализированных
public static function showTableCountIncidentForUsers() 
	{
	try{
		//объект для чтения файла setup_site.xml
		$ReadXMLSetup = new ReadXMLSetup;		
		//массив логинов дежурных
		$array_worker = $ReadXMLSetup->getArrayUserNumGroup(20);	
		//массив логинов аналитиков
		$array_analyst = $ReadXMLSetup->getArrayUserNumGroup(30);				
		?>
			<div style="border-width: 1px; border-style: solid; width: 745px; border-color: #B7DCF7;">				
			<table id="elTable" border="0" width="745px" cellpadding="2">
				<?php echo "<tr ".COLOR_HEADER.">"; ?>
					<th colspan="2" style="font-size: 10px; font-family: 'Times New Roman', serif;">пользователи</th>
					<th colspan="5" style="font-size: 10px; font-family: 'Times New Roman', serif;">информация по воздействиям</th>
				</tr>
				<?php echo "<tr ".COLOR_HEADER.">"; ?>
					<th class="tableHeader" onclick="sortColumn('sortFIO')" style="width: 195px;">
						Ф.И.О. дежурного
						<img id="sortFIO" src="/<?= self::$directory ?>/img/button_blue_down.png" style="cursor: pointer; vertical-align: middle;" title="сортировать по пользователям"/>
					</th>
					<th class="tableHeader" onclick="sortColumn('sortVisit')" style="width: 95px;">
						посещений
						<img id="sortVisit" src="/<?= self::$directory ?>/img/button_blue_down.png" style="cursor: pointer; vertical-align: middle;" title="сортировать по количеству посещений"/>
	



				</th>
					<th class="tableHeader" onclick="sortColumn('addImpacts')" style="width: 110px;">
						добавлено
						<img id="addImpacts" src="/<?= self::$directory ?>/img/button_blue_down.png" style="cursor: pointer; vertical-align: middle" title="сортировать по количеству добавленных воздействий"/>
					</th>
					<th class="tableHeader" onclick="sortColumn('numTrueKA')" style="width: 120px;" colspan="2">
						компьютерные атаки
						<img id="numTrueKA" src="/<?= self::$directory ?>/img/button_blue_down.png" style="cursor: pointer; vertical-align: middle;" title="сортировать по количеству воздействий признанных КА"/>
					</th>
					<th class="tableHeader" onclick="sortColumn('numFalseKA')" style="width: 100px;">
						ложные
						<img id="numFalseKA" src="/<?= self::$directory ?>/img/button_blue_down.png" style="cursor: pointer; vertical-align: middle;" title="сортировать по количгруппировать по Ф.И.О. добавившегоеству воздействий признанных ложными"/>
					</th>
					<th class="tableHeader" onclick="sortColumn('mail')" style="width: 120px;">
						письма
						<img id="mail" src="/<?= self::$directory ?>/img/button_blue_down.png" style="cursor: pointer; vertical-align: middle;" title="сортировать по количеству подготовленных писем"/>					
					</th>
				</tr>
			<?php
			foreach($array_worker as $worker){
				$query_user_session = self::linkDataBase()->connectionDB()->query("SELECT `user_login`, `count_visit_user` FROM `user_session` WHERE `user_login`='".$worker."'");
				$query_impacts = self::linkDataBase()->connectionDB()->query("SELECT (SELECT COUNT(*) FROM `incident_additional_tables` WHERE `login_name`='".$worker."') AS NUM, 
																			 (SELECT COUNT(*) FROM `incident_additional_tables` t1 JOIN `incident_analyst_tables` t2  ON t1.id=t2.id 
																			  WHERE `true_false`='1' AND t1.login_name='".$worker."') AS NUM_KA, (SELECT COUNT(*) FROM `incident_additional_tables` 
																			  WHERE (`number_mail_in_CIB` is not Null) AND `login_name`='".$worker."') AS NUM_MAIL");				
				$row_user = $query_user_session->fetch(PDO::FETCH_OBJ);
				$row_num = $query_impacts->fetch(PDO::FETCH_OBJ);
				//вывод информации о дежурных
				echo "<tr bgcolor=".color().">";				
				?>
					<td class="tableHeader">
					<?= $ReadXMLSetup->usernameFIO($row_user->user_login);	?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?= $row_user->count_visit_user; ?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?= $row_num->NUM; ?>
					</td>
					<td class="tableHeader" style="text-align: center;" colspan="2">
					<?= $row_num->NUM_KA; ?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?= ($row_num->NUM - $row_num->NUM_KA); ?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?= $row_num->NUM_MAIL; ?>
					</td>
				</tr>
				<?php
				}
				?>
			</table>
			<table border="0" width="745px" cellpadding="2">
				<!-- вывод информации об аналитике -->
				<?php echo "<tr ".COLOR_HEADER.">"; ?>
					<th class="tableHeader" style="width: 210px;">
					Ф.И.О. аналитика
					</th>
					<th class="tableHeader" style="width: 80px;">
					посещений
					</th>
					<th class="tableHeader" style="width: 80px;">
					выполнен анализ
					</th>
					<th class="tableHeader" style="width: 100px;">
					воздействия признанные КА
					</th>
					<th class="tableHeader" style="width: 110px;">
					ложные воздействия  (не найден сетевой трафик)
					</th>
					<th class="tableHeader" style="width: 80px;">
					КА имеющие полное описание (шт.)
					</th>
					<th class="tableHeader" style="width: 80px;">
					полнота описания КА (%)
					</th>
				</tr>
				<?php
			foreach($array_analyst as $analyst){
				$query_user_session = self::linkDataBase()->connectionDB()->query("SELECT `user_login`, `count_visit_user` FROM `user_session` WHERE `user_login`='".$analyst."'");
				$query_impacts = self::linkDataBase()->connectionDB()->query("SELECT (SELECT COUNT(*) FROM `incident_analyst_tables` WHERE `login_name`='".$analyst."') AS NUM, 
																			 (SELECT COUNT(*) FROM `incident_analyst_tables` WHERE `login_name`='".$analyst."' AND `true_false`='1') AS NUM_KA, 
																			 (SELECT COUNT(*) FROM `incident_analyst_tables` WHERE `login_name`='".$analyst."' AND `true_false`='1' AND 
																			 (`information_analyst`!='')) AS NUM_MAIL");				
				$row_user = $query_user_session->fetch(PDO::FETCH_OBJ);
				$row_num = $query_impacts->fetch(PDO::FETCH_OBJ);
				echo "<tr bgcolor=".color().">";				
				?>
					<td class="tableHeader">
					<?= $ReadXMLSetup->usernameFIO($row_user->user_login);	?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?= $row_user->count_visit_user; ?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?= $row_num->NUM; ?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?= $row_num->NUM_KA; ?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?= ($row_num->NUM - $row_num->NUM_KA); ?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?= $row_num->NUM_MAIL; ?>
					</td>
					<td class="tableHeader" style="text-align: center;">
					<?= round($row_num->NUM_MAIL / ($row_num->NUM_KA / 100), 1) ?> %
					</td>
				</tr>
				<?php
				}
				?>
			</table>			
			</div>
		<script type="text/javascript" >
		var flag = true;
		function sortColumn(elName)
			{
			//получаем числовой индекс по имени поля
			var id;
			switch(elName) {
				case 'sortFIO':
					id = 0;
				break;
				case 'sortVisit':
					id = 1;
				break;
				case 'addImpacts':
					id = 2;
				break;
				case 'numTrueKA':
					id = 3;
				break;
				case 'numFalseKA':
					id = 4;
				break;
				case 'mail':
					id = 5;
				break;
				}
			var elSort = document.getElementById(elName);
			//складываем путь к картинке находящийся в src в массив
			var arrayString = elSort.src.split('/');
			//изменяем изображение (направление стрелки)
			if(flag){
				elSort.src = '/' + arrayString[3] + '/img/button_blue_up.png';
				} else {
				elSort.src = '/' + arrayString[3] + '/img/button_blue_down.png';
				}
			//сортируем таблицу
			var elTable = document.getElementById('elTable');
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
			//сортируем массив, при этом если id не равно 0 сортируем числа 
			if(flag){
				if(id === 0){
					arraySort.sort();	
					} else {
					arraySort.sort(function(a,b){return a[0] - b[0];});
					}
				} else {
				if(id === 0){	
					arraySort.reverse();
					} else {
					arraySort.sort(function(a,b){return b[0] - a[0];});	
					}
				}
			//добавляем в таблицу отсортированные значения
			for(var j = 1; j < arraySort.length; j++){
				if(arraySort[j][1] !== undefined){
					//проверяем на четность
					if(j % 2 == 0){
						arraySort[j][1].setAttribute('bgcolor', '#D1E7F7');
						} else {
						arraySort[j][1].setAttribute('bgcolor', '#B7DCF7');	
						}
					elTable.appendChild(arraySort[j][1]);
					}
				}
			flag = !flag;
			}
		</script>			
		<?php
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}			
	}
}

?>
