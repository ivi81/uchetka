
<?php
						
						/*++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		страница с информацией о сенсорах и 			*/
						/*   формой для редактирования данной информации 	*/
						/*													*/
						/*								v.0.1 11.08.2014 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 

//подключаем основную, для дежурного страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/worker/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

//объект для работы с информацией о сенсорах
$TotalInformationForSensor = new TotalInformationForSensor();

?>
<!-- подключаем скрипт для работы с XMLHttpRequest -->
	<script type="text/javascript" src='/<?= $array_directory[1] ?>/js/objectXMLHttpRequest.js'></script>
	<script type="text/javascript">
	//добавляем функцию обработчик каждой ссылке
	function setProcessingFunction(){
		var elementLinks = document.getElementsByTagName('A');
		for(var i = 0; i < elementLinks.length; i++){
			if(elementLinks[i].hasAttribute('sensorId')){
				elementLinks[i].onclick = new Function('setXmlHttpReguest(this)');
			}
		}
	}

	//функция отправки ajax запроса
	function setXmlHttpReguest(element){
		var sensorId = element.getAttribute('sensorId');
		var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', "/<?= $array_directory[1] ?>/worker/process/ajax_process.php", element, "informationSensorId=" + sensorId);
		newObjectXMLHttpRequest.sendRequest();
	}

	//функция отправки запроса для поиска информации по id сенсора
	function searchSensorId(){
		var elementDiv = document.getElementById('searchSensorId');
		if(elementDiv.childNodes[1].nodeType != 1) return false;
		var sensorId = elementDiv.childNodes[1].value;
		if(sensorId === undefined || sensorId.length == 0) return false;
		if(!/^[0-9]{1,}$/.test(sensorId)) return false;
		var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', "/<?= $array_directory[1] ?>/worker/process/ajax_process.php", elementDiv, "informationSensorId=" + sensorId);
		newObjectXMLHttpRequest.sendRequest();
		return false;
	}
	</script> 
<!-- ОБЩИЙ БЛОК для блока краткой информации и блока основного контента -->
	<div style="position: relative; top: 0; left: 0; width: 960px;">	
<!-- блок меню -->	
		<div id="menu" style="float: left; margin-bottom: 10px; width: 200px; min-height: 410px; background: #F8F8F8; display: inline-block;">
			<div id="searchSensorId" style="width: 200px; text-align: center; padding-top: 15px;">
				<input type="text" placeholder="№ сенсора" style="width: 100px;">
				<input type="button" onclick="return searchSensorId()" style="width: 50px;" value="поиск">
			</div> 
			<div style="position: relative; top: 10px; left: 10px; width: 180px; margin-bottom: 15px;">
				<?php $TotalInformationForSensor->showSensorInformationMenu() ?>
			</div>
		</div>
<!-- поле основной информации -->
		<div id="sensorInformation" style="float: left; margin-left: 5px; width: 755px; min-height: 410px; border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">
			<?php
			//изменяем информацию о выбранном сенсоре
			if(isset($_POST['changeInformation'])){
				$TotalInformationForSensor->changeInformation();
				}
			?>
			<div style="text-align: center;">
<!-- вывод краткой информации -->
				<?= $TotalInformationForSensor->showShortInformation(); ?>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		setProcessingFunction();
	</script>
<?php		
include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>