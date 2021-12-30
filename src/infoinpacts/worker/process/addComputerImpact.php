<?php
						
						/*++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		страница с формой для ввода дежурными 		*/
						/*		информации о компьютерных воздействиях		*/
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

?>
<!-- область для вывода информации по компьютерному воздействию -->
		<div style="position: relative; top: 0px; left: -10px; width: 980px; border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">
			<div style="position: relative; top: 20px; left: 20px; text-align: center; width: 940px; font-size: 20px; font-family: 'Times New Roman', serif; letter-spacing: 2px; margin: 10px 0px; background: #CCFFFF;">
			введите информацию о компьютерном воздействии
			</div>
<!-- таблица содержащая всю информацию -->
			<div style="position: relative; top: 40px; left: 20px; margin: 10px 0px;">
				<form name="addIncident" method="POST" action="addComputerImpact_process.php" onsubmit="return validateForm()">				
				<table border="0" width="940px">
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
							<input type="date" name="dateStart" style="width: 140px; height: 20px;"><input type="time" name="timeStart" value="00:00" style="width: 70px; height: 20px;">
							<span style="font-size: 10px; font-family: Verdana, serif; position: relative; top: 0px; left: 5px; color: #CD0000;">*</span>
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
							<input type="date" name="dateEnd" style="width: 140px; height: 20px;"><input type="time" name="timeEnd" value="23:59" style="width: 70px; height: 20px;">
							<span style="font-size: 10px; font-family: Verdana, serif; position: relative; top: 0px; left: 5px; color: #CD0000;">*</span>
						</td>
					</tr>
					<tr>
<!-- IP-адреса источников -->
						<th style="width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
						<span onclick="delSrcIP()"><img src='<?php echo "/{$array_directory[1]}/img/button_minus.png"; ?>' style="cursor: pointer;" title="удалить IP-адрес"/></span>
						IP-адреса источники / количество воздействий
						<span onclick="addSrcIP()"><img src='<?php echo "/{$array_directory[1]}/img/button_plus.png"; ?>' style="cursor: pointer;" title="добавить IP-адрес" /></span>
						</th>
						<th style="width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
						количество компьютерных воздействий с выбранного IP-адреса<br>(общее/положительное/ложное)
						</th>
					</tr>
					<tr id="srcIp" name="srcIp">
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">						
						<input type="text" name="ipSrc[]" onchange="checkIpSrc(this)" style="width: 100px; height: 18px"> 
						<span style="font-size: 10px; font-family: Verdana, serif; position: relative; top: 0px; left: 5px; color: #CD0000;">*</span>
						&nbsp;&nbsp;/
						<input type="text" name="ipNum[]" value="0" style="width: 60px; height: 18px">
						</td>
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;"></td>
					</tr>
					<tr id="exitId">
<!-- IP-адрес назначения -->
						<th colspan="2" style="width: 302px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
						IP-адрес назначения
						</th>
					</tr>
					<tr>
						<td colspan="2" style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
							<input type="text" name="ipDst" style="width: 100px; height: 18px"><?php ListBox::listDomainName(); ?>
							<span style="font-size: 10px; font-family: Verdana, serif; position: relative; top: 0px; left: 5px; color: #CD0000;">*</span>
						<br>
						</td>
					</tr>
<!-- тип компьютерного воздействия -->					
						<td style="text-align: right; padding-right: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						тип компьютерного воздействия					
						</td>
						<td style="text-align: left; padding-left: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
							<?php ListBox::listTypeKA() ?>								
						</td>
					</tr>
					<tr>
<!-- доступность информационного ресурса -->
						<td style="text-align: right; padding-right: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						доступность информационного ресурса
						</td>
						<td style="padding-left: 15px;">
						
<!--
1 - доступен
2 - недоступен
-->	
						<select class="formFiledsText formList" name="ping" style="width: 100px; height: 23px;">
							<option value="1" selected style="background: #B0E2FF;">доступен</option>
							<option value="2" style="background: #FF5A6A;">недоступен</option>
						</select>
						</td>					
					</tr>
					<tr>
