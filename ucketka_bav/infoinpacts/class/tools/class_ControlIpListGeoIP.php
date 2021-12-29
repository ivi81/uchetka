<?php

						/*----------------------------------------------------------------------*/
						/*  	управление списками IP-адресов геопозиционирования (GeoIP)		*/
						/* 					 	 							v.0.1 06.06.2014    */
						/*----------------------------------------------------------------------*/

class ControlIpListGeoIP extends ControlIpList
{
//директория сайта
private $directory;
//имя загруженного файла
private $uploadFileName;

public function __construct()
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directory = $array_directory[1];
	//проверяем доступность таблицы GeoIP
	try{
		//создаем таблицу если ее нет
		$this->createIpList();
		}
	catch (PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_DB_CONNECT, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//вывод общей информации о таблице геопозиционирования
public function showBDInfo()
	{
	try{
		?>
		<div style="">
			<lu style="margin-left: 20px;">
<!-- кол-во записей -->
				<li style="list-style-type: none; margin-left: 20px; padding-left: 20px;">
				<span style="font-size: 14px; font-family: 'Times New Roman', serif; color: #0000">
				Всего записей в таблице геопозиционирования IP-адресов (GeoIP): <span style="text-decoration: underline;"><?php echo $this->showIpListInfo(); ?></span>
				</span>
				</li>
<!-- дата последнего обновления -->
				<li style="list-style-type: none; margin-left: 20px; padding-left: 20px;">
				<span style="font-size: 14px; font-family: 'Times New Roman', serif; color: #0000;">
				Дата последнего обновления таблицы: 
				<span style="text-decoration: underline;"><?php echo ConversionData::showDateConvertStr($this->showTableIpListInfo()) ?></span>
				</span>
				</li>
			</lu>
		</div>
		<?php
		}
	catch (PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_DB_CONNECT, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//вывод формы загрузки файла со списком IP-адресов
public function loadFile()
	{
	?>
	<div style="width: 745px; text-align: center;">
		<form name="loadFile" method="POST" enctype="multipart/form-data" action="">
			<input type="file" name="loadFiles" value="обзор" class="formProtocol" style="height: 20px;" title="загрузка файла">
			<input type="submit" name="send" value="загрузить" title="загрузить данные" style="width: 100px; height: 20px;">
		</form>
	</div>
	<?php
	}

//вывод информации о списке IP-адресов
protected function showIpListInfo()
	{
	$query = self::getConnectionDB()->connectionDB()->query("SELECT COUNT(*) AS `num` FROM `geoip_data`");
	return $query->fetch(PDO::FETCH_OBJ)->num;
	}

//вывод информации о обновлении таблицы GeoIP
protected function showTableIpListInfo()
	{
	$query = self::getConnectionDB()->connectionDB()->query("SHOW TABLE STATUS FROM `data_on_KA` WHERE `Name`='geoip_data'");
	return $query->fetch(PDO::FETCH_OBJ)->Update_time;	
	}

//создание таблицы БД
protected function createIpList()
	{
	self::getConnectionDB()->connectionDB()->query("CREATE TABLE IF NOT EXISTS `geoip_data` (
								     				start INT(10) UNSIGNED NOT NULL,
								     				end INT(10) UNSIGNED NOT NULL,
									 				code VARCHAR(2) NOT NULL,						  					  
									 				country VARCHAR(255),
									 				INDEX index_start(start),
									 				INDEX index_end(end)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	}

//удаляем таблицу БД
protected function deleteIpList()
	{
	self::getConnectionDB()->connectionDB()->query("DROP TABLE IF EXISTS `geoip_data`");
	}

//обновление списков IP-адресов
public function updateIpList()
	{
	if(isset($_POST['send'])){
		//загружаем файл
		$this->uploadFile();
		//читаем файл и записываем данные в БД
		$this->readFile();
		//удаляем загруженный файл
		$this->deleteUploadFile();
		}
	}

//чтение загруженного файла
private function readFile()
	{
	if(!$array_file = file($this->uploadFileName)){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_FILE, "\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: невозможно прочитать файл ".$this->uploadFileName);
		}
	//проверяем является ли загруженный файл файлом в формате csv с сайта maxmind.com
	if(count(explode(",", $array_file[0])) != 7){
		//удаляем загруженный файл
		$this->deleteUploadFile();
		echo MessageErrors::userMessageError(MessageErrors::ERROR_NOT_RIGHT_FILE_FORMAT, "\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: неверный формат файла ".$this->uploadFileName);		
		} 
	$GeoIP = new GeoIP;
	try{
		//удаляем таблицу БД
		$this->deleteIpList();
		//создаем новую таблицу БД
		$this->createIpList();
		//$y = 1;
		//читаем файл и загружаем данные в БД
		foreach ($array_file as $value){
			list(,,, $start, $end, $code,) = explode('","', $value);
			//парсить в базу данных только если значение переменной $code не равно 'A2'
			if($code !== "A2"){
				//echo "строка ".$y++.$value.'<br>';
				$query = self::getConnectionDB()->connectionDB()->prepare("INSERT INTO `geoip_data` 
																		  (`start`,
																		   `end`,
																		   `code`,
																		   `country`) 
						   							  					  VALUES 
						   							  					   (:start, 
						   							  					   	:end, 
						   							  					   	:code, 
						   							  					   	:country)");
				$query->execute(array(':start' => $start, 
									  ':end' => $end, 
									  ':code' => $code, 
									  ':country' => $GeoIP::$codeCountry[$code]));				
				}
			}
		}
	catch (PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	ShowMessage::messageOkRedirect("файл {$_FILES['loadFiles']['name']} был успешно загружен", 150);	 
	}

//загрузка файла
private function uploadFile()
	{
	//директория для загруженных файлов
	$dirUpload = $_SERVER['DOCUMENT_ROOT']."/{$this->directory}/tmp/";
	if(empty($_FILES['loadFiles']['tmp_name'])){
		exit();
		}
	//расположение и имя загруженного файла
	$uploadFile = $dirUpload.basename($_FILES['loadFiles']['name']);
	if(copy($_FILES['loadFiles']['tmp_name'], $uploadFile)){
		chdir($dirUpload);
		if (!opendir($dirUpload)){
			echo MessageErrors::userMessageError(MessageErrors::ERROR_FILE, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
			}
		$fileName = $dirUpload.$_FILES['loadFiles']['name'];
		if(!fopen($fileName, "r")){
			echo MessageErrors::userMessageError(MessageErrors::ERROR_FILE, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
			}
		}
	return $this->uploadFileName = $fileName;
	}

//удаление загруженного файла
private function deleteUploadFile()
	{
	if(!unlink($this->uploadFileName)){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_FILE, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//отключаемся от БД
function closeConnectionDB()
	{

	}
}
?>