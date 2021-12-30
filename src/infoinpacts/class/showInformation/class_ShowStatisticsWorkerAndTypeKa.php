<?php

							/*------------------------------------------------------------------------------------------*/
							/*  		 класс вывода статистической информации	в виде соотношения типов 				*/
							/*							компьютерных атак к именам дежурных								*/
							/*		(проще говоря статистика добавления дежурным раздичных типов компьютерных атак)		*/
							/* 	 														v.0.1 29.12.2014    			*/
							/*------------------------------------------------------------------------------------------*/

class ShowStatisticsWorkerAndTypeKa
{
private $DBO;
private $XML;

function __construct()
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directory = $array_directory[1];
	$this->DBO = new DBOlink;
	$this->XML = new ReadXMLSetup;
	?>
<!-- подключаем jQuery -->
	<script src="<?php echo '/'.$this->directory.'/js/jquery-2.1.1.min.js' ?>"></script>	
<!-- подключаем HighChart.js -->
	<script src="<?php echo '/'.$this->directory.'/js/highcharts.js' ?>"></script>
	<script src="<?php echo '/'.$this->directory.'/js/exporting.js' ?>"></script>
	<?php
	}	

//добавляем ','
private function getFix($a, array $array)
	{
	$count = count($array);
	return $fix = ($a < $count - 1) ? ', ': ' ';	
	}

//вывод подготовленной информации
public function showInformation()
	{
	//выполняем запрос к БД
	$array_information = $this->sendRequectDB($this->XML->getArrayUserNumGroup(20));
	//прелбразуем массив в строку categories
	$categoriesString = $this->getCategoriesString($array_information);
	//преобразуем массив в строку data
	$seriesString = $this->getSeriesString($array_information);
	//строим график
	$this->getCharts($categoriesString, $seriesString);	
	}

//запрос к БД и формирование массива данных
private function sendRequectDB(array $login_name)
	{
	$array = array();
	try{
		foreach($login_name as $key => $login){
			$query = "SELECT `login_name`, `type_attack`, COUNT(`type_attack`) AS NUM 
					  FROM `incident_chief_tables` t0 LEFT JOIN `incident_additional_tables` t1 
					  ON t0.id=t1.id WHERE `login_name`='".$login."' GROUP BY `type_attack`, `login_name`";
			//создаем дополнительный массив с типами КА
			$array_tmp = array_fill(1, count($this->XML->giveTypeKA()), 0);
			$queryResult = $this->DBO->connectionDB()->query($query);		
			$array[$login] = $array_tmp;
			while($row = $queryResult->fetch(PDO::FETCH_OBJ)){
				if($row->type_attack != '0'){
					if(array_key_exists($row->type_attack, $array_tmp)){
						$array[$login][$row->type_attack] = intval($row->NUM);
						}
					}
				}
			}

		$array_delete = array();
		//ищем массивы содержащие только '0'
		foreach($array as $key => $value){
			$array_tmp = array_unique($value);
			if(count($array_tmp) == 1 && $array_tmp[1] == 0){
				$array_delete[] = $key;
				}
			}

		//удаляем 'пустые' массивы
		foreach($array_delete as $key){
			unset($array[$key]);
			}

		return $array;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	}

//готовим строку с данными (поле categories)
private function getCategoriesString(array $array)
	{
	$newArray = array();
	foreach($array as $login => $value) {
		$newArray[$this->XML->giveUserNameAndSurname($login)] = 0;
		}
	return "['".implode("', '", array_keys($newArray))."']";
	}

//готовим строку с данными (поле series)
private function getSeriesString(array $array)
	{
	$countTypeKa = count($this->XML->giveTypeKA());
	$string = '[';
	for($i = 1; $i < $countTypeKa + 1; $i++){
		$string .= "{ name: '".$this->XML->giveTypeKAForId($i)."', ";
		$string .= "data: [";
		$a = 0;
		foreach($array as $login => $value){
			$string .= $value[$i].$this->getFix($a, $array);
			$a++;
			}
		$string .= "]}".$this->getFix($i - 1, $this->XML->giveTypeKA());
		}
	$string .= "]";
	return $string;
	}

//строим график
private function getCharts($categories, $series)
	{
	$num = count($this->XML->getArrayUserNumGroup(20)) * 150;
	?>
	<!-- диаграмма -->
	<div id="container" style="width: 735px; min-height: <?= $num.'px' ?>; margin-left: 2px; float: left;"></div>
	<script type="text/javascript">
	$(function (){
		$('#container').highcharts({
			chart: {
				type: 'bar'
				},
			title: {
				text: 'дежурные'
				},
			subtitle: {
				text: 'статистика по типам компьютерных атак'
				},
			xAxis: {
				title: {
					text: null
					},
				categories: <?= $categories ?>
				},
			yAxis: {
				min: 0,
				title: {
					text: 'количество компьютерных атак',
					align: 'high'
					},
				labels: {
					overflow: 'justify'
					}
				},
			tooltip: {
				valueSuffix: ''
				},
			plotOptions: {
				bar: {
					dataLabels: {
						enabled: true
						}
					}
				},
			legend: {
				layout: 'vertical',
				align: 'right',
				verticalAlign: 'top',
				x: -10,
				y: 60,
				floating: true,
				borderWidth: 1,
				backgroundColor: '#FFFFFF',
				shadow: true
				},
			credits: {
				enabled: false
				},
			series: <?= $series ?>
			});
		});
	</script>
	<?php
	}

}
?>