<?php

							/*------------------------------------------------------------------*/
							/*  класс создания отчета по компьютерным атакам в формате docx		*/
							/* 	 										v.0.1 04.03.2014		*/
							/*------------------------------------------------------------------*/

class CreateDocxStatement extends CreateDocx
{
/*
private $zipArchive;
protected $fileXML, $array_directory, $directory, $fileName;
//загружаем конфигурационный XML файл
public function __construct()
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 
	$this->array_directory = $array_directory[1];
	//проверяем существует ли файл
	if(!file_exists($_SERVER['DOCUMENT_ROOT']."/{$this->array_directory}/docx/pattern_mail_docx/word/document.xml")) {
		MessageErrors::userMessageError(MessageErrors::ERROR_NOIDENTIF, "\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: файла шаблонов не существует");		
		exit();
		}
	$this->fileXML = new DOMDocument();
	$this->directory = $_SERVER['DOCUMENT_ROOT']."/{$this->array_directory}/docx/pattern_mail_docx";
	//загружаем XML файл	
	$this->fileXML->load($_SERVER['DOCUMENT_ROOT']."/{$this->array_directory}/docx/pattern_mail_docx/word/document.xml");
	}

//получаем имя docx файла
public function getFileDocx()
	{
	return $this->fileName;
	}

//запись текстовой строки
public function writeText($text) 
	{
	//находим родительский элемент содержащий необходимый элемент
	foreach($this->fileXML->documentElement->childNodes as $nodes){
		foreach($nodes->childNodes as $nodesp){
			//ищем основной узел содержащий текст письма 
			if($nodesp->getAttribute('w:rsidR') == '0060764C'){
				foreach($nodesp->childNodes as $nodesr){
					foreach($nodesr->childNodes as $nodest){
						if($nodest->nodeName == 'w:t'){
						
							//ВРЕМЕННО
//							echo $nodest->nodeName." - ".$nodest->nodeValue."<br>";			
						
						//удаляем старый элемент						
							$nodest->parentNode->removeChild($nodest);
						//создаем новый элемент и добавляем туда текст		
							$nodesr->appendChild($this->fileXML->createElement('w:t', $text));	
							}					
						}				
					}			
				}
			}
		}
		
//-----------ПРОВЕРЯЕМ записанную строку (временно) 		
/*	foreach($this->fileXML->documentElement->childNodes as $nodes){
		foreach($nodes->childNodes as $nodesp){
			foreach($nodesp->childNodes as $nodesr){
				foreach($nodesr->childNodes as $nodest){
					echo $nodest->nodeName." - ".$nodest->nodeValue."<br>";			
					}				
				}			
			}
		} */
//-----------		
/*		
	$this->saveXMLFile();
	//создаем zip архив
	$this->createZipArchive($this->directory.'/');
	}

//создаем zip архив
protected function writeZipArchive($directory, $dir_in_archive='') 
	{
	$dirHandle = opendir($directory);
	while($file = readdir($dirHandle)){
		if($file != '.' && $file != '..'){
			if(!is_dir($directory.$file)){
				$this->zipArchive->addFile($directory.$file, $dir_in_archive.$file);
      	} else {
				$this->zipArchive->addEmptyDir($dir_in_archive.$file);
				$this->writeZipArchive($directory.$file.DIRECTORY_SEPARATOR, $dir_in_archive.$file.DIRECTORY_SEPARATOR);
				}
			}
		}
	}
 
protected function createZipArchive($directory) 
	{
	$this->zipArchive = new ZipArchive();
	$this->fileName = $_SERVER['DOCUMENT_ROOT']."/{$this->array_directory}/docx/document_".date('d-m-Y_H-i-s', time()).".docx";	
	if(!$this->zipArchive->open($this->fileName, ZIPARCHIVE::CREATE)){
		MessageErrors::userMessageError(MessageErrors::ERROR_NOIDENTIF, "\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: не могу создать zip архив");
		exit();
		}	
	$this->writeZipArchive($directory);
	$this->zipArchive->close();
	return true;
	}

//сохраняем в XML файле
protected function saveXMLFile() 
	{
	if(!$sizeFile = $this->fileXML->save($_SERVER['DOCUMENT_ROOT']."/{$this->array_directory}/docx/pattern_mail_docx/word/document.xml")){
		MessageErrors::userMessageError(MessageErrors::ERROR_FILE, "\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: не могу сохранить xml файл шаблона документа docx");	
		exit();		
		}
	}

//удаляем zip файл	
public function deleteZipArchive() 
	{
	if(file_exists($this->fileName)){
		unlink($this->fileName);
		}
	}
*/
}

?>