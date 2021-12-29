<?php

							/*----------------------------------------------------------*/
							/*  	 класс вывода статистической информации				*/
							/*	 			по разделу время реагирования 				*/
							/* 	 						v.0.1 09.12.2014    			*/
							/*----------------------------------------------------------*/

class ShowStatisticsTimeReact extends StatisticsTimeReact
{
const WORKER = 1;
const ANALYST = 2;
const COMMON = 3;
//вывод информации
public function showInformation($role)
	{
	switch($role){
		case self::WORKER:
			$query = "SELECT `login_name`, `date_time_incident_start`, `date_time_create` 
					  FROM `incident_chief_tables` t0 JOIN `incident_additional_tables` t1 
			  		  ON t0.id=t1.id GROUP BY `date_time_incident_start` 
			  		  ORDER BY `login_name`, `date_time_incident_start`";
			//получаем данные о пользователе
			$data = $this->proccesingRequest($this->queryDataBase($query, 'date_time_incident_start', 'date_time_create'), self::WORKER);
			echo "<div style='width: 370px; overflow: hidden;'>";
			//строим график
			$this->buildCharSemiCircleWorker($data);
			echo "</div>";
		break;
		case self::ANALYST:
			$query = "SELECT `login_name`, `date_time_analyst`, `date_time_create` 
					  FROM `incident_analyst_tables` t0 JOIN `incident_chief_tables` t1 
			  		  ON t0.id=t1.id WHERE (`date_time_analyst` is not Null) GROUP BY `date_time_create` 
			  		  ORDER BY `login_name`, `date_time_create`";
			//получаем данные о пользователе
			$data = $this->proccesingRequest($this->queryDataBase($query, 'date_time_create', 'date_time_analyst'), self::ANALYST);
			echo "<div style='width: 370px; overflow: hidden;'>";				
			//строим график
			$this->buildCharSemiCircleAnalyst($data);
			echo "</div>";
		break;
		case self::COMMON:
			$query = "SELECT t0.id, `date_time_incident_start`, `time_forming_mail` 
					  FROM `incident_chief_tables` t0 JOIN `incident_additional_tables` t1 ON t0.id=t1.id 
					  WHERE (`time_forming_mail` is not Null) GROUP BY `date_time_incident_start` ORDER BY `date_time_incident_start` DESC";
			//получаем данные о пользователе
			$this->proccesingRequestCommonInfo($query);				
		break;		
		default:
			echo 'нет такой роли';
		break;
		}
	}

//функция выполнения запроса и подготовки вывода среднего количества часов необходимых для обработки компьютерного воздействия
protected function proccesingRequestCommonInfo($query)
	{
	try{
		$query = $this->DBO->connectionDB()->query($query);
		$middleTime = $i = 0;
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$middleTime += ($row->time_forming_mail - strtotime($row->date_time_incident_start));
			$i++;
			}
		$hour = round((($middleTime / $i) / 3600), 2);
		if(strripos($hour, '.') === false){
			echo $hour.' '.DeclansionWord::declansionNum($hour, array('час', 'часа', 'часов'));
			} else {
			$array_time = explode('.', $hour);
			if($array_time[1] > 60){
				$hour = $array_time[0] + 1;
				$min = $array_time[1] - 60;
				echo $hour.' '.DeclansionWord::declansionNum($hour, array('час', 'часа', 'часов'))
				.' '.$min.' '.DeclansionWord::declansionNum($min, array('минута', 'минуты', 'минут'));
				} else {
				echo $array_time[0].' '.DeclansionWord::declansionNum($array_time[0], array('час', 'часа', 'часов'))
				.' '.$array_time[1].' '.DeclansionWord::declansionNum($array_time[1], array('минута', 'минуты', 'минут'));	
				}
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//обработка данных
private function proccesingRequest(array $array_Data, $titleName)
	{
	$array_Name = array();
	//получаем среднее время для каждого пользователя
	foreach($array_Data as $name => $value){
		$middleTime = '';
		for($i = 0; $i < count($value); $i++){
			if($titleName == 1){
				$middleTime += ($value[1][$i] - strtotime($value[0][$i]));
				}
			elseif($titleName == 2){
				$middleTime += ($value[1][$i] - $value[0][$i]);
				}
			}
		$array_Name[$name][] = $middleTime / $i;
		}
	//выводим данные
	$i = 0;
	$data = '[';
	foreach($array_Name as $name => $value){
		list($family,,) = explode(' ', $this->XML->usernameFIO($name));
		$data .= "['".$family."',  ".$this->timeTranslation($value[0])."]".$this->getFix($array_Name, $i);
		$i++;
		}
	$data .= ']';
	return $data;
	}

//график для дежурного
private function buildCharSemiCircleWorker($data)
	{
	?>
<!-- диаграмма -->
		<div id="containerone" style="width: 365px; height: 350px; margin-left: 2px; float: left;"></div>
<!-- строим диаграмму -->
		<script type="text/javascript">
			$(function () {
    			$('#containerone').highcharts({
        			chart: {
            			plotBackgroundColor: null,
            			plotBorderWidth: 0,
            			plotShadow: false
        			},
        			title: {
            			text: 'дежурные',
            			align: 'center',
            			verticalAlign: 'middle',
            			y: 50
        			},
        			tooltip: {
            			//pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        			},
        			plotOptions: {
            			pie: {
                		dataLabels: {
                    		enabled: true,
                    		distance: -50,
                    		style: {
                        		fontWeight: 'bold',
                        		color: 'white',
                        		textShadow: '0px 1px 2px black'
                    		}
                		},
                		startAngle: -90,
                		endAngle: 90,
                		center: ['50%', '75%']
            			}
        			},
        			series: [{
            			type: 'pie',
            			name: 'реагирование в часах',
            			innerSize: '50%',
            			data: <?= $data ?>        				
            			}]
    				});
				});
		</script>
	<?php
	}

//график для аналитика
private function buildCharSemiCircleAnalyst($data)
	{
	?>
<!-- диаграмма -->
		<div id="containertwo" style="width: 365px; height: 350px; margin-left: 2px; float: left;"></div>
<!-- строим диаграмму -->
		<script type="text/javascript">
			$(function () {
    			$('#containertwo').highcharts({
        			chart: {
            			plotBackgroundColor: null,
            			plotBorderWidth: 0,
            			plotShadow: false
        			},
        			title: {
            			text: 'аналитики',
            			align: 'center',
            			verticalAlign: 'middle',
            			y: 50
        			},
        			tooltip: {
            			//pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        			},
        			plotOptions: {
            			pie: {
                		dataLabels: {
                    		enabled: true,
                    		distance: -50,
                    		style: {
                        		fontWeight: 'bold',
                        		color: 'white',
                        		textShadow: '0px 1px 2px black'
                    		}
                		},
                		startAngle: -90,
                		endAngle: 90,
                		center: ['50%', '75%']
            			}
        			},
        			series: [{
            			type: 'pie',
            			name: 'реагирование в часах',
            			innerSize: '50%',
            			data: <?= $data ?>        				
            			}]
    				});
				});
		</script>
	<?php
	}
}
?>