<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>компьютерное воздействие №<?php echo $_POST['showAllInformation']; ?></title>
		<?php
		//получаем корневую директорию сайта
		$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
		?>
		<link rel="stylesheet" type="text/css" href="/<?= $array_directory[1] ?>/css/style_index.css"></link>
			<style type="text/css">
				html, body { height: 100%; margin: 0; }
			</style>	
	</head>
	<body>
<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		скрипт вывода на страницу всей найденной по запросу информации	*/
						/*												v.0.1 18.08.2014	 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//подключаем config
require_once ($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/config/config.php");

session_start();

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

//объект для подключения к БД
$DBO = new DBOlink();

//объект БД GeoIP
$GeoIP = new GeoIP();

//объект для чтения файла setup_site.xml
$ReadXMLSetup = new ReadXMLSetup;
?>
<!-- основная цветовая подложка -->
<div class="allInfoArea">
	<div style="position: relative; top: 10px; left: 10px; z-index: 10; width: 980px; display: table; vertical-align: middle;	border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">	
		<br>				
		<span style="position: absolute; left: 235px; font-size: 20px; font-family: 'Times New Roman', serif; letter-spacing: 2px; border-radius: 3px; color: #0000CC;">
			информация по компьютерному воздействию №	 <?php echo $_POST['showAllInformation']; ?>			
		</span><br><br>
		<table border="0" width="960px" align="center">
			<tr>
<!-- дата добавления -->
				<td class="mapFullInformationText" style="text-align: right; width: 302px; padding-right: 10px;">
					добавлен
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px;">
				<?php
				//массив полученный в результате запроса даты добавления воздействия и логина пользователя 
				$array_date_name = AllInformationForIncident::showUserNameAddInformation($DBO, $_POST['showAllInformation']);				
				//вывод даты добавления
				echo ConversionData::showDateConvert($array_date_name['date_create']);
				?>		
				</td>
			</tr>
			<tr>
<!-- Ф.И.О добавившего воздействие -->
				<td class="mapFullInformationText" style="text-align: right; width: 302px; padding-right: 10px;">
					оперативный дежурный
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px;">
				<?php 
				//кем добавлено воздействие
				echo $ReadXMLSetup->usernameFIO($array_date_name['login_name']); 
				?>
				</td>
			</tr>
			<tr>
<!-- интервал времени воздействия -->
				<th class="mapFullInformationText" style="width: 302px; background: #FFEDB9;">
				начало компьютерного воздействия (дата/время)
				</th>
				<th class="mapFullInformationText" style="width: 302px; background: #FFEDB9;">
				конец компьютерного воздействия (дата/время)						
				</th>
			</tr>
			<tr>
				<td class="mapFullInformationText" style="text-align: center; width: 302px;">
				<?php
				//получаем массив содержащий начальную и конечную дату и время, IP-адреса источников и назначения и тип воздействия
				$array_ip_and_date = AllInformationForIncident::showDateStartAndEndAndIpSrcIpDst($DBO, $_POST['showAllInformation']);
				//дата и время начала инцидента
				echo "с ".substr($array_ip_and_date['date_start'][0], -8, 8);
				echo " ".ConversionData::showDateConvert(strtotime($array_ip_and_date['date_start'][0]));
				?>
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px;">
				<?php
				//дата и время конца воздействия
				echo "по ".substr($array_ip_and_date['date_end'][0], -8, 8);
				echo " ".ConversionData::showDateConvert(strtotime($array_ip_and_date['date_end'][0]));
				?>
				</td>
			</tr>
			<tr>
<!-- IP-адреса источников и назначения -->
				<th class="mapFullInformationText" style="width: 302px; background: #FFEDB9;">
				IP-адреса источники / количество обращений
				</th>
				<th class="mapFullInformationText" style="width: 302px; background: #FFEDB9;">
				IP-адрес назначения
				</th>
			</tr>
			<tr>
				<td class="mapFullInformationText" style="text-align: center; width: 302px;">
				<?php
				//IP-адреса источников
				for($i = 0; $i < count($array_ip_and_date['ip_src']); $i++){
					echo long2ip($array_ip_and_date['ip_src'][$i])." (".$GeoIP->countryIP($DBO, $array_ip_and_date['ip_src'][$i]); ?> 
					<img src= <?php echo "/{$array_directory[1]}/img/flags/".$GeoIP->flags(); ?> />) / 
					<span style='color: #FF0000;'>
					<?php
					echo ($array_ip_and_date['count_impact'][$i] == 0) ? "нет данных" : $array_ip_and_date['count_impact'][$i];
					?>
					</span><br>
					<?php	
					}
				?>
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px;">
				<?php
				$ipDst = long2ip($array_ip_and_date['ip_dst'][0]);
				//IP-адреса назначения
				echo $ipDst.'<br>';
				//и доменное имя если оно есть
				echo ($ReadXMLSetup->obtainDomainName($ipDst) != '') ? $ReadXMLSetup->obtainDomainName($ipDst).'<br>' : '';
				//принадлежность определенному сенсору
				$GetSensorInformation = new GetSensorInformation;
				echo $GetSensorInformation->getInformationForIp($ipDst);
				?>
				</td>
			</tr>
			<tr>
<!-- доступность информационного ресурса -->
				<td class="mapFullInformationText" style="text-align: right; width: 302px; padding-right: 10px;">
				доступность информационного ресурса
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px;">
				<?php
				$array_space_traffic = AllInformationForIncident::showSpaceSafeNetTraffic($DBO, $_POST['showAllInformation']);
				//доступность Web-ресурса						
				//	1 - доступен
				// 2 - недоступен
				echo ($array_space_traffic['availability_host'][0] == 1) ? "информационный ресурс доступен" : "<span style='color: red;'>зафиксирована недоступность информационного ресурса</span>";
				?>						
				</td>
			</tr>
			<tr>
<!-- направление воздействия -->
				<td class="mapFullInformationText" style="text-align: right; width: 302px; padding-right: 10px;">
				направление воздействия
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px;">
				<?php
				//информация о направлении воздействия
				//1 - input home network
				//0 - output home network
				echo ($array_space_traffic['direction_attack'][0] == 1) ? "воздействие направленно к home network" : "воздействие выполнялось из home network";
				?>									
				</td>
			</tr>
<!-- принятое решение -->
			<?php
			if(!empty($array_space_traffic['solution'])){
			?>
			<tr>
				<td class="mapFullInformationText" style="text-align: right; width: 302px; background: #FFE4E1; padding-right: 10px;">
				принятое решение
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px; background: #FFE4E1;">
				<?php echo $array_space_traffic['solution']; ?>									
				</td>
				</tr>
				<?php	
				}
			if(!empty($array_space_traffic['number_mail_in_CIB'])){
			?>
			<tr>
<!-- № письма в 18 Центр ФСБ России -->
				<td class="mapFullInformationText" style="text-align: right; width: 302px; background: #FFE4E1; padding-right: 10px;">
				номер письма в 18 Центр ФСБ России						
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px; background: #FFE4E1;">
				<?php echo "№149/2/1/".$array_space_traffic['number_mail_in_CIB']; ?>									
				</td>
			</tr>
			<?php	
				}
			if(!empty($array_space_traffic['number_mail_in_organization'])){
			?>
			<tr>
<!-- № письма в стороннюю организацию -->
				<td class="mapFullInformationText" style="text-align: right; width: 302px; background: #FFE4E1; padding-right: 10px;">
				номер письма в стороннюю организацию					
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px; background: #FFE4E1;">
				<?php echo $array_space_traffic['number_mail_in_organization']; ?>									
				</td>
			</tr>
			<?php	
				}
			if(!empty($array_space_traffic['explanation'])){
			?>
			<tr>
<!-- пояснение дежурного -->
				<td class="mapFullInformationText" style="text-align: right; width: 302px; padding-right: 10px;">
				пояснение дежурного						
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px;">
				<?php 
				echo FormattingText::formattingTextLength($array_space_traffic['explanation'], 60);	
				?>									
				</td>
			</tr>
			<?php	
				}
			?>
			<tr>
<!-- место нахождения отфильтрованного сетевого трафика -->
				<td class="mapFullInformationText" style="text-align: right; width: 302px; padding-right: 10px;">
				место нахождения отфильтрованного сетевого трафика						
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px;">
				<?php
				//место нахождения отфильтрованного сетевого трафика
				echo FormattingText::formattingTextLength($array_space_traffic['space_safe'], 60);	
				?>									
				</td>
			</tr>
			<tr>
<!-- Ф.И.О. выполнившего анализ сетевого трафика -->
				<td class="mapFullInformationText" style="text-align: right; width: 302px; padding-right: 10px;">
				анализ сетевого трафика выполнил						
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px;">
				<?php
				//кем выполнен анализ сетевого трафика
				$array_space_traffic = AllInformationForIncident::showAllInformationAnalyst($DBO, $_POST['showAllInformation']);
				echo $ReadXMLSetup->usernameFIO($array_space_traffic['login_name'][0]);
				?>									
				</td>
			</tr>
<!-- дата выполнения анализа сетевого трафика -->
			<?php
			if($array_space_traffic['date_time_analyst'][0]){
				?>
			<tr>
				<td class="mapFullInformationText" style="text-align: right; width: 302px; padding-right: 10px;">
				дата анализа						
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px;">
				<?php
				//дата  анализа сетевого трафика
				echo ConversionData::showDateConvert($array_space_traffic['date_time_analyst'][0]); 
				?>									
				</td>
			</tr>
				<?php
			}
			?>
			<tr>
<!-- количество пакетов информационной безопасности по мнению аналитика -->
				<td class="mapFullInformationText" style="text-align: right; width: 302px; padding-right: 10px;">
				количество пакетов информационной безопасности по мнению аналитика						
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px;">
				<?php
				//количество пакетов информационной безопасности по мнению аналитика	
				echo ($array_space_traffic['count_alert_analyst'][0] == 0) ? "<span style='color: #000;'>подсчет не производился</style>" : "<span style='color: #FF0000;'>".$array_space_traffic['count_alert_analyst'][0]."</span>";	
				?>									
				</td>
			</tr>
			<tr>
<!-- мнение аналитика -->
				<td class="mapFullInformationText" style="text-align: right; width: 302px; background: #CCFFFF; padding-right: 10px;">
				мнение аналитика по компьютерному воздействию
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px; background: #CCFFFF;">
				<?php
				if($array_space_traffic['true_false'][0] == "1"){
					echo "компьютерная атака";
					}
				elseif($array_space_traffic['true_false'][0] == "2"){
					echo "ложное срабатывание";
					}
				elseif($array_space_traffic['true_false'][0] == "3"){
					echo "отсутствует сетевой трафик";								
					}	
				?>									
				</td>
			</tr>
<!-- типа компьютерной атаки -->
			<?php
			//вывод информации только если компьютерная атака
			if($array_space_traffic['true_false'][0] == "1"){
			?>
			<tr>
				<td class="mapFullInformationText" style="text-align: right; width: 302px; background: #CCFFFF; padding-right: 10px;">
				тип компьютерной атаки	
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px; background: #CCFFFF;">
				<?php 
				$typeKa = $ReadXMLSetup->giveTypeKAForId($array_ip_and_date['type_attack'][0]);
				echo ($typeKa) ? $typeKa : 'тип компьютерной атаки не определен'; 
				?>
				</td>
			</tr>
			<?php
				}
			?>
			<tr>
<!-- информация аналитика -->
				<td class="mapFullInformationText" style="text-align: right; width: 302px; background: #CCFFFF; padding-right: 10px;">
				информация аналитика
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px; background: #CCFFFF;">
				<?php
				//дополнительная информация аналитика	
				echo FormattingText::formattingTextLength($array_space_traffic['information_analyst'][0], 60);	
				?>									
				</td>
			</tr>
			<tr>
<!-- номер сигнатуры/количество срабатываний -->
				<th class="mapFullInformationText" style="width: 302px; background: #FFEDB9;">
				номер сигнатуры / количество срабатываний
				</th>
				<th class="mapFullInformationText" style="width: 302px; background: #FFEDB9;">
				краткое описание сигнатуры
				</th>
			</tr>
			<?php
			//полная информация о сигнатурах						
			$array_signature = AllInformationForIncident::showSignature($DBO, $_POST['showAllInformation']);
			$num = count($array_signature['sid']);
			//номер сигнатуры / количество срабатываний
			for($i = 0; $i < $num; $i++){
				?>
				<tr>
				<td class="mapFullInformationText" style="text-align: center; width: 302px;">
					<?php
					echo $array_signature['sid'][$i]." / <span style='color: #FF0000;'>";
					echo ($array_signature['count_alert'][$i] == 0) ? "нет данных" : $array_signature['count_alert'][$i];
					echo "</span></td>";
					?>
					<td class="mapFullInformationText" style="text-align: left; width: 302px; padding-left: 10px;">
					<?php
					echo " - ".$array_signature['short_message'][$i]."</td></tr>";							
					}						
				?>
		</table><br>
	</div><br>
</div><br>
<?php
//закрываем соединения с БД
$DBO->onConnectionDB();
?>
	</body>
</html>