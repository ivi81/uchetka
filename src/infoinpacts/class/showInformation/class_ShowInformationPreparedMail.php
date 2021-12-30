<?php

							/*----------------------------------------------------------------------*/
							/*  класс вывода информации о подготовленных письмах	 (Руководство)	*/
							/* 	 											 v.0.1 21.10.2014       */
							/*----------------------------------------------------------------------*/

class ShowInformationPreparedMail
{
//директория сайта
public static $directory;
//объект подключения к БД
private static $DBO;

function __construct()
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	self::$directory = $array_directory[1];
	}

//формируем подключение к БД
private static function linkDataBase()
	{
	if(empty(self::$DBO)){
		//объект для подключения к БД
		self::$DBO = new DBOlink(); 
		}
	return self::$DBO;
	}

//поиск номеров подготовленных письмах
public function searchPreparedMail($numLimit)
	{
	try{
		$query = self::linkDataBase()->connectionDB()->query("SELECT `date_time_incident_start`, `number_mail_in_CIB` FROM `incident_chief_tables` t0 
											    			  LEFT JOIN `incident_additional_tables` t1 ON t0.id=t1.id LEFT JOIN `incident_analyst_tables` t2 
											    			  ON t0.id=t2.id WHERE `true_false`=1 AND (`number_mail_in_CIB` is not Null) AND (`number_mail_in_CIB`!='0') 
											    			  GROUP BY `number_mail_in_CIB` ORDER BY `date_time_incident_start` DESC LIMIT ".$numLimit);
	
		echo '<ul>';
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			?>
			<li onclick="(function(){ var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', '/<?= self::$directory ?>/major/process/ajax_process.php', this, 'queryMailMajorInfo=<?= $row->number_mail_in_CIB ?>');
							newObjectXMLHttpRequest.sendRequest()})();" 
				style="cursor:pointer; text-decoration: underline; color: #000080; list-style-type: none;">
			<?php
			echo '№149/2/1/'.$row->number_mail_in_CIB;
			echo "</li>";
			}
			echo "</ul>";
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//поиск краткой информации о подготовленных письмах
public function searchShortInfoMail($mailNum)
	{
	//объект чтения файла XML
	$ReadXMLSetup = new ReadXMLSetup;
	//объект БД GeoIP
	$GeoIP = new GeoIP();
	try{
		$query = self::linkDataBase()->connectionDB()->query("SELECT t0.id AS id, `date_time_incident_start`, `date_time_incident_end`, `ip_src`, `country`, `ip_dst`, t1.login_name AS `dlogin`, t2.login_name AS `alogin` 
											  				  FROM `incident_chief_tables` t0 LEFT JOIN `incident_additional_tables` t1 ON t0.id=t1.id 
											  				  LEFT JOIN `incident_analyst_tables` t2 ON t1.id=t2.id WHERE t0.id IN (SELECT `id` FROM `incident_additional_tables` 
											  				  WHERE `number_mail_in_CIB`='".$mailNum."') ORDER BY `date_time_incident_start` ASC");
		//временный массив под IP-адреса источников		
		$array_tmp = $array_id = $array_ip_src = $array_ip_dst = $array_alogin = $array_alogin_tmp = $array_dlogin = $array_dlogin_tmp = array();
		while($row = $array_tmp[] = $query->fetch(PDO::FETCH_ASSOC)){
			$array_id[] = $row['id'];
			$array_ip_dst[] = $row['ip_dst'];
			$array_ip_src[] = $row['ip_src'];
			$array_alogin_tmp[$row['id']][] = $row['alogin'];
			$array_dlogin_tmp[$row['id']][] = $row['dlogin'];
			}
		//логин дежурных
		foreach($array_dlogin_tmp as $id){
			foreach($id as $login){
				$array_dlogin[] = $login;
				}
			}
		//логин аналитика
		foreach($array_alogin_tmp as $id){
			foreach($id as $login){
				$array_alogin[] = $login;
				}
			}
		//получаем список номеров компьютерных воздействий
		$stringId = implode(',', $array_id);
		//и их количество
		$countId = count($array_id);
		//группируем логины дежурных
		$array_dlogin = array_unique($array_dlogin);	
		//группируем логины аналитиков
		$array_alogin = array_unique($array_alogin);
		//группируем ip_dst
		$array_ip_dst = array_unique($array_ip_dst);
		//группируем Ip_src
		$array_ip_src = array_unique($array_ip_src);
		?>
		<div style="position: relative; left: 25px; top: 15px; width: 535px; border-radius: 5px; border-width: 1px; border-style: solid; border-color: #00C5Cb;"><br>
<!-- временной интервал -->
			<div style="text-align: center;">
				<span style="font-size: 12px; font-family; 'Times New Roman', serif; font-weight: bold;">
					интервал времени компьютерных воздействий
				</span><br>
				<span style="font-size: 14px; font-family; 'Times New Roman', serif;">
					<?php
					//начальная и конечная даты
					$dateStart = $array_tmp[0]['date_time_incident_start'];
					$dateEnd = $array_tmp[((count($array_tmp)) - 2)]['date_time_incident_end'];
					//начальный и конечный день
					$dayStart = (int) substr($array_tmp[0]['date_time_incident_start'], 8, 2);
					$dayEnd = (int) substr($array_tmp[((count($array_tmp)) - 2)]['date_time_incident_end'], 8, 2);
					//если начальное и конечное число совпадает
					if($dayStart == $dayEnd){
						echo ConversionData::showDateConvertStr($dateStart);
						} else {
						//проверяем совпадает ли месяц
						if((substr($dateStart, 5, 2)) == (substr($dateEnd, 5, 2))){
							echo "c {$dayStart} по ".ConversionData::showDateConvertStr($dateEnd);		
							} else {
							$yearStart = substr($dateStart, 0, 4);
							$yearEnd = substr($dateEnd, 0, 4);
							if($yearStart == $yearEnd){
								echo "с {$dayStart} ".ConversionData::showMonth($dateStart)." по {$dayEnd} ".ConversionData::showMonth($dateEnd)." {$yearStart} года";
								} else {
								echo "с {$dayStart} ".ConversionData::showMonth($dateStart)." {$yearStart} года по {$dayEnd} ".ConversionData::showMonth($dateEnd)." {$yearEnd} года";
								}
							}	
						}	
					?>
				</span>
			</div>
<!-- Ф.И.О. дежурного -->
			<div style="text-align: center;">
				<span style="font-size: 12px; font-family; 'Times New Roman', serif; font-weight: bold;">
					информацию о компьютерных воздействиях добавил:
				</span><br>
				<?php
				foreach($array_dlogin as $login){
					echo "<span style='color: #000; font-size: 14px; font-family; 'Times New Roman', serif;'>".$ReadXMLSetup->usernameFIO($login)."</span><br>";
					}
				?>
			</div>
<!-- Ф.И.О. аналитика -->
			<div style="text-align: center;">
				<span style="font-size: 12px; font-family; 'Times New Roman', serif; font-weight: bold;">
					анализ компьютерных воздействий выполнил:
				</span><br>
				<?php
				foreach($array_alogin as $login){
					echo "<span style='color: #000; font-size: 14px; font-family; 'Times New Roman', serif;'>".$ReadXMLSetup->usernameFIO($login)."</span><br>";
					}
				?>
			</div>
<!-- цели компьютерных воздействий -->
			<div style="text-align: center;">
				<span style="font-size: 12px; font-family; 'Times New Roman', serif; font-weight: bold;">
					цели компьютерных воздействий
				</span><br>
				<?php
				foreach($array_ip_dst as $ip_dst){
					?>
					<span style="color: #000; font-size: 14px; font-family; 'Times New Roman', serif;"><?= long2ip($ip_dst)?></span> 
					<span style="font-style: italic; color: #000080; font-size: 14px; font-family; 'Times New Roman', serif;"><?=$ReadXMLSetup->obtainDomainName(long2ip($ip_dst))?></span><br>
					<?php
					}
				?>
			</div>
<!-- источники компьютерных воздействий -->
			<div style="text-align: center;">
				<span style="font-size: 12px; font-family; 'Times New Roman', serif; font-weight: bold;">
					источники компьютерных воздействий
				</span><br>
				<?php
				foreach($array_ip_src as $ip_src){
					?>
					<span style="color: #000; font-size: 14px; font-family; 'Times New Roman', serif;"><?= long2ip($ip_src) ?></span>
					<span>
						<?php $GeoIP->countryIP(self::linkDataBase(), $ip_src)." "; ?> 
						<img src= <?php echo "/".self::$directory."/img/flags/".$GeoIP->flags(); ?> />
					</span><br>
					<?php
					}
				?>
			</div>
<!-- кнопка 'подробно' -->
			<div style="position: relative; top: 5px; left: 435px; width: 83px;">
				<a class="buttonG" href='<?php echo "/".self::$directory."/major/process/showAllInformationForId.php?stringId={$stringId}&number=3&count={$countId}" ?>' target="_blank">подробно</a>
			</div><br>
		</div><br>
		<?php
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	}
}