<!-- направление воздействия -->
						<td style="text-align: right; padding-right: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						направление компьютерного воздействия
						</td>
<!--
1 - input home network
0 - output home network
-->
						<td style="padding-left: 15px;">
						<select class="formFiledsText formList" name="direction" style="width: 170px; height: 23px;">
							<option value="1" selected style="background: #B0E2FF;">к домашней сети</option>
							<option value="0" style="background: #FF5A6A;">из домашней сети</option>
						</select>
						</td>					
					</tr>
<!-- принятое решение -->
					<tr>
						<td style="text-align: right; padding-right: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						принятое решение			
						</td>
						<td style="text-align: left; padding-left: 15px; width: 50%;">
							<?php ListBox::listSolutionWorker() ?>	
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
						вероятность ложного срабатывания сигнатуры (%)						
						</th>
					</tr>
					<tr>
					<tr id="nameSid">
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
							<input type="text" name="sid[]" onchange="checkSid(this);" style="width: 70px; height: 18px">
							<span style="font-size: 10px; font-family: Verdana, serif; position: relative; top: 0px; left: 5px; color: #CD0000;">*</span>
							&nbsp;&nbsp;/
							<input type="text" name="sidNum[]" value="0" style="width: 50px; height: 18px">
						</td>							
						<td style="text-align: center; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;"></td>
					</tr>
					<tr id="stopId">
<!-- место нахождения отфильтрованного сетевого трафика -->					
						<td style="text-align: right; padding-right: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						место нахождения отфильтрованного сетевого трафика					
						</td>
						<td style="text-align: left; padding-left: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
							<input type="text" name="spaceSafe" style="width: 301px; height: 18px">									
							<span style="font-size: 10px; font-family: Verdana, serif; position: relative; top: 0px; left: 5px; color: #CD0000;">*</span>
						</td>
					</tr>
					<tr>
<!-- пояснение дежурного по компьютерному воздействию -->
						<td style="text-align: right; padding-right: 15px; width: 302px; font-size: 14px; font-family: 'Times New Roman', serif;">
						пояснение дежурного по компьютерному воздействию
						</td>
						<td style="padding-left: 15px;">
							<textarea type="text" name="explanation" style="width: 300px; height: 36px; title="пояснения""></textarea>
						</td>					
					</tr>
<!-- кнопка "сохранить" -->
					<tr>
						<td style="text-align: center; width: 50%;">
						<br><span style="font-size: 10px; font-family: Verdana, serif; color: #000;">поля отмеченные &nbsp;&nbsp;<span style="color: #CD0000;">*</span>&nbsp;&nbsp; обязательные для заполнения</span>
						</td>
						<td style="text-align: left; width: 50%;">
						<br><input type="submit" name="save" style="width: 100px; height: 20px;" value="сохранить">
						</td>
					</tr>
				</table>
				</form><br><br><br>	
			</div>
		</div>

<?php
include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>

<!-- подключаем скрипт с функциями добавления и удаления элементов -->
<script type="text/javascript" src='/<?= $array_directory[1] ?>/js/changeNumElements.js'></script>
<!-- подключаем скрипт для работы с XMLHttpRequest -->
<script type="text/javascript" src='/<?= $array_directory[1] ?>/js/objectXMLHttpRequest.js'></script>
<!-- проверка формы -->
<script type="text/javascript" >

//проверяем номер сигнатуры													}
function checkSid(elem)
	{
	//проверяем поля ввода номера сигнатуры
	var numPattern = /^[0-9]+$/;			
	if(numPattern.test(elem.value)){
		var newObjectXMLHttpRequest = new objectXMLHttpRequest("POST", "/<?= $array_directory[1] ?>/worker/process/ajax_process.php", elem, 'querySid=' + elem.value);
		//выполняем запрос
		newObjectXMLHttpRequest.sendRequest();
		}
	}

