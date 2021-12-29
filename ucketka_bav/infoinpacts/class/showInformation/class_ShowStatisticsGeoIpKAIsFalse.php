<?php
							/*--------------------------------------------------*/
							/*  	класс вывода статистической информации 		*/
							/*	 	по разделу геопозиционирования (GeoIP)		*/
							/*	 статистическая информация о ложных событиях 	*/
							/* 			информационной безопасности				*/
							/* 	 					v.0.1 01.12.2014    		*/
							/*--------------------------------------------------*/

class ShowStatisticsGeoIpKAIsFalse extends StatisticsGeoIp
{
public function showInformation()
	{
	//выводим форму
	$this->showForm();
	}

//получить количество ip_dst участвовавших в КА или КА с которых были признанны ложными, объеденить данные по странам
protected function getNumCounry($flag, $DATE_TIME, $IP_DST)
	{
	$array = array();
	$trueFalse = ($flag) ? '1': '2'; 
	$query = "SELECT `country`, COUNT(`country`) AS NUM FROM `incident_chief_tables` t0 JOIN `incident_analyst_tables` t1 ON t0.id=t1.id 
			  WHERE ".$DATE_TIME." `true_false`='".$trueFalse."' ".$IP_DST." GROUP BY `country` ORDER BY  NUM DESC";
	try{
		$query = $this->DBO->connectionDB()->query($query);
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			if($row->country !== '' && preg_match('/^[A-Z]{2}$/', $row->country)) $array[$row->country] = $row->NUM;
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	return $array;
	}

//получаем JSON строку
protected function getJsonString($array_data, $value = '')
	{
	$array = array();
	$GeoIP = $this->GeoIp;
	foreach($array_data as $code => $count){
		if($value == '0' || !empty($value)){
			$array[] = $count[$value];
			} else {
			$array[] = $GeoIP::$codeCountry[$code];
			}
		}
	return json_encode($array, JSON_UNESCAPED_UNICODE);
	}

//подготовка общего массива с данными
protected function getDataArray(array $array_first, array $array_second)
	{
	$array = array();
	foreach($array_first as $key => $value){
		$array[$key][0] = $value;
		if(!empty($array_second[$key])){
			$array[$key][1] = $array_second[$key];
			} else {
			$array[$key][1] = '0';
			}
		}
	return $array;
	}

//форма для вывода статистической информации по IP-адресу назначения
protected function showForm()
	{
	$array_ip_dst = array();
	try{
		//информация для выпадающего списка IP-адресов назначения
		$a = 0; 
		$queryIpDst = $this->DBO->connectionDB()->query("SELECT DISTINCT(`ip_dst`) AS LIST_IP_DST FROM `incident_chief_tables`");
		while($row = $queryIpDst->fetch(PDO::FETCH_OBJ)){
			$ipDst = long2ip($row->LIST_IP_DST);
			$array_ip_dst[$a][0] = $ipDst;
			if($this->XML->obtainDomainName($ipDst)){
				$array_ip_dst[$a][1] = (string) $this->XML->obtainDomainName($ipDst);
				}
			$a++;
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	?>
<!-- DIV формы -->
	<div style="width: 720px;">
<!-- интервалы времени -->
		<div style="width: 720px; font-size: 14px; font-family: 'Times New Roman', serif;">
			<div style="float: left; width: 220px; padding-top: 4px;">
				<input type="radio" name="intervalDate" class="intervalDate" value="7">неделя
				<input type="radio" name="intervalDate" class="intervalDate" value="30">месяц
				<input type="radio" name="intervalDate" class="intervalDate" value="120">квартал
			</div>
			<div id="showInterval" style="float: left; width: 120px; padding-top: 8px;"><a href="#" class="getInterval">другой интервал</a></div>
<!-- интервал времени и даты -->	
			<div id="setIntervalDate" style="float: left; width: 250px; display: none;">
				<div style="font-size: 12px; font-family: 'Times New Roman', serif;">
				<span style="padding-left: 12px; color: #000;">с</span>
					<input type="date" id="dateStart" style="width: 140px; height: 20px;"><input type="time" id="timeStart" value="00:00" style="width: 70px; height: 20px;">
				</div>
				<div style="padding-top: 3px; font-size: 12px; font-family: 'Times New Roman', serif;">
					<span style="padding-left: 5px; color: #000;">по</span>
					<input type="date" id="dateEnd" style="width: 140px; height: 20px;"><input type="time" id="timeEnd" value="23:59" style="width: 70px; height: 20px;">
				</div>
			</div>
<!-- кнопка 'поиск' -->
			<div style="float: left; width: 50px; padding-top: 6px;">
				<a href='#' onclick="return sendRequest(this)" class="buttonG" style="height: 16px; text-align: center; font-size: 14px;">поиск</a>
			</div>
		</div>
	</div>
	<!-- IP-адрес назначения -->
	<div style="width: 155px;">
		<select id="ipDst" style="width: 150px; height: 23px; font-size: 12px; font-family: 'Times New Roman', serif;">
			<option value="">ip-адрес назначения</option>
			<?php
			sort($array_ip_dst);
			for($i = count($array_ip_dst) - 1; $i >= 0; $i--){
				if(count($array_ip_dst[$i]) == 1){
					echo "<option value='".$array_ip_dst[$i][0]."'>{$array_ip_dst[$i][0]}</option>";
					} else {
					echo "<option value='".$array_ip_dst[$i][0]."'>{$array_ip_dst[$i][1]}</option>";								
					}
				}
			?>
		</select>
	</div>
<!-- вывод интервала времени -->
	<input id="areaIntervalDate" type="hidden" 
		value="(function(){
				var buttonInterval = document.getElementById('showInterval');
				buttonInterval.onclick = function(){
				var setIntervalDate = document.getElementById('setIntervalDate');
				var button = buttonInterval.parentNode.getElementsByTagName('DIV');
				if(setIntervalDate.style.display == 'none'){
					setIntervalDate.style.display = 'block';
					button[5].style.paddingTop = '12px';
					} else {
					setIntervalDate.style.display = 'none';
					button[5].style.paddingTop = '2px';
					}
				}
			})();">
<!-- для формирования Ajax запроса -->
	<input id="areaSendRequest" type="hidden" 
		value="function sendRequest(elem){
			var checked = false;
			var nodeRadio = document.getElementsByClassName('intervalDate');
			var div = document.getElementById('setIntervalDate');
			//проверяем выбраны ли переключатели radio
			for(var i in nodeRadio){
				if(nodeRadio[i].checked == true){
					checked = nodeRadio[i].value;
					}
				}
			if(div.style.display == 'none' && checked == false){
				alert('выберите интервал времени');
				return false;
				}
			//проверяем интервал времени
			if(checked == false){
				var obj = {};
				obj.fdateStart = document.getElementById('dateStart');
				obj.ftimeStart = document.getElementById('timeStart');
				obj.fdateEnd = document.getElementById('dateEnd');
				obj.ftimeEnd = document.getElementById('timeEnd');
				//проверяем заполненность полей
				for(var j in obj){
					for(var i in obj) obj[i].style.borderColor = '';
					if(obj[j].value.length == 0){
						obj[j].style.borderColor = 'red';
						return false;
						}
					}
				//проверяем чтобы начальная дата была меньше конечной
				var dStart = new Date(obj.fdateStart.value);
				var dEnd = new Date(obj.fdateEnd.value);		
				if(dStart > dEnd){
					obj.fdateStart.style.borderColor = 'red';
					obj.fdateEnd.style.borderColor = 'red';
					return false;
					}
				checked = obj.fdateStart.value + '_' + obj.ftimeStart.value 
						  + '_' + obj.fdateEnd.value + '_' + obj.ftimeEnd.value;
				}
			//Ajax запрос
			var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', '/<?= $this->directory ?>/<?= $this->getUserRole() ?>/process/ajax_process.php', elem, 
																   'queryStatistics=GeoIp_4&intervalDate=' + checked + '&ipDst=' + document.getElementById('ipDst').value);
			newObjectXMLHttpRequest.sendRequest();	
			return true;
			}">
	<script type="text/javascript" src='/<?= $this->directory ?>/js/objectXMLHttpRequest.js'></script>
	<?php
	//получаем статистическую информацию
	$this->showData();
	}

