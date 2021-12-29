<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>задача №<?php echo $_GET['showFullTask']; ?></title>
		<link rel="stylesheet" type="text/css" href=""></link>
			<style type="text/css">
				html, body { height: 100%; margin: 0; }
				body {
   				background: -webkit-linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent);
   				background: -moz-linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent);
   				background: -o-linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent);
   				background: -ms-linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent);
					background: linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent); }
			</style>	
	</head>
	<body>
<?php

						/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		скрипт вывода на страницу всей информации по выбранной задаче	*/
						/*																v.0.1 31.01.2014	 	*/
						/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));

//подключаем config
require_once ($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/config/config.php");

session_start();

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

//объект для подключения к БД
$DBO = new DBOlink();

//объект БД GeoIP
$GeoIP = new GeoIP();

//объект для чтения файла setup_site.xml
$ReadXMLSetup = new ReadXMLSetup;

//показывать в новом окне всю имеющуюся информацию по поставленной задаче 	
if(isset($_GET['showFullTask']) && !empty($_GET['showFullTask']))
	{
	$numTask = (int) $_GET['showFullTask'];

		?>
<!-- основная цветовая подложка -->
		<div style="position: relative; top: 10px; left: 10px; z-index: 10; width: 490px; display: table; vertical-align: middle;	border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">	
			<br>				
			<span style="position: absolute; left: 130px; font-weight: bold; font-size: 16px; font-family: 'Times New Roman', serif; letter-spacing: 2px; border-radius: 3px; color: #0000CC;">
				информация по задаче № <?php echo $numTask; ?>			
			</span><br><br>
			<?php
			$array_showInformationOnTableBasic = AllInformationForTask::showInformationOnTableBasic($DBO, $numTask);
			$array_showInformationOnTableadditional = AllInformationForTask::showInformationOnTableadditional($DBO, $numTask);	 
			?>
			<table border="0" width="480px" align="center">
				<tr>
<!-- дата постановки задачи -->
					<th colspan="3" style=" text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
					дата постановки задачи
					</th>
				</tr>
				<tr>
					<td colspan="3" style="text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: ;">
					<?php
					echo substr($array_showInformationOnTableBasic['task_date_time'], -8, 8);
					echo "<br>".ConversionData::showDateConvert(strtotime($array_showInformationOnTableBasic['task_date_time']));
					if(($array_showInformationOnTableBasic['task_criticality'] == 30) 
					&& ((strtotime($array_showInformationOnTableBasic['task_date_time']) + TIME_TASK_EXPRESS) < time()))
						{
					 	?>
<!-- информационное сообщение о просроченной задаче -->
					</td>
				</tr>
				<tr>
					<th colspan="3" style=" text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
					текущее время
					</th>
				</tr>
				<tr>
					<td colspan="3" style="text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: ;">					
					<?php					
					echo date('H:i:s', time());
					echo "<br>".ConversionData::showDateConvert(time())."<br>";				 	
					?>				 	
				 	<span style="font-size: 14px; font-family: 'Times New Roman', serif; color: #FF0000;">срочная задача выполняется более суток</span>
				 	<?php
						}					
					?>
					</td>							
				</tr>
				<tr>
<!-- критичность задачи -->
					<th colspan="3" style=" text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
					критичность задачи
					</th>
				</tr>
				<tr>
					<td colspan="3" style="text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: ;">
					<?php
					echo TaskProcessMajor::showCriticality($array_showInformationOnTableBasic['task_criticality']);
					?>
					</td>							
				</tr>
				<tr>
<!-- информация об исполнителе -->
					<th style=" text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
					исполнитель
					</th>
					<th style=" text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
					ход выполнения<br>задачи
					</th>
					<th style=" text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
					время изменения статуса выполнения
					</th>
				</tr>
				<?php
				$num = count($array_showInformationOnTableadditional['task_login_addressee']);
				for($i = 0; $i < $num; $i++) 
					{				
					echo "<tr bgcolor=".color().">";				
				?>
					<td style="text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: ;">
					<?php
					echo $ReadXMLSetup->usernameFIO($array_showInformationOnTableadditional['task_login_addressee'][$i]);
					?>
					</td>
					<td style="text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: ;">
					<?php	
					if($array_showInformationOnTableadditional['task_progress'][$i] == 12)
						{
						?><img src="/<?= $array_directory[1] ?>/img/check.png">&nbsp;<?php echo TaskProcessMajor::showProgress($array_showInformationOnTableadditional['task_progress'][$i]);	
						}
					else 
						{
						echo TaskProcessMajor::showProgress($array_showInformationOnTableadditional['task_progress'][$i]);
						} 					
					//echo TaskProcessMajor::showProgress($array_showInformationOnTableadditional['task_progress'][$i]);
					?>
					</td>
					<td style="text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: ;">
					<?php
					echo substr($array_showInformationOnTableadditional['task_date_time_change'][$i], -8, 8);
					echo "<br>".ConversionData::showDateConvert(strtotime($array_showInformationOnTableadditional['task_date_time_change'][$i]));
					?>
					</td>														
				</tr>
				<?php
					}
				?>		
				<tr>
<!-- задача -->
					<th colspan="3" style=" text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
					задача
					</th>
				</tr>
				<tr>
					<td colspan="3" style="text-align: center; width: 480px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFE4E1;">
					<?php
					//проверяем есть ли в строке пробел, если нет режем строку на подстроки
					if(!strpbrk($array_showInformationOnTableBasic['task_show'], " ")) 
						{
						for($i = 0; $i < abs(strlen($array_showInformationOnTableBasic['task_show']) / 40); $i++) 
							{
							echo substr($array_showInformationOnTableBasic['task_show'], ($i * 40), 40)."\n";
							}
						}
					else 
						{
						echo $array_showInformationOnTableBasic['task_show'];
						}
					?>
					</td>							
				</tr>
<!-- пояснение исполнителя -->
				<?php
				//проверяем есть ли пояснения от исполнителя
				$array_shInfOnTadlAdditional = array_unique($array_showInformationOnTableadditional['task_message_addressee']);
				if((count($array_shInfOnTadlAdditional) >= 1) && ($array_shInfOnTadlAdditional[0] != null)) 
					{
					?>
					<tr>
					<th colspan="3" style=" text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
					пояснение исполнителя
					</th>
					<?php
					for($i = 0; $i < count($array_showInformationOnTableadditional['task_message_addressee']); $i++) 
						{
						if($array_showInformationOnTableadditional['task_message_addressee'][$i] != null) 
							{
							?>
							</tr>
								<tr>
								<td style=" text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: <?php echo color(); ?>;">
								<?= $ReadXMLSetup->giveUserNameAndSurname($array_showInformationOnTableadditional['task_login_addressee'][$i]);?>
								</td>
								<td colspan="2" style="text-align: center; width: 160px; font-size: 14px; font-family: 'Times New Roman', serif; background: <?php echo color(); ?>;">
								<?= $array_showInformationOnTableadditional['task_message_addressee'][$i];?>
								</td>							
							</tr>
							<?php
							}
						}
					}				
				?>
			</table><br>
		</div><br>
		<?php

	}
//закрываем соединения с БД
$DBO->onConnectionDB();
?>
	</body>
</html>