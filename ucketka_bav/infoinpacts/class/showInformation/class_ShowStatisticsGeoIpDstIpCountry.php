<?php
							/*------------------------------------------*/
							/*  класс вывода статистической информации 	*/
							/*	 по разделу геопозиционирования GeoIP	*/
							/*      вывод статистической информации 		*/
							/*	       по странам для IP-адресов 		*/
							/* 				  назначения				*/
							/* 	 					v.0.1 11.11.2014    */
							/*------------------------------------------*/

class ShowStatisticsGeoIpDstIpCountry extends StatisticsGeoIp
{
public function showInformation()
	{
	//выводим форму
	$this->showForm();
	}

//форма для вывода статистической информации по IP-адресу назначения
protected function showForm()
	{
	?>
<!-- DIV формы -->
	<div style="position: relative; top: 0; left: 0px; min-height: 50px; width: 720px;">
		<div style="float: left; width: 325px; font-size: 14px; font-family: 'Times New Roman', serif;">
<!-- интервалы времени -->
			<div style="width: 300px;">
				<div style="float: left; width: 200px;">
					<input type="radio" name="intervalDate" class="intervalDate" value="1">сутки
					<input type="radio" name="intervalDate" class="intervalDate" value="7">неделя
					<input type="radio" name="intervalDate" class="intervalDate" value="30">месяц
				</div>
				<div style="float: left; width: 100px; padding-top: 4px;"><a href="#" onclick="showFormIntervalDate()" class="getInterval">другой интервал</a></div>
			</div>
<!-- страна -->
			<div style="padding-top: 5px;">
			<select id="countryCode" style="width: 170px; height: 23px; font-size: 12px; font-family: 'Times New Roman', serif;">
				<option value="">страна источник</option>
				<?php 
				//объект БД GeoIP
				$GeoIP = $this->GeoIp;
				foreach($GeoIP::$codeCountry as $key => $value){
					echo "<option value='$key'>$value</option>";
					}
				?>
			</select>
			</div>
		</div>	
<!-- интервал времени и даты -->	
		<div id="setIntervalDate" style="float: left; width: 260px; display: none;">
			<div style="font-size: 12px; font-family: 'Times New Roman', serif;">
			<span style="padding-left: 12px; position: relative; top: 0px; left: 0px; color: #000;">с</span>
				<input type="date" id="dateStart" style="width: 140px; height: 20px;"><input type="time" id="timeStart" value="00:00" style="width: 70px; height: 20px;">
			</div>
			<div style="padding-top: 3px; font-size: 12px; font-family: 'Times New Roman', serif;">
				<span style="padding-left: 5px; position: relative; top: 0px; left: 0px; color: #000;">по</span>
				<input type="date" id="dateEnd" style="width: 140px; height: 20px;"><input type="time" id="timeEnd" value="23:59" style="width: 70px; height: 20px;">
			</div>
		</div>
<!-- кнопка 'поиск' -->
		<div style="position: relative; top: -1px; width: 50px; float: left; padding-top: 2px;">
			<a href='#' onclick="return sendRequest(this)" class="buttonG" style="height: 16px; text-align: center; font-size: 14px;">поиск</a>
		</div>
<!-- вывод интервала времени -->
		<input id="areaIntervalDate" type="hidden" 
			value="function showFormIntervalDate(){ 
				var div = document.getElementById('setIntervalDate');
				var button = div.parentNode.getElementsByTagName('DIV');
				if(div.style.display == 'none'){ 
					div.style.display = 'block'; 
					button[8].style.paddingTop = '12px';
					} else { 
					div.style.display = 'none'; 
					button[8].style.paddingTop = '2px';
					}
				}">
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
				var countryCode = document.getElementById('countryCode').value;
				if(countryCode == 'undefined') countryCode = 'RU';
				if(checked != false){
					//Ajax запрос
					var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', '/<?= $this->directory ?>/<?= $this->getUserRole() ?>/process/ajax_process.php', elem, 
																		   'queryStatistics=GeoIp_2&intervalDate=' + checked + '&countryCode=' + countryCode);
					newObjectXMLHttpRequest.sendRequest();	
					return true;
					}
				}">
		<script type="text/javascript" src='/<?= $this->directory ?>/js/objectXMLHttpRequest.js'></script>
	</div>
	<?php
	//получаем статистичискую информацию
	$this->showData();
	}

