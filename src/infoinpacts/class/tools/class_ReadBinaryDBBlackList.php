<?php

						/*----------------------------------------------------------*/
						/*  	поиск информации по бинарной БД списков IP-адресов	*/
						/* 					 	 				v.0.1 04.08.2014    */
						/*----------------------------------------------------------*/

/*
	С помощью данного скрипта можно осуществлять не только поиск IP-адреса в бинарной БД
но и получать дополнительную информацию о найденном IP-адресе, однако для этого необходима
таблица `black_ip_list` БД `data_on_KA`.

									ВАЖНО!!!
				Искомый IP-адрес задается через метод setIp()
*/

class ReadBinaryDBBlackList extends ReadBinaryDB
{
private $ipAddress;
private $fileDB;
private $DB;
private $index_1;
private $index_2;
private $arrayIndexOne;
private $arrayIndexTwo;
private $directory;
private static $readXml;

function __construct()
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directory = $array_directory[1];
	//основной файл БД
	$this->fileDB = $_SERVER['DOCUMENT_ROOT']."/".$array_directory[1]."/binDB/blackListIp.dat";
	}

//установить искомый IP-адрес
public function setIp($ipAddress /* в нормальном виде, то есть с точками */)
	{
	//проверяем что бы переменная $ipAddress не была пуста и содержала IP-адрес 
	if(empty($ipAddress) || ExactilyUserData::takeIP($ipAddress) == false){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_USER,"\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: отсутствует или неверный IP-адрес, невозможно выполнить поиск по бинарной БД");
		}
	$this->ipAddress = $ipAddress;
	}

private static function readXML()
	{
	if(empty(self::$readXml)){
		self::$readXml = new ReadXMLSetup();
		}
	return self::$readXml;
	}

//информация о бинарной БД
public function showInfoBinaryBD()
	{
	if(file_exists($this->fileDB)){
		$file = fopen($this->fileDB, 'rb');
		$string = fgets($file);
		list($indTmp1, $indTmp2, $sizeTmp) = explode(';', $string);
		$index_1 = explode('-', $indTmp1);
		$index_2 = explode('-', $indTmp2);	
		$size = explode('-', $sizeTmp);
		$sizeDB = explode(' ', $size[1]);
		fclose($file);
		?>
		<div style="position: relative; top: 18px; text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
			Общий размер (в байтах) бинарной базы данных: <span style="font-weight: bold;"><?php echo $index_1[1] + $index_2[1] + $sizeDB[1]; ?></span><br>
			- размер первого индекса: <span style="font-weight: bold;"><?= $index_1[1] ?></span><br>
			- размер второго индекса: <span style="font-weight: bold;"><?= $index_2[1] ?></span><br>
			- размер базы данных: <span style="font-weight: bold;"><?= $sizeDB[1] ?></span>
		</div>
		<?php
		}
	}

