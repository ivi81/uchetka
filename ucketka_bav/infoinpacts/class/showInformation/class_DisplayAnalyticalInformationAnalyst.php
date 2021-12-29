<?php

							/*------------------------------------------------------*/
							/*  класс вывода аналитической информации (аналитик)		*/
							/* 	 							v0.1	27.08.2014		*/
							/*------------------------------------------------------*/

class DisplayAnalyticalInformationAnalyst extends DisplayAnalyticalInformation
{

//получаем цвет по количеству процентов
private function getColor($num)
	{
	if($num > 80) return '#FF0000';
	elseif($num < 80 && $num > 60) return '#FFA500';
	elseif($num < 60 && $num > 40) return '#FFFF00';
	elseif($num < 40 && $num > 20) return '#ADFF2F';
	else return '#00FF00';		
	}

//вывод аналитической информации по сигнатуре
public function showInformationSid(array $sid)
	{
	try{
		foreach($sid as $numSid){
			$response = $list = false;
			$query = self::getConnectionDB()->connectionDB()->query("SELECT (SELECT COUNT(t1.id) FROM (SELECT * FROM `incident_number_signature_tables` 
											  						 WHERE `sid`='".$numSid."') AS t0 LEFT JOIN `incident_analyst_tables` AS t1 ON t0.id=t1.id 
											 						 WHERE `true_false`='1') AS true_false_1, (SELECT COUNT(t1.id) FROM (SELECT * FROM `incident_number_signature_tables` 
											  						 WHERE `sid`='".$numSid."') AS t0 LEFT JOIN `incident_analyst_tables` AS t1 ON t0.id=t1.id 
											  						 WHERE `true_false`='2') AS true_false_2");
			
			$row = $query->fetch(PDO::FETCH_OBJ);
			//проверяем есть ли сигнатуры у которых возможность ложного срабатывания равна 100%
			if($row->true_false_1 == 0 && $row->true_false_2 != 0){
				$percent = 100;
				}
			elseif($row->true_false_1 == 0 && $row->true_false_2 == 0){
				$percent = 0;
				} else {
				$percent = ceil(($row->true_false_2 * 100) / ($row->true_false_2 + $row->true_false_1));
				}

			//подчеркивание и цвет сигнатуры
			$underline = ($percent != 0) ? 'color: #0000CD; text-decoration: underline;' : '';

			if($percent == ''){
				$percent = "<span style='font-size: 18px; color: #0000CD;'>0 %</span>";
				$style = 'style=""';
				} else {
				$style = 'style="cursor: pointer;"';
				//для положительных срабатываний
				$query_true_false_1 = self::getConnectionDB()->connectionDB()->query("SELECT COUNT(`ip_dst`) AS COUNT, `ip_dst` FROM 
																					 (SELECT COUNT(`id`), `id`, `ip_dst` FROM `incident_chief_tables` 
																					  WHERE `id` IN (SELECT t1.id FROM (SELECT * FROM `incident_number_signature_tables` 
																					  WHERE `sid`='".$numSid."') AS t0 LEFT JOIN `incident_analyst_tables` AS t1 ON t0.id=t1.id 
																					  WHERE `true_false`='1') GROUP BY `id`) AS TAB GROUP BY `ip_dst` ORDER BY `COUNT` DESC");
				//для ложных срабатываний
				$query_true_false_2 = self::getConnectionDB()->connectionDB()->query("SELECT COUNT(`ip_dst`) AS COUNT, `ip_dst` FROM 
																					 (SELECT COUNT(`id`), `id`, `ip_dst` FROM `incident_chief_tables` 
																					  WHERE `id` IN (SELECT t1.id FROM (SELECT * FROM `incident_number_signature_tables` 
																					  WHERE `sid`='".$numSid."') AS t0 LEFT JOIN `incident_analyst_tables` AS t1 ON t0.id=t1.id 
																					  WHERE `true_false`='2') GROUP BY `id`) AS TAB GROUP BY `ip_dst` ORDER BY `COUNT` DESC");
				$list = "<ul style='display: none; text-align: left;'>";
				$list .= "<span style='padding-left: 10px; color: #000; font-size: 15px; font-weight: bold; letter-spacing: 1px;'>ложные компьютерные воздействия</span>: <span style='color: #0000CD; font-weight: bold;'>".$row->true_false_2."</span>";
				//для ложных
				while($row_true_false_2 = $query_true_false_2->fetch(PDO::FETCH_OBJ)){
					$list .= "<li style='list-style-type: none; font-size: 15px; text-align: left; padding-left: 30px;'><span style='color: #0000CD;'>";
					$list .= $row_true_false_2->COUNT."</span> - <a href='/{$this->directoryRoot}/analyst/process/showAllInformationForIpDst.php?ipDst=".long2ip($row_true_false_2->ip_dst)."&sid=".$numSid."&true=2&count=".$row_true_false_2->COUNT."&number=1' target='_blank'>";
					$list .= long2ip($row_true_false_2->ip_dst)."</a>".self::getDomainName(long2ip($row_true_false_2->ip_dst))."</li>";
					}
				$list .= "</ul>";

				$list .= "<ul style='display: none; text-align: left;'>";
				$list .= "<span style='padding-left: 10px; color: #000; font-size: 15px; font-weight: bold; letter-spacing: 1px;'>компьютерные атаки</span>: <span style='color: #0000CD; font-weight: bold;'>".$row->true_false_1."</span>";
				//для положительных
				while($row_true_false_1 = $query_true_false_1->fetch(PDO::FETCH_OBJ)){
					$list .= "<li style='list-style-type: none; font-size: 15px; text-align: left; padding-left: 30px;'><span style='color: #0000CD;'>";
					$list .= $row_true_false_1->COUNT."</span> - <a href='/{$this->directoryRoot}/analyst/process/showAllInformationForIpDst.php?ipDst=".long2ip($row_true_false_1->ip_dst)."&sid=".$numSid."&true=1&count=".$row_true_false_1->COUNT."&number=1' target='_blank'>";
					$list .= long2ip($row_true_false_1->ip_dst)."</a>".self::getDomainName(long2ip($row_true_false_1->ip_dst))."</li>";
					}
				$list .= "</ul>";
				$percent = "<span style='font-size: 18px; color: ".$this->getColor($percent)."; text-shadow: #696969 1px 0 0px, #696969 0 1px 0px, #696969 -1px 0 0px, #696969 0 -1px 0px;'>{$percent} %</span>";
				}
			?>
			<div onclick="(function(elem){ 
							var ul = elem.nextSibling; 
							if(ul != undefined && ul.nextSibling != undefined && ul.nodeType == 1 && ul.nextSibling.nodeType == 1) 
								if(ul.style.display == 'none'){ 
									ul.style.display = 'block'; 
									ul.nextSibling.style.display = 'block'; 
									} else { 
									ul.style.display = 'none'; 
									ul.nextSibling.style.display = 'none'; }})(this)" <?= $style ?>>
			<?= "<span style='font-size: 14px;".$underline."'>".$numSid.'</span> - '.$percent ?></div><?= $list ?>			
			<?php
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//вывод аналитической информации по IP-адресу источника
public function showInformationSrcIp(array $ipSrc)
	{
	try{
		foreach($ipSrc as $ip){
			$response = $list = false;
			$query = self::getConnectionDB()->connectionDB()->query("SELECT (SELECT COUNT(t1.id) FROM (SELECT * FROM `incident_chief_tables` 
																	 WHERE `ip_src`='".$ip."') AS t0 LEFT JOIN `incident_analyst_tables` AS t1 ON t0.id=t1.id 
																	 WHERE `true_false`='1') AS true_false_1, (SELECT COUNT(t1.id) FROM 
																	(SELECT * FROM `incident_chief_tables` WHERE `ip_src`='".$ip."') AS t0 LEFT JOIN 
																	`incident_analyst_tables` AS t1 ON t0.id=t1.id WHERE `true_false`='2') AS true_false_2");
			
			$row = $query->fetch(PDO::FETCH_OBJ);
			//проверяем есть ли сигнатуры у которых возможность ложного срабатывания равна 100%
			if($row->true_false_1 == 0 && $row->true_false_2 != 0){
				$percent = 100;
				}
			elseif($row->true_false_1 == 0 && $row->true_false_2 == 0){
				$percent = 0;
				} else {
				$percent = ceil(($row->true_false_2 * 100) / ($row->true_false_2 + $row->true_false_1));
				}

			//подчеркивание и цвет сигнатуры
			$underline = ($percent != 0) ? 'color: #0000CD; text-decoration: underline;' : '';

			if($percent == ''){
				$percent = "<span style='font-size: 18px; color: #0000CD;'>0 %</span>";
				$style = 'style=""';
				} else {
				$style = 'style="cursor: pointer;"';
				//для положительных срабатываний
				$query_true_false_1 = self::getConnectionDB()->connectionDB()->query("SELECT `ip_dst`, COUNT(`ip_dst`) AS COUNT FROM `incident_chief_tables` t0 
																					  JOIN `incident_analyst_tables` t1 ON t0.id=t1.id WHERE `ip_src`='".$ip."' 
																					  AND `true_false`='1' GROUP BY `ip_dst` ORDER BY COUNT DESC");
				//для ложных срабатываний
				$query_true_false_2 = self::getConnectionDB()->connectionDB()->query("SELECT `ip_dst`, COUNT(`ip_dst`) AS COUNT FROM `incident_chief_tables` t0 
																					  JOIN `incident_analyst_tables` t1 ON t0.id=t1.id WHERE `ip_src`='".$ip."' 
																					  AND `true_false`='2' GROUP BY `ip_dst` ORDER BY `COUNT` DESC");
				$list = "<ul style='display: none; text-align: left;'>";
				$list .= "<span style='padding-left: 10px; color: #000; font-size: 15px; font-weight: bold; letter-spacing: 1px;'>ложные компьютерные воздействия</span>: <span style='color: #0000CD; font-weight: bold;'>".$row->true_false_2."</span>";
				//для ложных
				while($row_true_false_2 = $query_true_false_2->fetch(PDO::FETCH_OBJ)){
					$list .= "<li style='list-style-type: none; font-size: 15px; text-align: left; padding-left: 30px;'><span style='color: #0000CD;'>";
					$list .= $row_true_false_2->COUNT."</span> - <a href='/{$this->directoryRoot}/analyst/process/showAllInformationForIpDst.php?ipDst=".long2ip($row_true_false_2->ip_dst)."&ipSrc=".long2ip($ip)."&true=2&count=".$row_true_false_2->COUNT."&number=2' target='_blank'>";
					$list .= long2ip($row_true_false_2->ip_dst)."</a>".self::getDomainName(long2ip($row_true_false_2->ip_dst))."</li>";
					}
				$list .= "</ul>";

				$list .= "<ul style='display: none; text-align: left;'>";
				$list .= "<span style='padding-left: 10px; color: #000; font-size: 15px; font-weight: bold; letter-spacing: 1px;'>компьютерные атаки</span>: <span style='color: #0000CD; font-weight: bold;'>".$row->true_false_1."</span>";
				//для положительных
				while($row_true_false_1 = $query_true_false_1->fetch(PDO::FETCH_OBJ)){
					$list .= "<li style='list-style-type: none; font-size: 15px; text-align: left; padding-left: 30px;'><span style='color: #0000CD;'>";
					$list .= $row_true_false_1->COUNT."</span> - <a href='/{$this->directoryRoot}/analyst/process/showAllInformationForIpDst.php?ipDst=".long2ip($row_true_false_1->ip_dst)."&ipSrc=".long2ip($ip)."&true=1&count=".$row_true_false_1->COUNT."&number=2' target='_blank'>";
					$list .= long2ip($row_true_false_1->ip_dst)."</a>".self::getDomainName(long2ip($row_true_false_1->ip_dst))."</li>";
					}
				$list .= "</ul>";
				$percent = "<span style='font-size: 18px; color: ".$this->getColor($percent)."; text-shadow: #696969 1px 0 0px, #696969 0 1px 0px, #696969 -1px 0 0px, #696969 0 -1px 0px;'>{$percent} %</span>";
				}
			?>
			<div onclick="(function(elem){ 
							var ul = elem.nextSibling; 
							if(ul != undefined && ul.nextSibling != undefined && ul.nodeType == 1 && ul.nextSibling.nodeType == 1) 
								if(ul.style.display == 'none'){ 
									ul.style.display = 'block'; 
									ul.nextSibling.style.display = 'block'; 
									} else { 
									ul.style.display = 'none'; 
									ul.nextSibling.style.display = 'none'; }})(this)" <?= $style ?>>
			<?= "<span style='font-size: 14px;".$underline."'>".long2ip($ip).'</span> - '.$percent ?></div><?= $list ?>			
			<?php
			}
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//вывод аналитичиской информации о совпадении IP-адреса источника, IP-адреса назначения и номера сигнатуры
public function showInformationEqual(array $arrayIpSrc, array $arraySid, $ipDst, $idImpact)
	{
	$IP_SRC = $SID = '';
	//получаем sql запрос для ipSrc
	$countIpSrc = count($arrayIpSrc);
	if($countIpSrc == 0){
		MessageErrors::showInformationError("нет IP-адресов источников");
		exit();
		} else {
		$IP_SRC = " `ip_src`='".intval($arrayIpSrc[0])."'";
		if($countIpSrc > 1){
			for($i = 1; $i < ($countIpSrc - 1); $i++){
				$IP_SRC .= " OR `ip_src`='".intval($arrayIpSrc[$i])."'";
				}
			$IP_SRC .= " OR `ip_src`='".intval($arrayIpSrc[$i])."'";
			}
		}
	//получаем sql запрос для Sid
	$countSid = count($arraySid);
	if($countSid == 0){
		MessageErrors::showInformationError("нет номеров сигнатур");
		exit();
		} else {
		$SID = " `sid`='".intval($arraySid[0])."'";
		if($countSid > 1){
			for($i = 1; $i < ($countSid - 1); $i++){
				$SID .= " OR `sid`='".intval($arraySid[$i])."'";
				}
			$SID .= " OR `sid`='".intval($arraySid[$i])."'";
			}
		}	

	try{
		$query = self::getConnectionDB()->connectionDB()->query("SELECT DISTINCT(id) AS num_id FROM (SELECT t0.id FROM `incident_chief_tables` t0
																 LEFT JOIN `incident_number_signature_tables` t1 ON t0.id=t1.id
																 LEFT JOIN `incident_analyst_tables` t2 ON t0.id=t2.id
																 WHERE `ip_dst`='".intval($ipDst)."' AND (".$IP_SRC.") AND (".$SID.")
																 AND (`true_false`='1' OR `true_false`='2')) AS tabl ORDER BY `num_id` ASC");
		$arrayId = array();
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$arrayId[] = $row->num_id;
			}
		$countEqual = count($arrayId);
		?>
		<span style="font-size: 18px;">
			<?php
			if($countEqual != 0){
				$stringId = implode(',', $arrayId);
				echo "<a href='/{$this->directoryRoot}/analyst/process/showAllInformationForIpDst.php?ipDst=".long2ip($ipDst)."&stringId={$stringId}&count={$countEqual}&idImpact={$idImpact}&number=3' target='_blank'>".$countEqual."</a>";
				} else {
				echo $countEqual;	
				} 
			 ?>
		</span>
		<?php
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}	
	}
}

?>