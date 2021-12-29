<?php
							/*--------------------------------------------------*/
							/*  	класс вывода статистической информации 		*/
							/*	 	по разделу геопозиционирования GeoIP		*/
							/*	 статистическая информация за отрезок времени	*/
							/*		  по странам и IP-адресам назначения		*/
							/* 	 					v.0.1 19.11.2014    		*/
							/*--------------------------------------------------*/

class ShowStatisticsGeoIpDateCountry extends StatisticsGeoIp
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
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	?>
<!-- DIV формы -->
	<div style="width: 720px; min-height: 80px;">
<!-- страна -->
		<div style="float: left; width: 200px; font-size: 12px; font-family: 'Times New Roman', serif;">
			<select id="countryCode" name="selectCountry[]" size="5" multiple="multiple" title="для того чтобы выбрать несколько стран удерживаете нажатой кнопку Ctrl" style="width: 170px; font-size: 12px; font-family: 'Times New Roman', serif;">
				<option value="">страна источник</option>
				<?php
				$GeoIP = $this->GeoIp;
				foreach($GeoIP::$codeCountry as $key => $value){
					echo "<option value='$key'>$value</option>";
					}
				?>
			</select>
		</div>	
<!-- IP-адрес назначения -->
		<div style="float: left; width: 160px;">
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
<!-- кнопка 'поиск' -->
		<div style="position: relative; top: 0; width: 50px; float: left;">
			<a href='#' onclick="return sendRequest(this)" class="buttonG" style="height: 16px; text-align: center; font-size: 14px;">поиск</a>
		</div>
<!-- для формирования Ajax запроса -->
		<input id="areaSendRequest" type="hidden" 
			value="function sendRequest(elem){
				var objCountryCode = document.getElementById('countryCode');
				if(objCountryCode.value == '' || objCountryCode.value == 'undefined'){
					objCountryCode.style.borderColor = 'red';
					return false;
					}
				var arraySelected = [];
				for(var i = 0; i < objCountryCode.length; i++){
					if(objCountryCode.options[i].selected == true){ 
						console.log(objCountryCode[i].value);
						arraySelected.push(objCountryCode[i].value);
						}
					}
				var code = arraySelected.join('_');
				//Ajax запрос
				var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', '/<?= $this->directory ?>/<?= $this->getUserRole() ?>/process/ajax_process.php', elem, 
																	   'queryStatistics=GeoIp_3' + '&countryCode=' + code + '&ipDst=' + document.getElementById('ipDst').value);
				newObjectXMLHttpRequest.sendRequest();	
				return true;
				}">
		<script type="text/javascript" src='/<?= $this->directory ?>/js/objectXMLHttpRequest.js'></script>
	</div>
	<?php
	//получаем статистическую информацию
	$this->showData();
	}

