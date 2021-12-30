<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		скрипт по обработки данных вводимых дежурными	*/
						/*		загрузка данных в таблицы:						*/
						/*	-incident_chief_tables, 							*/
						/*	-incident_additional_tablesб						*/
						/*	-incident_number_signature_tables					*/
						/*								v.0.1 14.08.2014	 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 

//подключаем основную, для дежурного страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/worker/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

try
	{
	//получаем порядковый номер инцидента
	$id = 1;
	$query_db = $DBO->connectionDB()->query("SELECT MAX(id) AS maxid FROM `incident_additional_tables`");
	if($row = $query_db->fetch(PDO::FETCH_OBJ)){
		$id += $row->maxid;
		}
		
	//дата и время начала инцидента
	$dateTimeStart = ExactilyUserData::takeDate($_POST['dateStart'])."  ".ExactilyUserData::takeTime($_POST['timeStart']);
	$dateTimeEnd = ExactilyUserData::takeDate($_POST['dateEnd'])."  ".ExactilyUserData::takeTime($_POST['timeEnd']);
	
	//проверяем что бы начальная дата была всегда меньше конечной 
	if(strtotime($dateTimeStart) > strtotime($dateTimeEnd)) {
		echo "<div style='position: absolute; margin-left: 210px; top: 250px;'>".ShowMessage::showInformationError("начальная дата должна быть<br>меньше конечной")."</div>";
		exit();							
		}

	//IP-адрес назначения (доменное имя или IP-адрес)
	if(count(ExactilyUserData::takeIP($_POST['dstIPName'])) == 0){
		if(count(ExactilyUserData::takeIP($_POST['ipDst'])) == 0) {
			echo "<div style='position: absolute; margin-left: 210px; top: 250px;'>".ShowMessage::showInformationError("необходимо ввести IP-адрес")."</div>";
			exit();				
			} else {
			foreach(ExactilyUserData::takeIP($_POST['ipDst']) as $ip){
				$ip_dst = ip2long($ip);
				} 
			}
		} else {
		foreach(ExactilyUserData::takeIP($_POST['dstIPName']) as $ip){
			$ip_dst = ip2long($ip);
			} 	
		}
		
	//тип компьютерной атаки
	$type_attack = 0; //то есть не определен	
	if(!empty($_POST['typeKA'])){
		$type_attack = ExactilyUserData::takeString($_POST['typeKA']);
		}

	//доступность хоста (учитывается только в случае выбора доменного имени)
	//1 - host доступен, 2 - host недоступен
	$pingHost = ExactilyUserData::takeIntager($_POST['ping']);
	//принятое решение
	$solution = ExactilyUserData::takeStringAll($_POST['solution']);
	//место расположения отфильтрованного сетевого трафика
	$space_safe_traff = ExactilyUserData::takeStringAll($_POST['spaceSafe']);
	//пояснение дежурного 
	$explanation = ExactilyUserData::takeStringAll($_POST['explanation']);

	//массив сигнатур (sid)
	$arraySid = ExactilyUserData::takeSid($_POST['sid']);

	//массив количества срабатываний сигнатур		
	$arrayNumberActiveSid = ExactilyUserData::takeSid($_POST['sidNum']);	

	$arraySidEndNum = array();
	for($i = 0; $i < count($arraySid); $i++){
		if(isset($arrayNumberActiveSid[$i])){
			//количество срабатываний
			$numberActiveSid = $arrayNumberActiveSid[$i];
			} else {
			//количество срабатываний
			$numberActiveSid = 0;
			}
		//номер сигнатуры
		$arraySidEndNum[$i] = array($arraySid[$i] => $numberActiveSid);
		}

	//IP-адреса источников
	if(count(ExactilyUserData::takeIP($_POST['ipSrc'])) == 0) {
		echo ShowMessage::showInformationError("необходимо ввести IP-адрес");
		exit();
		} else {
			
			/*===============================*/	
			/*	+++ incident_chief_tables +++ */
			/*===============================*/	
