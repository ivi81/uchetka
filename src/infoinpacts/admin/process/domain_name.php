<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		страница редактирования пользовательских учетных данных	  */
						/*											v.0.1 12.05.2014 	  */
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

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
//$array_all_domain_name = $ReadXMLSetup->getArrayAllInformWebSite();

?>
<script type="text/javascript" >

	//изменение изображения	
	function chageImg(elem, flag){
		switch(flag){
			//удаление
			case '1':
				elem.src = '/<?= $array_directory[1]?>/img/delete_recording.png';
			break;
			//удаление			
			case '2':
				elem.src = '/<?= $array_directory[1]?>/img/delete_recording_1.png';
			break;
			//сохранение информации
			case '3':
				elem.src = '/<?= $array_directory[1]?>/img/save.png';
			break;
			//сохранение информации
			case '4':
				elem.src = '/<?= $array_directory[1]?>/img/save_1.png';
			break;
			//сохранение информации
			case '5':
				elem.src = '/<?= $array_directory[1]?>/img/add-to-database.png';
			break;
			//сохранение информации
			case '6':
				elem.src = '/<?= $array_directory[1]?>/img/add-to-database_1.png';
			break;
			}
		}
		
	//проверка формы добавления новой записи
	function checkAddNewWritebl(){
		var form = document.formAddNewWritebl;
		var obj = { dname: form.addDomainName,
					ip: form.addIpAddress,
					official: form.addOfficial };
		//убираем красную рамку
		delRedFrame(obj);
		//проверяем элементы формы на пустоту
		for(var a in obj){
			if(obj[a].value.length == 0){
				obj[a].style['borderColor'] = 'red';
				document.getElementById('mess').innerHTML = 'необходимо заполнить все поля';
				return false;
				}
			}
		//проверяем IP-адрес на корректность
		var ipPattern = /^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/;
		if(!ipPattern.test(obj.ip.value)){
			obj.ip.style['borderColor'] = 'red';			
			document.getElementById('mess').innerHTML = 'некорректный IP-адрес';
			return false;
			}
		}

	//подтверждение удаления записи о доменном имени
	function confirmDomNameDel(domName){
		var testOk = confirm('Подтвердите удаление доменного имени ' + domName);
		return (testOk) ? true : false;
		}

	//убираем красную рамку
	function delRedFrame(elem){
		for(var a in elem){
			elem[a].style['borderColor'] = '';
			}
		}

	//проверка формы изменения записей
	function changeWritebl(elem){
		var obj = { ip: elem[0],
					domName: elem[2],
					dTitle: elem[3] };
		//убираем красную рамку
		delRedFrame(obj);
		var flag = false;
		//проверяем поля формы на заполненность и были ли они изменены
		for(var a in obj){
			if(obj[a].value.length == 0){
				obj[a].style['borderColor'] = 'red';
				document.getElementById('message').innerHTML = 'необходимо заполнить все поля';
				return false;
				} else {
				if(obj[a].defaultValue != obj[a].value){
					flag = true;
					}
				}
			}
		if(flag == false){
			document.getElementById('message').innerHTML = 'не было сделано ни одного изменения';
			return false;
			}
		//проверяем IP-адрес на корректность
		var ipPattern = /^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/;
		if(!ipPattern.test(obj.ip.value)){
			obj.ip.style['borderColor'] = 'red';			
			document.getElementById('message').innerHTML = 'некорректный IP-адрес';
			return false;
			}
		}
		
	//проверка формы изменения информации о пользователе
	function checkChangeUserInfo(elem, id){
		var arrayElem = { group: elem.userGroup,
						  login: elem.userLoginNew,
						  uname: elem.userName,
						  passOne: elem.userPassOne,
						  passTwo: elem.userPassTwo };
		elem.userGroup.defaultValue = id;
		var flag = false;
		for(var a in arrayElem){
			if(typeof(arrayElem[a]) !== 'undefined' && arrayElem[a].value.length != 0){
				if(arrayElem[a].defaultValue != arrayElem[a].value){
					flag = true;
					}
				}
			}
		if(flag == false){
			showMessage('message', 'не было сделано ни одного изменения');
			return false;
			}
		//если форма изменения пароля активирована
		if(elem.userPassOne.style.display == 'block'){	
			//при изменении пароля проверяем его на длину и совпадение
			if((typeof(elem.userPassOne) !== 'undefined' && elem.userPassOne.value.length != 0) 
			&&	(typeof(elem.userPassTwo) !== 'undefined' && elem.userPassTwo.value.length != 0)){
				//проверка на длину пароля
				if(elem.userPassOne.value.length < 7){
					showMessage('message', 'пароль должен быть длиннее шести символов');
					return false;
					}
				//проверка на совпадение паролей
				if(elem.userPassOne.value != elem.userPassTwo.value){
					showMessage('message', 'пароли не совпадают');
					return false;
					}
				} else {
				elem.userPassOne.style['borderColor'] = 'red';
				elem.userPassTwo.style['borderColor'] = 'red';
				showMessage('message', 'необходимо подтвердить пароль');
				return false;
				}
			}
		}
</script>

<!-- поле основной информации -->
	<div style="position: relative; top: -1px; left: 210px; display: inline-block; z-index: 10; width: 745px; min-height: 500px; border-radius: 3px; box-shadow: inset 0 0 8px 0px #B7DCF7;">
