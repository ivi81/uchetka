<?php
							/*------------------------------------------*/
							/*  класс вывода статистической информации 	*/
							/*	 по разделу геопозиционирования GeoIP	*/
							/*      вывод статистической информации 	*/
							/*	       по IP-адресу назначения			*/
							/* 	 					v.0.1 11.11.2014    */
							/*------------------------------------------*/

class ShowStatisticsGeoIpDstIp extends StatisticsGeoIp
{
public function showInformation()
	{
	//выводим форму
	$this->showForm();
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
		?>
<!-- DIV формы -->
		<div style="position: relative; top: 0px; left: 0px; width: 720px;">	
<!-- интервал времени и даты -->	
			<div style="float: left; width: 480px; font-size: 12px; font-family: 'Times New Roman', serif;">
				<span style="position: relative; top: 0px; left: 0px; color: #000;">с</span>
				<input type="date" id="dateStart" style="width: 140px; height: 20px;"><input type="time" id="timeStart" value="00:00" style="width: 70px; height: 20px;">
				<span style="position: relative; top: 0px; left: 0px; color: #000;">по</span>
				<input type="date" id="dateEnd" style="width: 140px; height: 20px;"><input type="time" id="timeEnd" value="23:59" style="width: 70px; height: 20px;">
			</div>
<!-- IP-адрес назначения -->
			<div style="float: left; width: 160px;">
				<select id="ipDst" style="width: 150px; height: 25px; font-size: 12px; font-family: 'Times New Roman', serif;">
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
		<!-- кнопка 'поиск' -->
			<div style="position: relative; top: -1px; width: 50px; float: left;">
				<a href='#' onclick="return sendRequest(this)" class="buttonG" style="height: 16px; text-align: center; font-size: 14px;">поиск</a>
			</div>
<!-- если через Ajax -->
			<input id="areaSendRequest" type="hidden" 
			value="function sendRequest(elem){
					var obj = {};
					obj.fdateStart = document.getElementById('dateStart');
					obj.ftimeStart = document.getElementById('timeStart');
					obj.fdateEnd = document.getElementById('dateEnd');
					obj.ftimeEnd = document.getElementById('timeEnd');
					obj.fipDst = document.getElementById('ipDst');
					for(var a in obj){
						obj[a].style.borderColor = '';
						}
					//проверяем заполненность полей
					for(var a in obj){
						if(obj[a].value.length == 0){
							obj[a].style.borderColor = 'red';
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
					//Ajax запрос
					var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', '/<?= $this->directory ?>/<?= $this->getUserRole() ?>/process/ajax_process.php', elem, 
																		   'queryStatistics=GeoIp_1&dateStart=' + encodeURIComponent(obj.fdateStart.value) 
																		 + '&timeStart=' + encodeURIComponent(obj.ftimeStart.value)
																		 + '&dateEnd=' + encodeURIComponent(obj.fdateEnd.value)
																		 + '&timeEnd=' + encodeURIComponent(obj.ftimeEnd.value)
																		 + '&ipDst=' + encodeURIComponent(obj.fipDst.value));
					newObjectXMLHttpRequest.sendRequest();	
					return true;
					}">
			<script type="text/javascript" src='/<?= $this->directory ?>/js/objectXMLHttpRequest.js'></script>
			<script type="text/javascript">
				function sendRequest(elem){
					var obj = {};
					obj.fdateStart = document.getElementById('dateStart');
					obj.ftimeStart = document.getElementById('timeStart');
					obj.fdateEnd = document.getElementById('dateEnd');
					obj.ftimeEnd = document.getElementById('timeEnd');
					//obj.fipDst = document.getElementById('ipDst');
					for(var a in obj){
						obj[a].style.borderColor = '';
						}
					//проверяем заполненность полей
					for(var a in obj){
						if(obj[a].value.length == 0){
							obj[a].style.borderColor = 'red';
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
					//Ajax запрос
					var newObjectXMLHttpRequest = new objectXMLHttpRequest("POST", "/<?= $this->directory ?>/<?= $this->getUserRole() ?>/process/ajax_process.php", elem, 
																		   'queryStatistics=GeoIp_1&dateStart=' + encodeURIComponent(obj.fdateStart.value) 
																		 + '&timeStart=' + encodeURIComponent(obj.ftimeStart.value)
																		 + '&dateEnd=' + encodeURIComponent(obj.fdateEnd.value)
																		 + '&timeEnd=' + encodeURIComponent(obj.ftimeEnd.value)
																		 + '&ipDst=' + encodeURIComponent(document.getElementById('ipDst').value));
					newObjectXMLHttpRequest.sendRequest();	
					return true;
					}
			</script>
		</div>
		<?php
		//получаем статистичискую информацию
		$this->showData();
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	}

//статистическая информация по IP-адресу назначения
protected function showData()
	{
	try{
		//готовим SQL-запросы для получения основной информации
		$WHERE = $DATE_TIME = $IP_DST = '';
		//дата и время
		if(!empty($_POST['dateStart']) && !empty($_POST['dateEnd'])){
			//начальная дата и время
			$dateTimeStart = ExactilyUserData::takeDate($_POST['dateStart'])." ".ExactilyUserData::takeTime($_POST['timeStart']);

			//конечная дата и время
			$dateTimeEnd = ExactilyUserData::takeDate($_POST['dateEnd'])." ".ExactilyUserData::takeTime($_POST['timeEnd']);		
			$DATE_TIME = "`date_time_incident_start` BETWEEN STR_TO_DATE('$dateTimeStart', '%Y-%m-%d %H:%i:%s') 
					 	   AND STR_TO_DATE('$dateTimeEnd', '%Y-%m-%d %H:%i:%s')";
			$WHERE = 'WHERE';
			}
		$titleIp = 'для всех IP-адресов назначения';
		//IP-адрес назначения
		if(!empty($_POST['ipDst'])){
			foreach(ExactilyUserData::takeIP($_POST['ipDst']) as $ip){
				$IP_DST = "AND `ip_dst`='".ip2long($ip)."'";
				$WHERE = 'WHERE';
				$titleIp = ($this->XML->obtainDomainName($ip)) ? $this->XML->obtainDomainName($ip): $ip;
				$titleIp = '<br>IP-адрес назначения '.$titleIp;
				}
			}
		$queryIpDst = $this->DBO->connectionDB()->query("SELECT `country`, COUNT(`country`) AS COUNT FROM `incident_chief_tables` 
												        ".$WHERE." ".$DATE_TIME." ".$IP_DST." GROUP BY `country` ORDER BY COUNT DESC");
		$tbody = $flag = false;
		$arrayCountry = array();
		while($row = $queryIpDst->fetch(PDO::FETCH_OBJ)){
			if($row->country){
				$flag = true;
				$tbody .= "<tr style='text-align: center; font-size: 11px; font-family: \'Times New Roman\', serif;' bgcolor=".color().">
						   <td style=''>".$this->GeoIp->nameAndFlagsOfCode($row->country)." ({$row->country})</td>
						   <td style=''>{$row->COUNT}</td></tr>";
				$arrayCountry[$row->country] = $row->COUNT;
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

		if($DATE_TIME == ''){
			$title = 'количество IP-адресов источников по их геопренадлежности<br>за весь период времени '.$titleIp;
			} else {
			$title = 'количество IP-адресов источников по их геопренадлежности <br>'
					 .StatisticsGeoIP::showStringDateTime($dateTimeStart, $dateTimeEnd, false)
					 .'<br>'.$titleIp;
			}
		//выводимая вместе с графиком информация
		$odjInfo = "{ title: '".$title."' }";
		//проверяем найдена ли запрашиваемая информация
		if($flag == false) echo ShowMessage::informationNotFound('информация отсутствует', 180);
		?>
<!-- поле информации -->
		<div style="width: 725px; overflow: hidden;">
<!-- диаграмма -->
			<br><div id="container" style="width: 505px; margin-left: 2px; float: left;"></div>
<!-- строим диаграмму -->
			<script type='text/javascript' src='/<?= $this->directory ?>/js/hightChartsConstruction.js'></script>
<!-- строим диаграмму при AJAX запросе -->
			<input id="elemHidden" type="hidden" value="getCharts('Pie_type_1', <?= $dataCountry ?>, <?= $odjInfo ?>)">
<!-- строим диаграмму при обычной загрузке -->
			<script type="text/javascript">getCharts('Pie_type_1', <?= $dataCountry ?>, <?= $odjInfo ?>)</script>
<!-- таблица -->
			<div style="width: 210px; margin-left: 5px; display: inline-block; border-width: 1px; border-style: solid; border-color: #B7DCF7; clear: left;">
			<?php
			if($tbody){
				?>
				<table border="0" style="width: 210px;">
					<thead>
						<tr style='text-align: center; font-size: 10px; font-family: 'Times New Roman', serif;' <?= COLOR_HEADER ?> >
						<th style="width: 150px;">страна</th>
						<th style="width: 60px;">количество</th>
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