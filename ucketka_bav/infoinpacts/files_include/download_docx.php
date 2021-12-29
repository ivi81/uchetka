<?php

						/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*	 формируем документ docx и отдаем пользователю		 */
						/*											16.03.2015	 */
						/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//объект для подключения к БД
$DBO = new DBOlink();
//объект для чтения файла setup_site.xml
$ReadXMLSetup = new ReadXMLSetup;

//			*** подготовка официального письма ***
//			**************************************
if(isset($_POST['showMailDocx']) && !empty($_POST['showMailDocx'])){
	$showMailDocx = $_POST['showMailDocx'];
	//массив для текста письма
	$array_mail_text = array();
	//текст письма о компьютерных атаках
	foreach($showMailDocx as $value){
		$array_mails[$value] = AllInformationForIncident::showDateStartAndEndAndIpSrcIpDst($DBO, $value);
		}
	//объект формирования письма
	$FormingMail = new FormingMail($array_mails);

	//готовим первый абзац письма 
	$array_mail_text[0] = "Направляем Вам информацию о компьютерных атаках, зафиксированных средствами ГосСОПКА ФСБ России ".$FormingMail->getOneParagraph().".";		

	//готовим второй абзац письма (основной абзац)
	$array_mail_text[1] = $FormingMail->getTwoParagraph($DBO);

	//готовим третий абзац письма (заключительный)
	$array_mail_text[2] = "Фактов нарушения доступности ".$FormingMail->getThreeParagraph()." не зафиксировано.";	

	//Ф.И.О. исполнителя
	$array_mail_text[3] = $ReadXMLSetup->usernameFIO($_SESSION['userSessid']['userLogin']);
	}

//			*** подготовка отчета по компьютерным атакам ***
//			************************************************
if(isset($_POST['showStatementDocx']) && !empty($_POST['showStatementDocx'])){
	
	}
	
//готовим файл
$CreateDocxMail = new CreateDocxMail('mail');
//запись текста в файл docx
$CreateDocxMail->writeText($array_mail_text);
//получаем имя созданного файла
$fileDocx = $CreateDocxMail->getFileDocx();

if(!file_exists($fileDocx)){
	header("HTTP/1.0 404 Not Found");
	exit;
	}

$fsize = filesize($fileDocx);
$ftime = date('D, d M Y H:i:s T', filemtime($fileDocx));

$num = strlen($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/docx/");

header("HTTP/1.1 200 OK");
header("Content-type: application/docx");
//имя файла
header("Content-Disposition: attachment; filename=".substr($fileDocx, $num)."");
header("Last-Modified: {$ftime}");
header("Content-Length: {$fsize}");
//запрещаем кеширование
header("Cache-Control: no-store; max-age=0");

//читаем файл
readfile($fileDocx);

//удаляем файл
$CreateDocxMail->deleteZipArchive();

?>