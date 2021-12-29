<?php

							/*----------------------------------------------------------*/
							/*  класс создания текстового документа в формате docx		*/
							/* 	 								v.0.1 04.03.2014		*/
							/*----------------------------------------------------------*/

class CreateDocx
{

private $zipArchive;
protected $fileXML, $array_directory, $directory, $fileName;
//загружаем конфигурационный XML файл
public function __construct($pattern)
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 
	$this->array_directory = $array_directory[1];
	switch($pattern) 
		{
		//шаблон для официального письма
		case 'mail':
			//проверяем существует ли файл
			if(!file_exists($_SERVER['DOCUMENT_ROOT']."/{$this->array_directory}/docx/pattern_mail_docx/word/document.xml")) {
				MessageErrors::userMessageError(MessageErrors::ERROR_NOIDENTIF, "\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: файла шаблонов не существует");		
				exit();
				}
			$this->fileXML = new DOMDocument();
			$this->directory = $_SERVER['DOCUMENT_ROOT']."/{$this->array_directory}/docx/pattern_mail_docx";
			//поучаем имя файла			
			$this->fileName = $_SERVER['DOCUMENT_ROOT']."/{$this->array_directory}/docx/document_mail_".date('d-m-Y_H-i-s', time()).".docx";	
		break;
		
		//шаблон для отчета по компьютерным атакам
		case 'statement':
			//проверяем существует ли файл
			if(!file_exists($_SERVER['DOCUMENT_ROOT']."/{$this->array_directory}/docx/pattern_mail_docx/word/document.xml")) {
				MessageErrors::userMessageError(MessageErrors::ERROR_NOIDENTIF, "\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: файла шаблонов не существует");		
				exit();
				}
			$this->fileXML = new DOMDocument();
			$this->directory = $_SERVER['DOCUMENT_ROOT']."/{$this->array_directory}/docx/pattern_statement_docx";
			//поучаем имя файла			
			$this->fileName = $_SERVER['DOCUMENT_ROOT']."/{$this->array_directory}/docx/document_statement_".date('d-m-Y_H-i-s', time()).".docx";	
		break;
		
		default:
			MessageErrors::userMessageError(MessageErrors::ERROR_NOIDENTIF, "\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: файла шаблонов не существует");		
			exit();	
		}
	//загружаем XML файл	
	$this->fileXML->load("{$this->directory}/word/document.xml");
	}

//получаем имя docx файла
public function getFileDocx()
	{
	return $this->fileName;
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
	if(!$sizeFile = $this->fileXML->save("{$this->directory}/word/document.xml")){
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
}

?>