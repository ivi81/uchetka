<?php

						/*----------------------------------------------*/
						/*  класс авторизации пользователей и проверки  */
						/*  их учетных данных (на сессиях)				*/
						/* 	 						v.0.31 28.03.2014   */
						/*----------------------------------------------*/

class Authorization
{
public $userId;
public $userLogin;
public $messageError = array();
//основная директория сайта
public $directory;
//объект доступа к файлу setup_site.xml
protected $newReadXML;
//время жизни сессии
protected $lifeTimeSession;
//объект доступа к БД
protected $DBO; 

//проверка учетных данных для ГЛАВНОЙ страницы
public function __construct() 
	{
	//объект доступа к БД
	$this->DBO = new DBOlink();
	//объект доступа к файлу setup_site.xml
	$this->newReadXML = new ReadXMLSetup();
	//основная директория сайта
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directory = $array_directory[1];
	//устанавливаем время жизни сессий в php.ini
	ini_set('session.gc_maxlifetime', 10800);
	ini_set('session.cookie_lifetime', 10800);
	$this->lifeTimeSession = ini_get('session.gc_maxlifetime');
	
	if((isset($_POST['userLogin']) && isset($_POST['userPassword'])) && (!empty($_POST['userLogin']) && !empty($_POST['userPassword']))){
		//---если вводили логин и пароль
		//проверка логина и пароля
		$this->checkLoginPass();
		try{
			//запись информации о сессии в таблицу user_session БД
			$this->loadUserSession();
			} 
		catch(PDOException $e){
			echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
			}
		} else {
		//---если пользователь не ввел логин и пароль
		//и в cookie нет идентификатора сессии
		if(!isset($_SESSION['userSessid']['userLogin'])){
			$this->inputFormAuthorization();	//назад к форме авторизации
			}
		}
	}
	
//получение данных из xml файла с настройками
protected function getDataXML($login) 
	{
	//ищем пользователя по его логину
	if(!$userString = $this->newReadXML->accounts($login)){
		$this->messageError[] = "неверный логин или пароль"; 	
		$this->inputFormAuthorization();	//назад к форме авторизации
		}
	return $userString;
	}

//проверка логина и пароля
private function checkLoginPass() 
	{
	list($userLogin, $userId, $userPass) = explode(":", $this->getDataXML($_POST['userLogin']));		 
	if((addslashes(trim($_POST['userLogin'])) == $userLogin) && (md5(trim($_POST['userPassword'])) != $userPass)){ 
		$this->messageError[] = "неверный логин или пароль"; 	
		$this->inputFormAuthorization();	//назад к форме авторизации
		}
	//регенерация сессии
	session_regenerate_id(true);
	$this->userId = $_SESSION['userSessid']['userId'] = $userId;
	//записываем логин пользователя в сессию и присваеваем его переменной $userLogin
	$this->userLogin = $_SESSION['userSessid']['userLogin'] = $userLogin;
	$_SESSION['userSessid']['sessionStart'] = time();
	$_SESSION['userSessid']['userIP'] = $_SERVER['REMOTE_ADDR'];		
	}
	
//создание таблицы используемой для хранения сессий и добавление или изменение данных о пользователе
private function loadUserSession() 
	{
/*
user_login - логин пользователя
user_id - идентификатор пользователя
session_start - время начала сессии
session_end - время конца сессии
count_visit_user - подсчет количества посещений
authorization - авторизован ли пользователь (yes/no)
user_fio - Ф.И.О. пользователя
*/
	$this->DBO->connectionDB()->query("CREATE TABLE IF NOT EXISTS `user_session` (
									   user_login VARCHAR(15) NOT NULL,
									   user_id TEXT NOT NULL,
									   session_start INT NOT NULL,
									   session_end INT NOT NULL,
									   count_visit_user INT(6) NOT NULL,
									   authorization VARCHAR(5),
									   user_fio TEXT,
									   PRIMARY KEY(user_login)) ENGINE=MyISAM DEFAULT CHARSET=utf8");

	//получаем количество посещений для авторизованного пользователя
	$query_search_user = $this->DBO->connectionDB()->prepare("SELECT `count_visit_user` FROM `user_session` WHERE `user_login`=:user_login");
	$query_search_user->execute(array(':user_login' => $this->userLogin));
	$row_search_user = $count_visit_user = $query_search_user->fetch(PDO::FETCH_OBJ);
		
	if($row_search_user->count_visit_user == 0){
		//---если пользователя с таким именем нет создаем новую запись		
		//создания новой записи
		$query_db = $this->DBO->connectionDB()->prepare("INSERT INTO `user_session` (
																	 `user_login`,
																	 `user_id`, 
																	 `session_start`,
																	 `session_end`, 
																	 `count_visit_user`, 
																	 `authorization`, 
																	 `user_fio`)
																 	 VALUES (
																 	 :user_login,
																 	 '".session_id()."', 
																 	 '".time()."',
																 	 '".(time() + $this->lifeTimeSession)."', 
																 	 '1',
																 	 'yes',
																 	 '".$this->newReadXML->usernameFIO($this->userLogin)."')");
		$query_db->bindParam(':user_login', $this->userLogin);
		$query_db->execute();
		} else {
		//---если пользователь существует обновляем существующую запись
		//обновление существующей записи
		$query_db = $this->DBO->connectionDB()->prepare("UPDATE `user_session` SET
		 														`user_id`='".session_id()."',
																`session_start`='". time() ."',
																`session_end`='".(time() + $this->lifeTimeSession)."', 
																`count_visit_user`='".($count_visit_user->count_visit_user + 1)."',
																`authorization`='yes' WHERE `user_login`=:user_login");
		$query_db->bindParam(':user_login', $this->userLogin);
		$query_db->execute();			
		}		
	}

//выход и изменение статуса пользователя на не авторизован
public function exitUserSession() 
	{
	try{
		//объект доступа к БД
		$DBO = new DBOlink();
		
		//пишем в базу что пользователь не авторизован и время его выхода
		$query = $DBO->connectionDB()->query("UPDATE `user_session` SET `session_end`='".time()."',
													 `authorization`='no' WHERE `user_id`='".session_id()."'");
		//закрываем соединения с БД
		$DBO->onConnectionDB();
		} 
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	unset($userSessid);
	session_destroy();
	setcookie(session_name(), '');
	//получаем корневую директорию сайта
	header("Location: /{$this->directory}/index.php");	
	}

//вывод формы авторизации		
public function inputFormAuthorization() 
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	if(isset($this->messageError)) {
		if((!empty($_POST['userLogin'])) || (!empty($_POST['userPassword']))){
			MessageErrors::userMessageError(MessageErrors::ERROR_AUTHORIZATION, "\t ошибка: неправильный ввод логина '".$_POST['userLogin']."' или пароля, пользователем с IP-адресом ".$_SERVER['REMOTE_ADDR']);
			}
		?>
		<div style="text-align: center; position: absolute; top: 30%; left: 50%; margin:-50px 0 0 -50px;">
<!-- выводим ошибки -->		
		<?php
		foreach($this->messageError as $message){
			?>
			<span style="font-size: 14px; font-family: 'Times New Roman'">
			<?= $message; ?>
			</span>
			<?php			
			}
		?>
		</div>
		<?php
		}
		?>
<!-- скрипт проверки заполнения формы -->			
	<script type="text/javascript" >
	//функция проверяющая заполненные формы
	function validateForm()
		{
		var login = document.author.userLogin;
		var pass = document.author.userPassword;
		login.style.borderColor = '';
		pass.style.borderColor = '';
		if(login.value.length == 0){
			document.getElementById('alarm').innerHTML = "введите логин";
			login.style.borderColor = 'red';
			return false;
			}
		if(pass.value.length == 0){
			document.getElementById('alarm').innerHTML = "введите пароль";
			pass.style.borderColor = 'red';
			return false;	
			}
		}
	</script>
	<div id="author" style="position: absolute; top: 40%; left: 45%; margin:-50px 0 0 -50px; width: 300px; height: 170px; background-color: #FFFAFA; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0, .2)">
		<div style="text-align: center; position: absolute; top: 50%; left: 38%; margin:-50px 0 0 -50px;">
		<form name="author" method="POST" action="/<?= $array_directory[1] ?>/index.php" onsubmit="return validateForm()">
			<div style="width: 100px; height: 10px;">
				<span style="font-size: 10px;	font-family: Verdana, serif; position: absolute; top: -10px; left: 55px; color: #CD0000;" id="alarm"></span></div>
			<span style="font-size: 14px; font-family: 'Times New Roman'">Логин</span><br>
			<input type="text" name="userLogin" style="width: 170px; height: 16px;"><br>
			<span style="font-size: 14px; font-family: 'Times New Roman'">Пароль</span><br>
			<input type="password" name="userPassword" style="width: 170px; height: 16px;"><br>
			<input type="submit" name="submit" style="width: 60px; height: 20px; margin-top: 5px;" value="Вход">
		</form>		
		</div>
	</div>
	<!-- проверка даты (вывод плюшек к праздникам) -->		
	<script src="/<?= $this->directory ?>/js/checkDate.js"></script>
	<script type="text/javascript"> window.onload = checkDateAuthorization('<?= $this->directory ?>'); </script>
	<?php
	exit();
	}
}

?>