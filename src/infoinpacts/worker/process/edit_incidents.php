<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		скрипт редактирования дежурными добавленных компьютерных воздействий	*/
						/*														v.0.2 03.04.2014	 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));

//подключаем основную, для дежурного страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/worker/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

?>
<script type="text/javascript">
//функция проверки полей "номер письма в 18 Центр ФСБ России" или "место нахождения отфильтрованного сетевого трафика"
function checkField(elem)
	{
	var inputRadio = document.getElementsByName('radioNumMail');
	for(var i = 0; i < inputRadio.length; i++){
		var testElement = inputRadio[i].nodeType == 1;
		var radioCloseMail = inputRadio[i].value == 3;
		if(testElement && radioCloseMail && inputRadio[i].checked == true){
			return true;
		}
	}
	var numPattern = /^[0-9]+$/;
	if(elem.name == 'editFullKA'){
		//проверяем поле номера письма
		var number = document.editFullKA.numberMail;
		if(!numPattern.test(number.value)){
			number.style['borderColor'] = 'red';
			return false;
		}
		return true;
	} 
	else if(elem.name == 'changeNetTraffic'){
	//проверяем поле месторасположения отфильтрованного сетевого трафика
		var spaseTraff = document.changeNetTraffic.newSpaceNetTraffic;
		spaseTraff.style['borderColor'] = '';
		if(spaseTraff.value.length < 3){
			spaseTraff.style['borderColor'] = 'red';
			return false;
		}
		return true;
	}
	return false;
	}

//вывод поля для заполнения номера письма при закрытии компьютерного воздействия
function viewFieldMailNumber(){
	var divNamberMail = document.getElementById('numberMail');
	if(this == true){
		divNamberMail.style.display = 'block';
	} else {
		divNamberMail.style.display = 'none';
	}
}

//вывод поля заметок
function viewFieldNodes(){
	var divFieldNodes = document.getElementById('fieldNodes');
	if(divFieldNodes.style.display == 'none'){
		divFieldNodes.style.display = 'block';
	} else {
		divFieldNodes.style.display = 'none';
	}
}

//функция добавления обработчиков
window.onload = function(){
	//для кнопок radio
	(function(){
		var inputRadio = document.getElementsByName('radioNumMail');
		var arrayNum = ['1', '2'];
		for(var i = 0; i < inputRadio.length; i++){
			if(inputRadio[i].nodeType == 1){
				if(arrayNum.indexOf(inputRadio[i].value) != -1){
					inputRadio[i].addEventListener('click', viewFieldMailNumber.bind(true));
				} else {
					inputRadio[i].addEventListener('click', viewFieldMailNumber.bind(false));
				}
			}
		}
	})();
	//для поля заметок
	(function(){
		var textAddNodes = document.getElementById('addNodes');
		textAddNodes.addEventListener('click', viewFieldNodes);
	})();
}


</script>
<?php

			/*												---------------------------------					 			 */	
			/* 					форма для добавления номера письма к выбранному компьютерному воздействию 					 */
			/*												---------------------------------					 			 */

	//глобальная переменная $_POST['editIncident'] представляет собой массив
	if(isset($_POST['editIncident'])){
        $ReadBinaryDBBlackList = new ReadBinaryDBBlackList;
		?>
		<div style="position: relative; top: 0; left: 0; z-index: 10; width: 960px; border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">
			<div style="position: relative; top: 10px; text-align: center; width: 960px; font-size: 20px; font-family: 'Times New Roman', serif; margin: 10px 0px;">
				ввод дополнительной информации<br>по следующим компьютерным воздействиям
			</div>
			<?php
			//строка с номерами выбранных инцидентов
			$string_id_incindents = "";
			foreach($_POST['editIncident'] as $editIAll){
				$string_id_incindents .= $editIAll.":"; 
			?>

<!-- таблица содержащая всю информацию -->
				<div style="position: relative; top: 10px; left: 20px; margin: 10px 0px;">
					<div style="text-align: center; width: 940px; font-size: 18px; font-family: 'Times New Roman', serif; letter-spacing: 2px; border-radius: 3px; color: #0000CC;">
						компьютерное воздействие №<?php echo $editIAll; ?>
					</div><br>
					<table border="0" width="920px" style="font-size: 14px; font-family: 'Times New Roman';">
						<tr>
<!-- дата добавления -->
							<td style="text-align: right; width: 302px; padding-right: 10px;">добавлен</td>
							<td style="text-align: center; width: 302px;">
							<?php
							//массив полученный в результате запроса даты добавления воздействия и логина пользователя 
							$array_date_name = AllInformationForIncident::showUserNameAddInformation($DBO, $editIAll);				
                            echo ConversionData::showDateConvert($array_date_name['date_create']);
							?>		
							</td>
						</tr>
						<tr>
<!-- Ф.И.О добавившего инцидент -->
							<td style="text-align: right; width: 302px; padding-right: 10px;">оперативный дежурный</td>
							<td style="text-align: center; width: 302px;">
							<?php 
							//кем добавлено компьютерное воздействие 
							echo $ReadXMLSetup->usernameFIO($array_date_name['login_name']); 
							?>
							</td>
						</tr>
						<tr>
<!-- интервал времени инцидента -->
							<th style="width: 302px; background: #FFEDB9;">
							начало инцидента (дата/время)
							</th>
							<th style="width: 302px; background: #FFEDB9;">
							конец инцидента (дата/время)						
							</th>
						</tr>
						<tr>
							<td style="text-align: center; width: 302px;">
							<?php
							//получаем массив содержащий начальную и конечную дату и время, IP-адреса источников и назначения и тип воздействия
							$array_ip_and_date = AllInformationForIncident::showDateStartAndEndAndIpSrcIpDst($DBO, $editIAll);
							//дата и время начала воздействия
							echo "с ".substr($array_ip_and_date['date_start'][0], -8, 8);
							echo " ".ConversionData::showDateConvert(strtotime($array_ip_and_date['date_start'][0]));
							 ?>
							</td>
							<td style="text-align: center; width: 302px;">
							<?php
							//дата и время конца инцидента
							echo "по ".substr($array_ip_and_date['date_end'][0], -8, 8);
							echo " ".ConversionData::showDateConvert(strtotime($array_ip_and_date['date_end'][0]));
							?>
							</td>
						</tr>
						<tr>
<!-- IP-адреса источников и назначения -->
							<th style="width: 302px; font-size: 14px; background: #FFEDB9;">
							IP-адреса источники / количество воздействий
							</th>
							<th style="width: 302px; font-size: 14px; background: #FFEDB9;">
							IP-адрес назначения
							</th>
						</tr>
						<tr>
							<td style="text-align: center; width: 302px;">
							<?php
							//IP-адреса источников
							for($i = 0; $i < count($array_ip_and_date['ip_src']); $i++) 
								{
                                //устанавливаем IP-адрес для поиска в бинарной БД
                                $ReadBinaryDBBlackList->setIp(long2ip($array_ip_and_date['ip_src'][$i]));
                                //вывод найденной информации
								echo $ReadBinaryDBBlackList->showInfoSearchIp()." (".$GeoIP->countryIP($DBO, $array_ip_and_date['ip_src'][$i]); ?>
								<img src= <?php echo "/{$array_directory[1]}/img/flags/".$GeoIP->flags(); ?> />) / 
								<span style='color: #FF0000;'>
								<?php
								if($array_ip_and_date['count_impact'][$i] == 0){
									echo "нет данных";
								} else { 
									echo $array_ip_and_date['count_impact'][$i];
									} 
								?>
								</span><br>
								<?php	
								}
							?>
							</td>
							<td style="text-align: center; width: 302px;">
							<?php
							$ipDst = long2ip($array_ip_and_date['ip_dst'][0]);
							//IP-адреса назначения
							echo $ipDst."<br>";
							//и доменное имя если оно есть
							echo $ReadXMLSetup->obtainDomainName($ipDst);
							?>
							</td>
						</tr>
						<tr>
<!-- тип воздействия -->
							<td style="text-align: right; width: 302px; padding-right: 10px; background: #FFE4E1;">
							тип компьютерного воздействия
							</td>
							<td style="text-align: center; width: 302px; background: #FFE4E1;">
							<?php
							//тип инцидента выбранный оперативным дежурным
							echo $ReadXMLSetup->giveTypeKAForId($array_ip_and_date['type_attack'][0]);
							?>
							</td>
						</tr>
                        <tr>
<!-- пояснения дежурного -->
                            <td style="text-align: right; width: 302px; padding-right: 10px; background: #FFE4E1;">
                                пояснения дежурного
                            </td>
                            <td style="text-align: center; width: 302px; background: #FFE4E1;">
                                <?php
                                //пояснения оперативного дежурного
                                $array_explanation = AllInformationForIncident::showSpaceSafeNetTraffic($DBO, $editIAll);
                                echo FormattingText::formattingTextLength($array_explanation['explanation'], 40);
                                ?>
                            </td>
                        </tr>
<!-- Ф.И.О. выполнившего анализ сетевого трафика -->
							<td style="text-align: right; width: 302px; padding-right: 10px;">
							анализ сетевого трафика выполнил						
							</td>
							<td style="text-align: center; width: 302px;">
							<?php
							$array_space_traffic = AllInformationForIncident::showAllInformationAnalyst($DBO, $editIAll);
							//место нахождения отфильтрованного сетевого трафика
							echo $ReadXMLSetup->usernameFIO($array_space_traffic['login_name'][0]);
							?>									
							</td>
						</tr>
						<tr>
<!-- Время анализа сетевого трафика -->
							<td style="text-align: right; width: 302px; padding-right: 10px;">
							дата анализа сетевого трафика						
							</td>
							<td style="text-align: center; width: 302px;">
							<?php
							$array_space_traffic = AllInformationForIncident::showAllInformationAnalyst($DBO, $editIAll);
							
							echo ConversionData::showDateConvert($array_space_traffic['date_time_analyst'][0]);
							?>									
							</td>
						</tr>
						<tr>
<!-- количество пакетов информационной безопасности по мнению аналитика -->
							<td style="text-align: right; width: 302px; padding-right: 10px;">
							количество пакетов информационной безопасности по мнению аналитика						
							</td>
							<td style="text-align: center; width: 302px;">
							<?php
							//количество пакетов информационной безопасности по мнению аналитика	
							if($array_space_traffic['count_alert_analyst'][0] == 0){
								echo "<span style='color: #000;'>подсчет не производился</style>";
								} else {
								echo "<span style='color: #FF0000;'>".$array_space_traffic['count_alert_analyst'][0]."</span>";	
								}
							?>									
							</td>
						</tr>
						<tr>
<!-- информация аналитика -->
							<td style="text-align: right; width: 302px; padding-right: 10px; background: #CCFFFF;">
							информация аналитика
							</td>
							<td style="text-align: center; width: 302px; background: #CCFFFF;">
							<?php
							//дополнительная информация аналитика	
							echo FormattingText::formattingTextLength($array_space_traffic['information_analyst'][0], 40);	
							?>									
							</td>
						</tr>
						<tr>
<!-- месторасположение сетевого трафика -->
							<td style="text-align: right; width: 302px; padding-right: 10px;">
							месторасположение сетевого трафика
							</td>
							<td style="text-align: center; width: 302px;">
							<?php
							//дополнительная информация аналитика	
							echo FormattingText::formattingTextLength($array_date_name['space_safe'], 40);	
							?>									
							</td>
						</tr>
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
                        $array_signature = AllInformationForIncident::showSignature($DBO, $editIAll);
                        $num = count($array_signature['sid']);
                        //номер сигнатуры / количество срабатываний
                        for($i = 0; $i < $num; $i++){
                        ?>
                        <tr>
                            <td class="mapFullInformationText" style="text-align: center; width: 302px;">
                                <!-- просмотреть всю информацию по сигнатуре (JavaScript) -->
                                <?= $array_signature['sid'][$i] ?>
                                / <span style='color: #FF0000;'><?= $array_signature['count_alert'][$i]?></span>
                            </td>

                            <td class="mapFullInformationText" style="padding-left: 10px; text-align: left; width: 302px;">
                                <?php
                                echo " - ".$array_signature['short_message'][$i]."</td></tr>";
                                }
                                ?>
					</table><br>
				</div>
				<?php
				}
				?>
		</div>
