<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		создание, изменение и удаление записей 		*/
						/*		из конфигурационного файла в формате xml	*/
						/*							v.0.1 20.02.2014	 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++*/

?>
<script type="text/javascript" >
	function locationPage(id){
		var string = window.location.pathname;
		var array = string.split('/');
		var path = '/' + array[1] + '/' + array[2] + '/' + array[3] + '/control_information.php?id=' + id;
		window.location.href = path;
		}
</script> 
<?php

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 

//подключаем основную, для дежурного страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/admin/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

//объект класса редактирования конфигурационного XML файла
$writeXMLSetupUser = new WriteXMLSetupUser();
$writeXMLSetupDomainName = new WriteXMLSetupDomainName();
$writeXMLSetupTypeIpList = new WriteXMLSetupTypeIpList();
?>
<!-- поле основной информации -->
	<div style="position: relative; top: -1px; left: 210px; display: inline-block; z-index: 10; width: 745px; min-height: 500px; border-radius: 3px; box-shadow: inset 0 0 8px 0px #B7DCF7;">	
	<?php
	//проверяем пароли
	function checkPasswd($passOne, $passTwo)
		{
		//проверяем совпадения паролей
		if($passOne != $passTwo){
			echo ShowMessage::showInformationError("пароли не совпадают");
			exit();
			}
		//проверяем длину паролей
		if(strlen($passOne) < 6){
			echo ShowMessage::showInformationError("пароль должен быть длиннее шести символов");
			exit();
			}
		}
		
	if(isset($_POST['id']) && !empty($_POST['id'])){
		switch($_POST['id']){
//-------------------------- добавляем нового пользователя -------
			case 'userAdd':
				//проверяем пользовательские данные
				if(empty($_POST['userGroup']) || empty($_POST['userAddLogin']) || empty($_POST['userAddName']) 
				|| empty($_POST['userAddPassOne']) || empty($_POST['userAddPassTwo'])){
					echo ShowMessage::showInformationError(" не все поля были заполнены");
					exit();
					}
					$userGroup = ExactilyUserData::takeIntager($_POST['userGroup']);
					$userAddLogin = ExactilyUserData::takeStringAll($_POST['userAddLogin']);
					$userAddName = ExactilyUserData::takeStringAll($_POST['userAddName']);
					$userAddPassOne = ExactilyUserData::takeStringAll($_POST['userAddPassOne']);
					$userAddPassTwo = ExactilyUserData::takeStringAll($_POST['userAddPassTwo']);
				//проверяем пароли
				checkPasswd($userAddPassOne, $userAddPassTwo);
				//кодируем пароль в md5
				$userAddPassOne = md5($userAddPassOne);
				//добавляем нового пользователя	
				$writeXMLSetupUser->addNewElement(array($userGroup, $userAddLogin, $userAddName, $userAddPassOne));
				//вывод сообщения об успешном выполнении и редирект на главную страницу
				ShowMessage::messageOkRedirect("запись успешно добавлена", 150);
			break;
//-------------------------- удаляем пользователя -------
			case 'userDel':
				//удаляем пользователя
				$writeXMLSetupUser->deleteElement(ExactilyUserData::takeStringAll($_POST['user']));				
				//вывод сообщения об успешном выполнении и редирект на главную страницу
				ShowMessage::messageOkRedirect("запись удалена", 150);
			break;
//-------------------------- меняем информацию о пользователе -------
			case 'userChange':
				//проверяем данные пользователя
				$userGroup = ExactilyUserData::takeIntager($_POST['userGroup']);
				$userLoginOld = ExactilyUserData::takeStringAll($_POST['userLoginOld']);
				$userLoginNew = ExactilyUserData::takeStringAll($_POST['userLoginNew']);
				$userName = ExactilyUserData::takeStringAll($_POST['userName']);
				//получаем пароли если они есть
				if(!empty($_POST['userPassOne'])){
					$userPassOne = ExactilyUserData::takeStringAll($_POST['userPassOne']);
					$userPassTwo = ExactilyUserData::takeStringAll($_POST['userPassTwo']);
					} else {
					$userPassOne = false;
					$userPassTwo = false;
					}
				//проверяем пароли
				if($userPassOne != false){
					checkPasswd($userPassOne, $userPassTwo);
					}
				//изменяем ВСЮ информацию о пользователе
				$writeXMLSetupUser->changeAllInfo(array($userGroup, $userLoginOld, $userLoginNew, $userName, md5($userPassOne)));
				//вывод сообщения об успешном выполнении и редирект на главную страницу
				ShowMessage::messageOkRedirect("учетные данные пользователя изменены", 150);
			break;
//-------------------------- добавляем новое доменное имя -------
			case 'addDomName':
				//проверяем данные
				$domName = ExactilyUserData::takeStringAll($_POST['addDomainName']);
				$ip = ExactilyUserData::takeIP($_POST['addIpAddress']);
				$official = ExactilyUserData::takeStringAll($_POST['addOfficial']);
				//добавляем новое доменное имя
				$writeXMLSetupDomainName->addNewElement(array($domName, $ip[0], $official));
				//вывод сообщения об успешном выполнении и редирект на главную страницу
				ShowMessage::messageOkRedirect("запись успешно добавлена", 150);
			break;
//-------------------------- удаляем доменное имя -------
			case 'deleteDomName':
				//удаляем доменное имя
				$writeXMLSetupDomainName->deleteElement(ExactilyUserData::takeStringAll($_POST['idDomName']));				
				//вывод сообщения об успешном выполнении и редирект на главную страницу
				ShowMessage::messageOkRedirect("запись удалена", 150);
			break;			
//-------------------------- меняем информацию относящуюся к доменному имени -------
			case 'changeDomName':
				//проверяем данные
				$domNameOld = ExactilyUserData::takeStringAll($_POST['domNameOld']);
				$domNameNew = ExactilyUserData::takeStringAll($_POST['domNameNew']);
				$ip = ExactilyUserData::takeIP($_POST['ipAddress']);
				$official = ExactilyUserData::takeStringAll($_POST['domTitle']);
				//добавляем новое доменное имя
				$writeXMLSetupDomainName->changeAllInfo(array($domNameOld, $domNameNew, $ip[0], $official));
				//вывод сообщения об успешном выполнении и редирект на главную страницу
				ShowMessage::messageOkRedirect("запись успешно изменена", 150);
			break;
//-------------------------- добавляем новый тип списков IP-адресов -------
			case 'addTypeIpList':
				//проверяем данные
				$typeListIp = ExactilyUserData::takeStringAll($_POST['typeListIp']);
				$infoListIp = ExactilyUserData::takeStringAll($_POST['infoListIp']);
				$writeXMLSetupTypeIpList->addNewElement(array($typeListIp, $infoListIp));
				?>
				<script type="text/javascript" >
				//останемся на этой странице
				locationPage('3');
				</script> 
				<?php
			break;
//-------------------------- удаление типа списков IP-адресов -------
			case 'deleteTypeIpList':
				//удаляем доменное имя
				$writeXMLSetupTypeIpList->deleteElement((int) $_POST['code']);				
				?>
				<script type="text/javascript" >
				//останемся на этой странице
				locationPage('3');
				</script> 
				<?php
			break;
			}						
		}
	?>	
	</div>
