<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		основная страница анализа компьютерных воздействий (АНАЛИТИК)	*/
						/*													v.0.1 04.02.2014 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));

//подключаем основную, для аналитика страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/analyst/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

//чтение бинарной БД со списком IP-адресов
$ReadBinaryDBBlackList = new ReadBinaryDBBlackList;

//вывод аналитической информации
$DisplayAnaliticalInformation = new DisplayAnalyticalInformationAnalyst;
?>
<!-- проверка заполнения формы (выбор принятого решения) -->			
	<script type="text/javascript" >
	//функция проверяющая заполненные формы
	function validateForm(){
		var obj = { solution: document.formAnalyst.solution,
					typeKA: document.formAnalyst.typeKA };

		//выводим информационное сообщение
		function showMessage(message){
			if(typeof message == 'string'){
				document.getElementById('message').innerHTML = message;
				}
			return false;
			}

		//убираем выделенные пункт формы
		for(var a in obj){
			obj[a].style.borderColor = '';
			}

		//проверяем выбрано ли решение					
		if(obj.solution.value == 0){
			obj.solution.style.borderColor = 'red';
			return showMessage("необходимо принять решение");
			}

		//проверяем выбран ли тип КА
		if(obj.solution.value == 1 && obj.typeKA.value == 0){
			obj.typeKA.style.borderColor = 'red';
			return showMessage("выберите тип КА");
			}
		}
	</script>
<!-- ОБЩИЙ БЛОК для блока краткой информации и блока основного контента -->
	<div style="position: relative; top: 0px; left: 5px; width: 960px;">	
<!-- форма заполняемая аналитиком -->
		<div style="position: absolute; top: 0px; left: 750px; margin-left: 5px; width: 200px; height: 450px; display: inline-block; border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">
			<div style="">
			<form name="formAnalyst" method="POST" action="analysis_incident_process.php" onsubmit="return validateForm()">
<!-- выпадающий список принятых дежурным решений -->	
				<span style="position: absolute; top: 20px; left: 15px; font-size: 14px; font-family: 'Times New Roman', serif;">решение аналитика</span>
				<div style="position: absolute; top: 40px; left: 15px;">
					<?php ListBox::listSolutionAnalyst() ?>	
				</div>
<!-- скрытое поле с информацией о компьютерном воздействии -->
				<input type="hidden" name="numInt" value=<?php echo $_POST['editAnalyst']; ?>>
			
<!-- выпадающий список типов КА -->	
				<span style="position: absolute; top: 65px; left: 15px; font-size: 14px; font-family: 'Times New Roman', serif;">тип компьютерной атаки</span>
				<div style="position: absolute; top: 85px; left: 15px;">
					<?php ListBox::listTypeKA() ?>			
				</div>
				
<!-- кол-во срабатываний -->	
				<span style="position: absolute; top: 130px; left: 15px; font-size: 14px; font-family: 'Times New Roman', serif;">количество срабатываний</span>	
				<div style="position: absolute; top: 150px; left: 15px;">
					<input type="text" name="count_alert" title="количество воздействий" style="width: 100px; height: 20px;"> 
				</div>

<!-- информация от аналитика -->
				<span style="position: absolute; top: 200px; left: 15px; font-size: 14px; font-family: 'Times New Roman', serif;">описание</span>	
				<div style="position: absolute; top: 220px; left: 15px;">
					<textarea type="text" name="information" title="подробная информация" style="width: 165px; height: 165px;"></textarea> 
				</div>

<!-- кнопка "сохранить" -->
				<div style="position: absolute; top: 400px; left: 14px;">
					<input type="submit" name="safe" value="сохранить">
				</div>
			</form>
			</div>
<!-- сообщение "не выбранно решение" -->
			<div id="message" style="position: relative; top: 5px; left: 0; width: 200px; text-align: center; font-size: 10px; font-family: Verdana, serif; color: #CD0000;"></div>
		</div>
		
<!-- область вывода аналитику всей доступной информации по компьютерному воздействию -->
		<div style="position: relative; top: 0; width: 740px;">
			<div style="position: relative; top: 0; z-index: 10; width: 750px; border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">
				<div style="position: relative; top: 10px; text-align:center;">
					<span style="font-size: 20px; font-family: 'Times New Roman', serif; letter-spacing: 2px; color: #0000CC;">
					компьютерное воздействие №<?php echo intval($_POST['editAnalyst']); ?>
					</span>
				</div>
			
<!-- таблица содержащая всю информацию -->
				<div style="position: relative; top: 30px; left: 20px; margin: 10px 0px;">
				<table border="0" width="710px">
					<tr>
<!-- дата добавления -->
						<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 302px;">
						добавлен
						</td>
						<td class="mapFullInformationText" style="text-align: center; width: 302px;">
						<?php
						//массив полученный в результате запроса даты добавления воздействия и логина пользователя 
						$array_date_name = AllInformationForIncident::showUserNameAddInformation($DBO, $_POST['editAnalyst']);				
						//вывод даты добавления
						echo ConversionData::showDateConvert($array_date_name['date_create']); 
						?>		
						</td>
					</tr>
					<tr>
<!-- Ф.И.О добавившего воздействие -->
						<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 302px;">
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
						$array_ip_and_date = AllInformationForIncident::showDateStartAndEndAndIpSrcIpDst($DBO, $_POST['editAnalyst']);
						//дата и время начала воздействия
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
							//устанавливаем IP-адрес для поиска в бинарной БД
							$ReadBinaryDBBlackList->setIp(long2ip($array_ip_and_date['ip_src'][$i]));
							//вывод найденной информации
							$ReadBinaryDBBlackList->showInfoSearchIp();
							echo " (".$GeoIP->countryIP($DBO, $array_ip_and_date['ip_src'][$i]);
							//echo long2ip($array_ip_and_date['ip_src'][$i])." (".$GeoIP->countryIP($DBO, $array_ip_and_date['ip_src'][$i]); 
							?> 
							<img src= <?php echo "/{$array_directory[1]}/img/flags/".$GeoIP->flags(); ?> />) / 
							<span style='color: #FF0000;'><?php echo $array_ip_and_date['count_impact'][$i]; ?></span><br>
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
						echo ($ReadXMLSetup->obtainDomainName($ipDst) != '') ? $ReadXMLSetup->obtainDomainName($ipDst).'<br>' : '';						//принадлежность определенному сенсору
						$GetSensorInformation = new GetSensorInformation;
						echo $GetSensorInformation->getInformationForIp($ipDst);
						?>
						</td>
					</tr>
					<tr>
<!-- тип воздействия -->
						<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 302px;">
						тип компьютерного воздействия
						</td>
						<td class="mapFullInformationText" style="text-align: center; width: 302px;">
						<?php
						//тип воздействия выбранный оперативным дежурным
						foreach($ReadXMLSetup->giveTypeKA() as $id_attack => $name){						
							if($array_ip_and_date['type_attack'][0] == $id_attack){
								echo $name;
								}
							}
						?>
						</td>
					</tr>
					<tr>
<!-- доступность информационного ресурса -->
						<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 302px;">
						доступность информационного ресурса
						</td>
						<td class="mapFullInformationText" style="text-align: center; width: 302px;">
						<?php
						$array_space_traffic = AllInformationForIncident::showSpaceSafeNetTraffic($DBO, $_POST['editAnalyst']);
						//доступность Web-ресурса						
						// 1 - доступен
						// 2 - недоступен
						echo ($array_space_traffic['availability_host'][0] == 1) ? "информационный ресурс доступен" : "<span style='color: red;'>зафиксирована недоступность информационного ресурса</span>";			
						?>						
						</td>
					</tr>
					<tr>
<!-- направление воздействия -->
						<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 302px;">
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
<!-- место нахождения отфильтрованного сетевого трафика -->
					<tr>
						<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 302px;">
						место нахождения отфильтрованного сетевого трафика						
						</td>
						<td class="mapFullInformationText" style="text-align: center; width: 302px;">
						<?php
						//место нахождения отфильтрованного сетевого трафика
						echo FormattingText::formattingTextLength($array_space_traffic['space_safe'], 45);
						?>									
						</td>
					</tr>
<!-- пояснение дежурного -->
					<?php
					if(!empty($array_space_traffic['explanation'])){
						?>
					<tr>
						<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 302px;">
						пояснение дежурного						
						</td>
						<td class="mapFullInformationText" style="text-align: center; width: 302px;">
						<?php 
						//вывод данных с учетом максимальной длинны строки
						echo FormattingText::formattingTextLength($array_space_traffic['explanation'], 45); 	
						?>									
						</td>
					</tr>
						<?php	
						}
					?>
					<tr>
<!-- номер сигнатуры/количество срабатываний -->
						<th class="mapFullInformationText" style="width: 302px; background: #FFEDB9;">
						номер сигнатуры/количество срабатываний
						<!--	showSignature($DBO)
						'sid' => $row->sid,
						'count_alert' => $row->count_alert,
						'short_message' => $row->short_message,
						'snort_rules' => $row->snort_rules); -->
						</th>
						<th class="mapFullInformationText" style="width: 302px; background: #FFEDB9;">
						краткое описание сигнатуры
						</th>
					</tr>
						<?php
						//полная информация о сигнатурах						
						$array_signature = AllInformationForIncident::showSignature($DBO, $_POST['editAnalyst']);
						$num = count($array_signature['sid']);
						//номер сигнатуры / количество срабатываний
						for($i = 0; $i < $num; $i++){
							?>
						<tr>
							<td class="mapFullInformationText" style="text-align: center; width: 302px;">
<!-- просмотреть всю информацию по сигнатуре (JavaScript) -->
							<a href="showAllInformationForSignature.php?showRule=<?php echo $array_signature['sid'][$i] ?>" target="_blank" onclick="popupWin = window.open(this.href, 'displayWindow', 'location,width=530,height=300,status=no,toolbar=no,menubar=no,scrollbars=no'); popupWin.focus(); return false;"><?= $array_signature['sid'][$i] ?></a>
								 / <span style='color: #FF0000;'><?= $array_signature['count_alert'][$i]?></span>
							</td>
							
							<td class="mapFullInformationText" style="padding-left: 10px; text-align: left; width: 302px;">
							<?php
							echo " - ".$array_signature['short_message'][$i]."</td></tr>";							
							}						
						?>
				</table>
				</div><br><br>
			</div><br>
		</div>
<!-- область вывода аналитической информации -->
		<div style="position: relative; top: -14px; width: 740px; z-index: 3;">
			<div style="position: relative; top: 0px; text-align: center; z-index: 2; width: 750px; border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">
				<span style="position: relative; top: 10px; font-size: 18px; font-family: 'Times New Roman', serif; letter-spacing: 2px; color: #000;">
				дополнительная информация
				</span><br><br>
<!-- совпадение IP-адреса источника, IP-адреса назначения и номера сигнатуры, заголовок -->
				<div style="position: relative; top: 0px; left: 20px; width: 710px; background: #BBFFFF;">
					<span style="font-size: 14px; font-family: 'Times New Roman', serif; font-weight: bold; letter-spacing: 1px;">количество совпадений IP-адреса источника, назначения и номера сигнатуры</span>
				</div>
<!-- совпадение IP-адреса источника, IP-адреса назначения и номера сигнатуры, информация -->				
				<?php $DisplayAnaliticalInformation->showInformationEqual($array_ip_and_date['ip_src'], $array_signature['sid'], $array_ip_and_date['ip_dst'][0], intval($_POST['editAnalyst'])); ?>
<!-- IP-адреса источники, заголовок -->
				<div style="position: relative; top: 0px; left: 20px; width: 710px; background: #BBFFFF;">
					<span style="font-size: 14px; font-family: 'Times New Roman', serif; font-weight: bold; letter-spacing: 1px;">IP-адрес источник, % ложных компьютерных воздействий</span>
				</div>
<!-- IP-адреса источники, информация -->
				<?php $DisplayAnaliticalInformation->showInformationSrcIp($array_ip_and_date['ip_src']); ?>
<!-- сигнатуры, заголовок -->				
				<div style="position: relative; left: 20px; width: 710px; background: #BBFFFF;">
					<span style="font-size: 14px; font-family: 'Times New Roman', serif; font-weight: bold; letter-spacing: 1px;">вероятность ложного срабатывания сигнатуры (%)</span>
				</div>
<!-- сигнатуры, информация -->
				<?php $DisplayAnaliticalInformation->showInformationSid($array_signature['sid']); ?>				
				<br> 
			</div>
		</div>
	</div>

<?php
include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>