//функция возвращающая массив из заданного количества наиболее активных стран
protected function getNumActiveCounry($num)
	{
	$array = array();
	try{
		$query = $this->DBO->connectionDB()->query("SELECT `country`, COUNT(`country`) AS COUNT FROM `incident_chief_tables` 
													GROUP BY `country` ORDER BY COUNT DESC LIMIT 0, ".$num);
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$array[] = $row->country;
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	return $array;
	}

//статистическая информация по IP-адресу назначения
protected function showData()
	{
	//готовим SQL-запросы для получения основной информации
	$titleIpDst = $IP_DST = '';
	$flag = false;
	//получаем массив из самых активных стран, с которых идет наибольшее количество КА
	if(!empty($_POST['countryCode'])){
		if(strpos($_POST['countryCode'], '_')){
			$array_code_tmp = explode('_', $_POST['countryCode']);
			foreach($array_code_tmp as $code){
				if(preg_match('/^[A-Z]{2}$/', trim($code))){
					$array_code[] = $code;
					}
				}	
			} else {
			if(preg_match('/^[A-Z]{2}$/', trim($_POST['countryCode']))){
				$array_code[] = $_POST['countryCode'];
				}
			}
		} else {
		$array_code = $this->getNumActiveCounry(5);
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
		$titleIpDst = '<br>и направленных на '.$ipName;
		}	

	//для построение оси Y получаем список дат начиная с текущей даты и на один год назад
	$arrayMonth = array();
	for($a = 0; $a < 12; $a++){
		$arrayMonth[] = date('Y-m-d', strtotime('-'.$a.' month'));
		}

	$array_data = array();
	foreach($array_code as $code){
		$reguest = "SELECT `country`, `date_time_incident_start`, COUNT(`ip_src`) AS NUM 
					FROM `incident_chief_tables` WHERE `date_time_incident_start` BETWEEN STR_TO_DATE('".$arrayMonth[11]." 00:01:00', '%Y-%m-%d %H:%i:%s') 
					AND STR_TO_DATE('".$arrayMonth[0]." 23:59:00', '%Y-%m-%d %H:%i:%s') AND `country`='".$code."' ".$IP_DST." 
					GROUP BY date_format(date_time_incident_start, '%Y-%m') LIMIT 0, 12";
		//выполняем sql-запрос и сохраняем данные в массив $array_data
		$array_data[] = $this->sqlReguest($reguest, array('country', 'date_time_incident_start', 'NUM'));
		}

	//удаляем пустые массивы (если информация для выбранной страны не найдена)
	$e = 0;
	foreach($array_data as $key => $value){
		if(count($value) == 0){
			array_splice($array_data, ($key - $e), 1);
			$e++;
			}
		}

	//проверяем что есть по запросу 
	if((count($array_data) == 0) || (count($array_data) == 1 && count($array_data[0]) == 0)) echo ShowMessage::informationNotFound('информация отсутствует', 180);
	//подписи для графика (categories)
	$arrayDate = array();
	$array_categories = array();
	foreach($arrayMonth as $value){
		$arrayDate[] = substr($value, 0 , 7);
		}
	sort($arrayDate);

	//преобразуем массив дат в строку
	$categories = $this->getDateString($arrayDate);

	$dataCountry = '[';
	for($j = 0; $j < count($array_data); $j++){
		$array_data_tmp = array_fill(0, 12, '0');
		$dataCountry .= '{';
		$dataCountry .= "name: '".GeoIP::$codeCountry[$array_data[$j]['country'][0]]."', ";
		$dataCountry .= "data: ";
		for($i = 0; $i < count($arrayDate); $i++){
			$key = '';
			$key = array_search($arrayDate[$i], $array_data[$j]['date']);
			if($key === 0 || !empty($key)){
				$array_data_tmp[$i] = $array_data[$j]['NUM'][$key];
				}
			}
		//получаем ','
		$dataCountry .= str_replace("\"", "", json_encode($array_data_tmp));
		$dataCountry .= '}';	
		//получаем ','
		$dataCountry .= $this->getFix($array_data, $j);			
		}
	$dataCountry .= ']';
	//меняем "" кавычки на ''
	$categories = str_replace("\"", "'", $categories);
	$objInfo = "{ title: 'количество компьютерных атак с разных стран<br>за прошедший год сгруппированных по месяцам ".$titleIpDst."', 
				  titleY: 'компьютерные атаки (КА)',
				  dates: ".$categories.",
				  tooltip: ' КА'}";
	?>
<!-- поле информации -->
	<div style="padding-top: 10px; width: 735px; overflow: hidden;">	
<!-- диаграмма -->
		<div id="container" style="position: relative; top: 0; width: 725px; margin-left: 2px; float: left;"></div>
<!-- строим диаграмму при AJAX запросе -->		
		<input id="elemHidden" type="hidden" value="getCharts('SimpleLine', <?= $dataCountry ?>, <?= $objInfo ?>)">
	<br>
	</div>
	<?php
	}
}
?>