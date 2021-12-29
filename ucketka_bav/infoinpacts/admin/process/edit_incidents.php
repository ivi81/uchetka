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

						/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		скрипт редактирования компьютерных воздействий	 */
						/*								v.0.1 02.06.2014	 	 */
						/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));

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

if(!isset($_POST['editAllInformation'])){
	if(isset($_GET['editAllInformation'])){
		$id = $_GET['editAllInformation'];
		} else {
		echo MessageErrors::userMessageError(MessageErrors::ERROR_NOIDENTIF,"\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: отсутствуют идентификационный номер компьютерного воздействия");
		}
	} else {
	$id = $_POST['editAllInformation'];
	}
?>
<!-- основная цветовая подложка -->
	<div class="allInfoArea">
<!-- область для вывода информации по компьютерному воздействию -->
		<div style="position: relative; top: 0px; left: 10px; z-index: 10; width: 980px; border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">
			<span style="position: absolute; top: 10px; left: 330px; font-size: 20px; font-family: 'Times New Roman', serif; letter-spacing: 2px; border-radius: 3px; color: #0000CC;">
			компьютерное воздействие №<?php echo intval($id); ?>
			</span>
<!-- таблица содержащая всю информацию -->
			<div style="position: relative; top: 40px; left: 20px; margin: 10px 0px;">
				<form name="changeIncident" method="POST" action="edit_incidents_process.php" onsubmit="return validateForm()">				
				<table border="0" width="940px">
					<tr>
<!-- дата добавления компьютерного воздействия -->
						<th style="width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
						дата добавления компьютерного воздействия
						</th>
<!-- Ф.И.О. добавившего компьютерное воздействие -->
						<th style="width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
						Ф.И.О. добавившего компьютерное воздействие
						</th>
					</tr>
					<tr>
<!-- дата добавления компьютерного воздействия -->
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">						<?php
						//получаем массив содержащий начальную и конечную дату и время, IP-адреса источников и назначения и тип воздействия
						$array_ip_and_date = AllInformationForIncident::showDateStartAndEndAndIpSrcIpDst($DBO, $id);
						//вывод даты добавления
						echo ConversionData::showDateConvert($array_ip_and_date['date_time_create']); 
						?>
						</th>
<!-- Ф.И.О. добавившего компьютерное воздействие -->
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<?php
						$array_space_traffic = AllInformationForIncident::showSpaceSafeNetTraffic($DBO, $id);
						//место нахождения отфильтрованного сетевого трафика
						echo $ReadXMLSetup->usernameFIO($array_space_traffic['login_name']);
						?>									
						</th>
					</tr>
					<tr>
<!-- интервал времени воздействия -->
						<th style="width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
						начало компьютерного воздействия (дата/время)
						</th>
						<th style="width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
						конец компьютерного воздействия (дата/время)						
						</th>
					</tr>
					<tr>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<?php
						//начальная дата и время
						$dateTimeStart = substr($array_ip_and_date['date_start'][0], 0 , 4);
						$dateTimeStart .=	"-".substr($array_ip_and_date['date_start'][0], 5 , 2);		
						$dateTimeStart .= "-".substr($array_ip_and_date['date_start'][0], 8 , 2);
						//конечное дата и время
						$dateTimeEnd = substr($array_ip_and_date['date_end'][0], 0 , 4);
						$dateTimeEnd .=	"-".substr($array_ip_and_date['date_end'][0], 5 , 2);		
						$dateTimeEnd .= "-".substr($array_ip_and_date['date_end'][0], 8 , 2);
						?>
						<input type="date" name="dateStart" style="width: 140px; height: 20px;" value="<?php echo $dateTimeStart; ?>"><input type="time" name="timeStart" value="<?= substr($array_ip_and_date['date_start'][0], -8, 8) ?>" style="width: 70px; height: 21px;">
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<input type="date" name="dateEnd" style="width: 140px; height: 20px;" value="<?php echo $dateTimeEnd; ?>"><input type="time" name="timeEnd" value="<?= substr($array_ip_and_date['date_end'][0], -8, 8) ?>" style="width: 70px; height: 21px;">
						</td>
					</tr>
					<tr>
