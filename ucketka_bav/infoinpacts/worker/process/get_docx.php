<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*  подключаем файл формирующий документ в формате docx */
						/*		 								04.03.2014		*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

session_start();

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 
//подключаем config
require_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/config/config.php");

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

require_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/files_include/download_docx.php");
?>