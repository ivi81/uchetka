<?php

							/*------------------------------------------------------*/
							/*  	абстрактный класс вывода статистической 		*/
							/*		информации	по разделу время реагирования		*/
							/* 	 						v.0.1 04.12.2014    		*/
							/*------------------------------------------------------*/

abstract class StatisticsTimeReact
{
protected $DBO;
protected $XML;
protected $GeoIp;
protected $directory;

function __construct()
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directory = $array_directory[1];
	$this->DBO = new DBOlink;
	$this->XML = new ReadXMLSetup;
	$this->GeoIp = new GeoIP;
	?>
<!-- подключаем jQuery -->
	<script src="<?php echo '/'.$this->directory.'/js/jquery-2.1.1.min.js' ?>"></script>	
<!-- подключаем HighChart.js -->
	<script src="<?php echo '/'.$this->directory.'/js/highcharts.js' ?>"></script>
	<script src="<?php echo '/'.$this->directory.'/js/exporting.js' ?>"></script>
	<?php
	}

//функция вывода ','
protected function getFix(array $array, $num)
	{
	$countArray = count($array);
	if($countArray > 1)	return ($num < $countArray - 1) ? ', ':' ';
	if($countArray == 1) return ($num < $countArray) ? ', ':' ';
	}

//функция выполнения запроса и подготовки массива данных содержащего login пользователя и интервал времени
protected function queryDataBase($queryString, $timeStart, $timeEnd)
	{
	$array = array();
	try{
		$query = $this->DBO->connectionDB()->query($queryString);
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$array[$row->login_name][0][] = $row->$timeStart;
			$array[$row->login_name][1][] = $row->$timeEnd;
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	return $array;
	}

//функция перевода временного интервала в формате Unix в часы и минуты
protected function timeTranslation($timeUnixFormat)
	{
	$timeUnixFormat = $timeUnixFormat / 360;
	if($timeUnixFormat < 60){
		return round($timeUnixFormat, 2);
		} else {
		$timeUnixFormat = (string) round($timeUnixFormat / 60);
		$timeUnixFormat = (string) $timeUnixFormat;
		if(strripos($timeUnixFormat, '.') === false) return $timeUnixFormat;
		$array_time = explode('.', $timeUnixFormat);
		if($array_time[1] > 60){
			$hour = $array_time[0] + 1;
			$min = $array_time[1] - 60;
			return $hour.'.'.$min;
			}
		return $timeUnixFormat;	
		}
	}

abstract public function showInformation($role);
}
?>