<!-- IP-адреса источников и назначения -->
						<th style="width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
						<span onclick="delSrcIP()"><img src='<?php echo "/{$array_directory[1]}/img/button_minus.png"; ?>' style="cursor: pointer;" title="удалить IP-адрес"/></span>
						IP-адреса источники / количество воздействий
						<span onclick="addSrcIP()"><img src='<?php echo "/{$array_directory[1]}/img/button_plus.png"; ?>' style="cursor: pointer;" title="добавить IP-адрес" /></span>
						</th>
						<th style="width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
						IP-адрес назначения
						</th>
					</tr>
					<tr>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<div id="srcIp">						
						<?php
						//IP-адреса источников
						for($i = 0; $i < count($array_ip_and_date['ip_src']); $i++){
							?>
							<div>
							<input type="text" name="ipSrc[]" value="<?= long2ip($array_ip_and_date['ip_src'][$i]);?>" style="width: 100px; height: 18px">
							<?php echo "(".$GeoIP->countryIP($DBO, $array_ip_and_date['ip_src'][$i]); ?>  
							
							<img src= <?php echo "/{$array_directory[1]}/img/flags/".$GeoIP->flags(); ?> />) / 
								<?php
								if($array_ip_and_date['count_impact'][$i] == 0){
									$count_impact = 0;
									} else { 
									$count_impact = $array_ip_and_date['count_impact'][$i];
									} 
								?>
							<input type="text" name="ipNum[]" value="<?= $count_impact;?>" style="width: 60px; height: 18px">
							<br>
							</div>
							<?php	
							}
						?>
						</div>
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<!-- IP-адреса назначения -->
							<input type="text" name="ipDst" value="<?= long2ip($array_ip_and_date['ip_dst'][0])?>" style="width: 100px; height: 18px">
						<br>
						<?php
						//и доменное имя если оно есть
						if($domaiName = $ReadXMLSetup->obtainDomainName(long2ip($array_ip_and_date['ip_dst'][0]))){
							?><span style="background: #FFE4E1;"> <?= $domaiName ?></span><?php
							}
						?>
						</td>
					</tr>
					<?php
					$array_analyst = AllInformationForIncident::showAllInformationAnalyst($DBO, $id);
					//если в таблице incident_analyst_table есть запись по данному инциденту
					if(count($array_analyst) != 0){
					?>
					<tr>
<!-- Ф.И.О. выполнившего анализ сетевого трафика -->
						<td style="text-align: right; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						анализ сетевого трафика выполнил
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<?php
						//место нахождения отфильтрованного сетевого трафика
						echo $ReadXMLSetup->usernameFIO($array_analyst['login_name'][0]);
						?>									
						</td>
					</tr>
<!-- дата выполнения анализа сетевого трафика -->
				<?php
				if($array_analyst['date_time_analyst'][0]){
					?>
					<tr>
						<td style="text-align: right; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						дата анализа						
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<?php
						//дата анализа сетевого трафика
						echo ConversionData::showDateConvert($array_analyst['date_time_analyst'][0]); 
						?>									
						</td>
					</tr>
					<?php
					}
					?>
					<tr>
<!-- количество пакетов информационной безопасности по мнению аналитика -->
						<td style="text-align: right; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						количество пакетов информационной безопасности по мнению аналитика						
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<input type="text" name="analystCount" value="<?= $array_analyst['count_alert_analyst'][0] ?>" style="width: 50px; height: 18px">
						</td>
					</tr>
					<tr>
<!-- мнение аналитика -->
						<td style="text-align: right; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #CCFFFF;">
						мнение аналитика по компьютерному воздействию&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
						<td style="text-align: left; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #CCFFFF;">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php ListBox::listSolutionAnalyst($array_analyst['true_false'][0]); ?>			
						</td>
					</tr>
					<tr>
<!-- информация аналитика -->
						<td style="text-align: right; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #CCFFFF;">
						информация аналитика&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
						<td style="text-align: left; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #CCFFFF;">&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="text" name="analystInfo" value="<?= $array_analyst['information_analyst'][0];?>" style="width: 410px; height: 18px">
						</td>
					</tr>
					<?php } ?>
					<tr>
<!-- тип компьютерного воздействия -->					
						<td style="text-align: right; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						тип компьютерного воздействия&nbsp;&nbsp;&nbsp;&nbsp;					
						</td>
						<td style="text-align: left; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php ListBox::listTypeKA($array_ip_and_date['type_attack'][0]) ?>								
						</td>
					</tr>
					<tr>
<!-- доступность информационного ресурса -->
						<td style="text-align: right; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						доступность информационного ресурса&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
						<td>
						&nbsp;&nbsp;&nbsp;&nbsp;
