<?php

							/*--------------------------------------*/
							/*  класс простейшего чата для общения	*/ 
							/*	аналитиков и оперативных дежурных 	*/
							/* 	 				  v.0.2 21.01.2015  */
							/*--------------------------------------*/

class SimplyChat
{
//объект доступа к файлу setupe_site.xml
private $ReadXMLSetup;
//объект доступа к БД
private $DBO;
//логин авторизованного пользователя
private $userLogin;

//конструктор формы ввода сообщения
public function __construct($userLogin) 
	{
	//объект для чтения файла setup_site.xml
	$this->ReadXMLSetup = new ReadXMLSetup();
	
	//объект доступа к БД
	$this->DBO = new DBOlink();
	
	//логин авторизованного пользователя
	$this->userLogin = $userLogin;
	?>
	<style>
	/* шрифт текста пояснения  */			
		.textHelp {
			font-size: 14px;
			font-family: Verdana, serif; }
	</style>
	
<!-- проверка заполнения формы -->			
	<script type="text/javascript" >
	//функция проверяющая ввод даты
	function validateForm()
		{
		var loginName = document.allForms.loginName.value;
		var message = document.allForms.message.value;
		//проверяем заполнение поля для сообщений		
		if( message.length == 0){
			document.getElementById('messageDate').innerHTML = "нельзя отправлять пустое сообщение";
			return false;
			}
		//проверяем выбран ли получатель		
		if(loginName.length == 0){
			document.getElementById('messageDate').innerHTML = "необходимо выбрать получателя";
			return false;
			}
		}
	</script>	

	<div style="width: 960px; height: 65px;">	
	<form name="allForms" method="POST" onsubmit="return validateForm()" action="">
<!-- ввод сообщения -->
		<div style="position: relative; top: 15px; left: 20px; width: 610px;">
			<textarea name="message" style="width: 610px; height: 18px;" placeholder="сообщение" title="введите сообщение которое желаете отправить"></textarea>
		</div>
<!-- выпадающий список выбора получателя сообщения -->	
		<div style="position: relative; top: -13px; left: 650px; width: 170px;">
			<?php ListBox::listUserNameForSimleChat(); ?>
		</div>
<!-- кнопка "отправить" -->
		<div style="position: relative; top: -36px; left: 830px; width: 110px;">
			<input type="submit" name="send" value="отправить" style="width: 110px; height: 23px;">
		</div>

<!-- информационное сообщение -->
		<div style="text-align: center; width: 960px;">
			<span style="font-size: 10px;	font-family: Verdana, serif; position: relative; top: -35px; color: #CD0000;" id="messageDate"></span>
		</div>	
	</form>
	</div>
	<?php
	}

//добавление сообщений пользователей
public function sendUserMessage() 
	{
	//проверяем наличие переменных $_POST['loginName'] и $_POST['message']
	if(!isset($_POST['loginName']) || !isset($_POST['message']) || empty($_POST['loginName']) || empty($_POST['message'])){
		echo ShowMessage::showInformationError("необходимо заполнить все поля");
		return false;
		}
	$login_addressee = $_POST['loginName'];
	$message = ExactilyUserData::takeStringAll($_POST['message']);		
	try{
/*
date_time - дата и время размещения сообщения											  								  
login_source - логин разместившего сообщение
login_addressee - логин или логины получателей сообщения
message TEXT - текст сообщения
who_reade TEXT - логин человека просмотревшего сообщение которое ему было адресовано 	
*/						
		$query = $this->DBO->connectionDB()->prepare("INSERT `simply_chat` 
																  (`date_time`,
																	`login_source`,
																	`login_addressee`,
																	`message`) 
																	VALUE 
																  ('".date('Y-m-d H:i:s', time())."',
																	'".$this->userLogin."',
																	:login_addressee,
																	:message)");
		$query->execute(array(':login_addressee' => $login_addressee, 'message' => $message));
		?>

<!-- скрипт для предотвращении постоянного добавления одной и той же информации -->		
		<script type="text/javascript" >
			window.location.href = window.location.pathname;
		</script>

		<?php
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//вывод сообщений
public function showUserMessage(array $array_color_message_chat, $simply_chat_time_delete) 
	{
	//запускаем удаление старых сообщений в таблице simply_chat
	//второй параметр	позволяет отключить удаление
	$this->deleteUserMessage($simply_chat_time_delete, true);
	
	try{
		//проверяем пуста ли таблица simply_chat
		if($this->DBO->connectionDB()->query("SELECT COUNT(*) AS num FROM `simply_chat`")->fetch(PDO::FETCH_OBJ)->num == 0){
			echo "<div style='text-align: center;'><span style='font-size: 16px; font-family: 'Times New Roman', serif; letter-spacing: 2px;'>сообщений нет</span></div>";
			} else {
			//получаем логин авторизованного пользователя
			$login_user_authorization = $_SESSION['userSessid']['userLogin'];
			//получаем индекс группы авторизованного пользователя
			$id_user_authorization = $_SESSION['userSessid']['userId'];
			//массив для определения группы пользователя
			$array_user_id_group = array('20' => 'worker', '30' => 'analyst');
			$array_group = array('all' => 'всем пользователям', 'analyst' => 'всем аналитикам', 'worker' => 'всем дежурным');	 
		
			$query = $this->DBO->connectionDB()->query("SELECT `date_time`, `login_source`, `login_addressee`, `message`, `who_reade` FROM `simply_chat` ORDER BY `date_time` DESC");	
			$i = 1;

			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
				$show_message = false;
				//показывать все сообщения отправленные авторизованным пользователем
				if($row['login_source'] == $login_user_authorization){
					foreach($array_group as $key => $name){
						($key == $row['login_addressee']) ? $user_addressee = $name : false;
						}
					if(!isset($user_addressee)){
						$user_addressee = $this->ReadXMLSetup->giveUserNameAndSurname($row['login_addressee']);
						}
					$show_message = true;
					}
				//показывать сообщение адресованное ВСЕМ или группе к которой относится пользователь
				elseif(($row['login_addressee'] == 'all') || ($row['login_addressee'] == $array_user_id_group[$id_user_authorization])){
					$user_addressee = $array_group[$row['login_addressee']];
					$show_message = true;
					}
				//показывать сообщение если оно было адресовано авторизованному пользователю
				elseif($row['login_addressee'] == $login_user_authorization){
					$user_addressee = $this->ReadXMLSetup->giveUserNameAndSurname($row['login_addressee']);
					$show_message = true;
					}

				//получаем ИМЯ и ФАМИЛИЮ источника сообщения
				$user_source = $this->ReadXMLSetup->giveUserNameAndSurname($row['login_source']);
				//получаем "человеческую дату"
				$date = ConversionData::showDateConvertStr($row['date_time']);
				//получаем время
				$time = substr($row['date_time'], -8);

				//цвет рамки по умолчанию (если для пользователя не задан цвет в конфигурационном файле)
				$color_shadow = "#CDCDCD";	
				//получаем цвет рамки пользователя отправившего сообщение
				foreach($array_color_message_chat as $login_n => $color){
					$login_n == $row['login_source'] ? $color_shadow = $color : false;
					}
				//выставляем интервал от левого края
				($login_user_authorization == $row['login_source']) ? $position_left = "40px": $position_left = "100px";
				$i++;
				//переменная определяющая это старое или новое сообщение
				$new_message = "";
				//определяем доступно ли сообщение пользователю
				if($show_message) {
					//проверяем читал ли пользователь сообщение
					//если в поле who_reade уже есть записи
					if($row['who_reade']){
						$array_who_reade = explode(" ",$row['who_reade']);
						$user_reade = false;
						foreach($array_who_reade as $name){
							if($login_user_authorization == $name) {
								$user_reade = true;
								break;
								}
							}
						if(!$user_reade){
							$this->DBO->connectionDB()->query("UPDATE `simply_chat` SET `who_reade`='".$row['who_reade']." ".$login_user_authorization."' WHERE `date_time`='".$row['date_time']."'");	
							//переменная определяющая это старое или новое сообщение
							$new_message = "new";						
							}
						} else {
						//если сообщение вообще ни кто не читал
						$this->DBO->connectionDB()->query("UPDATE `simply_chat` SET `who_reade`='".$login_user_authorization."' WHERE `date_time`='".$row['date_time']."'");	
						//переменная определяющая это старое или новое сообщение
						$new_message = "new";											
						}	
					unset($array_who_reade);	
					echo "<div style='position: relative; top: 0px; left: {$position_left}; width: 820px; border-radius: 6px; box-shadow: inset 0 0 10px 0px {$color_shadow};'>";
					?>
						<span style="position: relative; top: 10px; left: 30px;">
<!-- источник сообщения -->
							<span style="font-size: 12px; font-family: 'Times New Roman', serif; font-weight: 500; text-decoration: underline;"><?php echo $user_source; ?></span>
							<span style="font-size: 12px; font-family: 'Times New Roman', serif;">&nbsp; => &nbsp;</span>
<!-- получатель сообщения -->
							<span style="font-size: 12px; font-family: 'Times New Roman', serif; font-weight: bold;"><?php echo $user_addressee; ?></span>&nbsp;&nbsp;&nbsp;
<!-- дата и время -->
							<span style="font-size: 12px; font-family: 'Times New Roman', serif;">написал в &nbsp;&nbsp;<?php echo "<span style='letter-spacing: 2px;'>".$time." ".$date."</span>"; ?></span>
<!-- информация о "свежести" сообщения -->
							<span style="position: absolute; left: 720px; font-size: 12px; font-family: 'Times New Roman', serif; letter-spacing: 2px; color: red;"><?php echo ($new_message == "new") ? "НОВОЕ": ""; ?></span>					
						</span>
<!-- текст сообщения -->
						<div style="position: relative; top: 15px; left: 20px; padding: 10px; border-radius: 3px; width: 760px; border-style: solid; border-width: 1px; background: #E8E8E8;">
							<span style="font-size: 14px; font-family: 'Times New Roman', serif;"><?php echo $row['message']; ?></span>
						</div><br>
						<div style="position: relative; top: 0px; left: 10px; height 10px; font-size: 12px; font-family: 'Times New Roman', serif;">&nbsp;</div>
					</div><br>
					<?php
					}
				}
			}		
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
	
//удаление старых сообщений пользователей
//метод принимает два значения 1. кол-во секунд старше которых сообщение можно удалять 2. вкл./выкл. удаление (true/false)
private function deleteUserMessage($delete_time, $delete = true) 
	{
	try{
		//проверяем активировать функцию или нет
		if(!$delete){
			return false;
			}
		$old_time = date("Y-m-d H:i:s", (time() - $delete_time));
		$this->DBO->connectionDB()->query("DELETE FROM `simply_chat` WHERE `date_time`<='".$old_time."'");	
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

}
?>