<!-- форма выбора решения принятого по компьютерному воздействию и ввода номера письма -->
		<div style="position: relative; top: 0; left: 0; margin-top: 5px; width: 960px; border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">
			<div style="position: relative; top: 10px; text-align: center;">
				<span style="position: relative; top: 0px; font-size: 20px; font-family: 'Times New Roman', serif; color: #000;">
				решение по вышеперечисленным компьютерным атакам 
				</span>
			</div>
			<form name="editFullKA" method="POST" action="edit_incidents_process.php" onsubmit="return checkField(this)">
			<div style="position: relative; top: 25px; left: 0; width: 900px; text-align: center;">
				<div style="display: inline-block;">
					<span class="tableHeader" style="margin: 5px;">номер письма в</span>
					<input type="radio" name="radioNumMail" value="1">
					<span class="textSizeSmall">
						18 Центр ФСБ России
					</span>
					<input type="radio" name="radioNumMail" value="2">
					<span class="textSizeSmall">
						региональное управление ФСБ России
					</span>
					<input type="radio" name="radioNumMail" value="3" checked="checked">
					<span class="textSizeSmall">
						закрыть компьютерное воздействие без письма
					</span>
				</div>
				<div id="numberMail" style="padding: 10px 0 5px 0; display: none;">
<!-- номер письма в ЦИБ -->
					<input type="text" name="numberMail" style="width: 120px; height: 15px; background: #FFC1C1;" title="номер подготовленного письма в ЦИБ">