//статистическая информация по IP-адресу назначения
protected function showData()
	{
	$titleIpDst = $DATE_TIME = $IP_DST = '';
	$titleData = '<br>за весь период времени';
	//дата и время
	if(!empty($_POST['intervalDate'])){
		//определяем заданный интервал (диапазон дат или количество суток)
		if(strpos($_POST['intervalDate'], '_') == false){
			//если диапазон
			$interval = ExactilyUserData::takeIntager($_POST['intervalDate']);
			$currentDate = time();
			$startCurrentDate = $currentDate - ($interval * 3600 * 24);
			$currentDate = date('Y-m-d H:i:s', $currentDate);
			$startCurrentDate = date('Y-m-d H:i:s', $startCurrentDate);
			$dateTimeStart = ExactilyUserData::takeDate(substr($startCurrentDate, 0, 10))." ".ExactilyUserData::takeTime(substr($startCurrentDate, 11, 5));
			$dateTimeEnd = ExactilyUserData::takeDate(substr($currentDate, 0, 10))." ".ExactilyUserData::takeTime(substr($currentDate, 11, 5));
			$DATE_TIME = "`date_time_incident_start` BETWEEN STR_TO_DATE('$dateTimeStart', '%Y-%m-%d %H:%i:%s') 
				 	   	   AND STR_TO_DATE('$dateTimeEnd', '%Y-%m-%d %H:%i:%s') AND";
			//интервал времени для заголовка диаграммы
			$titleData = '<br>'.StatisticsGeoIp::showStringDateTime($startCurrentDate, $currentDate, true);
			} else {
			//если временной интервал
			$array_date = explode('_', $_POST['intervalDate']);
			$dateTimeStart = ExactilyUserData::takeDate($array_date[0])." ".ExactilyUserData::takeTime($array_date[1]);
			$dateTimeEnd = ExactilyUserData::takeDate($array_date[2])." ".ExactilyUserData::takeTime($array_date[3]);		
			$DATE_TIME = "`date_time_incident_start` BETWEEN STR_TO_DATE('$dateTimeStart', '%Y-%m-%d %H:%i:%s') 
					 	   AND STR_TO_DATE('$dateTimeEnd', '%Y-%m-%d %H:%i:%s') AND";
			//интервал времени для заголовка диаграммы
			$titleData = '<br>'.StatisticsGeoIp::showStringDateTime($dateTimeStart, $dateTimeEnd, false);
			}
		}
	//IP-адрес назначения
	if(!empty($_POST['ipDst'])){
		$array_ip = ExactilyUserData::takeIP($_POST['ipDst']);
		$ipDst = ip2long($array_ip[0]);
		$IP_DST = " AND `ip_dst`= '{$ipDst}'";
		$ipName = $array_ip[0];
		if($this->XML->obtainDomainName($array_ip[0])){
			$ipName = (string) $this->XML->obtainDomainName($array_ip[0]);
			}
		$titleIpDst = '<br>воздействия с которых направлены на '.$ipName;
		}

	//массив данных по КА
	$array_true_KA = $this->getNumCounry(true, $DATE_TIME, $IP_DST);
	//массив данных по ложным срабатываниям
	$array_false_KA = $this->getNumCounry(false, $DATE_TIME, $IP_DST);
	
	$countArrayTrueKA = count($array_true_KA);
	$countArrayFalseKA = count($array_false_KA);

	//проверяем что есть по запросу 
	if($countArrayTrueKA == 0 && $countArrayFalseKA == 0) echo ShowMessage::informationNotFound('информация отсутствует', 180);
	
	//готовим основной массив с данными
	if($countArrayTrueKA > $countArrayFalseKA){
		$array_true_false_KA = $this->getDataArray($array_true_KA, $array_false_KA);
		$grafHeight = $countArrayTrueKA * 12;
		} else {
		$array_true_false_KA = $this->getDataArray($array_false_KA, $array_true_KA);
		$grafHeight = $countArrayFalseKA * 12;
		}

	//получаем строку в формате JSON содержащую имена стран
	$stringCountryName = str_replace('"', '\'', $this->getJsonString($array_true_false_KA));
	//получаем строку в формате JSON содержащую данные о количестве КА
	$stringDateTrueKA = str_replace('"', '', $this->getJsonString($array_true_false_KA, '0'));
	//получаем строку в формате JSON содержащую данные о количестве ложных срабатываний
	$stringDateFalseKA = str_replace('"', '', $this->getJsonString($array_true_false_KA, '1'));

	$objInfo = "{ title: 'ложные срабатывания по отношению к компьютерным атакам {$titleData}',
				  titleY: 'количество IP-адресов источников {$titleIpDst}',
				  grafHeight: {$grafHeight}, 
				  categories: {$stringCountryName} }";

	$data = "[{
			 name: 'компьютерные атаки',	
			 data: ".$stringDateTrueKA."
			 }, {
			 name: 'ложное срабатывание',
			 data: ".$stringDateFalseKA."
			 }]";

	?>