//статистическая информация по IP-адресу назначения
protected function showData()
	{
	try{
		//готовим SQL-запросы для получения основной информации
		$DATE_TIME = $titleData = '';
		//по умолчанию поиск осуществляется для России
		$COUNTRY = 'RU';		
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
		if(!empty($_POST['countryCode'])){
			if(preg_match('/^[A-Z]{2}$/', trim($_POST['countryCode']))) $COUNTRY = $_POST['countryCode'];
			}
		$query = $this->DBO->connectionDB()->query("SELECT `ip_dst`, COUNT(ip_src) AS COUNT_IP_SRC FROM `incident_chief_tables` 
													WHERE ".$DATE_TIME." `country`='".$COUNTRY."' GROUP BY `ip_dst` ORDER BY `COUNT_IP_SRC` DESC");
		$tbody = $flag = false;
		$arrayCountry = array();
		$GeoIP = $this->GeoIp;
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			if(!empty($row->ip_dst)){
				$flag = true;
				$ip = long2ip($row->ip_dst);
                if($this->XML->obtainDomainName($ip)){
                    $name = (string) $this->XML->obtainDomainName($ip);
                    $domainName = "{$ip}<br><span style='font-weight: bold;'>({$name})</span>";
				    $arrayCountry[$name] = $row->COUNT_IP_SRC;
				    } else {
                    $domainName = $ip;
                    $arrayCountry[$ip] = $row->COUNT_IP_SRC;
                    }
                $tbody .= "<tr style='text-align: center; font-size: 11px; font-family: \'Times New Roman\', serif;' bgcolor=".color().">
					   	   <td style=''>{$domainName}</td>
						   <td style=''>{$row->COUNT_IP_SRC}</td></tr>";
				}
			}
		$a = 0;
		$arrayLength = count($arrayCountry) - 1;
		$dataCountry = '[';
		foreach($arrayCountry as $country => $count){
			$dataCountry .= "[ '{$country}', {$count} ]";
			//получаем ','
			$dataCountry .= $this->getFix($arrayCountry, $a);
			$a++;
			}
		$dataCountry .= ']';
		//выводимая вместе с графиком информация
		$objInfo = "{ title: 'IP-адреса источники, принадлежность - ".$GeoIP::$codeCountry[$COUNTRY].$titleData."' }";
		//проверяем найдена ли запрашиваемая информация
		if($flag == false) echo ShowMessage::informationNotFound('информация отсутствует', 180);
		?>
<!-- поле информации -->
		<div style="width: 725px; overflow: hidden;">
<!-- диаграмма -->
			<div id="container" style="padding-top: 10px; width: 505px; margin-left: 2px; float: left;"></div><br>
<!-- строим диаграмму -->
			<script type='text/javascript' src='/<?= $this->directory ?>/js/hightChartsConstruction.js'></script>
<!-- строим диаграмму при AJAX запросе -->		
			<input id="elemHidden" type="hidden" value="getCharts('Pie_type_1', <?= $dataCountry ?>, <?= $objInfo ?>)">
<!-- таблица -->
			<div style="position: relative; top: -10px; width: 210px; margin-left: 5px; display: inline-block; border-width: 1px; border-style: solid; border-color: #B7DCF7; clear: left;">
			<?php
			if($tbody){
				?>
				<table border="0" style="width: 210px;">
					<thead>
						<tr style="text-align: center; font-size: 10px; font-family: 'Times New Roman', serif;" <?= COLOR_HEADER ?> >
						<th style="width: 150px;">IP-адрес назначения</th>
						<th style="width: 60px;">количество IP-адресов источников</th>
						</tr>
					</thead>
					<tbody>
						<?= $tbody ?>
					</tbody>
				</table>
				<?php
				}
			?>
			</div>
		</div>
		<?php
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	}
}
?>