//краткая информация о найденном в бинарной БД IP-адресе
public function showShortInformationSearchIp()
	{
	//найденный IP-адрес сохраняется в нормальном виде (с точками)
	$searchIp = $this->searchIp();
	$responseString = $this->ipAddress;
		if(!empty($searchIp)){
		try{
			$ip = ip2long($searchIp);
			$query_ip = self::getConnectionDB()->connectionDB()->query("SELECT `type` FROM `black_ip_list` 
																 		WHERE `ip_address`='".$ip."'");
			$arrayTmp = array();
			while($row = $query_ip->fetch(PDO::FETCH_OBJ)){
				$arrayTmp[] = $row->type;
				}
			$typeIp = '';
			$countArrayTmp = count($arrayTmp);
			if(count($countArrayTmp) > 0){
				for($i = 0; $i < $countArrayTmp; $i++){
					$setFix = ($i == ($countArrayTmp - 1)) ? '': ', ';
					$typeIp .= self::readXML()->giveTypeIpList($arrayTmp[$i]).$setFix;
					}
				} else {
					$typeIp .= self::readXML()->giveTypeIpList($arrayTmp[0]);
				}
			return $responseString.'<br><span style="font-weight: bolder;">'.$typeIp.'</span>';
			}
		catch(PDOException $e){
			echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
			}
		}
		return $responseString;
	}

//полная информация о найденном в бинарной БД IP-адресе
public function showInfoSearchIp()
	{
	?>
	<style type="text/css">
	.windowComics {
		display: none;
		position: relative;
		margin: auto;
		left: 0px;
		top: 10px;
		width: 120px;
		height: auto;
		background: #fff;
		padding: 10px;
		-webkit-border-radius: 6px;
		-moz-border-radius: 6px;
		border-radius: 6px;
		-webkit-box-shadow: 0 0 7px #bbb;
		-moz-box-shadow: 0 0 7px #bbb;
		box-shadow: 0 0 7px #bbb;
	}
	.windowComics:before {
		content: "";
		position: absolute; left: 45%; top: 10px; margin-top: -20px; z-index: 1;
		display: block;
		width: 0px;
		height: 0px;
		border-left: 10px solid transparent;
		border-right: 10px solid transparent;
		border-bottom: 11px solid #fff;
	}
	</style>
	<script type="text/javascript">
	function showMessage(elem){
		var idSearch = elem.nextSibling.nextSibling;
		
		if((idSearch.style['display'] == '') || (idSearch.style['display'] == 'none')){
			idSearch.style['opacity'] = 0.7;
			idSearch.style['display'] = 'block';
			} else {
			idSearch.style['display'] = 'none';
		}
	}
	</script>
	<?php
	//найденный IP-адрес сохраняется в нормальном виде (с точками)
	$searchIp = $this->searchIp();
	//проверяем был ли найден искомый IP-адрес
	if(!empty($searchIp)){
		try{
			$ip = ip2long($searchIp);			
			//проверяем доступность таблицы black ip_list и получаем информацию если IP-адрес был найден
			$query = self::getConnectionDB()->connectionDB()->query("SELECT COUNT(`type`) AS NUM FROM `black_ip_list` 
																	 WHERE `ip_address`='".$ip."'");
			$row = $query->fetch(PDO::FETCH_OBJ);
			if($row->NUM > 0){
				$query_ip = self::getConnectionDB()->connectionDB()->query("SELECT `type`, `information` FROM `black_ip_list` 
																	 		WHERE `ip_address`='".$ip."'");
				?>
				<div onclick="showMessage(this)" style="color: #3300FF; text-decoration: underline; cursor: pointer;"><?= $searchIp ?></div>			
				<div class="windowComics">
				<?php
				$i = 0;
				while($row_ip = $query_ip->fetch(PDO::FETCH_ASSOC)){
				?>
					<span name="searchIpName" style="font-size: 10px; font-family: 'Times New Roman', serif; font-weight: bold;">
					<?php if($i != 0) echo '<br>'; ?>
					наименование списка: 
					</span><br>
					<span style="font-size: 12px; font-family: 'Times New Roman', serif; font-weight: bold; text-decoration: underline;">
					<?php echo self::readXML()->giveTypeIpList($row_ip['type']); ?>
					</span><?php
					if($row_ip['information']){
						?><br><span name="searchIpInfo" style="font-size: 10px; font-family: 'Times New Roman', serif;">
						доп. информация:</span><br>
						<span style="font-size: 12px; font-family: 'Times New Roman', serif; font-style: italic">
						<?php echo $row_ip['information']; ?>
						</span><?php
						}
					$i++;
					}
				} else {
					?>
					<span style="color: #FF0000;">что то странное случилось с таблицей БД,<br>доп. информация недоступна</span>
					<?php
				}
			echo "</div>";
			}
		catch (PDOException $e){
			echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
			}
		} else {
		echo $this->ipAddress.'<br>';
		}
	}  

//поиск IP-адреса в бинарной БД
public function searchIp()
	{
	if(isset($this->ipAddress) && !empty($this->ipAddress)){
		//читаем файл бинарной БД
		$this->readFile();
		//выполняем поиск IP-адреса
		return $this->search();
		}
	}

//чтение файла бинарной БД
private function readFile()
	{
	//проверим доступность файла БД
	if(!file_exists($this->fileDB)){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_FILE,"\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: отсутствует файл blackListIp.dat бинарной БД");
		}
	//открываем файл
	$file = fopen($this->fileDB, 'r');
	//Читаем информационную строку из файла бинарной БД
	$string = fread($file, 100);
	//проверяем что указанный файл действительно является файлом бинарной БД
	if(substr($string, 0, 8) === "FileDBip")
		{	
		$array = explode("; ", substr($string, 10));
		//получаем размер первого индекса
 		$this->index_1 = (int) substr($array[0], 10);
 		//получаем размер второго индекса
 		$this->index_2 = (int) substr($array[1], 10);
 		//получаем размер бинарной БД
 		$this->DB = (int) substr($array[2], 9);
		} else {
		echo MessageErrors::userMessageError(MessageErrors::ERROR_FILE,"\t файл: ".__FILE__."\t линия: ".__LINE__."\t ошибка: файл blackListIp.dat не является искомой бинарной БД");
		}
	//получаем массив первого индекса
	$this->readIndexOne($file);
	//получаем массив второго индекса
	$this->readIndexTwo($file);
	//закрываем файл
	fclose($file);
	}

//чтение первого индекса
private function readIndexOne($file) 
	{
	$array = array();
	$array[] = fread($file, $this->index_1);
	sort($array);
	//преобразуем полученный массив в нормальный с начальным ключем = 0
	foreach($array as $binString){
		$this->arrayIndexOne = array_values(unpack("N*", $binString));
		}
	}
	
//чтение второго индекса
private function readIndexTwo($file) 
	{
	$string = fread($file, $this->index_2);
	$this->arrayIndexTwo = explode(" ", $string);
	}

//поиск IP-адреса в бинарной БД
private function search()
	{
	//открываем файл
	$file = fopen($this->fileDB, 'r');
	//получаем первых два октета IP-адреса
	list($oktet_1, $oktet_2,,) = explode(".", $this->ipAddress);
	//Проверяем чтобы 1 и 2 октеты IP-адреса были <= 255
	if(($oktet_1 > 255) || ($oktet_2 > 255)){ 
		echo "<div align='center'>строка {$ip} не является IP-адресом</div>"; 
	} else {
		//переводим IP-адрес в цифровой вид (без точек)
		$ip = pack("N", ip2long($this->ipAddress));
		//получаем смещение в байтах для первого октета 
		$index_11 = $this->arrayIndexOne[$oktet_1];
		if($this->arrayIndexOne[$oktet_1 + 1] == 0){
			for($i = 1; (($this->arrayIndexOne[($oktet_1 + $i)] == 0) && ($oktet_1 + $i < 255)); $i++){
				$oktet = $oktet_1 + $i + 1;
				}
			if($oktet >= 255){
				$index_12 = $this->DB;
			} else {
				$index_12 = $this->arrayIndexOne[($oktet)];
				}
		} else { 
			$index_12 = $this->arrayIndexOne[($oktet_1 + 1)]; 
			}
		$array_index_2 = explode(",",$this->arrayIndexTwo[($oktet_2)]);
		$count_array_index_2 = count($array_index_2);
//echo $count_array_index_2."<br />";
		for($i = 0; $i < $count_array_index_2; $i++){
//echo "$index_11 <= ".$array_index_2[$i]." и ".$array_index_2[$i]." <= $index_12<br/>";
			if(($index_11 <= $array_index_2[$i]) && ($array_index_2[$i] <= $index_12)){
				$index_2 = $array_index_2[$i];
				$i = $count_array_index_2;
				}
			}
		if(isset($index_2)){
			$byte = 100 + $this->index_1 + $this->index_2 + $index_2;
			//Смещение в байтах в файле DB
			fseek($file, $byte, SEEK_SET);
			//Так как строка для fread не может быть = 0 присваиваем значение 4
			$strlen = $index_12 - $index_2;
			if($strlen == 0){ 
				$strlen = 4; 
				}
			$string_ip = fread($file, $strlen);
			$start = 0;
			$count_ip = ($index_12 - $index_2)/4;
			for($i = 0; $i < $count_ip; $i++){
				$ip_s = unpack("N*", substr($string_ip, $start, 4));
				$ip_bs = substr($string_ip, $start, 4);
				if($ip_bs == $ip){
					$ip_b = unpack("N*", $ip_bs);
					$i = $count_ip;
					return long2ip($ip_b[1]);
					}
				$start += 4;	
				}
			}
		}
	fclose($file);
	}
}
?>