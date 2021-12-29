<?php

						/*----------------------------------------------------*/
						/*			класс загрузки snort.rules в БД "data_on_KA"	*/
						/*											v.0.1 06.12.2013 	   */
						/*----------------------------------------------------*/

class ReadSnortRules
{

public function __construct() 
	{
	//объект для подключения к БД
	$DBO = new DBOlink();
	try
		{
		$DBO->connectionDB()->query("CREATE TABLE IF NOT EXISTS `signature_tables` (
											  sid INT(10) UNSIGNED UNIQUE NOT NULL,
											  short_message VARCHAR(200),
											  snort_rules TEXT,
											  INDEX index_for_sid(sid)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
		}
	catch(PDOException $e)
		{
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	//закрываем соединения с БД
	$DBO->onConnectionDB();	
	}

public function loadRules() 
	{
	//объект для подключения к БД
	$DBO = new DBOlink();

	if(empty($_FILES['loadFileSnort']['tmp_name']) && (empty($_POST["loadFileSnort"]))) 
		{
		MessageErrors::userMessageError(MessageErrors::ERROR_FILE, "не выбрано ни одного файла");
		}
	//временная директория для загружаемых файлов
	$uploaddir = $_SERVER['DOCUMENT_ROOT'].'/new_project/tmp/';
	for($i = 0; $i < count($_FILES['loadFileSnort']['tmp_name']); $i++) 
		{
		$uploadFiles = $uploaddir.basename($_FILES['loadFileSnort']['name'][$i]);
		if(!copy($_FILES['loadFileSnort']['tmp_name'][$i], $uploadFiles)) 
			{
			MessageErrors::userMessageError(MessageErrors::ERROR_FILE, "невозможно скопировать файл");
			}
		}
	//переходим в директорию с загруженными файлами
	chdir($uploaddir);
	//обработка ошибок открытия директории
	if (!$directory = opendir($uploaddir))
		{ 
		MessageErrors::userMessageError(MessageErrors::ERROR_FILE, "невозможно открыть директорию ".$uploaddir);
		}
	//чтение загруженных файлов
	while($files = readdir($directory))
		{
		if(($files != '.') && ($files != '..')) 
			{
			if(preg_match("/[\.rules]$/", $files)) 
				{
				//массив строк из файла
				$array_string = file($files);
				foreach($array_string as $value)
					{
					if(substr($value, 0, 5) == 'alert' ) 
						{
						list(, $string) = explode("(msg:\"", $value);
						$array = explode(";", $string);
						//краткое описание правила						
						$short_message = htmlspecialchars(substr($array[0], 0, -1));
						//полное правило
						$snort_rules = htmlspecialchars($value);
						// загрузка в БД					
						try
							{ 
							$query = $DBO->connectionDB()->prepare("INSERT IGNORE `signature_tables` (
																				`sid`, 
																				`short_message`,
																				`snort_rules`)
																				 VALUE (
																				 '".intval(substr($array[(count($array) - 3)], 5))."', 
																			 	 :short_message, 
																 				 :snort_rules)");
							$query->execute(array(':short_message' => $short_message, ':snort_rules' => $snort_rules));
							}
						catch(PDOException $e)
							{
							echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
							}
						}
					}
				//удаление файла
				unlink($files);
				}
			else 
				{
				unlink($files);
				}	
			}
		}
	//закрываем директорию
	closedir($directory);
	//закрываем соединения с БД
	$DBO->onConnectionDB();	
	}
}

?>