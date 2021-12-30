<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		страница редактирования пользовательских учетных данных		*/
						/*												v.0.1 12.05.2014 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

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
$array_all_users = $ReadXMLSetup->getArrayAllUsersInform();

//массив названий групп пользователей
$array_user_group = array('10' => 'руководство',
						  '20' => 'дежурный',
						  '30' => 'аналитик');
?>
<script type="text/javascript" >
	//подтверждение удаления пользователя
	function confirmUserDel(userName){
		var testOk = confirm('Подтвердите удаление пользователя ' + userName);
		return (testOk) ? true : false;
		}

	//вывод информационного сообщения
	function showMessage(id, message){
		document.getElementById(id).innerHTML = message;
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

	//проверка формы добавления нового пользователя
	function checkUserAdd(){
		var userForm = document.formUserAdd;
		userForm.userAddLogin.style['borderColor'] = '';
		userForm.userAddName.style['borderColor'] = '';
		userForm.userAddPassOne.style['borderColor'] = '';
		userForm.userAddPassTwo.style['borderColor'] = '';
		
		//логин пользователя
		if(userForm.userAddLogin.value.length == 0){
			userForm.userAddLogin.style['borderColor'] = 'red';
			return false;
			}
		//Ф.И.О. пользователя
		if(userForm.userAddName.value.length == 0){
			userForm.userAddName.style['borderColor'] = 'red';
			return false;
			}
		//пароль пользователя
		if(userForm.userAddPassOne.value.length == 0){
			userForm.userAddPassTwo.style['borderColor'] = 'red';
			return false;
			}
		if(userForm.userAddPassOne.value.length == 0){
			userForm.userAddPassTwo.style['borderColor'] = 'red';
			return false;
			}	
		//проверяем пароли на совпадения
		if(userForm.userAddPassOne.value != userForm.userAddPassTwo.value){
			userForm.userAddPassOne.style['borderColor'] = 'red';
			userForm.userAddPassTwo.style['borderColor'] = 'red';
			showMessage('mess', 'пароли не совпали');
			return false;
			}
		//проверяем пароли на длину
		if(userForm.userAddPassOne.value.length < 7){
			userForm.userAddPassOne.style['borderColor'] = 'red';
			showMessage('mess', 'пароль должен быть длиннее шести символов');	
			return false;			
			}
		}
		
	//изменение изображения	
	function chageImg(elem, flag){
		switch(flag){
			//удаление пользователя
			case '1':
				elem.src = '/<?= $array_directory[1]?>/img/user_del.png';
			break;
			//удаление пользователя			
			case '2':
				elem.src = '/<?= $array_directory[1]?>/img/user_del_red.png';
			break;
			//сохранение информации
			case '3':
				elem.src = '/<?= $array_directory[1]?>/img/save.png';
			break;
			//сохранение информации
			case '4':
				elem.src = '/<?= $array_directory[1]?>/img/save_1.png';
			break;
			}
		}
		
	//изменение пароля
	function changePassword(elem){
		//разблокируем ввод пароля
		var userPassOne = elem.childNodes[3];
		userPassOne.style.display = 'block';
		elem.childNodes[1].innerHTML = 'новый пароль';
		userPassOne.oninput = function(){
			var userPassTwo = elem.childNodes[5];
			userPassTwo.style.display = 'block';
			elem.childNodes[1].innerHTML = 'подтвердите пароль';
			};
		}
</script>

<!-- поле основной информации -->
	<div style="position: relative; top: -1px; left: 210px; display: inline-block; z-index: 10; width: 745px; min-height: 500px; border-radius: 3px; box-shadow: inset 0 0 8px 0px #B7DCF7;">
<!-- форма добавления нового пользователя -->
		<div style="position: relative; top: 10px; left: 10px; margin: 10px 0px;">
			<span style="position: relative; top: 0px; left: 270px; font-size: 12px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #990000;">
				добавить нового пользователя
			</span>
			<form name="formUserAdd" method="POST" action="/<?= $array_directory[1]?>/admin/process/process_write_xml_setup.php" onsubmit="return checkUserAdd()">
			<div style="border-width: 1px; border-style: solid; width: 725px; border-color: #B7DCF7;">
			<table id="" border="0" width="725px" cellpadding="2">
			<tr style="background: #FF6A6A;">
				<th style="width: 35px; font-size: 12px; font-family: 'Times New Roman', serif;">
				</th>
				<th style="width: 110px; font-size: 12px; font-family: 'Times New Roman', serif;">
					группа
				</th>
				<th style="width: 120px; font-size: 12px; font-family: 'Times New Roman', serif;">
					логин
				</th>
				<th style="width: 240px; font-size: 12px; font-family: 'Times New Roman', serif;">
					Ф.И.О.
				</th>
				<th style="width: 220px; font-size: 12px; font-family: 'Times New Roman', serif;">
					пароль
				</th>
			</tr>
<!-- иконка 'добавить нового пользователя' -->
			<tr style="background: #FFC1C1;">
				<td>
					<input type="image" name="userAdd" src="/<?= $array_directory[1]?>/img/user_add.png" title="добавить пользователя">
					<input type="hidden" name="id" value="userAdd">
				</td>
<!-- выбор группы -->
				<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<select name="userGroup" style="width: 90px; height: 23px;"><?php
						foreach($array_user_group as $id => $name){
							echo "<option value='$id'>".$name."</option>";
							}
					?></select>
				</td>
<!-- логин пользователя -->
				<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<input type="text" name="userAddLogin" value="" style="width: 80px; height: 18px">				
				</td>
<!-- Ф.И.О. пользователя -->
				<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<input type="text" name="userAddName" value="" style="width: 190px; height: 18px">				
				</td>
<!-- пароль пользователя -->	
				<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
					<div id="pass_1"><input type="text" name="userAddPassOne" value="" style="width: 140px; height: 18px"></div>
					<div id="pass_2" style="display: none;">
						<span id="mess" style="text-align: center; font-size: 10px; font-family: 'Times New Roman', serif;">подтвердите пароль</span>
						<input type="text" name="userAddPassTwo" value="" style="width: 140px; height: 18px">
					</div>
				</td>
			</tr>				
			</table>
			</div>
			</form>
		</div>	
		
<script type="text/javascript" >
	//вывод поля для ввода подтверждения пароля
	var div = document.getElementById('pass_1');
	var input = div.children[0];
	input.oninput = function (){
		document.getElementById('pass_2').style.display = 'block';
		}
</script>	

<!-- таблица с полной информацией о пользователях -->
		<div style="position: relative; top: 10px; left: 10px; margin: 10px 0px;">
<!-- текст -->
			<span style="position: relative; top: 0px; left: 270px; font-size: 12px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #0000CD;">
				учетные данные пользователей
			</span>
			<div style="border-width: 1px; border-style: solid; width: 725px; border-color: #B7DCF7; text-align: center;">			
			<table id="" border="0" width="725px" cellpadding="2">
				<tr <?= COLOR_HEADER ?> >
					<th style="width: 35px; font-size: 12px; font-family: 'Times New Roman', serif;">
					</th>
					<th style="width: 110px; font-size: 12px; font-family: 'Times New Roman', serif;">
						группа
					</th>
					<th style="width: 120px; font-size: 12px; font-family: 'Times New Roman', serif;">
						логин
					</th>
					<th style="width: 240px; font-size: 12px; font-family: 'Times New Roman', serif;">
						Ф.И.О.
					</th>
					<th style="width: 80px; font-size: 12px; font-family: 'Times New Roman', serif;">
						сохранить
					</th>
					<th style="width: 140px; font-size: 12px; font-family: 'Times New Roman', serif;">
						
					</th>
				</tr>
				<?php
				foreach($array_all_users as $key => $value){
					foreach($value as $login => $array){
					echo "<tr bgcolor='".color()."'>";
					?>
<!-- иконка удаления пользователя -->
					<td>
					<form name="formDelete" method="POST" action="/<?= $array_directory[1]?>/admin/process/process_write_xml_setup.php" onsubmit="return confirmUserDel('<?= $array['name'] ?>')">
						<input onmouseout="chageImg(this,'1')" onmouseover="chageImg(this,'2')" type="image" src="/<?= $array_directory[1]?>/img/user_del.png" name="userDelete" title="удаление пользователя">
						<input type="hidden" name="id" value="userDel">
						<input type="hidden" name="user" value="<?= $login ?>">											
					</form>
					</td>
<!-- группа пользователя -->
					<form name="formChangeInfo" method="POST" action="/<?= $array_directory[1]?>/admin/process/process_write_xml_setup.php" onsubmit="return checkChangeUserInfo(this, '<?php echo $key ?>')">
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
						<select name="userGroup" style="width: 90px; height: 23px;">
						<?php
						foreach($array_user_group as $id => $name){
							if($key != $id){
								echo "<option value='$id'>".$name."</option>";
								} else {
								echo "<option value='$id' selected style='background: #B0E2FF;'>".$name."</option>";
								}
							}
						?></select>
					</td>
<!-- логин пользователя -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
<!-- старый логин -->
						<input type="hidden" name="userLoginOld" value="<?= $login ?>">
<!-- новый логин -->
						<input type="text" name="userLoginNew" value="<?= $login ?>" style="width: 80px; height: 18px">
					</td>
<!-- Ф.И.О. -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
						<input type="text" name="userName" value="<?= $array['name'] ?>" style="width: 190px; height: 18px">
					</td>
<!-- сохранить изменения -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
						<input onmouseout="chageImg(this,'3')" onmouseover="chageImg(this,'4')" type="image" name="saveChange"  src="/<?= $array_directory[1]?>/img/save.png" title="сохранить изменения">
						<input type="hidden" name="id" value="userChange">											
					</td>
<!-- изменить пароль пользователя -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
						<div onclick="return changePassword(this)" style="width: 139px;">
						<span style="cursor: pointer;">сменить пароль</span>
						<input type="text" name="userPassOne" value="" style="width: 137px; height: 18px; display: none;">
						<input type="text" name="userPassTwo" value="" style="width: 137px; height: 18px; display: none;">
						</div>			
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
		</div><br><br>		
	</div>
</div><br><br>
<?php
include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>