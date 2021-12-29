<?php

						/*+++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		страница постановки задачи руководством		*/
						/*										v.0.1 04.02.2014		*/
						/*+++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 

//подключаем основную, для руководства, страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/major/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

//создаем новый объект класса работы с таблицей задач
$TaskProcessMajor	= new TaskProcessMajor(new DBOlink, new ReadXMLSetup);

//автоматически проверяем есть ли устаревшие задачи, если есть удаляем
$TaskProcessMajor->autoDeleteOldTask();
?>
<!-- проверка заполнения формы -->
	<script type="text/javascript">
		function validateForm()
			{
			var loginName = document.taskForms.loginName.value;
			var criticalityTask = document.taskForms.criticalityTask.value;
			var task = document.taskForms.task.value;
			if((loginName.length == 0) || (criticalityTask.length == 0) || (task.length == 0))
				{
				document.getElementById('message').innerHTML = "заполните все поля";
				return false;
				}
			}
	</script>

<!-- поле вывода (слева) -->
		<div style="position: relative; top: 0px; float: left; width: 250px; min-height: 350px; border-radius: 6px; background: #F0FFFF; box-shadow: inset 0 0 10px 0px #B7DCF7;">	
<!-- блок краткой информации о поставленных задачах -->
			<div style="position: relative; top: 5px; left: 10px; width: 330px;">
				<span style="position: relative; top: 5px; left: 10px; font-weight: bold; font-size: 12px; font-family: Verdana, serif; letter-spacing: 2px; color: #000;">
					задач:
				</span><br>
				<span style="position: relative; top: 5px; left: 10px; font-size: 12px; font-family: Palatino, 'Times New Roman', serif; letter-spacing: 1px; color: #000;">
					ожидающих выполнение -
				</span> 
				<span style="position: relative; top: 5px; left: 15px; font-size: 16px; font-family: Palatino, 'Times New Roman', serif; color: #3300FF;">				
				<?php echo TopicalityShortInformation::showAllTask($DBO); ?>
				</span>
				<br>
				<span style="position: relative; top: 5px; left: 10px; font-size: 12px; font-family: Palatino, 'Times New Roman', serif; letter-spacing: 1px; color: #000;">
					в процессе выполнения - 
				</span>
				<span style="position: relative; top: 5px; left: 18px; font-size: 16px; font-family: Palatino, 'Times New Roman', serif; color: #3300FF;">				
				<?php echo TopicalityShortInformation::showProcessTask($DBO); ?>
				</span>
			</div>
<!-- блок формы для постановки задач -->
			<div style="position: relative; top: 0px; left: 10px; width: 230px;">
			<form name="taskForms" method="POST" action="" onsubmit="return validateForm()">
<!-- выбор исполнителя -->
				<div style="position: relative; top: 25px; left: 10px; width: 180px; height: 23px;">
					<select name="loginName" style="width: 180px; height: 23px;">
						<option value="">исполнитель</option>
						<?php
						//переменные содержат логины определенных групп пользователей 
						$messageAnalyst = $messageWorker = null;
						//создаем элемент списка с логинами для групп
						$array_group = array('all' => '- все пользователи', '30' => '- все аналитики', '20' => '- все дежурные');
						foreach($array_group as $key => $value){
							echo "<option value='".$key."'>$value</option>";	
							}				
						//создаем массив для определения в дальнейшем какой группе пользователей было адресовано сообщение
						$array_user_name = $ReadXMLSetup->getArrayUserName();
						natcasesort($array_user_name);				
						//получаем массив содержащий логин пользователя и его Ф.И.О.
						foreach($array_user_name as $login => $user_name){
							if(($user_name != "") && ($login != $_SESSION['userSessid']['userLogin'])){
								list($surname, $name,) = explode(" ", $user_name);		
								echo "<option value='$login'>".$surname." ".$name."</option>";
								}
							}
						?>
					</select>				
				</div>
<!-- выбор критичности задачи -->
				<?php
				$array_criticality = array('10' => 'информационное сообщение', 
										   '20' => 'повседневная задача', 
										   '30' => 'срочная задача');
				?>
				<div style="position: relative; top: 35px; left: 10px; width: 180px; height: 23px;">
					<select name="criticalityTask" style="width: 180px; height: 23px;">
						<option value="">критичность</option>
						<?php
						foreach($array_criticality as $key => $value){
							echo "<option value='".$key."'>{$value}</option>";
							}
						?>
					</select>
				</div>
<!-- ввод текста задачи -->
				<div style="position: relative; top: 45px; left: 10px; width: 205px; height: 150px;">
					<textarea type="text" name="task" placeholder="постановка задачи" title="" style="width: 205px; height: 150px;"></textarea>
				</div>
<!-- кнопка "сохранить" -->
				<div style="position: relative; top: 55px; left: 8px; width: 140px;">
					<input type="submit" name="safe" value="сохранить">
				</div>
<!-- вывод информационного сообщения о том что не все поля заполнены -->
				<span id="message" style="position: relative; top: 35px; left: 110px; font-size: 12px;	font-family: 'Times New Roman', serif; color: #CD0000;"></span>						
			</form>
			</div>
		</div>
		
<!-- поле вывода основной информации по центру -->
		<div style="position: relative; top: 0px; float: left; margin-left: 5px; width: 705px; min-height: 350px; border-radius: 3px; background: #F0FFFF; box-shadow: inset 0 0 10px 0px #B7DCF7;">
			<?php
			//добавляем данные
			if(isset($_POST['safe'])){
				$TaskProcessMajor->setNewTask();
				}
			//удаляем данные
			if(isset($_POST['deleteTask'])){
				$TaskProcessMajor->deleteTask($_POST['deleteTask']);
				}				
			//вывод информации о поставленных задачах
			$TaskProcessMajor->showTask();
			?>
		</div>
		<br><br><br><br>

<?php
include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>	