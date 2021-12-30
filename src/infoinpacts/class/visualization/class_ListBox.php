<?php

						/*------------------------------------------*/
						/*  		класс выпадающих списков 		*/
						/* 					  v.0.12 21.07.2014 	*/
						/*------------------------------------------*/

class ListBox
{

private static $readXml;
static function readXML()
	{
	if(empty(self::$readXml)){
		self::$readXml = new ReadXMLSetup();
		}
	return self::$readXml;
	}

//список имен пользователей (по группе пользователей)
static function listUserName($idGroup)
	{
	?>
	<select name="userName" style="width: 130px; height: 23px;">
	<?php
	echo "<option value='null'>имя пользователя</option>";
	foreach(self::readXML()->getArrayUserNumGroup($idGroup) as $key => $login){
		$array_name = explode(' ', self::readXML()->usernameFIO($login));
		echo "<option value='$login'>".$array_name[0].' '.$array_name[1]."</option>";
		}
	?>
	</select>
	<?php
	}

//список имен пользователей с уже выбранным именем пользователя (по группе пользователей)
static function listUserNameLoginName($idGroup, $loginName)
	{
	//проверяем наличие параметров
	if(!isset($loginName) || empty($loginName)) return false;
	if(!isset($idGroup) || empty($idGroup)) return false;
	if(!preg_match("/^[0-9]{2}$/", $idGroup)) return false;
	if(self::readXML()->usernameFIO($loginName)){
		?>
		<select name="userName" style="width: 130px; height: 23px;">
		<?php
		$array_value_option = explode(' ', self::readXML()->usernameFIO($loginName));
		echo "<option value='".$loginName."'>".$array_value_option[0]." ".$array_value_option[1]."</option>";
		foreach(self::readXML()->getArrayUserNumGroup($idGroup) as $key => $login){
			if($loginName == $login) continue;
			$array_name = explode(' ', self::readXML()->usernameFIO($login));
			echo "<option value='$login'>".$array_name[0]." ".$array_name[1]."</option>";
			}
		?>
		</select>
		<?php
		}
	}

//список имен пользователей для Simpli chat
static function listUserNameForSimleChat()
	{
	$array_user = self::readXML()->getArrayAllUsersInform();
	function readArray($id, array $array)
		{
		if(!preg_match("/^[0-9]{2}$/", $id)) return false;
		foreach($array[$id] as $key => $value){
			list($surname, $name,) = explode(" ", $value['name']);
			echo "<option value='".$key."'>".$surname." ".$name."</option>";
			}
		}
	?>
	<select name="loginName" style="width: 170px; height: 23px;">
		<option value="">получатель</option>
		<?php
		$array_group = array('all' => '- все пользователи', 'analyst' => '- все аналитики', 'worker' => '- все дежурные');
		foreach($array_group as $key => $value){
			echo "<option value='".$key."'>$value</option>";	
			}
		//для дежурных				
		readArray(20, $array_user);
		//для аналитиков
		readArray(30, $array_user);
		?>
	</select>
	<?php
	}

//список доменных имен
static function listDomainName() 
	{
	$array = array();
	foreach(self::readXML()->giveDomainName() as $ip => $name){
		for($a = 0; $a < count($name); $a++ ){	
			$array[((string) $name[$a])] = $ip;
			}
		}
	//сортируем ключи массива по возрастанию 
	ksort($array);
	?><select class="formFiledsText formList" name="dstIPName" style="width: 130px; height: 23px;"><?php
	echo "<option value=''>доменное имя</option>";
	foreach($array as $name => $ip){
		echo "<option value='$ip'>".$name."</option>";
		}
	?></select><?php
	}

//список категории воздействия
static function listTypeKA($value = 0) 
	{
	?>
	<select class="formFiledsText formList" name="typeKA" style="width: 170px; height: 23px;">
	<option value="">тип воздействия</option>
	<?php
	foreach(self::readXML()->giveTypeKA() as $id => $name){
		if($value != $id){
			echo "<option value='$id'>".$name."</option>";
			} else {
			echo "<option value='$id' selected style='background: #B0E2FF;'>".$name."</option>";
			}
		}
	?></select><?php
	}

//список типов IP-адресов
static function listTypeIpList()
	{
	?>
	<select name="typeIpList" style="width 300px; height: 23px;">
	<option value="0">тип IP-списка</option>
	<?php
	foreach (self::readXML()->giveListTypeIpAddress() as $key => $value){
		echo "<option value='$key'>".$value[0]."</option>";
		}
	?></select><?php 
	}
	
//список принятых решений (Дежурный)
static function listSolutionWorker() 
	{
	$action = array('письмо (ЦИБ)' => 'подготовлено письмо в 18 Центр ФСБ России', 
					'рабочий порядок (ЦИБ)' => 'передано в рабочем порядке сотрудникам 18 Центра ФСБ России',
					'письмо (ЦИБ) и (организация)' => 'подготовлено письмо в 18 Центр ФСБ России а также в сопутствующую организацию',
					'рабочий порядок (организация)' => 'передано в рабочем порядке сотрудникам сопутствующей организации');
	?><select class="formFiledsText formList" name="solution" style="width: 230px; height: 23px;"><?php
	echo "<option value=' '>решение</option>";
	foreach($action as $value => $string){
		echo "<option value='$string'>".$value."</option>";
		}
	?></select><?php
	}

//список принятых решений (Аналитик)
static function listSolutionAnalyst($value = 0) 
	{
	$action = array('0' => 'решение',
					'1' => 'компьютерная атака',
					'2' => 'ложное срабатывание',
					'3' => 'отсутствует сетевой трафик',
					'4' => 'сетевой трафик утерян',
                    '5' => 'сетевой трафик не рассматривался');
	?><select class="formFiledsText formList" name="solution" style="width: 170px; height: 23px;"><?php
	foreach($action as $key => $name){
		if($value != $key){
			echo "<option value='$key'>".$name."</option>";
			} else {
			echo "<option value='$key' selected style='background: #B0E2FF;'>".$name."</option>";
			}
		}
	?></select><?php
	}
}
?>