<!-- текст -->
					<span style="position: relative; top: -1px; font-size: 14px; font-family: 'Times New Roman', serif;">
						&nbsp;и(или) в стороннюю организацию&nbsp;
					</span>
<!-- номер письма в организацию -->
					<input type="text" name="numberMailOrganization" style="width: 120px; height: 15px;" title="номер подготовленного письма в организацию">					
				</div>
<!-- поле заметок -->
				<div id="fieldNodes" style="padding: 5px 0 10px 0; display: none;">
					<input type="text" name="fieldNodes" style="width: 460px; height: 15px;" title="заметки по компьютерному воздействию">
				</div>
				<div style="margin-top: 5px;">
					<span id="addNodes" class="tableHeader" style="cursor: pointer; color: #0000FF; text-decoration: underline;">
						добавить заметку к компьютерным воздействиям
					</span>
<!-- кнопка "сохранить" -->
					<span style="margin-left: 10px;">
						<input type="submit" name="safe" value="сохранить">			
					</span>
				</div>
<!-- поле с номером инцидента -->
				<input type="hidden" name="idInc" value="<?= $string_id_incindents ?>">
			</div>
			</form>
			<br><br>
		</div>
		<?php
		}

			/*							---------------------------------						*/	
			/* форма для изменения местоположения сетевого трафика по компьютерному воздействию */
			/*							---------------------------------						*/

	if(isset($_POST['editIncidentTraffic'])){
		?>
<!-- область для вывода дежурному краткой информации по компьютерному воздействию -->
		<div style="position: relative; top: 0px; left: 0px; z-index: 10; width: 960px; border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">
			<span style="position: absolute; top: 10px; left: 340px; font-size: 20px; font-family: 'Times New Roman', serif; letter-spacing: 2px; border-radius: 3px; color: #0000CC;">
			компьютерное воздействие №<?php echo intval($_POST['editIncidentTraffic']); ?>
			</span>
<!-- таблица содержащая всю информацию -->
			<div style="position: relative; top: 40px; left: 20px; margin: 10px 0px;">
				<table border="0" width="920px">
					<tr>
<!-- дата добавления -->
						<td style="text-align: right; width: 302px; padding-right: 10px; font-size: 14px; font-family: 'Times New Roman', serif; background: ;">
						добавлен
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<?php
						//массив полученный в результате запроса даты добавления воздействия и логина пользователя 
						$array_date_name = AllInformationForIncident::showUserNameAddInformation($DBO, $_POST['editIncidentTraffic']);				
						//вывод даты добавления
						echo ConversionData::showDateConvert($array_date_name['date_create']); 
						?>		
						</td>
					</tr>
					<tr>
<!-- Ф.И.О добавившего информацию о компьютерном воздействии -->
						<td style="text-align: right; width: 302px; padding-right: 10px; font-size: 14px; font-family: 'Times New Roman', serif;">
						оперативный дежурный
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<?php 
						//кем добавлено компьютерное воздействие
						echo $ReadXMLSetup->usernameFIO($array_date_name['login_name']); 
						?>
						</td>
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
						//получаем массив содержащий начальную и конечную дату и время, IP-адреса источников и назначения и тип воздействия
						$array_ip_and_date = AllInformationForIncident::showDateStartAndEndAndIpSrcIpDst($DBO, $_POST['editIncidentTraffic']);
						//дата и время начала воздействия
						echo "с ".substr($array_ip_and_date['date_start'][0], -8, 8);
						echo " ".ConversionData::showDateConvert(strtotime($array_ip_and_date['date_start'][0]));
						?>
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<?php
						//дата и время конца воздействия
						echo "по ".substr($array_ip_and_date['date_end'][0], -8, 8);
						echo " ".ConversionData::showDateConvert(strtotime($array_ip_and_date['date_end'][0]));
						?>
						</td>
					</tr>
					<tr>
<!-- IP-адреса источников и назначения -->
						<th style="width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
						IP-адреса источники / количество воздействий
						</th>
						<th style="width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
						IP-адрес назначения
						</th>
					</tr>
					<tr>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<?php
						//IP-адреса источников
						for($i = 0; $i < count($array_ip_and_date['ip_src']); $i++){
							echo long2ip($array_ip_and_date['ip_src'][$i])." (".$GeoIP->countryIP($DBO, $array_ip_and_date['ip_src'][$i]); ?>
							<img src= <?php echo "/{$array_directory[1]}/img/flags/".$GeoIP->flags(); ?> />) / 
							<span style='color: #FF0000;'>
								<?php
								if($array_ip_and_date['count_impact'][$i] == 0){
									echo "нет данных";
									} else { 
									echo $array_ip_and_date['count_impact'][$i];
									} 
								?>
							</span><br>
							<?php	
							}
						?>
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<?php
						//IP-адреса назначения
						echo long2ip($array_ip_and_date['ip_dst'][0])."<br>";
						//и доменное имя если оно есть
						echo $ReadXMLSetup->obtainDomainName(long2ip($array_ip_and_date['ip_dst'][0]));
						?>
						</td>
					</tr>
					<tr>
