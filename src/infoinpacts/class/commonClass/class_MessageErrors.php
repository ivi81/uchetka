<?php

						/*------------------------------------------*/
						/*			класс обработки ошибок			*/
						/*					v.0.1 24.08.2013    	*/
						/*------------------------------------------*/

class MessageErrors
{
	
const ERROR_PHP = 1;
const ERROR_USER = 2;
const ERROR_SQL = 3;
const ERROR_FILE = 4;
const ERROR_NOIDENTIF = 5;
const ERROR_DB_CONNECT = 6;
const ERROR_AUTHORIZATION = 7;
const ERROR_NOT_RIGHT_FILE_FORMAT = 8;

//вывод краткого описания ТЕХНИЧЕСКОЙ ошибки, и запись в лог файл 
public static function userMessageError($error, $message) 
	{
	switch($error) 
		{
		case self::ERROR_PHP:
			self::errorLog($message);
			return self::showInformationError("ошибка интерпретатора PHP, пожалуйста обратитесь к администратору");		
		break;

		case self::ERROR_USER:
			return self::showInformationError("проверьте правильность вводимых данных");		
		break;
		
		case self::ERROR_SQL:
			self::errorLog($message);
			return self::showInformationError("ошибка в SQL запросе, пожалуйста обратитесь к администратору");
		break;		
		
		case self::ERROR_FILE:
			self::errorLog($message);
			return self::showInformationError("ошибка чтения файла, пожалуйста обратитесь к администратору");		
		break;		
		
		case self::ERROR_NOIDENTIF:
			self::errorLog($message);
			return self::showInformationError("не идентифицируемая ошибка, пожалуйста обратитесь к администратору");	
		break;
		
		case self::ERROR_DB_CONNECT:
			self::errorLog($message);
			return self::showInformationError("ошибка подключения к базе данных, пожалуйста обратитесь к администратору");	
		break;
		
		case self::ERROR_AUTHORIZATION:
			self::errorLog($message);
		break;

		case self::ERROR_NOT_RIGHT_FILE_FORMAT:
			self::errorLog($message);
			return self::showInformationError("формат файла не соответствует заданным требованиям");	
			break;
		}
	}

//функция для получения основной директории сайта	
static function getMajorDirectory() 
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	return $array_directory[1];
	}
	
//вывод пользователю ошибки содержащей информация об нарушении работы ЛОГИКИ скрипта
static function showInformationError($message) 
	{
	?>
	<style>
		.messageError {
		 	position: absolute; left: 50%; margin-left: -210px; z-index: 10;
		 	width: 420px;
		 	height: 150px;
		 	vertical-align: middle;
	 	 	border-radius: 8px;
		 	background: #FFF; 
		 	box-shadow: inset 0 0 10px 0px #FF4500; }
	</style>

	<div class="messageError">
		<table border="0" cellpadding="2" width="420px">
			<tr height="145px">
			<td style="width: 80px; text-align: right;">
			<img src="/<?= self::getMajorDirectory() ?>/img/cancel_48.png">
			</td>
			<td style="width: 340px; padding: 10px; text-align: center; font-size: 18px; font-family: 'Times New Roman', serif; color: #FF0000; letter-spacing: 1px;">		
			<?=$message?>
			</td> 
			</tr>
		</table>
	</div>
	<?php
	exit();
	}
	
//функция записи информации в лог файл
public static function errorLog($message) 
	{
	if(!$log_file = fopen($_SERVER['DOCUMENT_ROOT']."/".self::getMajorDirectory()."/log/error.log", 'a+'))
		{
		echo "<div align=center><font color=red>Невозможно создать лог файл!</font></div>";
		}
	fwrite($log_file, date("Y-m-d H:i:s",time())." $message \n");
	fclose($log_file);
	}		
}
?>