//проверяем IP-адрес источника компьютерного воздействия
function checkIpSrc(elem)
	{
	//проверяем IP-адрес
	var ipPattern = /^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/;
	if(ipPattern.test(elem.value)){
		var newObjectXMLHttpRequest = new objectXMLHttpRequest("POST", "/<?= $array_directory[1] ?>/worker/process/ajax_process.php", elem, 'queryIpSrc=' + elem.value);
		//выполняем запрос
		newObjectXMLHttpRequest.sendRequest();		
		}
	}

//функция проверяющая заполненные формы
function validateForm()
	{
	var colors = 'red';
	var o = { dateStart: document.addIncident.dateStart,
			  timeStart: document.addIncident.timeStart,
			  dateEnd: document.addIncident.dateEnd,
			  timeEnd: document.addIncident.timeEnd,
			  ipSrc: document.addIncident["ipSrc[]"],
			  ipNum: document.addIncident["ipNum[]"],
			  ipDst: document.addIncident.ipDst,
			  ipDstName: document.addIncident.dstIPName,
			  sid: document.addIncident["sid[]"],
			  sidNum: document.addIncident["sidNum[]"],
			  spaceSafe: document.addIncident.spaceSafe };

	//убираем выделение для всех полей
	var clearBorderColor = function(elem){
		for(var i in elem){
			if((!elem[i].name) && (elem[i].length > 1)){
				for(var y in elem[i]){
					if(elem[i][y].type == 'text'){
						elem[i][y].style.borderColor = '';
						}
					}
				} else {
				elem[i].style.borderColor = '';
				}
			}
		}
	//проверяем корректность IP-адреса
	var checkIp = function(ip){
		var ipPattern = /^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/;
		if(!ipPattern.test(ip.value)){
			ip.style.borderColor = colors;
			return false;
			}
		return true;
		}

	//проверяем числовые поля
	var checkNum = function(num){
		var numPattern = /^[0-9]+$/;			
		if(!numPattern.test(num.value)){
			num.style.borderColor = colors;
			return false;
			}
		return true;		
		}

	for(var i in o){
		//очищаем поля
		clearBorderColor(o);
		//проверяем поля на пустоту
		if(o[i].name != 'ipDst' && o[i].value == '' && o[i].type != 'select-one'){
			o[i].style.borderColor = colors;
			return false;
			}

		//проверяем начальную и конечную дату (начальная дата и время не может быть больше конечной)
		if(o[i].type == 'date' ){
			var dStart = new Date(o.dateStart.value + ' ' + o.timeStart.value);
			var dEnd = new Date(o.dateEnd.value + ' ' + o.timeEnd.value);
			}
		if(dStart > dEnd){
			o.dateStart.style.borderColor = colors;
			o.dateEnd.style.borderColor = colors;
			return false;
			}
			
		//проверяем IP-адрес назначения или доменное имя
		if(o[i].name == 'dstIPName'){
			if(o[i].value == 'null'){
				if(o.ipDst.value == ''){
					o.ipDst.style.borderColor = colors;
					o.ipDstName.style.borderColor = colors;
					return false;
				} else {
				if(checkIp(o.ipDst) == false) return false;
				}
			}
		}

		//проверяем IP-адреса и числовые поля
		if(!o[i].name && o[i].length > 1){
			//группа IP-адресов и числовых полей
			for(var s in o[i]){
				//проверяем IP-адреса источников
				if(o[i][s].name == 'ipSrc[]'){
					if(!checkIp(o[i][s])) return false;
					}
				//проверяем числовые поля
				if((o[i][s].name == 'ipNum[]') || (o[i][s].name == 'sid[]') || (o[i][s].name == 'sidNum[]')){
					if(!checkNum(o[i][s])) return false;
					}
				}
			} else {
			//единичные IP-адреса числовые поля
			if(o[i].name == 'ipSrc[]'){
				if(!checkIp(o[i])) return false;
				}
			//единичные числовые поля
			if((o[i].name == 'ipNum[]') || (o[i].name == 'sid[]') || (o[i].name == 'sidNum[]')){
				if(!checkNum(o[i])) return false;
				}
			}
		}
	}
</script>