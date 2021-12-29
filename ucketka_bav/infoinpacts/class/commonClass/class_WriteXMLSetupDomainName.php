<?php

							/*--------------------------------------------------*/
							/*  	класс поиска, изменения и добавления записи	*/
							/*	 о доменном имени в конфигурационный файл xml	*/
							/* 	 							  v.0.1 23.05.2014  */
							/*--------------------------------------------------*/

class WriteXMLSetupDomainName extends WriteXMLSetup
{
//добавляем новое доменное имя
public function addNewElement(array $array_domain) 
	{
	//доменное имя
	$domName = $array_domain[0];	
	//IP-адрес
	$ip = $array_domain[1];
	//подробное описание
	$title = $array_domain[2];
	//создаем новый элемент
	$this->setNewElement(array($domName, $ip, $title));
	}

//изменяем ВСЮ информацию о доменном имени
public function changeAllInfo(array $array) 
	{
	$domNameOld = $array[0];	
	$domNameNew = $array[1];
	$ip = $array[2];
	$title = $array[3];

	//удаляем старый элемент
	$this->deleteElement($domNameOld);
	//создаем новый элемент
	$this->setNewElement(array($domNameNew, $ip, $title));
	}

//создание нового элемента
public function setNewElement(array $array)
	{
	//находим родительский элемент содержащий необходимый элемент
	$accounts = $this->fileXML->getElementsByTagName('table_ip_address')->item(0);	
	$domains = $this->fileXML->getElementsByTagName('domname');
	//проверяем существования доменного имени
	if($this->checkAttribute('ip_address', 'domname', $array[0]) == false){
		//создаем новый элемент
		$newDomainName = $accounts->appendChild($this->fileXML->createElement('ip_address', $array[1]));
		//делаем перенос строки
		$accounts->appendChild($this->fileXML->createTextNode("\n"));
		//доменное имя
		$newDomainName->setAttributeNode(new DOMAttr('domname', $array[0]));
		//полное название
		$newDomainName->setAttributeNode(new DOMAttr('domfullname', $array[2]));
		} else {
		ShowMessage::messageWarning("Доменное имя {$array[0]} уже зарегистрировано", 150);
		exit();
		}
	//сохраняем файл
	$this->saveXMLFile();
	}
	
//удаляем запись
public function deleteElement($domName) 
	{
	$element_domain = $this->fileXML->getElementsByTagName('ip_address');
	foreach($element_domain as $domain){
		if($domain->getAttribute('domname') == $domName){
			//удаляем следующего за найденным элементом потомка, необходимо для удаления символа переноса строки иначе остаются пустые строки
			$domain->parentNode->removeChild($domain->nextSibling);
			//удаляем старый элемент
			$domain->parentNode->removeChild($domain);
			}
		}
	//сохраняем файл
	$this->saveXMLFile();	
	}
}
?>