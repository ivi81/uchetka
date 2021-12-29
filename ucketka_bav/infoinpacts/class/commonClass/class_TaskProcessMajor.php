<?php

							/*-------------------------------------------------------------------------------*/
							/*  класс постановки, вывода на экран и удаления из БД задач (для руководства)	*/
							/* 	 																			v.0.1 30.01.2014  */
							/*-------------------------------------------------------------------------------*/

class TaskProcessMajor
{
//доступ к файлу setupe_site.xml
protected $ReadXMLSetup;
//доступ к БД 
protected $DBO; 
//логин авторизованного пользователя
protected $userLogin;
//корневая директория сайта
protected $directory;

//определяем есть ли поставленные задачи
public function __construct(DBOlink $DBO, ReadXMLSetup $ReadXMLSetup) 
	{
	//объект для чтения файла setup_site.xml
	$this->ReadXMLSetup = $ReadXMLSetup;
	
	//объект доступа к БД
	$this->DBO = $DBO;
	//логин авторизованного пользователя
	$this->userLogin = $_SESSION['userSessid']['userLogin'];
	
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directory = $array_directory[1];
	}

//функция вывода информации о поставленной задачи
public function showTask($isEmptyTable = false) 
	{
	try{
		//проверяем пуста ли таблица problem_table_basic
		if($this->DBO->connectionDB()->query("SELECT COUNT(*) AS NUM FROM `problem_table_basic`")->fetch(PDO::FETCH_OBJ)->NUM == 0){
			$isEmptyTable = true;
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	echo $isEmptyTable ? $this->showMessage(): $this->shortInformationAboutTask();
	}

//функция вывода сообщения (руководство)
private function showMessage() 
	{
	?>
	<span style="position: relative; top: 70px; left: 100px; font-size: 16px; font-family: 'Times New Roman', serif; color: #000;">
		в настоящий момент нет ни одной поставленной задачи
	</span>
	<?php		
	}
	
//функция вывода краткой информации о поставленных задачах
private function shortInformationAboutTask() 
	{
	try{
		$query = $this->DBO->connectionDB()->query("SELECT A.task_id, `task_date_time`, `task_login_addressee`, `task_criticality`, `task_progress`, `task_message_addressee`
													FROM `problem_table_basic` A INNER JOIN `problem_table_additional` B 
													ON A.task_id = B.task_id ORDER BY `task_criticality` DESC, `task_date_time` DESC");
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	?>
	<span style="position: relative; top: 5px; left: 280px; font-size: 14px; font-family: Verdana, serif; letter-spacing: 2px; color: #000;">
		поставленные задачи			
	</span>
			
	<div style="position: relative; top: 10px; left: 10px;">
		<div style="border-width: 1px; border-style: solid; width: 685px; border-color: #B7DCF7;">				
		<table border="0" width="685px" cellpadding="1">
			<?php echo "<tr ".COLOR_HEADER.">"; ?>
			<th style="width: 75px; font-size: 12px; font-family: 'Times New Roman', serif; color: #000;"></th>
			<th style="width: 180px; font-size: 12px; font-family: 'Times New Roman', serif; color: #000;">дата постановки задачи</th>
			<th style="width: 140px; font-size: 12px; font-family: 'Times New Roman', serif; color: #000;">Ф.И.О. исполнителя</th>
			<th style="width: 130px; font-size: 12px; font-family: 'Times New Roman', serif; color: #000;">критичность задачи</th>
			<th style="width: 150px; font-size: 12px; font-family: 'Times New Roman', serif; color: #000;">ход выполнения задачи</th>
			</tr>
			<?php
			while($row = $query->fetch(PDO::FETCH_ASSOC)){
				echo "<tr bgcolor=".color().">";				
				?>
<!-- порядковый номер задачи -->
					<td style="text-align: center; font-size: 14px; font-family: 'Times New Roman', serif;">
					<form name="deleteTask" action="" method="POST">
	<!-- просмотреть всю информацию выбранной задаче по задаче (JavaScript) -->
						<a href="showAllInformationForTask.php?showFullTask=<?php echo $row['task_id'] ?>" target="_blank" onclick="popupWin = window.open(this.href, 'displayWindow', 'location,width=510,height=500,status=no,toolbar=no,menubar=no'); popupWin.focus(); return false;"><img src="/<?= $this->directory ?>/img/eye.png" title="подробности"></img></a>
	<!-- есть информация от исполнителя -->
						<?php	
						if($row['task_message_addressee']){
							?><img src="/<?= $this->directory ?>/img/Orb info.png"><?php 
							}
							echo "&nbsp;";	
						?>
	<!-- удалить выбранную задачу -->
						<input type="image" src="/<?= $this->directory ?>/img/delete_16.png" title="удалить задачу">				
						<input type="hidden" name="deleteTask" value="<?php echo $row['task_id'].":".$row['task_login_addressee']; ?>">					
					</form>					
					</td>
<!-- дата -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
						<?php	echo substr($row['task_date_time'], 11, 5)."&nbsp;&nbsp;&nbsp;".ConversionData::showDateConvert(strtotime(substr($row['task_date_time'], 0, 10))); ?>
					</td>
<!-- Ф.И.О. исполнителя -->
					<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
						<?php	echo $this->ReadXMLSetup->giveUserNameAndSurname($row['task_login_addressee']); ?>
					</td>
<!-- критичность задачи -->
					<td style="text-align: center; font-size: 11px; font-family: 'Times New Roman', serif;">
						<?php
						$date_start = strtotime($row['task_date_time']) + TIME_TASK_EXPRESS;
						if($row['task_criticality'] == 30){
							if($date_start < time() && $row['task_progress'] != 12){
						 		echo self::showCriticality($row['task_criticality']);
						 		?> &nbsp; <img src="/<?= $this->directory ?>/img/important_1.png" ><?php
						 		} else {
						 		echo self::showCriticality($row['task_criticality']);
						 		}
						 	} else {
						 	echo self::showCriticality($row['task_criticality']);
						 	} 
						 ?>
					</td>
<!-- прогресс -->
					<td style="text-align: center; font-size: 11px; font-family: 'Times New Roman', serif;">
						<?php 
						if($row['task_progress'] == 12){
							?><img src="/<?= $this->directory ?>/img/check.png">&nbsp;<?php echo self::showProgress($row['task_progress']);	
							} else {
							echo self::showProgress($row['task_progress']);
							} 
						?>
					</td>
					</tr>
				<?php
				}
			?>
		</table>
		</div>
<!-- информация по пиктограммам -->
		<div style="position: relative; top: 5px; left: 10px;">		
<!-- глаз -->
			<div style="position: relative; left: 5px; display: inline-block;"><img src="/<?= $this->directory ?>/img/eye.png">
				<span style="font-size: 11px; font-family: 'Times New Roman', serif;"> 
					просмотреть всю информацию
				</span>
			</div>
<!-- красный крестик -->
			<div style="position: relative; left: 15px; display: inline-block;"><img src="/<?= $this->directory ?>/img/delete_16.png">
				<span style="font-size: 11px; font-family: 'Times New Roman', serif;"> 
					удалить задачу
				</span>
			</div>
<!-- синий кружок -->
			<div style="position: relative; left: 25px; display: inline-block;"><img src="/<?= $this->directory ?>/img/Orb info.png">
				<span style="font-size: 11px; font-family: 'Times New Roman', serif;"> 
					пояснение исполнителя
				</span>
			</div>
<!--  -->
			<div style="position: relative; left: 30px; display: inline-block;"><img src="/<?= $this->directory ?>/img/important_1.png" >		
				<span style="font-size: 11px; font-family: 'Times New Roman', serif;"> 
					просроченная срочная задача
				</span>
			</div>
		</div>
		<br><br>
	</div>
	<?php
	}

//функция преобразования числового кода ПРОГРЕССА в текстовый вид
static function showProgress($progressId)
	{
	switch($progressId){
		case 10:
			return "<span style='color: #3300FF;'>исполнителем не просматривалась</span>";		
		break;
		case 11:
			return "<span style='text-decoration: underline;'>в процессе выполнения</span>";
		break;
		case 12:
			return "выполнена";
		default:
			return "<span style='color: #FF0000;'>код прогресса не определен</span>";
		}
	}
	
//функция преобразования числового кода КРИТИЧНОСТИ в текстовый вид
static function showCriticality($criticality)
	{
	switch($criticality){
		case 10:
			return "информационное сообщение";		
		break;
		case 20:
			return "повседневная задача";
		break;
		case 30:
			return "<span style='color: #FF0000;'>срочная задача</span>";
		default:
			return "<span style='color: #FF0000;'>код задачи не определен</span>";
		}
	}

//функция добавления новой задачи
public function setNewTask() 
	{
	//проверяем введенные пользователем данные
	!empty($_POST['loginName']) ? $login = ExactilyUserData::takeStringAll($_POST['loginName']) : $login = false;
	!empty($_POST['criticalityTask']) ? $criticalityTask = ExactilyUserData::takeIntager($_POST['criticalityTask']) : $criticalityTask = false;
	!empty($_POST['task']) ? $task = ExactilyUserData::takeStringAll($_POST['task']) : $task = false;
	echo ($login == false || $criticalityTask == false || $task == false) ? MessageErrors::showInformationError("переданы не все параметры") : "";	
	try{
		//problem_table_basic
		/*
		task_id - порядковый номер задачи
		task_date_time - дата и время постановки задачи											  								  
		task_criticality - критичность задачи
		task_show - задача
		*/	
		$query_basic = $this->DBO->connectionDB()->prepare("INSERT `problem_table_basic` (
															`task_date_time`,
															`login_source`,
															`task_criticality`,
															`task_show`) 
															VALUE (
															'".date("Y-m-d H:i:s", time())."',
															'".$_SESSION['userSessid']['userLogin']."',
															:task_criticality,
															:task_show)");
		$query_basic->execute(array(':task_criticality' => $criticalityTask, ':task_show' => $task));
		$last_id = $this->DBO->connectionDB()->query("SELECT MAX(task_id) AS `MAX_ID` FROM `problem_table_basic`")->fetch(PDO::FETCH_OBJ)->MAX_ID;
		echo $last_id;		
		//problem_table_additional
		/*
		task_id - порядковый номер задачи
		task_date_time_change - время модификации хода выполнения задачи
		task_login_addressee - логин исполнителя
		task_progress - ход выполнения задачи 
			10 - задача поставлена но не просмотрена
			11 - задача просмотрена исполнителем и выполняется
			12 - задача выполнена
		task_message_addressee - пояснение исполнителя
		*/																
		$query_additional = $this->DBO->connectionDB()->prepare("INSERT `problem_table_additional` (
																 `task_id`,
																 `task_date_time_change`,
																 `task_login_addressee`,
																 `task_progress`) 
																 VALUE (
																  ".$last_id.",
																  '".date("Y-m-d H:i:s", time())."',
																  :login,
																  '10')");
		if($login == 'all'){
			//меняем местами значение массива и исключаем из массива логин источника сообщения
			foreach(array_flip($this->ReadXMLSetup->getArrayUserName()) as $key){
				if($key != $_SESSION['userSessid']['userLogin']){
					$array[] = $key;
					}
				}
			}
		elseif($login == '20' || $login == '30'){
			$array = $this->ReadXMLSetup->getArrayUserNumGroup($login);
			} else {
			$array = array('name' => $login);	
			}
		foreach($array as $key){
			$query_additional->execute(array(':login' => $key));
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	?>
	<!-- скрипт для предотвращении постоянного добавления одной и той же информации -->		
		<script type="text/javascript" >
			window.location.href = window.location.pathname;
		</script> 
	<?php
	}

//функция удаления выбранной задачи
public function deleteTask($taskIdLogin) 
	{
	//проверяем введенные пользователем данные
	list($task_id, $login) = explode(":", $taskIdLogin);
	$task_id = ExactilyUserData::takeIntager($task_id);
	$login = ExactilyUserData::takeStringAll($login);
	try{
		$query_count = $this->DBO->connectionDB()->query("SELECT COUNT(task_id) AS NUM FROM `problem_table_additional` WHERE `task_id`='".$task_id."' GROUP BY `task_id`");
		//проверяем количество исполнителей одной задачи
		if($query_count->fetch(PDO::FETCH_OBJ)->NUM > 1){
			//если исполнителей у одной задачи больше 1, удаляем задачу для конкретного пользователя
			$query = $this->DBO->connectionDB()->prepare("DELETE FROM `problem_table_additional` WHERE `task_id`='".$task_id."' AND `task_login_addressee`=:login");			
			$query->execute(array(':login' => $login));			
			} else {
			//если исполнитель 1, удаляем задачу из всех таблиц
			$this->DBO->connectionDB()->query("DELETE `problem_table_basic`, `problem_table_additional` FROM `problem_table_basic`
														  LEFT JOIN `problem_table_additional` ON problem_table_basic.task_id = problem_table_additional.task_id 
														  WHERE problem_table_additional.task_id='".$task_id."'");
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
		?>
	<!-- скрипт для предотвращении удаления одной и той же информации -->		
		<script type="text/javascript" >
			window.location.href = window.location.pathname;
		</script> 
	<?php
	}
	
//функция автоматического удаления старых задач
public function autoDeleteOldTask() 
	{
	try{
		$query = $this->DBO->connectionDB()->query("DELETE `problem_table_basic`, `problem_table_additional` FROM `problem_table_basic`, `problem_table_additional` WHERE problem_table_basic.task_id=problem_table_additional.task_id
																  AND `task_date_time`<'".date('Y-m-d H:i:s', (time() - OLD_TASK))."' AND `task_progress`='12'");
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}		
	}
}

?>