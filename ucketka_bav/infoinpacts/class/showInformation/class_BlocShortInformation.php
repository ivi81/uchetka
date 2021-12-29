<?php

	/*
	*	Класс блока краткой информации о зафиксированным компьютерным воздействиям.
	*	Данный блок выводит следующую информацию:
	*	- количество пользователей on-line
	*	- общее количество записей о компьютерных воздействиях
	*	- общее количество ложных компьютерных воздействий
	*	- общее количество компьютерных воздействий за определенный год
	*	- общее количество ложных компьютерных воздействий за определенный год
	*	- количество компьютерных воздействий по которым не найден сетевой трафик
	*	- количество компьютерных воздействий по которым не не выполнен анализ
	*	- количество компьютерных воздействий по которым не подготовлено писем
	* 													
	* 													версия 0.1 12.02.2015
	 */

class BlocShortInformation
{
private static $DBO;
private $readXMLSetup;
private $directoryRoot;
private $directoryUser;
function __construct($directory)
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr($directory, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directoryRoot = $array_directory[1];
	$this->directoryUser = $array_directory[2];
	$this->readXMLSetup = new ReadXMLSetup();
	}

//устанавливаем соединение с БД
protected static function getConnectionDB()
	{
	if(empty(self::$DBO)){
		self::$DBO = new DBOlink;
		}
	return self::$DBO;
	}

//функция вывода блока краткой информации
public function showBlocShortInformation()
	{
	?>
	<style type="text/css">
	.propertiesText {
		font-family: 'Times New Roman', serif;
	}
	</style>
	<script type="text/javascript" src='/<?= $this->directoryRoot ?>/js/objectXMLHttpRequest.js'></script>
	<script type="text/javascript">
	//функция отслеживающая изменение пользователем года
	function changeYear(element){
		if(element.value != '') showInformationYear(element.value);
	}
	//функция изменяющая информацию в зависимости от выбранного года
	function showInformationYear(year){
		//Ajax запрос
		var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', '/<?= $this->directoryRoot."/".$this->directoryUser ?>/process/ajax_process.php', '', 
															   'queryShortInformationYear=' + year);
		newObjectXMLHttpRequest.sendRequest();	
		return true;
	}
	</script>
	<div style="position: relative; top: 5px; left: 0px; z-index: 10; width: 200px; min-height: 300px; border-radius: 3px; background: #F0FFFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">
<!-- пользователи on-line -->
		<div class="propertiesText" style="padding-top: 15px; text-align: center; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #3300FF;">
			пользователи on-line
		</div>
		<div class="propertiesText" style="margin-top: 10px; text-align: center; font-size: 14px; font-style: oblique; color: #000;">
			<?php $this->userOnLine() ?>
		</div>
<!-- общее количество воздействиё -->
		<div class="propertiesText" style="margin-top: 10px; text-align: center; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px;">
			воздействия
		</div>
		<div class="propertiesText" style="margin-top: 5px; text-align: center;">
			<span style="font-size: 12px;">всего в базе: </span>
			<span style="font-size: 20px; color: #000;"><?= $this->allCountImpact() ?></span>
		</div>
<!-- количество ложных воздействий -->
		<div class="propertiesText" style="text-align: center;">
			<span style="font-size: 12px;">из них ложных: </span>
			<span style="font-size: 20px; color: #000;"><?= $this->allCountFalseImpact() ?></span>
		</div>
<!-- текущий год или выбранный год -->
		<div id="choiceYear" class="propertiesText" style="margin-top: 5px; text-align: center; font-weight: bold;">
			<span style="font-size: 12px;">воздействия за </span> 
			<span style="font-size: 20px; color: #3300FF;"><?= $this->getCurrentYear() ?></span>
			<span style="font-size: 12px;"> год</span>
		</div>
<!-- воздействий за год -->
		<div id="allInformationYear" class="propertiesText" style="text-align: center;">
			<span style="font-size: 12px;">всего за год: </span>
			<span style="font-size: 20px;"><?= self::getCountImpactYear($this->getCurrentYear()) ?></span> 
		</div>
<!-- ложных воздействий за год -->
		<div id="falseInformationYear" class="propertiesText" style="text-align: center;">
			<span style="font-size: 12px;">из них ложных: </span>
			<span style="font-size: 20px;"><?= self::getCountFalseImpactYear($this->getCurrentYear()) ?></span> 
		</div>
<!-- выбор года -->
		<div class="propertiesText" style="margin-top: 10px; text-align: center;">
			<?php $this->getListSelectionYear(); ?>
		</div>
<!-- сейчас -->
		<div class="propertiesText" style="margin-top: 10px; text-align: center; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px;">
			сейчас
		</div>
<!-- количество компьютерных воздействий сетевой трафик по которым не найден -->
		<div class="propertiesText" style="text-align: center;">
			<span style="font-size: 12px;">не найден сетевой трафик: </span>
			<?php 
			$this->getCountCurrentInformationImpact($this->showCountNotShearchNetTraffic()); 
			?>
		</div>
<!-- количество компьютерных воздействий сетевой трафик по которым был утерян -->
		<div class="propertiesText" style="text-align: center;">
			<span style="font-size: 12px;">сетевой трафик утерян: </span>
			<?php 
			$this->getCountCurrentInformationImpact($this->showCountLostNetTraffic()); 
			?>
		</div>
<!-- количество компьютерных воздействий по которым не выполнен анализ -->
		<div class="propertiesText" style="text-align: center;">
			<span style="font-size: 12px;">не выполнен анализ: </span>
			<?php 
			$this->getCountCurrentInformationImpact($this->showCountNotSeeAnalyst()); 
			?>
		</div>
<!-- количество компьютерных воздействий не подготовлено писем -->
		<div class="propertiesText" style="padding-bottom: 10px; text-align: center;">
			<span style="font-size: 12px;">не подготовлено писем: </span>
			<?php 
			$this->getCountCurrentInformationImpact($this->showCountNotMail()); 
			?>
		</div>
	</div>
	<?php
	}