<!-- Ф.И.О. выполнившего анализ сетевого трафика -->
						<td style="text-align: right; width: 302px; padding-right: 10px; font-size: 14px; font-family: 'Times New Roman', serif;">
						анализ сетевого трафика выполнил						
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<?php
						$array_space_traffic = AllInformationForIncident::showAllInformationAnalyst($DBO, $_POST['editIncidentTraffic']);
						//место нахождения отфильтрованного сетевого трафика
						echo $ReadXMLSetup->usernameFIO($array_space_traffic['login_name'][0]);
						?>									
						</td>
					</tr>
					<tr>
<!-- информация аналитика -->
						<td style="text-align: right; width: 302px; padding-right: 10px; font-size: 14px; font-family: 'Times New Roman', serif; background: #CCFFFF;">
						информация аналитика
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #CCFFFF;">
						<?php
						//дополнительная информация аналитика	
						echo FormattingText::formattingTextLength($array_space_traffic['information_analyst'][0], 75);	
						?>									
						</td>
					</tr>
					<tr>
<!-- место нахождения отфильтрованного сетевого трафика -->					
						<td style="text-align: right; width: 302px; padding-right: 10px; font-size: 14px; font-family: 'Times New Roman', serif;">
						место нахождения отфильтрованного сетевого трафика						
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						<?php
						$array_space_traffic = AllInformationForIncident::showSpaceSafeNetTraffic($DBO, $_POST['editIncidentTraffic']);
						//место нахождения отфильтрованного сетевого трафика
						echo FormattingText::formattingTextLength($array_space_traffic['space_safe'], 75);	
						?>									
						</td>
					</tr>
				</table><br>
			</div>		
				
