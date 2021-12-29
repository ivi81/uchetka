<?php

	/*
	*	Класс выводящий таблицу с информацией по ссылке получаемой с блока краткой информации.
	* 	Выводится следующая информация о:
	* 	- неразобранных воздействиях
	*	- ненайденном сетевом трафике												
	*	- неподготовленных письмах
	*	
	*														версия 0.1 13.02.2015
	 */
	
class BlockShortInformationTable
{
private $GeoIp;
private $ReadXMLSetup;
private static $DBO;
function __construct()
	{
	//объект БД GeoIP
	$this->GeoIp = new GeoIP();
	//объект для чтения файла setup_site.xml
	$this->ReadXMLSetup = new ReadXMLSetup;
	}

//устанавливаем соединение с БД
private static function getConnectionDB()
	{
	if(empty(self::$DBO)){
		self::$DBO = new DBOlink;
		}
	return self::$DBO;
	}

//функция вывода таблицы с информацией о компьютерном воздействии
//как параметр принимает строку состоящую из номеров компьютерных воздействий разделенных ":"
public function showInformationTable($stringId)
	{
	$arrayId = explode(':', ExactilyUserData::takeStringAll($stringId));
	$stringQueryId = '';
	$countArrayId = count($arrayId);
	for($i = 0; $i < $countArrayId; $i++){
		$stringQueryId .= ($i == $countArrayId - 1) ? "t0.id='".ExactilyUserData::takeIntager($arrayId[$i])."'" : "t0.id='".ExactilyUserData::takeIntager($arrayId[$i])."' OR ";
		}

	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT t0.id, `date_time_incident_start`, `date_time_incident_end`, `ip_src`, 
																`country`, `ip_dst`, `login_name` FROM `incident_chief_tables` t0 JOIN `incident_additional_tables` t1 
											  					 ON t0.id=t1.id WHERE ".$stringQueryId." ORDER BY t0.id, `date_time_incident_start`");
		//временный массив под IP-адреса источников		
		$array_tmp = $array_ip = array();
		while($row = $array_tmp[] = $query->fetch(PDO::FETCH_ASSOC)){
			$array_ip[$row['id']][] = $row['ip_src'];
			$array_ip["country_".$row['id']][] = $row['country'];
			} 
		?>
		<div style="margin: 10px; border-width: 1px; border-style: solid; width: 860px; border-color: #B7DCF7;">
		<table id="informationTable" border="0">
			<?php echo "<tr ".COLOR_HEADER.">"; ?>
				<th class="textHeadTable" style="width: 40px;">№</th>
				<th class="textHeadTable" style="width: 125px;">начальное<br>дата/время</th>
				<th class="textHeadTable" style="width: 125px;">конечное<br>дата/время</th>
				<th class="textHeadTable" style="width: 110px;">IP-адреса источники</th>
				<th class="textHeadTable" style="width: 160px;">страна</th>
				<th class="textHeadTable" style="width: 110px;">IP-адрес назначения</th>
				<th class="textHeadTable" style="width: 160px;">добавлен</th>
			<?php
			$idImpact = '';
			$countArrayTmp = count($array_tmp) - 1;
			for($i = 0; $i < $countArrayTmp; $i++){
				if($idImpact === $array_tmp[$i]['id']) continue;
				$idImpact = $array_tmp[$i]['id'];
				echo "<tr bgcolor=".color().">";
				?>
<!-- номер компьютерного воздействия -->
					<td class="textTable"><?= $array_tmp[$i]['id'] ?></td>
<!-- начальное дата/время -->
					<td class="textTable">
						<?php
						$array_date_start_tmp = explode(" ", $array_tmp[$i]['date_time_incident_start']);
						echo ConversionData::showDateConvertStr($array_date_start_tmp[0]).'<br>';
						echo $array_date_start_tmp[1];
						?>
					</td>
<!-- конечное дата/время -->
					<td class="textTable">
						<?php
						$array_date_start_tmp = explode(" ", $array_tmp[$i]['date_time_incident_end']);
						echo ConversionData::showDateConvertStr($array_date_start_tmp[0]).'<br>';
						echo $array_date_start_tmp[1];
						?>
					</td>
<!-- IP-адреса источники -->
					<td class="textTable">
						<?php
						for($j = 0; $j < count($array_ip[$array_tmp[$i]['id']]); $j++){
							echo long2ip($array_ip[$array_tmp[$i]['id']][$j]).'<br>';
							}
						?>
					</td>
<!-- страна -->
					<td class="textTable">
						<?php
						for($j = 0; $j < count($array_ip["country_".$array_tmp[$i]['id']]); $j++){
							$codeCountry = $array_ip["country_".$array_tmp[$i]['id']][$j];
							if(!isset($codeCountry)) $codeCountry = '10';
							echo $this->GeoIp->nameAndFlagsOfCode($codeCountry).'<br>';
							}						
						?>
					</td>
<!-- IP-адрес назначения -->	
					<td class="textTable"><?= long2ip($array_tmp[$i]['ip_dst']) ?></td>
<!-- кем был добавлен -->
					<td class="textTable"><?= $this->ReadXMLSetup->usernameFIO($array_tmp[$i]['login_name']) ?></td>
				</tr>
				<?php
				}
		echo "</table>";
		echo "</div>";
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
}
?>