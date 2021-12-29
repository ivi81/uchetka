<?php

							/*------------------------------------------------------------------------------*/
							/*  класс формирования данных для построения графиков и диаграмм (РУКОВОДСТВО)	*/
							/* 	 													v0.1	01.10.2014		*/
							/*------------------------------------------------------------------------------*/

class GetChartsConstructionMajor extends GetChartsConstruction
{
//объект выбора произвольного цвета
private $objGetNewColor;

function __construct()
	{
	parent::__construct();
	$this->objGetNewColor = new GetNewColor;;
	}

//количество сотрудников по группам
public function getCountWorker()
	{
	$arrayTmp = array();
	foreach($this->ReadXMLSetup->getArrayAllUsersInform() as $key => $value){
		if($key == '10'){
			$arrayTmp[10][] = 'руководство'; 
			$arrayTmp[10][] = count($value);
			$arrayColor10 = explode(',', $this->objGetNewColor->getColorForPie());
			} 
		elseif($key == '20') {
			$arrayTmp[20][] = 'дежурные'; 
			$arrayTmp[20][] = count($value);
			$arrayColor20 = explode(',', $this->objGetNewColor->getColorForPie());
		} else {
			$arrayTmp[30][] = 'аналитики'; 
			$arrayTmp[30][] = count($value);
			$arrayColor30 = explode(',', $this->objGetNewColor->getColorForPie());
			}
		}
	$data = "[
			  { value: {$arrayTmp[10][1]}, color: '".$arrayColor10[0]."', highlight: '".$arrayColor10[1]."', label: '{$arrayTmp[10][0]}' },
			  { value: {$arrayTmp[20][1]}, color: '".$arrayColor20[0]."', highlight: '".$arrayColor20[1]."', label: '{$arrayTmp[20][0]}' },
			  { value: {$arrayTmp[30][1]}, color: '".$arrayColor30[0]."', highlight: '".$arrayColor30[1]."', label: '{$arrayTmp[30][0]}' }
			 ]";
	$dataLegend = "[
			  		{ value: {$arrayTmp[10][1]}, color: '".$arrayColor10[0]."', title: '{$arrayTmp[10][0]}' },
			  		{ value: {$arrayTmp[20][1]}, color: '".$arrayColor20[0]."', title: '{$arrayTmp[20][0]}' },
			  		{ value: {$arrayTmp[30][1]}, color: '".$arrayColor30[0]."', title: '{$arrayTmp[30][0]}' }
			 	   ]"; 
	//создаем круговую диаграмму 
	/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	+ данные для диаграммы предоставляются в формате JSON, например						+
	+ $data = [																			+
	+		  { value: 300, color: "#F7464A", highlight: "#FF5A5E", label: "Red" },		+
	+		  { value: 100, color: "#46BFBD", highlight: "#5AD3D1", label: "Green" },	+
	+		 ];																			+
	+ где, value - значение, 															+
	+ 	  color - цвет части диаграмма до наведения на неё, 							+
	+ 	  highlight - цвет части диаграмма после наведения на неё, 						+
	+ 	  label - название.																+
	+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
	$this->getChart->getChartPie($data, $dataLegend, 'legendChart_1');
	}

