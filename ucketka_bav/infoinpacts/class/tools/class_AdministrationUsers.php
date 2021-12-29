<?php

							/*----------------------------------------------*/
							/*  класс вывода информации о пользователях 		*/
							/* 	 						v.0.1 14.04.2014	*/
							/*----------------------------------------------*/
							
class AdministrationUsers 
{
//соединение с БД
private static $DBO;
//объект для чтения файла setup_site.xml
protected $ReadXMLSetup;
protected $directory;

function __construct() 
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	$this->directory = $array_directory[1];
	$this->ReadXMLSetup = new ReadXMLSetup();
	}

//формируем подключение к БД
protected static function linkDataBase()
	{
	if(empty(self::$DBO)){
		//объект для подключения к БД
		self::$DBO = new DBOlink(); 
		}
	return self::$DBO;
	}

//информация о пользователях (из файла XML)
public function infoUsers() 
	{
	?>
	<style>
		.groupList {
		line-height: 15px;
		font-size: 9px;
		font-family: 'Times New Ronam', serif;        	
		text-transform: uppercase;
		letter-spacing: 1px;
		font-weight: bold;
		display: none; 
		}
		.userList {
		line-height: 15px;
		font-size: 12px;
		letter-spacing: 0px;
		font-weight: normal;
		text-transform: none;
		font-family: 'Times New Ronam', serif;
		display: none; 
		}	
	</style>
	
	<script type="text/javascript" >
	function showUserList(elem, id){
		//получаем родительский элемент
		var topElem = elem.parentNode;
		var allUl = topElem.getElementsByTagName('UL');
		switch(id){
			//вывод списка групп
			case '1':
			var lis = '';
			//получаем список элементов 'li'
			for(var i = 0; allUl.length > i; i++){
				lis = allUl[i].getElementsByTagName('LI');
				if(lis.length > 0){
					for(var a = 0; lis.length > a; a++){
						if(typeof(lis[a]) !== undefined && lis[a].className == 'groupList'){
							if(lis[a].style.display == ''){
								lis[a].style.display = 'block';
								} else {
								lis[a].style.display = '';
								}
							}
						}
					}
				}
			break;
			//вывод списка пользователей
			case '2':
			var lis = '';
			//получаем список элементов 'li'
			var allLi = allUl[0].getElementsByTagName('LI');
			for(var i = 0; allUl.length > i; i++){
				lis = allUl[i].getElementsByTagName('LI');
				if(lis.length > 0){
					for(var a = 0; lis.length > a; a++){
						if(typeof(lis[a]) !== undefined && lis[a].className == 'userList'){
							if(lis[a].style.display == ''){
								lis[a].style.display = 'block';
								} else {
								lis[a].style.display = '';
								}
							}
						}
					}
				}
			break;
			}
		}
	</script>
	<div id="userInfo" style="width: 200px;">
	<ul>
		<!-- всего пользователей -->
		<li style="display: none; line-height: 20px; text-align: left; font-size: 12px; font-weight: bold; font-family: 'Times New Ronam', serif; text-transform: uppercase; letter-spacing: 2px; display: block;">
			<a href="#" onclick="showUserList(this,'1')" title="всего пользователей"><div style="position: relative; top: 2px; left: 45px;"><img src="/<?= $this->directory ?>/img/user_info.png"></div>
			<?php
			//считаем количество пользователей
			$num = 0;
			foreach($this->ReadXMLSetup->getArrayAllUsersInform() as $value){
				foreach($value as $key => $array){
					$num++;
					}
				}
			echo $num.' '.DeclansionWord::declansionNum($num, array('пользователь', 'пользователя', 'пользователей')); 
			?>
			</a>
			<ul>
			<?php
			$i = 0;
			$testGroup = null;
			foreach($this->ReadXMLSetup->getArrayAllUsersInform() as $key => $value){
				if($key == '10'){
					$numUser = count($value);
					$group = 'руководство';
					} 
				elseif($key == '20') {
					$numUser = count($value);
					$group = 'дежурные';	
					} else {
					$numUser = count($value);
					$group = 'аналитики';	
					}
				$a = 0;
				foreach($value as $login => $info){
					if($key == $testGroup){
						echo '<li class="userList"><span style="color: #0000CD; font-style: italic;"><span style="font-weight: bold;">'.++$a.'.</span> '.$info['name'].'</span></li>';					
						} else {
						if($i != 0){
							echo "</ul></li>";
							}
						?>
						<li class="groupList"><a href="#" onclick="showUserList(this, '2')"><?= $group.' - '.$numUser.' чел.' ?></a>
						<?php
						echo '<ul><li class="userList"><span style="color: #0000CD; font-style: italic;"><span style="font-weight: bold;">'.++$a.'.</span> '.$info['name'].'</span></li>';
						}
					$i++;
					$testGroup = $key;
					}
				}
			?>
			</ul>
		</li>
	</ul>
	</div>
	<?php
	}
	
//информация о сессиях пользователей (из таблиц БД)
public function infoSessionUsers() 
	{
	try{
		$query = self::linkDataBase()->connectionDB()->query("SELECT * FROM `user_session`");
		while($row = $query->fetch(PDO::FETCH_ASSOC)){
			echo 'Ф.И.О. '.$row['user_fio'].' (логин: '.$row['user_login'].') количество посещений: '.$row['count_visit_user']
					.' статус: '.$row['authorization'].' время входа: '.$row['session_start'].'<br>';			
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
	
//добавление пользователя
public function addUser()
	{
	}
	
//изменение логина пользователя
public function changeUserLogin()
	{
	}
	
//изменение пароля пользователя
public function changeUserPassword()
	{
	}
	
//изменение Ф.И.О. пользователя
public function changeUserName()
	{
	}
	
//изменение группы пользователя
public function changeUserGroup()
	{
	}

//удаление пользователя
public function delUser()
	{
	}
}
?>