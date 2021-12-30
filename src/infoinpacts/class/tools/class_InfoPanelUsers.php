<?php

										/*-------------------------------------------------*/
										/*	 класс информационной панели для пользователей	*/
										/*											v0.1 01.04.2014	*/
										/*-------------------------------------------------*/

class InfoPanelUsers extends InfoPanel
{

//вывод информационной панели	
public function showInfoPanel() 
	{
	//получаем массив с активными иконками
	$array = $this->checkInfoIcons();
	//ширина первого блока
	$width_1 = '525px';
	//ширина второго блока
	$width_2 = '450px';
	//максимально возможная ширина двух блоков 975
	$maxWidth = 975;
	//определяем ширину первого и второго блоков
	if(count($array) == 0){
		$width_1 = $maxWidth.'px';
		$width_2 = '0px';
		} else {
		$sum = 0;
		//если есть активные иконки отнимаем от первого блока по 75px
		for($i = 0; $i < count($array); $i++){
			$sum += 75;
			}
		$width_1 = ($maxWidth - $sum).'px';
		$width_2 = $sum.'px';		
		}
	$countInfoIcon = count($array);
	//блок информации о пользователе
	?><div style="position: relative; top: -12px; text-align: center; width: <?= $width_1 ?>; height: 60px; line-height: 15px; display: inline-block; margin-left: 5px; float: left;">
	<br>	
	<?php
	//получаем имя пользователя
	$this->showUserName();
	try{	
		//получаем дату последнего посещения
		$this->dateEndVisit();
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	?></div>
	<!-- блок с информационными иконками -->
	<div style="position: relative; top: -12px; text-align: right; width: <?= $width_2 ?>; height: 60px; line-height: 15px; display: inline-block; margin-left: 5px; float: left;">		
	<?php
	//выводим информационные иконки
	foreach($array as $val){
	?>
<!-- контейнер иконкой информирующей о новом сообщении -->
	<div style="position: relative; top: 0px; text-align: center; width: 65px; height: 60px; line-height: 8px; display: inline-block; margin-left: 5px;">
<!-- изображение -->
		<div style="position: relative; top: 0px;">
			<a href="/<?= $this->dirSite.$val['active']?>"><img src="/<?= $this->dirSite.$val['img'] ?>" width="35" height="35" class="imageButton" title="<?php echo $val['message'].' - '.$val['count'].' шт.'; ?>"></a>
		</div>				
<!-- текст -->
		<span style="font-size: 10px; font-family: 'Times New Roman', serif; color: #000;">
			<?php echo $val['message']; ?>
		</span>
	</div>
	<?php 
		} 
	echo "</div>";
	}
	
//вывод имени пользователя
protected function showUserName() 
	{
	echo "<span style='font-size: 12px; font-weight: bold; font-family: Palatino, Times New Roman, serif; text-transform: uppercase; letter-spacing: 1px; color: FFF;'>{$this->role}:&nbsp;</span>";
	echo "<span style='font-size: 14px; font-family: Times New Roman, serif; color: FFF; text-decoration: underline;'>".$this->ReadXMLSetup->usernameFIO($this->userLogin)."</span>";	
	}
	
//вывод даты последнего посещения
protected function dateEndVisit() 
	{
	$query = $this->DBO->connectionDB()->prepare("SELECT `session_end` FROM `user_session` WHERE `user_login`=:userLogin");
	$query->execute(array('userLogin' => $this->userLogin));
	$date = $query->fetch(PDO::FETCH_OBJ)->session_end;
	//получаем из php.ini ограничение на время жизни сессии и вычитаем его из значения полученного из БД
	$date -= ini_get('session.gc_maxlifetime');
	echo ", последнее посещение ".ConversionData::showDateConvert($date)." в ".substr(date("Y-m-d H:i:s" ,$date), 11, 5); 
	}
	
//определяем количество информационных иконок
protected function checkInfoIcons() 
	{
	$i = 0;
	$array = array();
	//считаем количество активных информационных иконок (когда ключ 'count' в массиве получаемом через метод $this->getIconsName() больше 0)
	foreach($this->getIconsName() as $value){
		if($value['count'] != 0){
			$array[$i]['count'] = $value['count'];
			$array[$i]['active'] = $value['active'];
			$array[$i]['img'] = $value['img'];
			$array[$i]['message'] = $value['message'];
			$i++;
			}
		}
	return $array;
	}

}

?>