<!-- поле информации -->
	<div style="padding-top: 10px; width: 735px; overflow: hidden;">	
	<?php	
	//получаем новые массивы в которых отсутствуют дублирующие значения
	$uniq_true_KA = array_unique($array_true_KA);
	$uniq_false_KA = array_unique($array_false_KA);
	//проверяем полноту полученных данных (если данные не полные выводим таблицу а не график)
	if($countArrayTrueKA == 1 || (count($uniq_true_KA) == 1 || count($uniq_false_KA) == 1) || array_shift($uniq_false_KA) == 0){
		?>
		<div style="text-align: center;">
			<span style="font-size: 14px; font-family; 'Times New Roman', serif;">
				Внимание, недостаточно данных для посторения графика.<br>Вывод информации в виде таблицы.
			</span>
			<div style="margin-top: 10px; width: 550px; margin-left: 5px; display: inline-block; border-width: 1px; border-style: solid; border-color: #B7DCF7; clear: left;">
			<table style="width: 550px;">
				<thead>
				<tr style='text-align: center; font-size: 10px; font-family: 'Times New Roman', serif;' <?= COLOR_HEADER ?> >
					<th style="width: 250px;">страна</th>
					<th style="width: 150px;">количество<br>компьютерных атак</th>
					<th style="width: 150px;">количество ложных срабатываний</th>
				</tr>
				</thead>
				<tbody>
					<?php
					foreach($array_true_false_KA as $country => $arrayValue){
						echo "<tr style='text-align: center; font-size: 11px; font-family: \'Times New Roman\', serif;' bgcolor=".color().">";
						//страна
						echo "<td>".$this->GeoIp->nameAndFlagsOfCode($country)."</td>";
						//КА или ложное срабатывание
						echo "<td>{$arrayValue[0]}</td><td>{$arrayValue[1]}</td>";
						echo "</tr>";
						}
					?>
				</tbody>
			</table>
			</div>
		</div>
		<?php 
		} else {
	?>
<!-- диаграмма -->
		<div id="container" style="position: relative; top: 0; width: 725px; margin-left: 2px; float: left;"></div>
<!-- строим диаграмму при AJAX запросе -->		
		<input id="elemHidden" type="hidden" value="getCharts('StackedBar', <?= $data ?>, <?= $objInfo ?>)">
	<?php
		}
	echo '</div>';
	}
}
?>