/*
id - порядковый номер инцидента
date_time_incident_start - дата и время инцидента, начало
date_time_incident_end - дата и время инцидента, конец
date_time_create - дата и время внесения инцидента в таблицу
ip_src - IP-адрес источника
count_impact - количество воздействий с каждого IP-адреса
ip_dst - IP-адрес назначения
type_attack - тип компьютерной атаки (в числовом виде, расшифровка в файле setup_site.xml)
*/

		$query_db = $DBO->connectionDB()->prepare("INSERT `incident_chief_tables` 
												 (`id`, 
												  `date_time_incident_start`, 
												  `date_time_incident_end`, 
												  `date_time_create`, 
												  `ip_src`,
												  `count_impact`, 
												  `ip_dst`, 
												  `type_attack`,
												  `country`)
												   VALUE 
												  ('".$id."', 
												   :date_start, 
												   :date_end, 
												  '".time()."', 
												   :ip_src,
												   :count_impact, 
												   :ip_dst, 
												  '".$type_attack."',
												  (SELECT `code` FROM `geoip_data` 
												   WHERE start<=:ip_src AND end>=:ip_src))");
//start<=:ip_src AND end>=:ip_src

		$query_db->bindValue(':date_start', $dateTimeStart);
		$query_db->bindValue(':date_end', $dateTimeEnd);
		$query_db->bindValue(':ip_dst', $ip_dst);				
		//массив для IP-адресов источников
		$arraySrcIp = ExactilyUserData::takeIP($_POST['ipSrc']);
		//массив с количеством воздействий от IP-адресов источников
		$arrayCountImpact = ExactilyUserData::takeSid($_POST['ipNum']);
		//проверка существования записи которая уже содержит передаваемые пользователем данные
		$query_db_test = $DBO->connectionDB()->prepare("SELECT COUNT(*) AS numbers FROM `incident_chief_tables` 
														WHERE `date_time_incident_start`=:date_time 
													 	AND `ip_src`=:ip_src AND `ip_dst`=:ip_dst");
		for($i = 0; $i < count($arraySrcIp); $i++){
			//список IP-адресов источников 
			$ip_src = ip2long($arraySrcIp[$i]);
			
			//проверка существования записи которая уже содержит передаваемые пользователем данные
			$query_db_test->execute(array(':date_time' => $dateTimeStart, 
										  ':ip_src' => $ip_src, 
										  ':ip_dst' => $ip_dst));
			if($query_db_test->fetch(PDO::FETCH_OBJ)->numbers > 0){
				echo ShowMessage::showInformationError("запись с такими данными уже существует");
				exit();
				}
	
			//количество срабатываний			
			if(isset($arrayCountImpact[$i])){
				$numberImpact = $arrayCountImpact[$i];
				} else {
				$numberImpact = 0;
				}

			$query_db->bindValue(':ip_src', $ip_src);
			$query_db->bindValue(':count_impact', $numberImpact);
			$query_db->execute();
			}

			/*====================================*/	
			/*	+++ incident_additional_tables +++ */
			/*====================================*/	

/*
id - порядковый номер инцидента
login_name - логин дежурного
availability_host - доступность информационного ресурса
direction_attack - направление компьютерной атаки
solution - решение дежурного
number_mail - номер письма
space_safe - место хранения трафика
visible_analyst - отметка, видел аналитик или нет (0 - нет, 1 - да)
*/
			$query_db = $DBO->connectionDB()->prepare("INSERT `incident_additional_tables` 
													 (`id`, 
													  `login_name`, 
													  `availability_host`, 
													  `direction_attack`, 
													  `solution`,  
													  `space_safe`,
													  `explanation`)
													  VALUE 
													 ('".$id."', 
													  '".$checkAuthorization->userLogin."', 
													  '".$pingHost."', 
													  '".ExactilyUserData::takeIntager($_POST['direction'])."', 
													  :solution,  
													  :space_safe_traff,
													  :explanation)");
		
			$query_db->bindParam(':solution', $solution);
			$query_db->bindParam(':space_safe_traff', $space_safe_traff);
			$query_db->bindParam(':explanation', $explanation);				
			$query_db->execute();

			
			/*==============================================*/	
			/*	+++ incident_number_signature_tables 	+++	*/
			/*==============================================*/

/*
id - порядковый номер инцидента
sid - номер сигнатуры
count_alert - количество срабатываний
*/
			$query_db = $DBO->connectionDB()->prepare("INSERT `incident_number_signature_tables` 
												   	 (`id`, 
													 `sid`, 
													 `count_alert`)
											 	      VALUE 
									  		 	     ('".$id."', 
									 		 	      :sid, 
											 	      :sidNum)");
			foreach($arraySidEndNum as $value){
				foreach ($value as $sid => $sidNum){
					$query_db->execute(array(':sid' => $sid, 'sidNum' => $sidNum));
				}
			}
			
		//вывод сообщения об успешном выполнении и редирект на главную страницу
		ShowMessage::messageOkRedirect("запись успешно добавлена");
		}
	}
catch(PDOException $e){
	echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
	}

include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>		
