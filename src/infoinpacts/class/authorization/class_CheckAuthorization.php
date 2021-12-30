<?php

						/*--------------------------------------------------------------*/
						/*  класс проверки авторизации и проверки соответствия группы	*/
						/*  учетных данных пользователем загруженной им странице		*/
						/*  НАСЛЕДНИК класса Authorization (на сессиях)					*/
						/* 	 										 v.0.21 28.03.2014  */
						/*--------------------------------------------------------------*/

class CheckAuthorization extends Authorization
{

//проверка данных в глобальном массиве
public function checkUserData()
	{
	//проверяем данные в глобальном массиве $_SESSION['userSessid']
	if(!isset($_SESSION['userSessid']['userId']) || !isset($_SESSION['userSessid']['userLogin'])){
		parent::exitUserSession();
		}
	if(empty($_SESSION['userSessid']['userId']) || empty($_SESSION['userSessid']['userLogin'])){
		parent::exitUserSession();
		}
	if($_SESSION['userSessid']['userIP'] != $_SERVER['REMOTE_ADDR']){
		parent::exitUserSession();
		}
	$this->userId = $_SESSION['userSessid']['userId'];
	$this->userLogin = $_SESSION['userSessid']['userLogin'];
	try{
		$this->changeUserSession();
		} 
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		} 
	}	

//проверка соответствия страницы идентификатору пользователя
public function checkPageUserId($dir, $login = false) 
	{
	//массив из userId и соответствующим им директориям 
	$array_dir_id = array('10' => 'major', '20' => 'worker', '30' => 'analyst', '40' => 'admin');
	//получаем массив из пути к обрабатываемому скрипту
	$stringDirFile = explode("/", $dir);
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	foreach($stringDirFile as $dirFile){
		($array_dir_id[$this->userId] == $dirFile) ? $login = true : false;
		}
	($login == false) ? header("Location:/{$array_directory[1]}/index.php") : false;
	}

//проверка времени последнего посещения пользователей
private function changeUserSession()
	{
	$query = $this->DBO->connectionDB()->query("SELECT `user_login`, `session_end` FROM `user_session` WHERE `authorization`='yes'");
	while($row = $query->fetch(PDO::FETCH_ASSOC)){
		//находим авторизованного на данный момент пользователя
		if($row['user_login'] == $_SESSION['userSessid']['userLogin']){
			$this->DBO->connectionDB()->query("UPDATE `user_session` SET `session_end`='".(time() + $this->lifeTimeSession)."' WHERE `user_login`='".$row['user_login']."'");
			} else {
			if($row['session_end'] < time()){
				$this->DBO->connectionDB()->query("UPDATE `user_session` SET `authorization`='no', `session_end`='".time()."' WHERE `user_login`='".$row['user_login']."'");
				}
			}
		}
	}

}
?>