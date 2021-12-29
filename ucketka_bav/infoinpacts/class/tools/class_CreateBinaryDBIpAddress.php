<?php

						/*----------------------------------------------------------*/
						/* 	формирование базы данных списков IP-адресов сохраняемых */
						/*					 в файл в двоичной форме					*/
						/*									v.0.1 	29.07.2014		*/
						/*----------------------------------------------------------*/

class CreateBinaryDBIpAddress
{
//временный файл бинарной БД
private $fileTmpDB;
//основной файл бинарной БД
private $fileDB;
//массив с первым октетом
private $array_1;
//массив с вторым октетом
private $array_2;
//ip адреса для записи
private $ipDB;

function __construct() 
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	//временный файл бинарной базы данных 
	$this->fileTmpDB = $_SERVER['DOCUMENT_ROOT']."/".$array_directory[1]."/binDB/blackListIp_tmp";
	//основной файл БД
	$this->fileDB = $_SERVER['DOCUMENT_ROOT']."/".$array_directory[1]."/binDB/blackListIp.dat";
	}

//создание бинарной БД
public function createDB(array $ipRead) 
	{
	//создание временного файла с бинарной БД	
	$this->createTmpFile($ipRead);
	//запись бинарной БД в основной файл
	$this->writeFileDB();
	//удаляем временный файл
	$this->deleteFileTmp();
	}

//создание временного файла с бинарной БД
private function createTmpFile($ipRead)
	{
	$fileTmpDBW = fopen($this->fileTmpDB, 'w+');
	$ip_DB = fwrite($fileTmpDBW, "DBi\n");
	natsort($ipRead);
	$array_1 = $array_2 = array_fill(0, 256, "0");
	$one = $two = 999;
	foreach($ipRead as $ip){
	//Проверяем IP-адрес
		if(preg_match("/^[0-9]{1,3}[\.][0-9]{1,3}[\.][0-9]{1,3}[\.][0-9]{1,3}$/", $ip)){
		//Делим IP-адрес на октеты
		list($oktet_1, $oktet_2,,) = explode('.', trim($ip));
		$ipn = ip2long(trim($ip));
		//Если первый октет IP-адреса не совпадает пишем смещение в байтах
			if($one != (int) $oktet_1){ 
				$array_1[$oktet_1] = ftell($fileTmpDBW); 
				}
			$one = (int) $oktet_1;	
			//Если второй октет IP-адреса не совпадает пишем смещение в байтах
			if($two != (int) $oktet_2){
				if($array_2[$oktet_2] != 0){ 
					$array_2[$oktet_2] = $array_2[$oktet_2].",".ftell($fileTmpDBW); 
					} else { 
					$array_2[$oktet_2] = ftell($fileTmpDBW); 
					}
				}
			$two = (int) $oktet_2;
			}			
		//Пишем IP-адреса в DB
		$ip_DB += fwrite($fileTmpDBW, pack('N', $ipn));
		}
	//размер первого октета
	$this->array_1 = $array_1;
	//размер второго октета
	$this->array_2 = $array_2;
	//размер БД IP-адресов
	$this->ipDB = $ip_DB;
	$this->closeFile($fileTmpDBW);
	}

//запись бинарной БД в основной файл
private function writeFileDB()
	{
	//открываем временный файл и дописываем индексы
	$fileTmpDBW = fopen($this->fileTmpDB, 'a');
	$writeIndex1 = $this->writeIndex1($fileTmpDBW);
	$writeIndex2 = $this->writeIndex2($fileTmpDBW);
	$this->closeFile($fileTmpDBW);
	//открываем временный файл БД для чтения
	$fileTmp = fopen($this->fileTmpDB, 'rb');
	//открываем для записи основной файл БД
	$fileDB = fopen($this->fileDB, 'w+');
	//читаем tmp файл временной DB
	$tmpDB = fread($fileTmp, $this->ipDB);
	$string = str_pad("FileDBip: "."index_1 - ".$writeIndex1."; index_2 - ".$writeIndex2."; sizeDB - ".$this->ipDB.";", 100); 
	$inf = fwrite($fileDB, $string);
	$array_index_1 = array ();
	$array_index_1[] = fread($fileTmp, $writeIndex1);
	$index_2 = fread($fileTmp, $writeIndex2);
	sort($array_index_1);
	//пишем первый индекс
	foreach($array_index_1 as $index){
		fwrite($fileDB, $index);
		}
	//пишем второй индекс
	fwrite($fileDB, $index_2);
	//пишем БД
	fwrite($fileDB, $tmpDB);
	//закрываем файлы
	$this->closeFile($fileTmp);
	$this->closeFile($fileDB);
	}

//1. Записываем первый индекс в БИНАРНОМ формате
private function writeIndex1($file) 
	{ 
	$indexShow = 0;
	foreach($this->array_1 as $value){
		$str = pack('N', $value.'\n');
		$indexShow += fwrite($file, $str); 
		}
	return $indexShow;
	}

//2. Записываем второй индекс в ТЕКСТОВОМ формате
private function writeIndex2($file) 
	{ 
	$indexShow = 0;
	fwrite($file, "\n");
	foreach($this->array_2 as $value){ 
		$indexShow += fwrite($file, $value." ");
		}
	return $indexShow;
	}

//закрываем файл
private function closeFile($file)
	{
	fclose($file);
	}

//удаления временного файла
private function deleteFileTmp()
	{
	unlink($this->fileTmpDB);	
	}
}
?>