<?php

							/*----------------------------------------------*/
							/*  		класс вывода формы используемой 		*/
							/*		 для поиска компьютерных воздействий	*/
							/*												*/
							/* 	 						 v.0.2 24.03.2015   */
							/*----------------------------------------------*/

class SearchComputerImpactShowForm
{

private static $PDO;
private static $readXml;
const LIST_USER_NAME = 1;
const LIST_SENSOR_ID = 2;

//ресурс БД
private static function linkDB()
	{
	if(empty(self::$PDO)){
		self::$PDO = new DBOlink;
		}
	return self::$PDO;
	}

//читаем файл Xml
private static function getXmlObject()
	{
	if(empty(self::$readXml)){
		self::$readXml = new ReadXMLSetup;
		}
	return self::$readXml;
	}

//получить строку запроса 
private static function getPathString()
	{
	return explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	}

//получить корневую директорию сайта
private static function getDirectoryRoot()
	{
	return self::getPathString()[1];
	}

//получить роль пользователя
private static function getUserRole()
	{
	$array = array('10' => 'major', '20' => 'worker', '30' => 'analyst', '40' => 'admin');
	return $array[$_SESSION["userSessid"]["userId"]];
	}

//запрос ответственных за сенсора
private static function querySensorUserName()
	{
	return "SELECT DISTINCT(`login`) FROM `sensor_information_main_two`";
	}

//запрос идентификационных номеров сенсоров
private static function querySensorId()
	{
	return "SELECT `sensor_id` FROM `sensor_information_main_two`";
	}

//формирование выпадающего списка
private static function showList($query, $typeList)
	{
	try{
		$queryDB = self::linkDB()->connectionDB()->query($query);
		if($typeList == self::LIST_USER_NAME){
			?>
			<select class="formFiledsText formList" name="sensorUser" style="width: 145px; height: 23px;">
				<option value="">фамилия дежурного</option>
				<?php
				while($row = $queryDB->fetch(PDO::FETCH_OBJ)){
					$userName = explode(" ", self::getXmlObject()->giveUserNameAndSurname($row->login));
					echo "<option value='".$row->login."'>{$userName[0]}</option>";
					}	
				?>
			</select>			
			<?php
			}
		elseif($typeList == self::LIST_SENSOR_ID){
			?>
			<select class="formFiledsText formList" name="sensorId" style="width: 90px; height: 23px;">
				<option value="">id сенсора</option>
				<?php
				while($row = $queryDB->fetch(PDO::FETCH_OBJ)){
					echo "<option value='".$row->sensor_id."'>{$row->sensor_id}</option>";
					}	
				?>
			</select>			
			<?php
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//поисковая форма
public static function showSearchForm()
	{
	?>
	<div style="width: 960px; height: 100px;">
	<form name="searchForm" method="POST" action="">
		<div style="width: 735px; height: 110px; float: left; margin-left: 5px; display: inline-block;">
<!-- временной интервал -->
			<div style="width: 725px; margin-top: 10px; margin-left: 10px; height: 30px;">
<!-- выпадающий список временного интервала -->
				<div style="float: left; width: 240px;">
					<select class="formFiledsText formList" name="dateTimeRange" style="width: 135px; height: 23px;">
						<option value="">интервал времени</option>
						<option value="1">сутки</option>
						<option value="7">неделя</option>
						<option value="30">месяц</option>
						<option value="120">квартал</option>
						<option value="365">год</option>
					</select>
<!-- другой интервал времени -->
					<span id="textRangeTime" style="font-size: 14px; font-family: 'Times New Roman', serif; color: #0000FF; cursor: pointer; text-decoration: underline;">
						другой интервал
					</span>
				</div>
				<div style="float: left; width: 360px; text-align: left;">
<!-- IP-адрес источника -->
					<input class="formFields formFiledsText" type="text" name="srcIp" style="width: 105px;"  placeholder="IP-адрес источника" title="введите IP-адрес источника компьютерной атаки">
<!-- IP-адрес назначения -->
					<input class="formFields formFiledsText" type="text" name="dstIp" style="width: 105px;" placeholder="IP-адрес назначения" title="введите IP-адрес назначения компьютерной атаки">
					<?php ListBox::listDomainName() ?>
				</div>
<!-- неотправленные письма -->
				<div style="float: left; width: 120px; padding-top: 3px;">
					<input type="checkbox" name="mailNotNumber" title="неотправлненные письма">
					<span class="tableHeader" style="position: relative; top: -2px;">
						письма без номера
					</span>
				</div>				
			</div>
<!-- задать точный интервал даты и времени -->
			<div id="fullDateTime" style="display: none; margin-top: 3px; width: 735px; text-align: center; font-family: 'Times New Roman', serif;">
				<span style="font-size: 14px;">c</span>
				<input class="formFields formFiledsText" type="date" name="dateStart" style="width: 140px; margin-left: 10px;">
				<input class="formFields formFiledsText" type="time" name="timeStart" value="00:00" style="width: 70px; margin-left: 2px;"> 
				<span style="font-size: 14px;">по</span>
				<input class="formFields formFiledsText" type="date" name="dateEnd" style="width: 140px; margin-left: 2px;">
				<input class="formFields formFiledsText" type="time" name="timeEnd" value="23:59" style="width: 70px; margin-left: 2px;">
			</div>
			<div style="width: 735px; margin: 10px;">
				<?php
				//выпадающий список номеров сенсоров
				self::showList(self::querySensorId(), self::LIST_SENSOR_ID);
				//выпадающий список фамилий ответственных за сенсора
				self::showList(self::querySensorUserName(), self::LIST_USER_NAME);
				?>
<!-- номер письма в ЦИБ -->
				<input class="formFields formFiledsText" type="text" name="numMail" style="width: 85px;" placeholder="№ письма" title="номер письма в 18 Центр ФСБ России">
<!-- номер сигнатуры -->
				<input class="formFields formFiledsText" type="text" name="numSid" style="width: 85px;" placeholder="№ сигнатуры" title="номер сигнатуры">
<!-- номер компьютерного воздействия -->
				<input class="formFields formFiledsText" type="text" name="numImpact" style="width: 100px;" placeholder="№ воздействия" title="номер компьютерного воздействия">
<!-- выпадающий список стран -->
				<select class="formFiledsText formList" name="listCountry" style="width: 170px; height: 23px;">
					<option value="">страна источник</option>
					<?php 
					$GeoIP = new GeoIP();
					foreach($GeoIP::$codeCountry as $key=>$value){
						echo "<option value='$key'>$value</option>";
						}
					?>
				</select>				
			</div>
		</div>		
		<div style="width: 210px; height: 110px; display: inline-block; margin-left: 10px;">
<!-- выпадающий список типов КА -->	
			<div style="margin: 10px 20px; width: 170px;">
				<?php ListBox::listTypeKA(); ?>			
			</div>
<!-- решение аналитика -->	
			<div style="margin: 10px 20px;">
				<select class="formFiledsText formList" name="answerAnalyst" style="width: 170px; height: 23px;">
					<option value="">решение аналитика</option>
					<option value="1">компьютерная атака</option>
					<option value="2">ложное срабатывание</option>
					<option value="3">отсутствует сетевой трафик</option>
					<option value="4">сетевой трафик утерян</option>
                    <option value="5">сетевой трафик не рассматривался</option>
				</select>
			</div>
<!-- количество выводимых на страницу строк -->
			<div style="margin: 10px 20px;">
				<select class="formFiledsText formList" disabled="disabled" id="stringLimit" name="stringLimit" style="width: 50px; height: 23px;">
					<option value="20">20</option>
					<option value="40">40</option>
					<option value="60">60</option>
					<option value="80">80</option>
					<option value="100">100</option>
				</select>
<!-- кнопка "поиск" -->
			<a class="buttonG" id="buttonSearch" name="searchStart" href="#" style="position: relative; top: -2px; width: 60px; height: 16px;">поиск</a>
			</div>
		</div>
	</form>
	</div>
	<script>
	//поиск в массиве
	Array.prototype.in_array = function(value){
		for(var i = 0; i < this.length; i++){
			if(this[i] == value){
				return true;
			}
		}
		return false;
	}

	//показать поля ввода точного интервала времени
	function showFullFiledsTime(){
		var divFullDateTime = document.getElementById('fullDateTime');
		var textRangeTime = document.getElementById('textRangeTime');
		if(divFullDateTime.style.display == 'none'){
			divFullDateTime.style.display = 'block';
			textRangeTime.style.textDecoration = '';
		} else {
			divFullDateTime.style.display = 'none';
			textRangeTime.style.textDecoration = '';
		}
	}

	//функция конструктор содержащая поля формы
	function CheckFunctionCunstruct(){
		//значения полей формы
		this.getFormElements = document.forms.searchForm.elements;
		//массив имен полей формы применяемый для проверки заполненности всех необходимых полей
		this.arrayElementsTypeList = [ 'dateTimeRange', 'dstIPName', 'sensorUser', 'sensorId',
								  	   'listCountry', 'typeKA', 'answerAnalyst', 'mailNotNumber' ];
		//массив имен полей применяемый для проверки IP-адреса
		this.arrayElementsTypeIp = [ 'srcIp', 'dstIp' ];
		//массив имен полей применяемый для проверки числовой информации
		this.arrayElementsTypeIntager = [ 'numMail', 'numSid', 'numImpact' ];
		//массив имен полей применяемый для проверки даты
		this.arrayElementsTypeDate = [ 'dateStart', 'dateEnd', 'timeStart', 'timeEnd' ];
		//создаем общий массив элементов
		this.arrayElementsAllType = this.arrayElementsTypeList.concat(this.arrayElementsTypeIp, 
																	  this.arrayElementsTypeIntager, 
																	  this.arrayElementsTypeDate);
	}
	var CheckFunction = new CheckFunctionCunstruct();

	//функция проверки заполнения полей информации
	/*
	функция возвращает true когда заполнено хотя бы одно поле, 
	исключение составляют поля dateStart и dateEnd или выбран пункт одного из
	выпадающих списков, кроме списка количества выводимых на страницу найденных строк
	*/
	CheckFunction.checkFiledsForm = function (){
		var result = false;
		var fullField = false;
		var element;

		//убираем рамки вокруг полей
		for(var i = 0; i < this.getFormElements.length; i++){
			this.getFormElements[i].style.borderColor = '';
			var isFieldTime = (this.getFormElements[i].name == 'timeStart') || (this.getFormElements[i].name == 'timeEnd');
			var isArrayElements = this.arrayElementsAllType.in_array(this.getFormElements[i].name);
			var lengthElements = this.getFormElements[i].value.length > 0;
			if(!isFieldTime){
				if((isArrayElements) && (lengthElements)){
					fullField = true;
				}
			}
		}
		//проверяем заполненность какого либо из полей
		if(fullField == false){
			document.getElementById('fieldInformation').innerHTML = '<div style="text-align: center; font-size: 14px; font-family: \'Times New Roman\', serif; color: #FF0000;">необходимо заполнить одно или несколько полей</div>';
		}

		for(var numElement = 0; numElement < this.getFormElements.length; numElement++){
			element = this.getFormElements[numElement];
			//проверяем значение флажка "письма без номера"
			if(element.name == 'mailNotNumber' && element.checked === true){
				result = true;
				}
			//проверяем наличие значения в массиве arrayElementsName
			if((element.nodeType == 1) && (element.value.length > 0)){
				//пропускаем поля timeStart и timeEnd так как они всегда заполнены
				if(element.name == 'timeStart' || element.name == 'timeEnd') continue;
				//пропускаем значение флажка "письма без номера"
				if(element.name == 'mailNotNumber') continue;
				//проверяем выбор выпадающих списков
				if(this.arrayElementsTypeList.in_array(element.name)){ 
					result = true;
				}
				//проверяем заполненность полей с IP-адресами и корректность данных
				if(this.arrayElementsTypeIp.in_array(element.name)){
					if(this.checkIpAddress(element)){
						result = true;
					} else {
						result = false;
						break;
					}
				}
				//проверяем заполненность полей с числами и корректность данных
				if(this.arrayElementsTypeIntager.in_array(element.name)){
					if(this.checkIntegerValue(element)){
						result = true;
					} else {
						result = false;
						break;
					}
				}
				//проверяем заполненность полей с датой
				if(this.arrayElementsTypeDate.in_array(element.name)){
					if(this.checkDate(this.getFormElements)){
						result = true;
					} else {
						result = false;
						break;
					}
				}
			}
		}
		return result;
	}
	
	//проверка цифровых значений
	CheckFunction.checkIntegerValue = function(element){
		if(/^[0-9]+$/.test(element.value)) return true;
		element.style.borderColor = "red";
		return false;
	}

	//проверка IP-адресов
	CheckFunction.checkIpAddress = function(element){
		var ipPattern = /^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/;
		if(ipPattern.test(element.value)) return true;
		element.style.borderColor = "red";
		return false;
	}

	//проверка даты
	CheckFunction.checkDate = function(listElements){
		var datePattern = /^\d{4}-\d{2}-\d{2}$/;
		var dateStart = listElements.dateStart;
		var dateEnd = listElements.dateEnd;

		var dateStartLength = dateStart.value.length > 0;
		var dateEndLength = dateEnd.value.length > 0;

		var dateStartTrue = datePattern.test(dateStart.value);
		var dateEndTrue = datePattern.test(dateEnd.value);

		if(dateStartLength && dateEndLength && dateStartTrue && dateEndTrue) return true;

		dateStart.style.borderColor = 'red';
		dateEnd.style.borderColor = 'red';
		return false;
	}

	//формируем строку содержащую имя = значение
	function getQueryString(){
		var string = '';
		//элементы формы
		var formElements = CheckFunction.getFormElements;
		//общий массив значений
		var arrayElementsName = CheckFunction.arrayElementsAllType;
		for(var i = 0; i < formElements.length; i++){
			if(arrayElementsName.in_array(formElements[i].name)){
				if(formElements[i].name == 'numMail') continue;
				string += formElements[i].name + '=' + formElements[i].value + '&';
			}
		}
		if(formElements['mailNotNumber'].checked === true){
			string += 'numMail=99999999&';
		} else {
			string += 'numMail=' + formElements['numMail'].value + '&';
		}
		return string + 'stringLimit=' + document.getElementById('stringLimit').value + '&';
	}

	//отправка формы
	function sendForm(event){
		if(!CheckFunction.checkFiledsForm()){
			event.preventDefault();
		} else {
			var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', '/<?= self::getDirectoryRoot() ?>/<?= self::getUserRole() ?>/process/ajax_process.php?', '', 'searchStart=1&' + getQueryString());
			newObjectXMLHttpRequest.sendRequest();
			var divFieldInformation = document.getElementById('fieldInformation');
			divFieldInformation.innerHTML = '<div id="indicatorFacebook"><div id="facebook"><div class="facebook_block" id="block_1"></div><div class="facebook_block" id="block_2"></div><div class="facebook_block" id="block_3"></div></div></div>';		
		}
	}

	//добавление обработчиков
	window.onload = function(){
		//вывод дополнительных полей точного интервала времени
		document.getElementById('textRangeTime').addEventListener("click", showFullFiledsTime);
		//отправка формы после проверки полей
		document.getElementById('buttonSearch').addEventListener("click", sendForm);
	}
	</script>
	<?php
	}
}
?>