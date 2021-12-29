<?php

						/*+++++++++++++++++++++++++++++++++++++++++++*/
						/*		страница вывода поставленной задачи 	*/
						/*								v.0.1 05.02.2014	 	*/
						/*+++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 

//подключаем основную, для дежурного страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/analyst/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

//объект класса задач
$TaskProcessWorker = new TaskProcessWorker(new DBOlink, new ReadXMLSetup);

//при посещении страницы меняем ход выполнения всех задач (поставленных пользователю) с "задача поставлена но не просмотрена" на "задача просмотрена исполнителем и выполняется"
$TaskProcessWorker->autoChangeTaskProgress();

//автоматически проверяем есть ли устаревшие задачи, если есть удаляем
$TaskProcessWorker->autoDeleteOldTask();
?>
<!-- вывод поставленной задачи -->
	<div style="position: relative; top: 0px; float: left; width: 960px; min-height: 680px; border-radius: 6px; background: #F0FFFF; box-shadow: inset 0 0 10px 0px #B7DCF7;">
		<?php
		$array_information_task = $TaskProcessWorker->demandsInformationTask();
		if(count($array_information_task) != 0) 
			{
			?>		
			<form name="addSolveTask" action="" method="POST">
			<div style="position: relative; top: 10px; width: 960px;">
				<span style="position: relative; top: 10px; left: 414px; font-size: 20px; font-family: 'Times New Roman', serif; letter-spacing: 2px;">
					список задач
				</span><br><br>
				<div style="position: relative; left: 30px; border-width: 1px; border-style: solid; width: 900px; border-color: #B7DCF7;">
				<table border="0" width="900" align="center" cellpadding="3" >
				<?php
/*
$array_task[$i]['task_id'] - порядковый номер задачи
$array_task[$i]['task_date_time'] - дата постановки задачи
$array_task[$i]['login_source'] - кем поставлена задача
$array_task[$i]['task_criticality'] - критичность задачи
$array_task[$i]['task_show'] - описание задачи
$array_task[$i]['task_date_time_change'] - дата и время изменения статуса задачи
$array_task[$i]['task_login_addressee'] - логин исполнителя
$array_task[$i]['task_progress'] - прогресс
$array_task[$i]['task_message_addressee'] - пояснение исполнителя
*/
				$add_date_task = null;
				for($i = 0; $i < count($array_information_task); $i++) 
					{
					list($date_task, $time_task) = explode(" ", $array_information_task[$i]['task_date_time']);
					if($date_task != $add_date_task)
						{
				?>
					<tr>
						<th colspan="5" align="center" style="width: 70px; font-size: 14px; font-family: 'Times New Roman', serif; color: #000; background: #FFF;">
						<?= ConversionData::showDateConvertStr($array_information_task[$i]['task_date_time']) ?>						
						</th>
					</tr>
					<?php echo "<tr ".COLOR_HEADER.">"; ?>
						<th style="text-align: center; width: 40px; font-size: 12px; font-family: 'Times New Roman', serif; color: #FFF;"></td>
						<th style="text-align: center; width: 115px; font-size: 12px; font-family: 'Times New Roman', serif; color: #FFF;">задача<br> поставлена в</td>
						<th style="text-align: center; width: 150px; font-size: 12px; font-family: 'Times New Roman', serif; color: #FFF;">важность</td>
						<th style="text-align: center; width: 395px; font-size: 12px; font-family: 'Times New Roman', serif; color: #FFF;">задача</td>
						<th style="text-align: center; width: 200px; font-size: 12px; font-family: 'Times New Roman', serif; color: #FFF;">замечание исполнителя</td>
					</tr>		
					<?php
						$add_date_task = $date_task;
						}					
					echo "<tr bgcolor=".color().">";					
					?>
						<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif; color: #000;">
						<?php
							if($array_information_task[$i]['task_progress'] == 12) 
								{
								?>							
								<img src="/<?= $array_directory[1] ?>/img/check.png" alt="" >
								<?php
								}
							else 
								{
								?>
								<input type="checkbox" name="addSolveTask[]" value="<?= $array_information_task[$i]['task_id'] ?>">								
						<?php } ?>				
						</td>
						<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif; color: #000;"><?= $time_task ?></td>
						<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif; color: #000;"><?= $array_information_task[$i]['task_criticality'] ?></td>
						<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif; color: #000;">
							<?php
							//проверяем есть ли в строке пробел, если нет режем строку на подстроки
							if(!strpbrk($array_information_task[$i]['task_show'], " ")) 
								{
								for($j = 0; $j < abs(strlen($array_information_task[$i]['task_show']) / 40); $j++) 
									{
									echo substr($array_information_task[$i]['task_show'], ($j * 40), 40)."\n";
									}
								}
							else 
								{
								echo $array_information_task[$i]['task_show'];
								}
							?>
						</td>
						<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif; color: #000;">
							<?php
							//вывод строки заполнения только в том случае если задача не выполнена
							if($array_information_task[$i]['task_progress'] != 12) 
								{
								?>
								<textarea name="message[<?= $array_information_task[$i]['task_id'] ?>]" style="width: 180px; height: 18px;" title="замечания, вопросы, предложения"></textarea>
								<?php
								}
							echo $array_information_task[$i]['task_message_addressee'];						
							?>
						</td>	
					</tr>
				<?php
					}
				?>
				</table>
				</div>
			</div>
			<div style="position: relative; top: 20px; left: 30px; width: 80px;">
				<input type="submit" name="safe" value="сохранить">
			</div>
			</form><br><br>
			<?php
			}
		else 
			{
			?>		
			<div style="position: relative; top: 10px; text-align: center;">
				<span style="width: 70px; font-size: 16px; font-family: 'Times New Roman', serif; color: #000;">
					в данный момент новых задач нет			
				</span>
			</div><br>		
			<?php
			}
		if((isset($_POST['addSolveTask']) && !empty($_POST['addSolveTask'])) && (isset($_POST['message']) && !empty($_POST['message'])))
			{
			$array_finally_task = array();
			$addSolveTask = $_POST['addSolveTask'];
			$message = $_POST['message'];
			$count_array = count($addSolveTask);
			$i = 0;
			foreach($addSolveTask as $value)
				{
				$array_finally_task[$i][$value] = $message[$value];
				$i++;
				}
			//сохраняем задачи отмеченные как выполненные, а также пояснения исполнителя
			$TaskProcessWorker->finallyChangeTaskProgress($array_finally_task);
			?>
			<!-- скрипт для предотвращении постоянного добавления одной и той же информации -->		
			<script type="text/javascript" >
				window.location.href = window.location.pathname;
			</script> 	
			<?php
			}
		?>
	</div>

<?php
include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>