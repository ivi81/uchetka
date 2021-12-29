<?php

							/*------------------------------------------------------*/
							/*  класс создания официального письма в формате docx	*/
							/* 	 							v.0.1 04.03.2014		*/
							/*------------------------------------------------------*/

class CreateDocxMail extends CreateDocx
{
//запись текстовой строки
public function writeText(array $array_text) 
	{
	if(count($array_text) != 4){
		MessageErrors::userMessageError(MessageErrors::ERROR_NOIDENTIF, "\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: количество элементов в переданном массиве не совпадает с заданным");		
		exit();	
		}
	//находим родительский элемент содержащий необходимый элемент
	foreach($this->fileXML->documentElement->childNodes as $nodes){
		foreach($nodes->childNodes as $nodesp){
/*
$nodesp - w:p
$nodesr - w:r
$nodest - w:t
*/
			//ищем основной узел содержащий ПЕРВЫЙ абзац письма 
			if($nodesp->getAttribute('w:rsidR') == '00AC6C1E'){
				foreach($nodesp->childNodes as $nodesr){
					foreach($nodesr->childNodes as $nodest){
						if($nodest->nodeName == 'w:t'){
							//удаляем старый элемент						
							$nodest->parentNode->removeChild($nodest);
							//создаем новый элемент и добавляем туда текст		
							$nodesr->appendChild($this->fileXML->createElement('w:t', $array_text[0]));	
							}					
						}				
					}			
				}
				
			//ищем основной узел содержащий ВТОРОЙ абзац письма 
			if($nodesp->getAttribute('w:rsidR') == '0063599F'){
				foreach($nodesp->childNodes as $nodesr){
					foreach($nodesr->childNodes as $nodest){
						if($nodest->nodeName == 'w:t'){
							//удаляем старый элемент						
							$nodest->parentNode->removeChild($nodest);
							//создаем новый элемент и добавляем туда текст		
							$nodesr->appendChild($this->fileXML->createElement('w:t', $array_text[1]));	
							}					
						}				
					}							
				}
				
			//ищем основной узел содержащий ТРЕТИЙ абзац письма 
			if($nodesp->getAttribute('w:rsidR') == '00A12240'){
				foreach($nodesp->childNodes as $nodesr){
					foreach($nodesr->childNodes as $nodest){
						if($nodest->nodeName == 'w:t'){
							//удаляем старый элемент						
							$nodest->parentNode->removeChild($nodest);
							//создаем новый элемент и добавляем туда текст		
							$nodesr->appendChild($this->fileXML->createElement('w:t', $array_text[2]));	
							}					
						}				
					}			
				}
			
			//ищем основной узел содержащий Ф.И.О. исполнителя 
			if($nodesp->getAttribute('w:rsidR') == '00701220'){
				foreach($nodesp->childNodes as $nodesr){
					if($nodesr->getAttribute('w:rsidRPr') == '00785FFD'){
						foreach($nodesr->childNodes as $nodest){
							if($nodest->nodeName == 'w:t'){
								//удаляем старый элемент						
								$nodest->parentNode->removeChild($nodest);
								//создаем новый элемент и добавляем туда текст		
								$nodesr->appendChild($this->fileXML->createElement('w:t', $array_text[3]));	
								}
							}					
						}				
					}			
				}
					
			}
		}
	//сохраняем изменения в шаблоне
	parent::saveXMLFile();
	//создаем zip архив
	parent::createZipArchive($this->directory.'/');
	}
}

?>