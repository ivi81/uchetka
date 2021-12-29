<?php

										/*------------------------------------------*/
										/*			класс вывода даты и времени		*/
										/*					v 0.1 05.06.2014		*/
										/*------------------------------------------*/

class ShowUserDate
{

public function __construct() 
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	?>
<!-- рамка для поля ДАТЫ И ВРЕМЕНИ -->	
		<style>
		.informationDate {
		 	width: 200px;
		 	height: 135px;
		 	vertical-align: middle;
	 	 	border-radius: 3px;
		 	background: #F0FFFF; 
		 	box-shadow: inset 0 0 8px 0px #B7DCF7; }
		</style>
	
<!-- вывод даты и времени -->
	<div class="informationDate">
<!-- изображение календаря -->
		<div style="position: absolute; top: 20px; left: 20px;">
			<img src=<?php echo "/{$array_directory[1]}/img/calendar/".$this->calendar(); ?> >		
		</div>
<!-- ЧИСЛО -->
		<span style="position: absolute; top: 20px; left: 70px; font-weight: bold; width: 40px; height: 40px; text-align: center;">
			<span style="font-size: 40px; font-family: 'Times New Roman', serif; font-style: italic;">
			<?php
			$day = date("d"); 
			if($day < 10){
				$day = substr($day, 1, 1);
				}
			if(date("w") == 0){
				//для воскресенья красный цвет
				?>
				<span style="color: #FF0000;"><?php echo $day; ?></span>
				<?php
				} else {
				//для рабочего дня темно синий
				?>
				<span style="color: #3300FF;"><?php echo $day; ?></span>
				<?php
				}
			?>			
			</span>
		</span>
<!-- ДЕНЬ НЕДЕЛИ -->
		<div style="position: absolute; top: 65px; left: 60px; text-align: center; width: 150px; height: 20px;">
		<span style="font-size: 16px; font-family:'Times New Roman', serif; letter-spacing: 1px;">
			<?php echo $this->dayWeekConvert(); ?>
		</span>
		</div>
<!-- блок месяца и года -->
		<div style="text-align: center; position: absolute; top: 20px; left: 120px;">
<!-- МЕСЯЦ -->
			<span style="font-size: 16px; font-family: Palatino, 'Times New Roman', serif; font-style: italic;">
				<?php echo $this->monthConvert(); ?>
			</span><br>
<!-- ГОД -->
			<span style="font-size: 16px; font-family: Palatino, 'Times New Roman', serif; font-style: italic;">
				<?php echo date("Y"); ?>
			</span>
		</div>
		<div style="position: absolute; top: 90px; left: 20px; width: 65px;">
			<form name="clockForm" align="right">
				<input type="text" name="clock" onload="getClock()" size="6" readonly class="form_time_color">
			</form>
		</div>
<!-- электронные часы -->
		<script type="text/javascript">
		//Функция формирующая часы
		function getClock()
			{
			var hours = new Date().getHours();
			var minutes = new Date().getMinutes();
			var seconds = new Date().getSeconds();
			if(hours < 10){
				hours = "0" + hours;
				}
			if(minutes < 10){
				minutes = "0" + minutes;
				}
			if(seconds < 10){
				seconds = "0" + seconds;
				}
			document.clockForm.clock.value = hours + ":" + minutes + ":" + seconds;
			setTimeout('getClock()', 300);
			}
		window.onload = getClock();
		</script>
	</div>
	<?php
	}
	
//функция вывода даты
private function calendar() 
	{
	$calendar = array('01' => '01.gif',
					  '02' => '02.gif',
					  '03' => '03.gif',
					  '04' => '04.gif',
					  '05' => '05.gif',
					  '06' => '06.gif',
					  '07' => '07.gif',
					  '08' => '08.gif',
					  '09' => '09.gif',
					  '10' => '10.gif',
					  '11' => '11.gif',
					  '12' => '12.gif');
	return $calendar[substr(date("m-d-Y"),0 , 2)];
	}
	
//Функция дня недели
private function dayWeekConvert() 
	{
	$day = array("воскресенье", "понедельник", "вторник", "среда", "четверг", "пятница", "суббота");
	if(date("w") == 0)
		{
		$dayName = "<span style='color: #FF0000;'>".$day[date("w")]."</span>";
		}
	else 
		{
		$dayName = "<span style='color: #000;'>".$day[date("w")]."</span>";
		}
	return $dayName;
	}
			
//Функция перевода месяца и числового формата в текстовый на русском языке
private function monthConvert() 
	{
	$month = array("-01-","-02-","-03-","-04-","-05-","-06-","-07-","-08-","-09-","-10-","-11-","-12-");
	$ds = array(" января "," февраля "," марта "," апреля "," мая "," июня "," июля "," августа "," сентября "," октября "," ноября "," декабря ");
	$newdate = str_replace($month, $ds, substr(date("d-m-Y"), 2, 4));
	return $newdate;
	}
}
?>