<!-- форма добавления нового доменного имени -->
		<div style="position: relative; top: 10px; left: 10px; margin: 10px 0px;">
			<span style="position: relative; top: 0px; left: 262px; font-size: 12px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #990000;">
				добавление нового доменного имени 
			</span>
			<form name="formAddNewWritebl" onsubmit="return checkAddNewWritebl()" method="POST" action="/<?= $array_directory[1]?>/admin/process/process_write_xml_setup.php">
			<div style="border-width: 1px; border-style: solid; width: 725px; border-color: #B7DCF7;">
			<table id="" border="0" width="725px" cellpadding="2">
			<tr style="background: #FF6A6A;">
				<th style="width: 45px; font-size: 12px; font-family: 'Times New Roman', serif;">
				</th>
				<th style="width: 130px; font-size: 12px; font-family: 'Times New Roman', serif;">
					доменное имя
				</th>
				<th style="width: 130px; font-size: 12px; font-family: 'Times New Roman', serif;">
					IP-адрес
				</th>
				<th style="width: 420px; font-size: 12px; font-family: 'Times New Roman', serif;">
					официальное название Web-сайта
				</th>
			</tr>
<!-- иконка 'добавить новое доменное имя' -->
			<tr style="background: #FFC1C1;">
				<td style="text-align: center;">
					<input onmouseout="chageImg(this,'5')" onmouseover="chageImg(this,'6')" type="image" name="userAdd" src="/<?= $array_directory[1]?>/img/add-to-database.png" title="добавить доменное имя">
					<input type="hidden" name="id" value="addDomName">
				</td>
<!-- доменное имя -->
				<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<input type="text" name="addDomainName" value="" style="width: 120px; height: 18px">				
				</td>
<!-- IP-адрес -->
				<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<input type="text" name="addIpAddress" value="" style="width: 120px; height: 18px">				
				</td>
<!-- описание -->	
				<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<input type="text" name="addOfficial" value="" style="width: 300px; height: 18px">
				</td>
			</tr>				
			</table>
			</div>
			</form>
			<div style="text-align: center;">
				<span id="mess" style="font-size: 12px; font-family: 'Times New Roman', serif; color: #FF0000;"></span>			
			</div>
		</div>	
		
<!-- таблица с полной информацией о доменных именах -->
		<div style="position: relative; top: 10px; left: 10px; margin: 10px 0px;">
<!-- текст -->
			<span style="position: relative; top: 0px; left: 260px; font-size: 12px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #0000CD;">
				зарегистрированные доменные имена
			</span>
			<?php $array_all_domain_name = $ReadXMLSetup->getArrayAllInformWebSite(); ?>
			<div style="border-width: 1px; border-style: solid; width: 725px; border-color: #B7DCF7; ">			
			<table id="" border="0" width="725px" cellpadding="2">
				<tr <?= COLOR_HEADER ?> >
					<th style="width: 45px; font-size: 12px; font-family: 'Times New Roman', serif;">
					</th>
					<th style="width: 130px; font-size: 12px; font-family: 'Times New Roman', serif;">
						IP-адрес
					</th>
					<th style="width: 130px; font-size: 12px; font-family: 'Times New Roman', serif;">
						доменное имя
					</th>
					<th style="width: 375px; font-size: 12px; font-family: 'Times New Roman', serif;">
						официальное название Web-сайта
					</th>
					<th style="width: 45px; font-size: 12px; font-family: 'Times New Roman', serif;">
						сохранить
					</th>
				</tr>
				<?php
				foreach($array_all_domain_name as $ip => $value){
					foreach($value as $domname => $title){
					echo "<tr bgcolor='".color()."'>";
					?>
<!-- иконка удаления доменного имени -->
					<td style="text-align: center;">
					<form name="formDelete" onsubmit="return confirmDomNameDel('<?= $domname ?>')" method="POST" action="/<?= $array_directory[1]?>/admin/process/process_write_xml_setup.php">
						<input onmouseout="chageImg(this,'1')" onmouseover="chageImg(this,'2')" type="image" src="/<?= $array_directory[1]?>/img/delete_recording.png" name="deleteRecording" title="удалить выбранную запись">
						<input type="hidden" name="id" value="deleteDomName">
						<input type="hidden" name="idDomName" value="<?php echo $domname; ?>">	
					</form>
					</td>
<!-- IP-адрес -->
					<form name="formChangeInfo" onsubmit="return changeWritebl(this)" method="POST" action="/<?= $array_directory[1]?>/admin/process/process_write_xml_setup.php">
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
						<input type="text" name="ipAddress" value="<?= $ip ?>" style="width: 100px; height: 18px">
					</td>
<!-- доменное имя -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
<!-- старое доменное имя -->
						<input type="hidden" name="domNameOld" value="<?= $domname ?>">
<!-- новое доменное имя -->
						<input type="text" name="domNameNew" value="<?= $domname ?>" style="width: 100px; height: 18px">
					</td>
<!-- официальное название Web-сайта -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
						<input type="text" name="domTitle" value="<?= $title ?>" style="width: 340px; height: 18px">
					</td>
<!-- сохранить изменения -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
						<input onmouseout="chageImg(this,'3')" onmouseover="chageImg(this,'4')" type="image" name="saveChange"  src="/<?= $array_directory[1]?>/img/save.png" title="сохранить запись">
						<input type="hidden" name="id" value="changeDomName">											
					</td>
					</form>
					</tr>
					<?php
						}
					}
				?>
			</table>
			</div>
			<div style="text-align: center;">
				<span id="message" style="font-size: 12px; font-family: 'Times New Roman', serif; color: #FF0000;"></span>
			</div>
		</div><br>		
		<br>
	</div>
</div><br><br>
<script type="text/javascript" >

</script>
<?php
include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>