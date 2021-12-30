<?php

							/*------------------------------------------------------*/
							/*  абстрактный класс вывода статистической информации 	*/
							/*	 		по разделу геопозиционирования GeoIP		*/
							/* 	 						v.0.2 12.11.2014    		*/
							/*------------------------------------------------------*/

abstract class StatisticsGeoIp
{
protected $DBO;
protected $XML;
protected $GeoIp;
protected $directory;

function __construct(DBOlink $DBO, ReadXMLSetup $XML)
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directory = $array_directory[1];
	$this->DBO = $DBO;
	$this->XML = $XML;
	$this->GeoIp = new GeoIP;
	}

abstract public function showInformation();
abstract protected function showForm();
abstract protected function showData();

//функция возвращающая роль пользователя
protected function getUserRole()
	{
	$role = $_SESSION['userSessid']['userId'];
	if(!preg_match("/^[0-9]{2}$/", $role)) echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL, "\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: роль пользователя не определена");
	switch($role){
		case '10':
			return 'major';
		break;
		case '20':
			return 'worker';
		break;
		case '30':
			return 'analyst';
		break;
		case '40':
			return 'admin';
		break;
		}
	}

//функция вывода ','
protected function getFix(array $array, $num)
	{
	$countArray = count($array);
	if($countArray > 1)	return ($num < $countArray - 1) ? ', ':' ';
	if($countArray == 1) return ($num < $countArray) ? ', ':' ';
	}

//функция возвращающая строку с датой 
//(получает массив со значениями в виде "2014-01", возвращает строку "январь 2014") 
protected function getDateString(array $array)
	{
	$array_tmp = array();
	$numMonth = array("01","02","03","04","05","06","07","08","09","10","11","12");
	$month = array(" январь "," февраль "," март "," апрель "," май "," июнь "," июль "," август "," сентябрь "," октябрь "," ноябрь "," декабрь ");
	foreach($array as $value){
		//проверяем значение
		if(preg_match("/^[0-9]{4}-[0-9]{2}$/", $value)){
			$array_tmp[] = str_replace($numMonth, $month, substr($value, 5, 2)).' '.substr($value, 0 , 4).' года';
			}
		}
	return json_encode($array_tmp, JSON_UNESCAPED_UNICODE);
	} 

//получить строку начальной и конечной даты и времени 
public static function showStringDateTime($startDateTime /* обычный формат */, 
										  $endDateTime /* обычный формат */, 
										  $flag /* true для интервала (не учитывается время), false для диапазона */)
	{
	//проверяем длину строки
	$num = ($flag) ? 19 : 16;
	if(strlen($startDateTime) != $num || strlen($endDateTime) != $num) return false;
	$arrayDateStart = explode('-', substr($startDateTime, 0, 10));
	$arrayTimeStart = explode(':', substr($startDateTime, 11, 8));
	$arrayDateEnd = explode('-', substr($endDateTime, 0, 10));
	$arrayTimeEnd = explode(':', substr($endDateTime, 11, 8));

	//проверяем год
	if($arrayDateStart[0] != $arrayDateEnd[0]){
		return "интервал с {$arrayTimeStart[0]}:{$arrayTimeStart[1]} {$arrayDateStart[2]} ".ConversionData::showMonth($startDateTime)
			   ." ".$arrayDateStart[0]." года по {$arrayTimeEnd[0]}:{$arrayTimeEnd[1]} {$arrayDateEnd[2]} ".ConversionData::showMonth($endDateTime)
			   ." ".$arrayDateEnd[0]." года";
		}

	//проверяем месяц
	if($arrayDateStart[1] != $arrayDateEnd[1]){
		return "интервал с {$arrayTimeStart[0]}:{$arrayTimeStart[1]} {$arrayDateStart[2]} ".ConversionData::showMonth($startDateTime)
			   ." по {$arrayTimeEnd[0]}:{$arrayTimeEnd[1]} {$arrayDateEnd[2]} ".ConversionData::showMonth($endDateTime)
			   ." ".$arrayDateEnd[0]." года";
		}

	//проверяем день
	if($arrayDateStart[2] != $arrayDateEnd[2]){
		return "интервал с {$arrayTimeStart[0]}:{$arrayTimeStart[1]} {$arrayDateStart[2]} "
			   ." по {$arrayTimeEnd[0]}:{$arrayTimeEnd[1]} {$arrayDateEnd[2]} ".ConversionData::showMonth($endDateTime)
			   ." ".$arrayDateEnd[0]." года";
		} else {
		return "интервал с {$arrayTimeStart[0]}:{$arrayTimeStart[1]} по {$arrayTimeEnd[0]}:{$arrayTimeEnd[1]} {$arrayDateEnd[2]} "
			   .ConversionData::showMonth($endDateTime)." ".$arrayDateEnd[0]." года";
		}
	}

//функция выполняет sql-запрос и возвращает результат в виде массива
protected function sqlReguest($reguest, array $sqlArguments)
	{
	$array = array();
	try{
		$query = $this->DBO->connectionDB()->query($reguest);
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			foreach($sqlArguments as $value){
				if($value === 'date_time_incident_start'){
					$array['date'][] = substr($row->$value, 0, 7);
					} else {
					$array[$value][] = $row->$value;
					}
				}
			}
		return $array;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	}

}

?>