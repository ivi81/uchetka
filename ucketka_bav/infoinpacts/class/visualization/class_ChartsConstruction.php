<?php

							/*------------------------------*/
							/*  класс графиков и диаграмм 	*/
							/* 	 	v0.1	01.10.2014		*/
							/*------------------------------*/

class ChartsConstruction
{

private $directoryRoot;

function __construct()
	{
	//получаем корневую директорию сайта
	$dir = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directoryRoot = $dir[1];
	//подключаем Chart.js
	?><script src="<?php echo '/'.$this->directoryRoot.'/js/Chart.js-master/Chart.js' ?>"></script><?php
	//подключаем legend.js
	?><script src="<?php echo '/'.$this->directoryRoot.'/js/Chart.js-master/legend.js' ?>"></script><?php
	?><link rel="stylesheet" type="text/css" href="/<?php echo $this->directoryRoot ?>/css/chart.js.legend.css"></link><?php
	}

//круговая диаграмма
public function getChartPie($data, $dataLegend, $id)
	{
	?>
	<div style="float: left; width: 365px;">
		<div style="float: left;"><canvas id="<?= 'chart_'.$id ?>" width="170" height="170" style="margin: 15px;"></canvas></div>
		<div id="<?= $id ?>" class="legend" style="margin: 5px; display: inline-block;"></div>
	</div>
	<script type="text/javascript">
	(function(){
		var elem = document.getElementById("<?= 'chart_'.$id ?>").getContext("2d");
		var myNewChartPie = new Chart(elem).Pie(<?= $data ?>, { segmentShowStroke: true,
																segmentStrokeColor: "#FFF",
																segmentStrokeWidth: 2,
																percentageInnerCutout: 0,
																animationSteps: 100,
																anumationEasing: "easeOutBounce",
																animateRotate: true,
																animateScale: false });
		//вывод легенды
		legend(document.getElementById("<?= $id ?>"), <?php echo $dataLegend; ?>);
		})();
	</script>
	<?php
	}
//диаграмма типа 'бублик'
public function getChartDoughnut($data, $dataLegend, $id)
	{
	?>
	<div style="float: left; width: 365px;">
		<div style="float: left;"><canvas id="<?= 'chart_'.$id ?>" width="170" height="170" style="margin: 15px;"></canvas></div>
		<div id="<?= $id ?>" class="legend" style="margin: 5px; display: inline-block;"></div>
	</div>
	<script type="text/javascript">
	(function(){
		var elem = document.getElementById("<?= 'chart_'.$id ?>").getContext("2d");
		var myNewChartPie = new Chart(elem).Pie(<?= $data ?>, { 
																segmentShowStroke: true,
																segmentStrokeColor: "#FFF",
																segmentStrokeWidth: 2,
																percentageInnerCutout: 50,
																animationSteps: 100,
																anumationEasing: "easeOutBounce",
																animateRotate: true,
																animateScale: false });
		//вывод легенды
		legend(document.getElementById("<?= $id ?>"), <?php echo $dataLegend; ?>);
		})();
	</script>
	<?php
	}

}
?>