//количество посещений по фамилиям
public function getCountVisitation()
	{
	try{
		$data = $dataLegend = "[";
		$query = self::getConnectionDB()->connectionDB()->query("SELECT `user_login`, `count_visit_user`, (SELECT COUNT(`user_login`) FROM `user_session` WHERE `user_login`!='admin') AS NUM 
																 FROM `user_session` WHERE `user_login`!='admin' ORDER BY `count_visit_user` DESC");
		$i = 0;
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$i++;
			list($color_1, $color_2) = explode(',', $this->objGetNewColor->getColorForPie());
				$name = explode(' ', $this->ReadXMLSetup->usernameFIO($row->user_login));
				if($i < $row->NUM){
					$fix = ', ';
					} else {
					$fix = '';
					}
				$data .= " { value: {$row->count_visit_user}, color: '".$color_1."', highlight: '".$color_2."', label: '{$name[0]}' }{$fix}";
				$dataLegend .= " { value: {$row->count_visit_user}, color: '".$color_1."', title: '{$this->ReadXMLSetup->giveUserNameAndSurname($row->user_login)}' }{$fix}";
				}
		$data .= "]";
		$dataLegend .= "]";
		//создаем диаграмму типа бублик
		/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		+ данные для диаграммы предоставляются в формате JSON, например						+
		+ $data = [																			+
		+		  { value: 300, color: "#F7464A", highlight: "#FF5A5E", label: "Red" },		+
		+		  { value: 100, color: "#46BFBD", highlight: "#5AD3D1", label: "Green" },	+
		+		 ];																			+
		+ где, value - значение, 															+
		+ 	  color - цвет части диаграмма до наведения на неё, 							+
		+ 	  highlight - цвет части диаграмма после наведения на неё, 						+
		+ 	  label - название.																+
		+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
		$this->getChart->getChartDoughnut($data, $dataLegend, 'legendChart_2');
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//количество добавленных компьютерных воздействий
public function getCountAddComputerImpact()
	{
	try{
		$data = $dataLegend = "[";
		$query = self::getConnectionDB()->connectionDB()->query("SELECT t1.login_name, COUNT(t1.id) AS NUM FROM `incident_analyst_tables` t0 
																 LEFT JOIN `incident_additional_tables` t1 ON t0.id=t1.id WHERE `true_false`='1' 
																 AND (t1.login_name is not Null) GROUP BY t1.login_name ORDER BY `NUM` DESC");
		$i = 0;
		$array_data = array();
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$name = explode(' ', $this->ReadXMLSetup->usernameFIO($row->login_name));
			$array_data[$i][] = $name[0];
			$array_data[$i][] = $row->NUM;
			$i++;
			}
		$count = count($array_data);
		for($a = 0; $a < $count; $a++){
			list($color_1, $color_2) = explode(',', $this->objGetNewColor->getColorForPie());
			if($a < $count - 1){
				$fix = ', ';
				} else {
				$fix = '';
				}
			$data .= " { value: {$array_data[$a][1]}, color: '".$color_1."', highlight: '".$color_2."', label: '{$array_data[$a][0]}' }{$fix}";
			$dataLegend .= " { value: {$array_data[$a][1]}, color: '".$color_1."', title: '{$array_data[$a][0]}' }{$fix}";
			}
		$data .= "]";
		$dataLegend .= "]";
		//создаем диаграмму типа бублик
		$this->getChart->getChartDoughnut($data, $dataLegend, 'legendChart_3');
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//количество подготовленных дежурными писем	
public function getCountMail()
	{
	try{
		$data = $dataLegend = "[";
		$query = self::getConnectionDB()->connectionDB()->query("SELECT t1.login_name, COUNT(t1.id) AS NUM FROM `incident_analyst_tables` t0 
																 LEFT JOIN `incident_additional_tables` t1 ON t0.id=t1.id WHERE `true_false`='2' 
																 AND (t1.login_name is not Null) GROUP BY t1.login_name ORDER BY `NUM` DESC");
		$i = 0;
		$array_data = array();
		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$name = explode(' ', $this->ReadXMLSetup->usernameFIO($row->login_name));
			$array_data[$i][] = $name[0];
			$array_data[$i][] = $row->NUM;
			$i++;
			}
		$count = count($array_data);
		for($a = 0; $a < $count; $a++){
			list($color_1, $color_2) = explode(',', $this->objGetNewColor->getColorForPie());
			if($a < $count - 1){
				$fix = ', ';
				} else {
				$fix = '';
				}
			$data .= " { value: {$array_data[$a][1]}, color: '".$color_1."', highlight: '".$color_2."', label: '{$array_data[$a][0]}' }{$fix}";
			$dataLegend .= " { value: {$array_data[$a][1]}, color: '".$color_1."', title: '{$array_data[$a][0]}' }{$fix}";
			}
		$data .= "]";
		$dataLegend .= "]";
		//создаем диаграмму типа бублик
		$this->getChart->getChartPie($data, $dataLegend, 'legendChart_4');
		}
	catch(PDOException $e){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}

//количество стран на IP-адрес назначения	
public function getCharIpDst(array $array)
	{
	$data = $dataLegend = '[';
	$count = count($array);
	$a = 0;
	foreach($array as $key => $value){
		list($color_1, $color_2) = explode(',', $this->objGetNewColor->getColorForPie());
		if($a < $count - 1){
			$fix = ', ';
			} else {
			$fix = '';
			}
		$data .= " { value: {$value}, color: '".$color_1."', highlight: '".$color_2."', label: '{$key}' }{$fix}";
		$dataLegend .= " { value: {$value}, color: '".$color_1."', title: '{$key}' }{$fix}";
		$a++;
		}
	$data .= "]";
	$dataLegend .= "]";
	//создаем диаграмму типа бублик
	$this->getChart->getChartPie($data, $dataLegend, 'legendChart_1');
	}

}
?>