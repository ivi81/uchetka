<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		скрипт редактирования проанализированных компьютерных воздействий	*/
						/*													v.0.1 15.04.2014	 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));

//подключаем основную, для аналитика страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/analyst/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);
	
			/*					---------------------------------	      */	
			/* форма для полного редактирования компьютерного воздействия */
			/*					---------------------------------		  */	

	if(isset($_POST['editInpacts']) && !empty($_POST['editInpacts'])){
		?>
		<!-- область для вывода информации по компьютерному воздействию -->
		<div style="position: relative; top: 0px; left: 0px; z-index: 10; width: 960px; border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">
			<span style="position: absolute; top: 10px; left: 340px; font-size: 20px; font-family: 'Times New Roman', serif; letter-spacing: 2px; border-radius: 3px; color: #0000CC;">
			компьютерное воздействие №<?php echo intval($_POST['editInpacts']); ?>
			</span>
	<!-- таблица содержащая всю информацию -->
			<div style="position: relative; top: 40px; left: 20px; margin: 10px 0px;">
				<form name="changeIncident" method="POST" action="edit_incidents_process.php" onsubmit="return validateForm()">				
				<table border="0" width="920px">
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
						//получаем массив содержащий начальную и конечную дату и время, IP-адреса источников и назначения и тип воздействия
						$array_ip_and_date = AllInformationForIncident::showDateStartAndEndAndIpSrcIpDst($DBO, $_POST['editInpacts']);
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
					<tr>
<!-- Ф.И.О. выполнившего анализ сетевого трафика -->
						<td style="text-align: right; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						анализ сетевого трафика выполнил						
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<?php
						$array_analyst = AllInformationForIncident::showAllInformationAnalyst($DBO, $_POST['editInpacts']);
						//место нахождения отфильтрованного сетевого трафика
						echo $ReadXMLSetup->usernameFIO($array_analyst['login_name'][0]);
						?>									
						</td>
					</tr>
					<tr>
<!-- количество пакетов информационной безопасности по мнению аналитика -->
						<td style="text-align: right; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						количество пакетов информационной безопасности по мнению аналитика						
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<input type="text" name="analystCount" value="<?= $array_analyst['count_alert_analyst'][0]?>" style="width: 50px; height: 18px">
						</td>
					</tr>
					<tr>
<!-- мнение аналитика -->
						<td style="text-align: right; padding-right: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #CCFFFF;">
							мнение аналитика по компьютерному воздействию
						</td>
						<td style="text-align: left; padding-left: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #CCFFFF;">
							<?php ListBox::listSolutionAnalyst($array_analyst['true_false'][0]); ?>			
						</td>
					</tr>
					<tr>
<!-- тип компьютерного воздействия -->					
						<td style="text-align: right; padding-right: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
							тип компьютерного воздействия					
						</td>
						<td style="text-align: left; padding-left: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
							<?php ListBox::listTypeKA($array_ip_and_date['type_attack'][0]) ?>								
						</td>
					</tr>
					<tr>
<!-- информация аналитика -->
						<td style="text-align: right; padding-right: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #CCFFFF;">
							информация аналитика
						</td>
						<td style="text-align: left; padding-left: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #CCFFFF;">
							<input type="text" name="analystInfo" value="<?= $array_analyst['information_analyst'][0];?>" style="width: 410px; height: 18px">
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
						$array_signature = AllInformationForIncident::showSignature($DBO, $_POST['editInpacts']);
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
						<td style="text-align: right; padding-right: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
							место нахождения отфильтрованного сетевого трафика					
						</td>
						<td style="text-align: left; padding-left: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
							<?php
							$array_space_traffic = AllInformationForIncident::showSpaceSafeNetTraffic($DBO, $_POST['editInpacts']);
							//место нахождения отфильтрованного сетевого трафика
							?>
						<input type="text" name="spaceSafe" value="<?= $array_space_traffic['space_safe'];?>" style="width: 250px; height: 18px">									
						</td>
					</tr>
<!-- кнопка "сохранить" -->
					<tr>
						<td style="text-align: center; width: 50%;"><!-- информационное сообщение общего характера -->
						<span style="font-size: 10px;	font-family: Verdana, serif; color: #CD0000;" id="message"></span>
						</td>
						<td style="text-align: left; width: 50%;">
						<br><input type="submit" name="save" style="width: 100px; height: 20px;" value="сохранить">
						</td>
					</tr>
				</table>
<!-- поле с номером инцидента -->
				<?php echo "<input type='hidden' name='editInpacts' value='".$_POST['editInpacts']."'>"; ?>
				</form><br><br><br>	
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
			elem.solution.defaultValue = <?= $array_analyst['true_false'][0] ?>;
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
				if(!elem[i].name == 'analystInfo'){
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
		}
include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>