<!--
1 - доступен
2 - недоступен
-->	
						<select name="ping" style="width: 100px; height: 23px;">
						<?php 
						if($array_space_traffic['availability_host'] == 1){
							?>
							<option value="1" selected style="background: #B0E2FF;"; >доступен</option>
							<option value="2">недоступен</option>
							<?php
						} else {
							?>
							<option value="1">доступен</option>
							<option value="2" selected style="background: #B0E2FF;"; >недоступен</option>
							<?php
						}
						?>
						</select>
						</td>					
					</tr>
					<tr>
<!-- направление воздействия -->
						<td style="text-align: right; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						направление компьютерного воздействия&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
<!--
1 - input home network
0 - output home network
-->
						<td>
						&nbsp;&nbsp;&nbsp;&nbsp;
						<select name="direction" style="width: 170px; height: 23px;">
						<?php 
						if($array_space_traffic['direction_attack'] == 1){
							?>
							<option value="1" selected style="background: #B0E2FF;"; >к домашней сети</option>
							<option value="0">из домашней сети</option>
							<?php
						} else {
							?>
							<option value="1">к домашней сети</option>
							<option value="0" selected style="background: #B0E2FF;"; >из домашней сети</option>
							<?php
						}
						?>
						</select>
						</td>					
					</tr>
					<tr>
<!-- номер письма в 18 Центр ФСБ России -->
						<td style="text-align: right; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						номер письма в 18 Центр ФСБ России №149/2/1/&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
						<td>
						&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="text" name="number_mail_in_CIB" value="<?= $array_space_traffic['number_mail_in_CIB'];?>" style="width: 250px; height: 18px">
						</td>					
					</tr>
					<tr>
<!-- номер письма в стороннюю организацию -->
						<td style="text-align: right; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						номер письма в стороннюю организацию&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
						<td>
						&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="text" name="number_mail_in_organization" value="<?= $array_space_traffic['number_mail_in_organization'];?>" style="width: 250px; height: 18px">
						</td>					
					</tr>
					<tr>
<!-- пояснение дежурного по компьютерному воздействию -->
						<td style="text-align: right; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						пояснение дежурного по компьютерному воздействию&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
						<td>
						&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="text" name="explanation" value="<?= $array_space_traffic['explanation'];?>" style="width: 250px; height: 18px">
						</td>					
					</tr>
					<tr>
<!-- номер сигнатуры / количество срабатываний -->
						<th style="width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
						<span onclick="delSid()"><img src='<?php echo "/{$array_directory[1]}/img/button_minus.png"; ?>' title="удалить IP-адрес" style="cursor: pointer;"/></span>
						номер сигнатуры / количество срабатываний
						<span onclick="addSid()"><img src='<?php echo "/{$array_directory[1]}/img/button_plus.png"; ?>' title="добавить IP-адрес" style="cursor: pointer;"/></span>						
						</th>
						<th style="width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
						краткое описание сигнатуры						
						</th>
					</tr>
					<tr>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<?php
						//полная информация о сигнатурах						
						$array_signature = AllInformationForIncident::showSignature($DBO, $id);
						$num = count($array_signature['sid']);
						//номер сигнатуры / количество срабатываний
						for($i = 0; $i < $num; $i++){
							?>
							<tr id="nameSid">
							<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
							<input type="text" name="sid[]" value="<?= $array_signature['sid'][$i]?>" style="width: 70px; height: 18px"><?php
							if($array_signature['count_alert'][$i] == 0){
								$countAlert = 0;							
								} else { 
								$countAlert = $array_signature['count_alert'][$i];
								}
							?>&nbsp;&nbsp;/&nbsp;&nbsp;<input type="text" name="sidNum[]" value="<?= $countAlert?>" style="width: 50px; height: 18px"></td>							
							<td style="text-align: left; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
							<?php
							echo " - ".$array_signature['short_message'][$i]."</td></tr>";							
							}						
							?>
						</td>
					</tr>
					<tr id="stopId">
<!-- место нахождения отфильтрованного сетевого трафика -->					
						<td style="text-align: right; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						место нахождения отфильтрованного сетевого трафика&nbsp;&nbsp;&nbsp;&nbsp;					
						</td>
						<td style="text-align: left; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="text" name="spaceSafe" value="<?= $array_space_traffic['space_safe'];?>" style="width: 250px; height: 18px">									
						</td>
					</tr>
