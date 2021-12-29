<?php

						/*--------------------------------------------------*/
						/*			класс вывода информационных сообщений	*/
						/*								v.0.2 18.11.2014 	*/
						/*--------------------------------------------------*/

class ShowMessage extends MessageErrors
{
//получаем корневую директорию сайта
public static function getDirectory() 
	{
	return explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	}

//вывод сообщения об успешном выполнении с редиректом на страницу	INDEX.PHP
public static function messageOkRedirect($message, $top = 250) 
	{
	$array_directory = self::getDirectory();
	?>
	<style>
		.messageOk {
			position: absolute; left: 50%; margin-left: -210px; top: <?php echo $top.'px' ?>; z-index: 10;
			width: 420px;
			height: 150px;
			vertical-align: middle;
	 		border-radius: 8px;
	 		text-align: center;
			display: table;
			background: #FFF; 
			box-shadow: inset 0 0 10px 0 #7CFC00; }
	</style>			
	<div class="messageOk">
		<table border="0" style="width: 100%; height: 100%;">
			<tr>
			<td style="width:70px; text-align: right"><img src="/<?= $array_directory[1] ?>/img/accepted_48.png"></td>
			<td style="width:230px; padding-left: 10px; padding-right: 20px;">
			<span style="font-size: 18px; font-family: Verdana, 'Times New Roman', serif; color: #000; letter-spacing: 1px;" >
			<?= $message ?><br>
			<span id="timer">
				<script type="text/javascript" >
					window.setTimeout(function(){
						window.location.href = "/<?= $array_directory[1] ?>/index.php"
						}, 1000); //интервал в секундах (1000 это 1 сек.)
				</script>
			</span>
			</span>
			</td>
			</tr>
		</table>
	</div>
	<?php
	}

//вывод сообщения о не найденной информации
public static function informationNotFound($message, $top = 250)
	{
	$array_directory = self::getDirectory();
		?>
	<style>
		.messageWarning {
			position: absolute; left: 50%; margin-left: -210px; top: <?php echo $top.'px' ?>; z-index: 10;
			width: 420px;
			height: 150px;
			vertical-align: middle;
	 		border-radius: 8px;
	 		text-align: center;
			display: table;			
			background: #FFF; 
			box-shadow: inset 0 0 10px 0 #FFD700; }
	</style>			
	<div class="messageWarning">
		<table border="0" style="width: 100%; height: 100%;">
			<tr>
			<td style="width:70px; text-align: right;"><img src="/<?= $array_directory[1] ?>/img/warning_48.png" style=""></td>
			<td style="width:230px; padding-left: 10px; padding-right: 20px;">
			<span style="font-size: 18px; font-family: Verdana, 'Times New Roman', serif; color: #000; letter-spacing: 1px;" >
			<?= $message; ?>
			</span>
			</td>
			</tr>
		</table>
	</div>
	<?php
	//exit();
	}
	
//вывод сообщения о невыполнении задачи
public static function messageWarning($message, $top = 250) 
	{
	$array_directory = self::getDirectory();
		?>
	<style>
		.messageWarning {
			position: absolute; left: 50%; margin-left: -210px; top: <?php echo $top.'px' ?>; z-index: 10;
			width: 420px;
			height: 150px;
			vertical-align: middle;
	 		border-radius: 8px;
	 		text-align: center;
			display: table;			
			background: #FFF; 
			box-shadow: inset 0 0 10px 0 #FFD700; }
	</style>			
	<div class="messageWarning">
		<table border="0" style="width: 100%; height: 100%;">
			<tr>
			<td style="width:70px; text-align: right;"><img src="/<?= $array_directory[1] ?>/img/warning_48.png" style=""></td>
			<td style="width:230px; padding-left: 10px; padding-right: 20px;">
			<span style="font-size: 18px; font-family: Verdana, 'Times New Roman', serif; color: #000; letter-spacing: 1px;" >
			<?= $message; ?> <br>
			<span id="timer">
				<script type="text/javascript" >
					window.setTimeout(function(){
						window.location.href = "/<?= $array_directory[1] ?>/index.php"
						}, 1000); //интервал в секундах (1000 это 1 сек.)
				</script>
			</span>
			</span>
			</td>
			</tr>
		</table>
	</div>
	<?php
	}
}
?>