//функция вывода информации о зарегистрированных пользователях on-line
private function userOnLine()
	{
	$array_name = array();
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT `user_login` FROM `user_session` WHERE `authorization`='yes'");
		while($row = $query->fetch(PDO::FETCH_ASSOC)){
			if($row['user_login'] != 'admin'){
				$array_name[] = $this->readXMLSetup->usernameFIO($row['user_login']);
				}
			}
		natsort($array_name);
		foreach($array_name as $user_name){
			if(strpbrk($user_name, ' ')){
				list($sorname, $name,) = explode(' ', $user_name);
				echo $name." ".$sorname."<br>";
				} else {
				echo $user_name."<br>";
				}
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//функция вывода общего количества записей о компьютерных воздействиях
private function allCountImpact()
	{
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT COUNT(*) AS NUMBER FROM `incident_additional_tables`");
		return $query->fetch(PDO::FETCH_OBJ)->NUMBER;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//функция вывода общего количества ложных компьютерных воздействий
private function allCountFalseImpact()
	{
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT COUNT(*) AS NUMBER FROM `incident_analyst_tables` WHERE `true_false`='2'");
		return $query->fetch(PDO::FETCH_OBJ)->NUMBER;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//функция определения текущего года
private function getCurrentYear()
	{
	return date("Y", time());	
	}

//функция вывода общего количества компьютерных воздействий за определенный год
public static function getCountImpactYear($year)
	{
	$checkedYear = self::checkYear($year);
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT COUNT(`id`) AS COUNT_ID FROM 
																(SELECT `id` FROM `incident_chief_tables` 
																 WHERE YEAR(`date_time_incident_start`)='".$checkedYear."' GROUP BY `id`) AS t0");
		return $query->fetch(PDO::FETCH_OBJ)->COUNT_ID;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//функция вывода общего количества ложных компьютерных воздействий за определенный год
public static function getCountFalseImpactYear($year)
	{
	$checkedYear = self::checkYear($year);		
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT COUNT(`id`) AS COUNT_ID FROM (SELECT t0.id FROM `incident_chief_tables` t0 JOIN `incident_analyst_tables` t1
																 ON t0.id=t1.id WHERE YEAR(`date_time_incident_start`)='".$checkedYear."' AND `true_false`='2' GROUP BY `id`) AS t0");
		return $query->fetch(PDO::FETCH_OBJ)->COUNT_ID;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	}

//функция вывода информации о количестве не найденного сетевого трафика
private function showCountNotShearchNetTraffic()
	{
	$array = array();
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT `id` FROM `incident_analyst_tables` WHERE `true_false`='3'");
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$array[] = $row->id;
			}
		return $array;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//функция вывода информации о количестве утерянного сетевого трафика
private function showCountLostNetTraffic()
	{
	$array = array();
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT `id` FROM `incident_analyst_tables` WHERE `true_false`='4'");
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$array[] = $row->id;
			}
		return $array;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	}

//функция вывода информации о не выполненном анализ компьютерного воздействия
private function showCountNotSeeAnalyst()
	{
	$array = array();
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT t0.id FROM `incident_chief_tables` t0 INNER JOIN `incident_additional_tables` t1 ON t0.id = t1.id 
									LEFT JOIN `incident_analyst_tables` t2 
 									 ON t1.id = t2.id WHERE t2.id is Null GROUP BY t1.id");
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$array[] =$row->id;
			}
		return $array;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//функция вывода о не подготовленных письмах
private function showCountNotMail()
	{
	$array = array();
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT t0.id FROM `incident_additional_tables` t0 LEFT JOIN `incident_analyst_tables` t1 
 											     				 ON t0.id = t1.id WHERE t1.true_false='1' AND (t0.number_mail_in_CIB is Null)");
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$array[] =$row->id;
			}
		return $array;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//функция построения выпадающего списка с имеющимися в БД годами
private function getListSelectionYear()
	{
	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT `date_time_incident_start` AS YEARS FROM `incident_chief_tables` 
																 GROUP BY YEAR(`date_time_incident_start`)");
		?>
		<select name="listYears" onchange="changeYear(this)" style="width: 100px; height: 23px; font-size: 11px;">
			<option value="">выберите год</option>
		<?php
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$year = substr($row->YEARS, 0, 4);
			echo "<option value='".$year."'>{$year}</option>";
			}
		echo "</select>";
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	}

//функция проверяющая корректность даты (года)
private static function checkYear($year)
	{
	if(preg_match("/^[0-9]{4}$/", $year)) return $year;
	return false;
	}

//функция выводящая актуальную на текущий момент информация о количестве
// - не подготовленных писем
// - не найденный сетевой трафик
// - не выполненных компьютерных воздействиях
// - утерянный сетевой трафик
//данные выводятся разным цветом и позволяют просмотреть детальную информацию
private function getCountCurrentInformationImpact(array $arrayInformationImpact)
	{
	$countImpact = count($arrayInformationImpact);
	$arrayString = implode(':', $arrayInformationImpact);
	if($countImpact === 0){ 
		echo "<span style='color: #3300FF; font-size: 20px;'>{$countImpact}</span>";
		} else {
		?>
		<style type="text/css">
		a {
			color: #FF0000;
		}
		a:visited {
			color: #3300FF;
		}
		a:hover {
			text-decoration: none;
		}
		</style>
		<a href="/<?= $this->directoryRoot.'/'.$this->directoryUser ?>/process/showInformationImpact.php?stringId=<?= $arrayString ?>" target="_blank" onclick="popupWin = window.open(this.href, 'displayWindow', 'location,width=900,height=500,status=no,toolbar=no,menubar=no,scrollbars=no'); popupWin.focus(); return false;">
			<span style="font-size: 20px;"><?= $countImpact ?></span>
		</a>
		<?php
		}
	}

}

?>