<!-- кнопка "сохранить" -->
					<tr>
						<td style="text-align: center; width: 50%;">
<!-- информационное сообщение общего характера -->
						<span style="font-size: 10px;	font-family: Verdana, serif; color: #CD0000;" id="message"></span>
						</td>
						<td style="text-align: left; width: 50%;">
						<br><input type="submit" name="save" style="width: 100px; height: 20px;" value="сохранить">
						</td>
					</tr>
				</table>
<!-- поле с номером инцидента -->
				<?php echo "<input type='hidden' name='editInpacts' value='".$id."'>"; ?>
				</form><br><br><br>	
			</div>
		</div>
	</div>
<!-- подключаем файл функциями добавления и удаления элементов -->
<script type="text/javascript" src='/<?= $array_directory[1] ?>/js/changeNumElements.js'></script>

<!-- проверка формы -->
<script type="text/javascript" >
		
	//функция проверяющая заполненные формы
	function validateForm()
		{
		var colors = 'red';
		var o = { dateStart: document.changeIncident.dateStart,
				  dateEnd: document.changeIncident.dateEnd,
				  ipSrc: document.changeIncident.ipSrc,
				  ipDst: document.changeIncident.ipDst };
		//получаем список элементов формы
		var elem = document.changeIncident.elements;
		var flag = false;
		//определяем значение по умолчанию для выбранного элемента
		elem.typeKA.defaultValue = "<?= $array_ip_and_date['type_attack'][0] ?>";
		elem.solution.defaultValue = "<?= $array_analyst['true_false'][0] ?>";
		elem.ping.defaultValue = "<?= $array_space_traffic['availability_host'] ?>";
		elem.direction.defaultValue = "<?= $array_space_traffic['direction_attack'] ?>";
		//убираем выделение для всех полей
		for(var i = 0; i < elem.length; i++){
			elem[i].style.borderColor = '';
			document.getElementById('message').innerHTML = '';
			//проверяем изменил ли пользователь какие либо значения
			if(elem[i].defaultValue != elem[i].value){
				flag = true;
				}
			}
			if(flag == false){
				document.getElementById('message').innerHTML = 'не было сделано ни одного изменения';
				return false;
				}				
			
		var numPattern = /^[0-9\s|\n]+$/;			
		var ipPattern = /^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}[\s|\n]+$/;

		for(var i = 0; i < elem.length; i++){
			//проверяем все поля, кроме поля "информация аналитика", на пустоту
			if(elem[i].name != 'analystInfo' && elem[i].name != 'number_mail_in_CIB' 
			   && elem[i].name != 'number_mail_in_organization' && elem[i].name != 'explanation'){
				if(elem[i].value == '' && elem[i].type != 'select-one'){
					elem[i].style.borderColor = colors;
					document.getElementById('message').innerHTML = 'пустое поле';
					return false;
					} else {
					document.getElementById('message').innerHTML = '';	
					}
				}
			//проверяем начальную и конечную дату
			if(elem[i].type == 'date' ){
				var dStart = new Date(o.dateStart.value);
				var dEnd = new Date(o.dateEnd.value);
				//начальная дата не может быть больше конечной
				if(dStart > dEnd){
					o.dateStart.style.borderColor = colors;
					o.dateEnd.style.borderColor = colors;
					document.getElementById('message').innerHTML = 'начальная дата больше конечной';
					return false;
					} else {
					document.getElementById('message').innerHTML = "";				
					}
				}
			//проверяем IP-адреса
			if(elem[i].name == 'ipSrc[]' || elem[i].name == 'ipDst'){
				//проверяем содержимое полей на соответствие IP-адресу
				if(!ipPattern.test(elem[i].value + '\n')){
					elem[i].style.borderColor = colors;
					document.getElementById('message').innerHTML = 'некорректный IP-адрес';
					return false;
					} else {
					document.getElementById('message').innerHTML = '';	
					}
				} 
			//проверяем числовые поля
			if(elem[i].name == 'ipNum[]' || elem[i].name == 'sid[]' || elem[i].name == 'sidNum[]' || elem[i].name == 'analystCount'){
				if(!numPattern.test(elem[i].value)){
					elem[i].style.borderColor = colors;
					document.getElementById('message').innerHTML = 'введите число';
					return false;
					} else {
					document.getElementById('message').innerHTML = '';		
					}
				}
			}
		}
	</script>	
<?php
//закрываем соединения с БД
$DBO->onConnectionDB();
?>
	</body>
</html>