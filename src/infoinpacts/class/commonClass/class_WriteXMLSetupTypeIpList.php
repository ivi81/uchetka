<?php

							/*----------------------------------------------------------*/
							/*  			класс удаления и добавления записи			*/
							/*	 о типе списка IP-адресов в конфигурационный файл xml	*/
							/* 	 							  		v.0.1 17.07.2014    */
							/*----------------------------------------------------------*/

class WriteXMLSetupTypeIpList extends WriteXMLSetup
{
//добавляем новой тип списков IP-адресов
public function addNewElement(array $array_domain) 
	{
	//название типа списка IP-адресов
	$name = $array_domain[0];	
	//подробное описание
	$title = $array_domain[1];
	//создаем новый элемент
	$this->setNewElement(array($name, $title));
	}

//изменяем ВСЮ информацию
public function changeAllInfo(array $array) {}

//создание нового элемента
public function setNewElement(array $array)
	{
	//определяем код типа списков IP-адресов
	$ReadXMLSetup = new ReadXMLSetup;	
	$typeListIp = $ReadXMLSetup->giveListTypeIpAddress();
	if(count($typeListIp) == 0){
		$code = 100;
		} else {
		$array_key = array_keys($typeListIp);
		$code = array_pop($array_key) + 1;
		}

	//находим родительский элемент содержащий необходимый элемент
	$accounts = $this->fileXML->getElementsByTagName('list_ip')->item(0);	
	$type_info = $this->fileXML->getElementsByTagName('type_info');
	//проверяем существования типа списка IP-адресов
	$a = true;
	foreach($typeListIp as $value){
		if(in_array($array[0], $value)){
			$a = false;
			break;
			}
		}
	if($a){
		//создаем новый элемент
		$newTypeIpList = $accounts->appendChild($this->fileXML->createElement('type_list_ip', $array[0]));
		//делаем перенос строки
		$accounts->appendChild($this->fileXML->createTextNode("\n"));
		//доменное имя
		$newTypeIpList->setAttributeNode(new DOMAttr('type_id', $code));
		//полное название
		$newTypeIpList->setAttributeNode(new DOMAttr('type_info', $array[1]));
		} else {
		ShowMessage::messageWarning("Тип списка IP-адресов <span style='color:#FF0000'> {$array[0]} </span> уже существует", 150);
		exit();
		}
	//сохраняем файл
	$this->saveXMLFile();
	}
	
//удаляем запись
public function deleteElement($code) 
	{
	$element = $this->fileXML->getElementsByTagName('type_list_ip');
	foreach($element as $value){
		if($value->getAttribute('type_id') == $code){
			//удаляем следующего за найденным элементом потомка, необходимо для удаления символа переноса строки иначе остаются пустые строки
			$value->parentNode->removeChild($value->nextSibling);
			//удаляем старый элемент
			$value->parentNode->removeChild($value);
			}
		}
	//сохраняем файл
	$this->saveXMLFile();	
	}
}
?>