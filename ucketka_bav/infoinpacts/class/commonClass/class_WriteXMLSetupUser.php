<?php

							/*----------------------------------------------*/
							/*  класс поиска, изменения и добавления записи	*/
							/*	 о пользователе в конфигурационный файл xml	*/
							/* 	 						v.0.1 20.02.2014    */
							/*----------------------------------------------*/

class WriteXMLSetupUser extends WriteXMLSetup
{
//добавляем нового пользователя
public function addNewElement(array $array_user) 
	{
	//идентификационный номер группы к которой принадлежит пользователь
	$idaccount = $array_user[0];	
	//логин пользователя
	$login = $array_user[1];
	//Ф.И.О. пользователя
	$name = $array_user[2];
	//пароль пользователя
	$password = $array_user[3];
	
	//создаем новый элемент
	$this->setNewElement(array($idaccount, $login, $name, $password));
	}

//изменяем ВСЮ информацию о пользователе
public function changeAllInfo(array $array) 
	{
	//идентификационный номер группы к которой принадлежит пользователь
	$idaccount = $array[0];	
	//старый логин пользователя
	$loginOld = $array[1];
	//новый логин пользователя
	$loginNew = $array[2];
	//Ф.И.О. пользователя
	$name = $array[3];
	//пароль пользователя
	$password = $array[4];
	
	//если пароль пользователя не менялся используем прежний пароль
	if($password === false){
		//создаем объект для чтения конфигурационного файла xml
		$ReadXMLSetup = new ReadXMLSetup;
		$list = explode(':',$ReadXMLSetup->accounts($loginOld));
		$password = $list[2];
		}
	//удаляем старый элемент
	$this->deleteElement($loginOld);
	//создаем новый элемент
	$this->setNewElement(array($idaccount, $loginNew, $name, $password));
	}

//создание нового элемента 'users'
public function setNewElement(array $array)
	{
	//находим родительский элемент содержащий необходимый элемент
	$accounts = $this->fileXML->getElementsByTagName('accounts')->item(0);	
	$users = $this->fileXML->getElementsByTagName('users');
	//проверяем существования пользователя
	if($this->checkAttribute('users', 'login', $array[1]) == false){
		//если группа существует
		if($this->checkAttribute('users', 'idaccount', $array[0])){
		//если такая группа пользователей уже существует добавляем в нее
			foreach($users as $user){
				if($user->getAttribute('idaccount') == $array[0]){
					$new_user = $user->parentNode->insertBefore($this->fileXML->createElement('users', $array[3]), $user);
					//делаем перенос строки
					$user->parentNode->insertBefore($this->fileXML->createTextNode("\n"), $user);
					//добавляем атрибуты в созданный элемент
					//идентификационный номер группы
					$new_user->setAttributeNode(new DOMAttr('idaccount', $array[0]));
					//логин пользователя
					$new_user->setAttributeNode(new DOMAttr('login', $array[1]));
					//Ф.И.О. пользователя
					$new_user->setAttributeNode(new DOMAttr('name', $array[2]));
					break;
					}
				}
			} else {
			//создаем новый элемент и добавляем в него пароль пользователя
			$new_user = $accounts->appendChild($this->fileXML->createElement('users', $array[3]));
			//делаем перенос строки
			$accounts->appendChild($this->fileXML->createTextNode("\n"));
			//добавляем атрибуты в созданный элемент
			//идентификационный номер группы
			$new_user->setAttributeNode(new DOMAttr('idaccount', $array[0]));
			//логин пользователя
			$new_user->setAttributeNode(new DOMAttr('login', $array[1]));
			//Ф.И.О. пользователя
			$new_user->setAttributeNode(new DOMAttr('name', $array[2]));	
			}
		} else {
		ShowMessage::messageWarning("Пользователь с логином {$array[1]} уже зарегестрирован", 150);
		exit();
		}
	//сохраняем файл
	$this->saveXMLFile();
	}

//удаляем пользователя
public function deleteElement($userName) 
	{
	$element_user = $this->fileXML->getElementsByTagName('users');
	foreach($element_user as $user){
		if($user->getAttribute('login') == $userName){
			//удаляем следующего за найденным элементом потомка, необходимо для удаления символа переноса строки иначе остаются пустые строки
			$user->parentNode->removeChild($user->nextSibling);			
			//удаляем старый элемент
			$user->parentNode->removeChild($user);
			}
		}
	//сохраняем файл
	$this->saveXMLFile();	
	}
}
	
//изменяем пользователю только один параметр
/*
public function changeOnlyInfOfUser(array $array_user) 
	{
	//логин пользователя
	$login = $array_user[0];
	//наименование изменяемого параметра
	$key = $array_user[1];
	//значение изменяемого параметра
	$value = $array_user[2];

	//находим родительский элемент содержащий необходимый элемент
	$accounts = $this->fileXML->getElementsByTagName('accounts')->item(0);	
	$element_user = $this->fileXML->getElementsByTagName('users');
	foreach($element_user as $user){
		switch($key){
			case 'idaccount':
				if($user->getAttribute('login') == $login){
					$user->setAttributeNode(new DOMAttr('idaccount', $value));			
					}
			break;
		
			case 'login':
				if($user->getAttribute('login') == $login){
					$user->setAttributeNode(new DOMAttr('login', $value));
					}
			break;		

			case 'name':
				if($user->getAttribute('login') == $login){
					$user->setAttributeNode(new DOMAttr('name', $value));
					}
			break;

			case 'password':
				if($user->getAttribute('login') == $login){
					//создаем новый элемент и добавляем в него пароль пользователя
					$new_user = $accounts->appendChild($this->fileXML->createElement('users', $value));
					//добавляем атрибуты в созданный элемент
					//идентификационный номер группы
					$new_user->setAttributeNode(new DOMAttr('idaccount', $user->getAttribute('idaccount')));
					//логин пользователя
					$new_user->setAttributeNode(new DOMAttr('login', $user->getAttribute('login')));
					//Ф.И.О. пользователя
					$new_user->setAttributeNode(new DOMAttr('name', $user->getAttribute('name')));
					//удаляем старый элемент
					$user->parentNode->removeChild($user);
					}
			break;
		
			default:
			echo 'некорректное значение';
			}
		}
	//сохраняем файл
	$this->saveXMLFile();	
	}
*/
?>