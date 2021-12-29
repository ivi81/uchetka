<?php

							/*----------------------------------------------------------*/
							/*  	 класс вывода статистической информации				*/
							/*	 			по разделу время реагирования 				*/
							/* 	 						v.0.1 09.12.2014    			*/
							/*----------------------------------------------------------*/

class ShowStatisticsForUsers
{
private $DBO;
private $XML;
const WORKER = 1;
const ANALYST = 2;

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

//вывод информации
public function showInformation($role)
	{
	switch($role){
		//дежурные
		case self::WORKER:
			$query = "SELECT `login_name`, `true_false`,  COUNT(`true_false`) AS NUM FROM 
					 (SELECT t0.login_name, `true_false` FROM `incident_additional_tables` t0 JOIN `incident_analyst_tables` t1 
					  ON t0.id=t1.id) tables GROUP BY `login_name`, `true_false`";
			//получаем результат запроса
			$queryRespons = $this->sendRequectDB($query); 
			//строим график
			$this->buildCharSemiCircle($this->getStringData($queryRespons), /* данные (data) */
											 $this->getStringCategories($queryRespons), /* Ф.И.О. (categories) */
											 '\'дежурные\'');
		break;
		//аналитик
		case self::ANALYST:
			$query = "SELECT `login_name`, `true_false`,  COUNT(`true_false`) AS NUM FROM `incident_analyst_tables`  
					  WHERE `true_false`!='3' and `login_name`!='' GROUP BY `login_name`, `true_false`";
			//получаем результат запроса
			$queryRespons = $this->sendRequectDB($query); 
			//строим график
			$this->buildCharSemiCircle($this->getStringData($queryRespons), /* данные (data) */
											 $this->getStringCategories($queryRespons), /* Ф.И.О. (categories) */
											 '\'аналитики\'');
		break;
		default:
			echo 'неизвестная роль';
		break;
		}
	}

//запрос к БД и обработка полученных данных
private function sendRequectDB($query)
	{
	$array = array();
	try{
		$queryDB = $this->DBO->connectionDB()->query($query);
		while($row = $queryDB->fetch(PDO::FETCH_OBJ)){
			$array[$row->login_name][$row->true_false] = $row->NUM;
			}
		return $array;
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	}

//подготовка строки data
private function getStringData(array $array)
	{
	$a = 0;
	$string = "[";
	foreach($array as $name => $value){
		$b = $a + 2;
		$sum = $value[1] + $value[2];
		$string .= "{ y: {$sum}, color: colors[{$b}], drilldown: {
			name: '".$this->XML->giveUserNameAndSurname($name)."', categories: [ 'компьютерная атака', 'ложное' ], data: [ {$value[1]}, {$value[2]} ], color: colors[{$a}]
			}}".$this->getFix($a, $array);
		$a++;
		}
	$string .= "]";
	return $string;
	}

//подготовка строки categories
private function getStringCategories(array $array)
	{
	$a = 0;
	$string = '[';
	foreach($array as $name => $value){
		$string .= "'".$this->XML->giveUserNameAndSurname($name)."'".$this->getFix($a, $array);
		$a++;
		}
	$string .= ']';
	return $string;
	}

//график пользователя
private function buildCharSemiCircle($data, $categories, $title)
	{
	?>
<!-- диаграмма -->
		<div id="container" style="padding: 10px; width: 720px; height: 460px; margin-left: 2px; float: left;"></div>
<!-- строим диаграмму -->
		<script type="text/javascript">
			$(function () {
				var colors = Highcharts.getOptions().colors,
					categories = <?= $categories ?>,
					data = <?= $data ?>,
					browserData = [],
					versionsData = [],
					dataLen = data.length,
					drillDataLen,
					brightness;
				//готовим основные данные
				for(var i = 0; i < dataLen; i++){
					browserData.push({
						name: categories[i],
						y: data[i].y,
						color: data[i].color
						});
					//готовим подробные данные
					drillDataLen = data[i].drilldown.data.length;
					for(var j = 0; j < drillDataLen; j++){
						brightness = 0.2 - (j / drillDataLen) / 5;
						//категории событий ИБ
						versionsData.push({
							name: data[i].drilldown.categories[j],
							y: data[i].drilldown.data[j],
							color: Highcharts.Color(data[i].color).brighten(brightness).get()
						});
					}
				}
				//создаем график	
    			$('#container').highcharts({
        			chart: {
						type: 'pie'
        			},
        			title: {
            			text: <?= $title ?>,
            			align: 'center',
            			verticalAlign: 'middle',
            			y: 50
        			},
        			plotOptions: {
        				pie: {
        					shadow: false,
        					center: ['50%', '50%']
        				}
        			},
        			tooltip: {
        				//valueSuffix: '%'
        			},
        			series: [{
        				//общее количество компьютерных воздействий
        				name: 'общее количество',
            			data: browserData,
            			size: '60%',
            			dataLabels: {
            				formatter: function (){
            					return this.y > 5 ? this.point.name : null;
            				}
            			},
            			color: 'white',
            			distance: -30
            		}, {
            			//количество компьютерных воздействий по типам
            			name: 'количество',
            			data: versionsData,
            			size: '80%',
            			innerSize: '60%',
            			dataLabels: function (){
            				return this.y > 1 ? '<b>' + this.point.name + ':</b>' + this.y + '%' : null;
            			}
            			}]
    				});
				});
		</script>
	<?php
	}
}
?>