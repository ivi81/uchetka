<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		страница добавления, удаления и редактирования 	*/
						/*				данных о сенсарах						*/
						/*									v.0.1 09.02.2015 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 

//подключаем основную страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/admin/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

//объект для чтения файла setup_site.xml
$ReadXMLSetup = new ReadXMLSetup;

?>
<style type="text/css">
	.addNewSensor {
		font-size: 12px; 
		font-family: 'Times New Roman', serif; 
		letter-spacing: 1px; 
		color: #0000CD;
		text-decoration: underline;
		text-align: center;
		cursor: pointer;
		}
	.addNewSensor:hover {
		text-decoration: none;
		}	

	.textHead {
		text-align: center;
		font-family: 'Times New Roman', serif; 
		font-size: 14px; 
		color: #1C1C1C;
		}
</style>
<script type="text/javascript">
	//функция изменение картинок
	function chageImg(num, elem){
		switch(num){
			case 1:
				elem.src = '<?php echo "/{$array_directory[1]}/img/button_minus.png"; ?>';
			break;
			case 2:
				elem.src = '<?php echo "/{$array_directory[1]}/img/button_minus_1.png"; ?>';
			break;
			case 3:
				elem.src = '<?php echo "/{$array_directory[1]}/img/button_plus.png"; ?>';			
			break;
			case 4:
				elem.src = '<?php echo "/{$array_directory[1]}/img/button_plus_1.png"; ?>';			
			break;
		}
	}

	//функция вывода формы добавления нового сенсора
	function formView(){
		var div = document.getElementById('addNewSensorForm');
		if(div.style.display == 'none'){
			div.style.display = 'block';
		} else {
			div.style.display = 'none';
		}
	}

	//функция добавления полей IP-адресов
	function addElemIp(){
		//создаем универсальный div
		createDiv = function(){
			var div = document.createElement('DIV');
			div.setAttribute('class', 'textHead');
			div.setAttribute('style', 'float: left; width: 360px; margin-top: 5px;');
			return div;
		};
		//создаем универсальный input
		createInput = function(){
			var input = document.createElement('INPUT');
			input.setAttribute('type', 'text');
			input.setAttribute('style', 'width: 140px; height: 15px; border-color: #C6E2FF;');
			return input;
		};

		var inputIp = document.getElementById('inputIp');

		//создаем поле ipStart
		var inputIpStart = createInput();
		var divStart = createDiv();
		inputIpStart.setAttribute('name', "ipStart[]");
		divStart.appendChild(inputIpStart);
		inputIp.appendChild(divStart);
		//создаем поле ipEnd
		var inputIpEnd = createInput();
		var divEnd = createDiv();
		inputIpEnd.setAttribute('name', "ipEnd[]");
		divEnd.appendChild(inputIpEnd);
		inputIp.appendChild(divEnd);
	}

	//функция удаления полей IP-адресов
	function delElemIp(){
		var inputIp = document.getElementById('inputIp');
		var inputIpChild = inputIp.childNodes;
		for(var i = inputIpChild.length; i > 1; i--){
			if(inputIpChild[i] == null || inputIpChild[i].nodeName != 'DIV') continue;
			if(!inputIpChild[i].hasAttribute('main')){
				inputIp.removeChild(inputIpChild[i]);
				inputIp.removeChild(inputIpChild[i - 1]);
				break;
				}
			}
		}

	//функция проверки формы
	function checkFormData(elemForm){

		function showBorderColor(elem){
			elem.style.borderColor = 'red';
			return false;
			}

		function checkInteger(integer){
			var numPattern = /^[0-9]+$/;			
			if(!numPattern.test(integer.value)) return showBorderColor(integer);
			return true;
			}

		function checkIpAddress(ip){
			var ipPattern = /^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/;
			if(!ipPattern.test(ip.value)) return showBorderColor(ip);
			return true;
			}

		//убираем рамку вокруг элемента
		for(var j = 0; j < elemForm.length; j++){
			elemForm[j].style.borderColor = '';
			}

		for(var i = 0; i < elemForm.length; i++){
			//пропускаем кнопку
			if(elemForm[i].name == 'save') continue;
			//проверяем на пустоту
			if(elemForm[i].value.length == 0) return showBorderColor(elemForm[i]); 
 			//если это поле ввода чисел
 			if(elemForm[i].name == 'sensorId' && checkInteger(elemForm[i]) !== true) return checkInteger(elemForm[i]);
 			//если это поле ввода IP-адреса
 			if((elemForm[i].name == 'sensorIp' || elemForm[i].name == 'ipStart[]' || elemForm[i].name == 'ipEnd[]') 
 				&& checkIpAddress(elemForm[i]) !== true) return checkIpAddress(elemForm[i]);
 			//проверяем выбор ответственного 
 			if(elemForm[i].name == 'userName' && elemForm[i].value == 'null') return showBorderColor(elemForm[i]);
			}
		return true;
		}

	//функция добавления обработчика
	function pageOnload(){
		//обработчик вывода формы добавления нового сенсора
		var elementAddNewSensor = document.getElementById('addNewSensor');
		if(elementAddNewSensor){
			elementAddNewSensor.addEventListener("click", formView, false);
			}

		//обработчик для изображения удаления IP-адресов
		var delIp = document.getElementById('delIp');
		if(delIp){
			//обработчик удаляющий поле IP-адресов
			delIp.addEventListener("click", delElemIp, false);		
			delIp.addEventListener("mouseout", function (){ chageImg(1, this) }, false);
			delIp.addEventListener("mouseover", function (){ chageImg(2, this) }, false);
		}
		//обработчик для изображения добавления IP-адресов
		var addIp = document.getElementById('addIp');
		if(addIp){
			//обработчик добавляющий поле IP-адресов
			addIp.addEventListener("click", addElemIp, false);
			addIp.addEventListener("mouseout", function (){ chageImg(3, this) }, false);
			addIp.addEventListener("mouseover", function (){ chageImg(4, this) }, false);
		}
	}
	window.onload = pageOnload;
</script>

<!-- поле основной информации -->
	<div style="position: relative; top: -1px; left: 210px; display: inline-block; z-index: 10; width: 745px; min-height: 500px; border-radius: 3px; box-shadow: inset 0 0 8px 0px #B7DCF7;">
	<?php
	//объект для работы с информацией о сенсорах
	$TotalInformationForSensor = new TotalInformationForSensorAdmin();
	/* изменение информации о сенсоре
	------------------------------- */
	if(isset($_POST['changeSensorId'])){
		$TotalInformationForSensor->showInformationChange();
		} 
	elseif(isset($_POST['changeInformation'])){
		$TotalInformationForSensor->changeInformation();
		} else {
	?>
<!-- форма добавления нового сенсора -->
		<div style="position: relative; top: 10px; left: 10px; margin-top: 10px;">
<!-- ссылка добавления нового сенсора -->
			<div id="addNewSensor" class="addNewSensor">добавление нового сенсора</div>
			<div id="addNewSensorForm" style="display: none; overflow: auto; background: #FFFAF0; width: 725px;">
<!-- форма для добавления нового сенсора -->
			<form name="formAddNewSensor" method="POST" action="" onsubmit="return checkFormData(this)">
				<div style="overflow: hidden; mardin: 10px; width: 725px;">
					<div class="textHead" style="margin-top: 5px; margin-left: 12px; width: 230px; float: left;">
						id сенсора
					</div>
					<div class="textHead" style="margin-top: 5px; margin-left: 5px; width: 230px; float: left;">
						IP-адрес сенсора
					</div>
					<div class="textHead" style="margin-top: 5px; margin-left: 5px; width: 230px; float: left;">
						Ф.И.О. ответственного
					</div>
<!-- id-сенсора (поле ввода) -->
					<div class="textHead" style="margin-top: 5px; margin-left: 12px; width: 230px; float: left;">
						<input type="text" name="sensorId" style="border-color: #C6E2FF;">
					</div>
<!-- ip-сенсора (поле ввода) -->
					<div class="textHead" style="margin-top: 5px; margin-left: 5px; width: 230px; float: left;">
						<input type="text" name="sensorIp" style="border-color: #C6E2FF;">
					</div>
<!-- Ф.И.О. ответственного (поле ввода) -->
					<div class="textHead" style="margin-top: 5px; margin-left: 5px; width: 230px; float: left;">
						<?php ListBox::listUserName(20) ?>
					</div>
				</div>
<!-- диапазон защищаемых сетей (заголовок) -->
				<div class="textHead" class="textHead" style="padding-top: 10px; padding-bottom: 5px; width: 725px;">
					<span style="font-weight: bold;">диапазон защищаемого сегмента сети или группы сетей</span>
				</div>
				<div style="overflow: hidden; width: 725px;">
					<div class="textHead" style="float: left; width: 360px; padding-bottom: 5px;">
						<img id="delIp" src='<?php echo "/{$array_directory[1]}/img/button_minus.png"; ?>' style="cursor: pointer;" title="удалить IP-адрес"/>
						начальный IP-адрес
					</div>
					<div class="textHead" style="float: left; width: 360px; padding-bottom: 5px;">
						конечный IP-адрес
						<img id="addIp" src='<?php echo "/{$array_directory[1]}/img/button_plus.png"; ?>' style="cursor: pointer;" title="добавить IP-адрес"/>
					</div>
					<div id="inputIp">
<!-- поле ввода начального IP-адреса -->					
						<div class="textHead" main="mainIp" style="float: left; width: 360px;">
							<input type="text" name="ipStart[]" style="width: 140px; height: 15px; border-color: #C6E2FF;">						
						</div>
<!-- поле ввода конечного IP-адреса -->					
						<div class="textHead" main="mainIp" style="float: left; width: 360px;">
							<input type="text" name="ipEnd[]" style="width: 140px; height: 15px; border-color: #C6E2FF;">						
						</div>
					</div>
					<div class="textHead" style="float: left; width: 230px; padding-top: 10px; padding-bottom: 5px; margin-left: 10px;">
						название защищаемого сегмента
					</div>
					<div class="textHead" style="float: left; width: 230px; padding-top: 10px; padding-bottom: 5px; margin-left: 8px;">
						информация о защищаемом сегменте
					</div>
					<div class="textHead" style="float: left; width: 230px; padding-top: 10px; padding-bottom: 5px; margin-left: 8px;">
						контактные данные
					</div>
<!-- краткое название защищаемого сегмента -->
					<div class="textHead" style="float: left; width: 230px; padding-top: 5px; padding-bottom: 5px; margin-left: 10px;">
						<input type="text" name="segmentName" style="width: 180px; height: 15px; border-color: #C6E2FF;">
					</div>
<!-- полное название защищаемого сегмента -->
					<div class="textHead" style="float: left; width: 230px; padding-top: 5px; padding-bottom: 5px; margin-left: 8px;">
						<textarea type="text" name="segmentInfo" style="width: 200px; height: 45px; border-color: #C6E2FF;"></textarea>
					</div>
<!-- контактные данные ответственного -->
					<div class="textHead" style="float: left; width: 230px; padding-top: 5px; padding-bottom: 5px; margin-left: 8px;">
						<textarea type="text" name="segmentContacts" style="width: 200px; height: 45px; border-color: #C6E2FF;"></textarea>
					</div>
				</div>
<!-- кнопка "сохранить" -->
				<div style="margin: 10px; text-align: right;">
					<input type="submit" name="save" style="border-color: #87CEEB; background: #E0EEEE; width: 100px; height: 20px;" value="сохранить">
				</div>
			</form>
			</div>
		</div>
	<?php
	/* добавление информации в БД
	----------------------------*/
	if(isset($_POST['save'])){
		$TotalInformationForSensor->addInformation();
		}
	?> 
<!-- вывод информации -->
		<div style="position: relative; top: 10px; left: 10px; margin-top: 10px;">
		<?php
		/* вывод информации
		------------------*/
		$TotalInformationForSensor->showInformation();
		/* удаление информации
		---------------------*/
		if(isset($_POST['delete'])){
			$TotalInformationForSensor->deleteAllInformation();
			}
		}
		?>
		</div>
	</div><br><br><br>
</div>
<?php		
include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>