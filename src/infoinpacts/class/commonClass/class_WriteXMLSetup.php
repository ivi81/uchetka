<?php

							/*----------------------------------------------------------*/
							/*  класс работы с конфигурационным файлом в формате xml		*/
							/* 	 								v.0.1 20.02.2014		*/
							/*----------------------------------------------------------*/

abstract class WriteXMLSetup
{
protected $fileXML, $array_directory;
//загружаем конфигурационный XML файл
public function __construct()
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 
	//проверяем существует ли файл
	if(!file_exists($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/config/".XML_FILE)) {
		echo "файла не существует";
		}
	$this->fileXML = new DOMDocument();
	$this->array_directory = $array_directory[1];
	//загружаем XML файл	
	$this->fileXML->load($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/config/".XML_FILE);
	$this->fileXML->formatOutput = true;
	}

//проверяем существование атрибута
public function checkAttribute($elem, $attribute, $value)
	{
	$element_user = $this->fileXML->getElementsByTagName($elem);
	foreach($element_user as $user){
		if($user->getAttribute($attribute) == $value){
			return true;
			}
		}
	return false;
	}

//сохраняем в файле
protected function saveXMLFile() 
	{
	$this->fileXML->formatOutput = true;
	//сохраняем XML файл
	$this->fileXML->save($_SERVER['DOCUMENT_ROOT']."/{$this->array_directory}/config/".XML_FILE);
	//	echo "записано ".$this->fileXML->save($_SERVER['DOCUMENT_ROOT']."/{$this->array_directory}/config/".XML_FILE)." байт";	
	}

//добавление элемента
abstract public function addNewElement(array $array);
//изменение всей информации
abstract public function changeAllInfo(array $array);	
//удаление элементов
abstract public function deleteElement($userName);

}

?>