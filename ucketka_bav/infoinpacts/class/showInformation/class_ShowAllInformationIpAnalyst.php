<?php

										/*------------------------------------------------------*/
										/*		класс вывод полной информации об компьютерных	*/
										/*		воздействиях (Аналитик)							*/
										/*								v 0.1 05.09.2014		*/
										/*------------------------------------------------------*/

class ShowAllInformationIpAnalyst
{

protected $GeoIP;
protected $ReadXMLSetup;
protected $directoryRoot;
protected static $DBO;

function __construct()
	{
	//получаем корневую директорию сайта
	$dir = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directoryRoot = $dir[1];
	//объект БД GeoIP
	$this->GeoIP = new GeoIP();
	//объект для чтения файла setup_site.xml
	$this->ReadXMLSetup = new ReadXMLSetup;
	}

//устанавливаем соединение с БД
protected static function getConnectionDB()
	{
	if(empty(self::$DBO)){
		self::$DBO = new DBOlink;
		}
	return self::$DBO;
	}

//проверка наличия решения аналитика
private function checkTrueFalse()
	{
	if(!isset($_GET['true']) && empty($_GET['true'])){
		echo ShowMessage::showInformationError("необходим вид принятого аналитиком решение");
		exit();
		}
	}

//вывод информации
public function showInformation()
	{
	if(isset($_GET['number']) && !empty($_GET['number'])){
		$num = intval($_GET['number']);
		switch($num){
			//при sid
			case 1:
			//проверяем наличие решения аналитика
			$this->checkTrueFalse();
			//получаем информацию
			$this->showInfoIpDstSid();
			break;
			//при ipSrc
			case 2:
			//проверяем наличие решения аналитика
			$this->checkTrueFalse();
			//получаем информацию
			$this->showInfoIpDstIpSrc();
			break;
			//при наличии совпадения между ipSrc, ipDst, sid и true_false
			case 3:
			$this->showInfoEqual();
			break;
			//ошибка
			default:
			echo MessageErrors::showInformationError();
			break;
			}
		}
	}

//вывод информации по IP-адресу назначения и номеру сигнатуры
private function showInfoIpDstSid()
	{
	$ipDst = ExactilyUserData::takeIP($_GET['ipDst']);
	$ip = ip2long($ipDst[0]);
	$sid = ExactilyUserData::takeIntager($_GET['sid']);
	$true = ExactilyUserData::takeIntager($_GET['true']);

	try{
		$query = self::getConnectionDB()->connectionDB()->prepare("SELECT DISTINCT t0.id FROM `incident_chief_tables` t0 
																   LEFT JOIN `incident_analyst_tables` t1 ON t0.id=t1.id 
																   LEFT JOIN `incident_number_signature_tables` t2 ON t0.id=t2.id 
																   WHERE `sid`=:sid AND `true_false`=:true AND `ip_dst`=:ipDst ORDER BY t0.id ASC");
		$query->execute(array(':ipDst' => $ip, ':sid' => $sid, 'true' => $true));		
		while($row = $query->fetch(PDO::FETCH_ASSOC)){
			$this->showInfo($row['id']);
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//поиск по IP-адресу источника и IP-адресу назначения
private function showInfoIpDstIpSrc()
	{
	$ipDst = ExactilyUserData::takeIP($_GET['ipDst']);
	$ipDst = ip2long($ipDst[0]);
	$ipSrc = ExactilyUserData::takeIP($_GET['ipSrc']);
	$ipSrc = ip2long($ipSrc[0]);

	$true = ExactilyUserData::takeIntager($_GET['true']);

	try{
		$query = self::getConnectionDB()->connectionDB()->prepare("SELECT DISTINCT t0.id FROM `incident_chief_tables` t0 
																   LEFT JOIN `incident_analyst_tables` t1 ON t0.id=t1.id 
																   WHERE `ip_src`=:ipSrc AND `ip_dst`=:ipDst AND `true_false`=:true ORDER BY t0.id ASC");
		$query->execute(array(':ipDst' => $ipDst, ':ipSrc' => $ipSrc, 'true' => $true));		
		while($row = $query->fetch(PDO::FETCH_ASSOC)){
			$this->showInfo($row['id']);
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//поиск по номеру компьютерного инцидента
private function showInfoEqual()
	{
	$stringId = ExactilyUserData::takeString($_GET['stringId']);
	$array = explode(',', $stringId);
	foreach($array as $id){
		$this->showInfo($id);
		}
	}

//формирование таблицы
private function showInfo($id)
	{
	$count = intval($_GET['count']);
	//определяем показывать таблицу или нет
	$display = 'none';
	if($count == 1){
		$display = 'block';
		}
	//при совпадении IP-адреса источника, IP-адреса назначения и номера сигнатуры выделяем найденные IP-адреса источника и номер сигнатуры
	if(!empty($_GET['idImpact'])){
		$arrayIdImpact = array();
		try{
			$query = self::getConnectionDB()->connectionDB()->prepare("SELECT `ip_src`, `sid` FROM `incident_chief_tables` t0 
																	   LEFT JOIN `incident_number_signature_tables` t1 ON t0.id=t1.id
																	   WHERE t0.id=:id");
			$query->execute(array(':id' => $_GET['idImpact']));		
			while($row = $query->fetch(PDO::FETCH_OBJ)){
				$arrayIdImpact['ip_src'][] = long2ip($row->ip_src);
				$arrayIdImpact['sid'][] = $row->sid;
				}
			}
		catch(PDOException $e){
			echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
			}
		}
	?>
	<div style="text-align: center; width: 980px;">
	<span style="font-size: 20px; font-family: 'Times New Roman', serif; letter-spacing: 2px; border-radius: 3px; color: #0000CC; cursor: pointer; text-decoration: underline;"
	onclick="(function(elem, count){
				if(count > 1)
					var table = elem.parentNode.nextSibling.nextSibling;
					if(table != undefined && table.nodeType == 1)
						if(table.style.display == 'none'){
							table.style.display = 'block';
							elem.style.textDecoration = 'none';
							} else {
							table.style.display = 'none';
							elem.style.textDecoration = 'underline';
							}
				})(this, <?= $count ?>)">
		компьютерное воздействие</span>
	<span style="font-size: 20px; font-family: 'Times New Roman', serif; letter-spacing: 2px; border-radius: 3px; color: #0000CC;"> №<?= $id ?></span>			
	</div>
	<table border="0" style="margin-top: 10px; position: relative; left: 38px; width: 904px; display: <?= $display ?>;">
<!-- дата добавления -->
		<tr>
			<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 452px; background: ;">
			добавлено
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px;">
			<?php
			//массив полученный в результате запроса даты добавления воздействия и логина пользователя 
			$array_date_name = AllInformationForIncident::showUserNameAddInformation(self::getConnectionDB(), $id);				
			//вывод даты добавления
			echo ConversionData::showDateConvert($array_date_name['date_create']); 
			?>		
			</td>
		</tr>
<!-- Ф.И.О добавившего воздействие -->
		<tr>
			<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 452px;">
			оперативный дежурный
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px;">
			<?php echo $this->ReadXMLSetup->usernameFIO($array_date_name['login_name']); ?>
			</td>
		</tr>
<!-- интервал времени инцидента -->
		<tr>
			<th class="mapFullInformationText" style="width: 452px; background: #FFEDB9;">
				начало компьютерного воздействия (дата/время)
			</th>
			<th class="mapFullInformationText" style="width: 452px; background: #FFEDB9;">
				конец компьютерного воздействия (дата/время)						
			</th>
		</tr>
		<tr>
			<td class="mapFullInformationText" style="text-align: center; width: 452px;">
			<?php
			//получаем массив содержащий начальную и конечную дату и время, IP-адреса источников и назначения и тип воздействия
			$array_ip_and_date = AllInformationForIncident::showDateStartAndEndAndIpSrcIpDst(self::getConnectionDB(), $id);
			//дата и время начала воздействия
			echo "с ".substr($array_ip_and_date['date_start'][0], -8, 8);
			echo " ".ConversionData::showDateConvert(strtotime($array_ip_and_date['date_start'][0]));
			?>
			</td>
			<td style="text-align: center; width: 452px; font-size: 14px; font-family: 'Times New Roman', serif;">
			<?php
			//дата и время конца воздействия
			echo "по ".substr($array_ip_and_date['date_end'][0], -8, 8);
			echo " ".ConversionData::showDateConvert(strtotime($array_ip_and_date['date_end'][0]));
			?>
			</td>
		</tr>
<!-- IP-адреса источников и назначения -->
		<tr>
			<th class="mapFullInformationText" style="width: 452px; background: #FFEDB9;">
			IP-адреса источники / количество обращений
			</th>
			<th class="mapFullInformationText" style="width: 452px; background: #FFEDB9;">
			IP-адрес назначения
			</th>
		</tr>
		<tr>
			<td class="mapFullInformationText" style="text-align: center; width: 452px;">
			<?php
			//IP-адреса источников
			for($i = 0; $i < count($array_ip_and_date['ip_src']); $i++){
				//IP-адрес источника
				if(!empty($_GET['idImpact'])){
					$ipSrc = (in_array(long2ip($array_ip_and_date['ip_src'][$i]), $arrayIdImpact['ip_src']))? "<span style='text-decoration: underline'>".long2ip($array_ip_and_date['ip_src'][$i])."</span>": long2ip($array_ip_and_date['ip_src'][$i]);
					} else {
					$ipSrc = long2ip($array_ip_and_date['ip_src'][$i]);
					}
				echo $ipSrc." (".$this->GeoIP->countryIP(self::getConnectionDB(), $array_ip_and_date['ip_src'][$i]); ?> 
				<img src= <?php echo "/{$this->directoryRoot}/img/flags/".$this->GeoIP->flags(); ?> />) / 
				<span style='color: #FF0000;'>
				<?php
				echo ($array_ip_and_date['count_impact'][$i] == 0) ? "нет данных" : $array_ip_and_date['count_impact'][$i];
				?></span><br><?php	
				}
			?>
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px;">
			<?php
			//IP-адреса назначения
			echo long2ip($array_ip_and_date['ip_dst'][0])."<br>";
			//и доменное имя если оно есть
			echo $this->ReadXMLSetup->obtainDomainName(long2ip($array_ip_and_date['ip_dst'][0]));
			?>
			</td>
		</tr>
<!-- доступность информационного ресурса -->
		<tr>
			<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 452px;">
			доступность информационного ресурса
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px;">
			<?php
			$array_space_traffic = AllInformationForIncident::showSpaceSafeNetTraffic(self::getConnectionDB(), $id);
			//доступность Web-ресурса						
			//	1 - доступен
			// 2 - недоступен
			echo ($array_space_traffic['availability_host'][0] == 1) ? "информационный ресурс доступен" : "<span style='color: red;'>зафиксирована недоступность информационного ресурса</span>";
			?>						
			</td>
		</tr>
<!-- направление воздействия -->
		<tr>
			<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 452px;">
			направление воздействия
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px;">
			<?php
			//информация о направлении воздействия
			//1 - input home network
			//0 - output home network
			echo ($array_space_traffic['direction_attack'][0] == 1) ? "воздействие направленно к home network" : "воздействие выполнялось из home network";
			?>									
			</td>
		</tr>
<!-- принятое по компьютерному воздействию решение -->
		<?php
		if(!empty($array_space_traffic['solution'])){
			?>
		<tr>
			<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 452px; background: #FFE4E1;">
			принятое решение
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px; background: #FFE4E1;">
			<?php echo $array_space_traffic['solution']; ?>									
			</td>
		</tr>
		<?php	
			}
		if(!empty($array_space_traffic['number_mail_in_CIB'])){
			?>
<!-- № письма в 18 Центр ФСБ России -->
		<tr>
			<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 452px; background: #FFE4E1;">
			номер письма в 18 Центр ФСБ России						
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px; background: #FFE4E1;">
			<?php echo "№149/2/1/".$array_space_traffic['number_mail_in_CIB']; ?>									
			</td>
		</tr>
		<?php	
			}
		if(!empty($array_space_traffic['number_mail_in_organization'])){
			?>
<!-- № письма в стороннюю организацию -->
		<tr>
			<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 452px; background: #FFE4E1;">
			номер письма в	стороннюю организацию					
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px; background: #FFE4E1;">
			<?php echo $array_space_traffic['number_mail_in_organization']; ?>									
			</td>
		</tr>
		<?php	
			}
		if(!empty($array_space_traffic['explanation'])){
			?>
<!-- пояснение дежурного -->
		<tr>
			<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 452px;">
			пояснение дежурного						
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px;">
			<?php
			echo FormattingText::formattingTextLength($array_space_traffic['explanation'], 65); 
			?>									
			</td>
		</tr>
		<?php } ?>
<!-- место нахождения отфильтрованного сетевого трафика -->
		<tr>
			<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 452px;">
			место нахождения отфильтрованного сетевого трафика						
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px;">
			<?php
			echo FormattingText::formattingTextLength($array_space_traffic['space_safe'], 65); 
			?>									
			</td>
		</tr>
<!-- Ф.И.О. выполнившего анализ сетевого трафика -->
		<tr>
			<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 452px;">
			анализ сетевого трафика выполнил						
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px;">
			<?php
			$array_space_traffic = AllInformationForIncident::showAllInformationAnalyst(self::getConnectionDB(), $id);
			//место нахождения отфильтрованного сетевого трафика
			echo $this->ReadXMLSetup->usernameFIO($array_space_traffic['login_name'][0]);
			?>									
			</td>
		</tr>
<!-- дата выполнения анализа сетевого трафика -->
		<?php
		if($array_space_traffic['date_time_analyst'][0]){
			?>
		<tr>
			<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 452px;">
			дата анализа						
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px;">
			<?php
			//дата анализа сетевого трафика
			echo ConversionData::showDateConvert($array_space_traffic['date_time_analyst'][0]); 
			?>									
			</td>
		</tr>
		<?php } ?>
<!-- количество пакетов информационной безопасности по мнению аналитика -->
		<tr>
			<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 452px;">
			количество пакетов информационной безопасности по мнению аналитика						
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px;">
			<?php echo ($array_space_traffic['count_alert_analyst'][0] == 0) ? "<span style='color: #000;'>подсчет не производился</style>" : "<span style='color: #FF0000;'>".$array_space_traffic['count_alert_analyst'][0]."</span>"; ?>									
			</td>
		</tr>
<!-- мнение аналитика -->
		<tr>
			<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 452px; background: #CCFFFF;">
			мнение аналитика по компьютерному воздействию
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px; background: #CCFFFF;">
			<?php
			if($array_space_traffic['true_false'][0] == "1")
				echo "компьютерная атака";
			elseif($array_space_traffic['true_false'][0] == "2")
				echo "ложное срабатывание";
			elseif($array_space_traffic['true_false'][0] == "3")
				echo "отсутствует сетевой трафик";
            elseif($array_space_traffic['true_false'][0] == "5")
                echo "сетевой трафик не рассматривался";
			?>									
			</td>
		</tr>
<!-- типа компьютерной атаки -->
		<?php
		//вывод информации только если компьютерная атака
		if($array_space_traffic['true_false'][0] == "1"){
			?>
			<tr>
				<td class="mapFullInformationText" style="padding-right: 10px; text-align: right; width: 302px; background: #CCFFFF;">
				тип компьютерной атаки	
				</td>
				<td class="mapFullInformationText" style="text-align: center; width: 302px; background: #CCFFFF;">
				<?php 
				$typeKa = $this->ReadXMLSetup->giveTypeKAForId($array_ip_and_date['type_attack'][0]);
				echo ($typeKa) ? $typeKa : 'тип компьютерной атаки не определен'; 
				?>
				</td>
			</tr>
			<?php
			}
		?>
<!-- информация аналитика -->
		<tr>
			<td class="mapFullInformationText" style="padding-right: 10px;text-align: right; width: 452px; background: #CCFFFF;">
			информация аналитика
			</td>
			<td class="mapFullInformationText" style="text-align: center; width: 452px; background: #CCFFFF;">
			<?php 
			echo FormattingText::formattingTextLength($array_space_traffic['information_analyst'][0], 65);  
			?>									
			</td>
		</tr>
<!-- номер сигнатуры/количество срабатываний -->
		<tr>
			<th class="mapFullInformationText" style="width: 402px; background: #FFEDB9;">
			номер сигнатуры / количество срабатываний
			</th>
			<th class="mapFullInformationText" style="width: 402px; background: #FFEDB9;">
			краткое описание сигнатуры
			</th>
		</tr>
		<?php
		//полная информация о сигнатурах						
		$array_signature = AllInformationForIncident::showSignature(self::getConnectionDB(), $id);
		$num = count($array_signature['sid']);
		//номер сигнатуры / количество срабатываний
		for($i = 0; $i < $num; $i++){
			?>
		<tr>
			<td class="mapFullInformationText" style="text-align: center; width: 452px;">
			<?php
			if(!empty($_GET['idImpact'])){
				$underline = (in_array($array_signature['sid'][$i], $arrayIdImpact['sid']))? 'text-decoration: underline;': '';
				} else {
				$underline = (isset($_GET['sid']) && $_GET['sid'] == $array_signature['sid'][$i])? 'text-decoration: underline;': ''; 
				}
			echo "<span style='".$underline."'>".$array_signature['sid'][$i]."</span> / <span style='color: #FF0000;'>";
			echo ($array_signature['count_alert'][$i] == 0) ? "нет данных" : $array_signature['count_alert'][$i];
			echo "</span></td>";
			?>
			<td class="mapFullInformationText" style="text-align: left; width: 452px;">
			<?php 
			echo " - ".$array_signature['short_message'][$i]."</td></tr>";							
			}						
			?>
	</table>
	<br>
	<?php
	}

}
?>