<!-- форма редактирования местонахождение сетевого трафика -->	
			<div style="position: relative; top: 20px; left: 20px; margin: 10px 0px;">
			<form name="changeNetTraffic" method="POST" action="edit_incidents_process.php" onsubmit="return checkField(this)">
			<!-- текст -->
				<div style="position: relative; top: 0px; left: 290px; margin: 10px 0px; width: 400px;">
					<span style="font-size: 14px; font-family: 'Times New Roman', serif;">новое местонахождение отфильтрованного сетевого трафика</span>
				</div>
<!-- ввод нового местоположения отфильтрованного сетевого трафика -->
				<div style="position: relative; top: 0px; left: 300px; margin: 10px 0px; width: 250px;">
					<textarea type="text" name="newSpaceNetTraffic" style="width: 250px; height: 21px;" title="новое местоположение"></textarea>
				</div>
<!-- поле с номером инцидента -->
				<?php echo "<input type='hidden' name='idInc' value='".$_POST['editIncidentTraffic']."'>"; ?>
<!-- кнопка "сохранить" -->
				<div style="position: relative; top: -42px; left: 570px; width: 80px;">
					<input type="submit" name="safe" value="изменить">
				</div>
			</form>
			</div>
		</div>
		<?php
		}
	
			/*					---------------------------------			    */	
			/* 	  форма для полного редактирования компьютерного воздействия 	*/
			/*					---------------------------------			    */	

	if(isset($_POST['editInpacts']) && !empty($_POST['editInpacts'])){
		?>
		<!-- область для вывода дежурному краткой информации по компьютерному воздействию -->
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
							<?php echo "(".$GeoIP->countryIP($DBO, $array_ip_and_date['ip_src'][$i]);?>  
							
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
<!-- тип компьютерного воздействия -->					
						<td style="text-align: right; padding-right: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
							тип компьютерного воздействия					
						</td>
						<td style="text-align: left; padding-left: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
							<?php ListBox::listTypeKA($array_ip_and_date['type_attack'][0]) ?>								
						</td>
					</tr>
					<tr>
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
					<tr>
<!-- пояснение дежурного по компьютерному воздействию -->
						<td style="text-align: right; padding-right: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
							пояснение к воздействию
						</td>
						<td style="padding-left: 15px;">
							<input type="text" name="explanation" value="<?= $array_space_traffic['explanation'];?>" style="width: 250px; height: 18px">
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
				<?php echo "<input type='hidden' name='editInpacts' value='".$_POST['editInpacts']."'>"; ?>
				</form><br><br><br>	
			</div>
		</div>

<!-- подключаем файл функциями добавления и удаления элементов -->
		<script type="text/javascript" src='/<?= $array_directory[1] ?>/js/changeNumElements.js'></script>

<!-- проверка заполнения формы -->			
		<script type="text/javascript" >

		//функция проверяющая заполненные формы
		function validateForm()
			{
			var colors = 'red';
			var o = {dateStart: document.changeIncident.dateStart,
					 dateEnd: document.changeIncident.dateEnd,
					 ipSrc: document.changeIncident.ipSrc,
					 ipDst: document.changeIncident.ipDst};
			//получаем список элементов формы
			var elem = document.changeIncident.elements;
			var flag = false;
			//определяем значение по умолчанию для выбранного элемента
			document.changeIncident.typeKA.defaultValue = <?= $array_ip_and_date['type_attack'][0] ?>;
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
				//проверяем поля на пустоту
				if(elem[i].value == '' && elem[i].type != 'select-one' && elem[i].name != 'explanation'){
					elem[i].style.borderColor = colors;
					document.getElementById('message').innerHTML = 'пустое поле';
					return false;
					} else {
					document.getElementById('message').innerHTML = '';	
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
				if(elem[i].name == 'ipNum[]' || elem[i].name == 'sid[]' || elem[i].name == 'sidNum[]'){
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