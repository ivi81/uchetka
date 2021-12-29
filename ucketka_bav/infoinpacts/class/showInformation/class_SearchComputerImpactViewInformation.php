<?php

							/*--------------------------------------------------------------*/
							/*  		класс вывода информации полученной в результате 		*/
							/*			  работы класса SearchComputerImpactProcess 		*/
							/*																*/
							/* 	 								 	v.0.1 28.04.2015   		*/
							/*--------------------------------------------------------------*/


class SearchComputerImpactViewInformation
{
//массив с результатами поиска
private $queryResult;
private static $readXml;
private $GeoIp;
private $ReadBinaryDBBlackList;
function __construct()
	{
	$this->GeoIp = new GeoIP;
	//чтение бинарной БД со списками IP-адресов
	$this->ReadBinaryDBBlackList = new ReadBinaryDBBlackList;

	$SearchComputerImpact = new SearchComputerImpactProcess;
	$this->queryResult = $SearchComputerImpact->executeSearch();
	}

//получить путь до файлов get_docx.php и showAllInformationForIncidents.php
private function getPathString()
	{
	$directoryRoot = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	return '/'.$directoryRoot[1].'/'.$this->getUserRole().'/process';
	}

//получить роль пользователя
private static function getUserRole()
	{
	$array = array('10' => 'major', '20' => 'worker', '30' => 'analyst', '40' => 'admin');
	return $array[$_SESSION["userSessid"]["userId"]];
	}

//читаем файл Xml
private static function getXmlObject()
	{
	if(empty(self::$readXml)){
		self::$readXml = new ReadXMLSetup;
		}
	return self::$readXml;
	}

//получить строку запроса 
private static function getDirectoryRoot()
	{
	$arrayDirectory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
	return $arrayDirectory[1];
	}

//функция подсчета количества найденных строк
private function countFindResultString()
	{
	return count($this->queryResult);
	}

//вывод ipSrc
private function getListIpSrc(array $arrayIpSrc)
	{
	$string = '';
	foreach($arrayIpSrc as $ipSrc){
		//устанавливаем IP-адрес для поиска в бинарной БД
		$this->ReadBinaryDBBlackList->setIp($ipSrc);
		//вывод найденной информации
		$string .= $this->ReadBinaryDBBlackList->showShortInformationSearchIp().'<br>';
		}
	return $string;
	}

//вывод country
private function getListCountry(array $arrayCountry)
	{
	$string = '';
	foreach($arrayCountry as $code){
		//проверяем найдена ли страна
		$string .= $this->GeoIp->nameAndFlagsOfCode($code).'<br>';
		}
	return $string;	
	}

//вывод IP-адрес и его доменное имя если оно есть
private function getIpAndDomainName($ip)
	{
	$domainName = self::getXmlObject()->obtainDomainName($ip);
	return ($domainName) ? $ip.'<br>'.$domainName: $ip;
	}

//получить решение аналитика по коду
private function getAnalystSolution($code)
	{
	$arraySolutuin = array('1' => '<span style="font-weight: bold;">компьютерная атака</span>', 
						   '2' => 'ложное срабатывание',
						   '3' => '<span style="color: red;">отсутствует сетевой трафик</span<',
						   '4' => '<span style="color: red;">сетевой трафик утерян</span>');
	return (array_key_exists($code, $arraySolutuin)) ? $arraySolutuin[$code] : '<span style="font-style: italic;">анализ не проводился</span>';
	}

//функция вывода таблицы с данными
private function showTableInformation()
	{
	$pathDirectoryRoot = $this->getPathString();
	?>
	<!-- для выделения строки в таблице -->
	<style>
	.tableHover tr:hover {
		background-color: #E0FFFF; }
	</style>
	<div style="border-width: 1px; border-style: solid; width: 958px; border-color: #B7DCF7;">			
	<form name="showDocx" method="POST" enctype="multipart/form-data" target="_blank" action="<?= $pathDirectoryRoot.'/get_docx.php'; ?>">
		<table class="tableHover" id="elTableSearch" border="0" width="958px" cellpadding="2">
			<?php echo "<tr ".COLOR_HEADER.">"; ?>
				<th class="tableHeader" style="width: 50px;">№</th>
				<th class="tableHeader" style="width: 30px;">
					<input type="image" name="showDocumentWord" src="/<?= self::getDirectoryRoot() ?>/img/doc_word.png" title="получить документ Word">
					<input type="checkbox" id="majorCheckBox" title="отметить всё">						
				</th>
				<th class="tableHeader" style="width: 95px;">
					начальное<br>дата/время<br>
					<img name="sortValue" orderInTable="3" src="/<?= self::getDirectoryRoot() ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="сортировать по начальному времени"/>
				</th>
				<th class="tableHeader" style="width: 95px;">
					аналитик
				</th>
				<th class="tableHeader" style="width: 115px;">
					IP-адрес(-а) источник(-ов)
				</th>
				<th class="tableHeader" style="width: 170px;">
					страна<br>
					<img name="sortValue" orderInTable="6" src="/<?= self::getDirectoryRoot() ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по стране источнику"/>
				</th>
				<th class="tableHeader" style="width: 125px;">
					IP-адрес назначения<br>
					<img name="sortValue" orderInTable="7" src="/<?= self::getDirectoryRoot() ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по IP-адресу назначения"/>
				</th>
				<th class="tableHeader" style="width: 105px;">
					решение<br>аналитика<br>
					<img name="sortValue" orderInTable="8" src="/<?= self::getDirectoryRoot() ?>/img/buttonblue_down.png" style="cursor: pointer; vertical-align: middle;" title="группировать по принятому аналитиком решению"/>				
				</th>
				<th class="tableHeader" style="width: 120px;">
					№ письма в<br>18 Центр ФСБ России
				</th>
			</tr>
			<?php
			foreach($this->queryResult as $key => $arrayAllInformation){
				echo "<tr bgcolor=".color().">";
				?>
<!-- № воздействия -->
					<td class="tableHeader" style="text-align: center;">
					<?= $key ?>
					</td>
					<td class="tableHeader" style="text-align: center;">
						<input type="image" id="showAllInformation" name="showAllInformationImg" value="<?= $key ?>" src="/<?= self::getDirectoryRoot() ?>/img/eye.png" style="cursor: pointer;" title="просмотреть полную информацию о компьютерном воздействии">
						<?php 
						//если компьютерная атака выводим checkbox
						if($arrayAllInformation['solution'] == '1'){
							echo '<input type="checkbox" name="showMailDocx[]" value="'.$key.'">';
							}
						?>
					</td>
					<?php
                    $readXMLSetup = new ReadXMLSetup;
					foreach($arrayAllInformation as $nameField => $valueField){
						echo '<td class="tableHeader" style="text-align: center; padding: 0 5px">';
						//выводим ipSrc или страну принадлежности IP-адреса
						if(is_array($valueField)){
							if($nameField == 'ipSrc'){
								echo $this->getListIpSrc($valueField);
								}
							elseif($nameField == 'country'){
								echo $this->getListCountry($valueField);
								}
							} 
						elseif($nameField == 'solution'){
							echo $this->getAnalystSolution($valueField);
							}
						elseif($nameField == 'ipDst'){
							echo $this->getIpAndDomainName($valueField);
							} 
						elseif($nameField == 'mailNumber'){
							echo ($valueField == '99999999') ? 'письмо не отправлялось' : $valueField;
							}
                        elseif($nameField == 'loginName'){
                            $arrayName = mb_split('\s', $readXMLSetup->usernameFIO($valueField));
                            echo $arrayName[0].' '.mb_substr($arrayName[1], 0, 1, 'UTF-8').'. '.mb_substr($arrayName[2], 0, 1, 'UTF-8').'.';
                        } else {
                            echo $valueField;
                        }
						echo '</td>';
						}
					?>
				</tr>
				<?php
				}
				?>
		</table>
	</form>
	<!-- скрытая форма -->
		<form name="showAllInfo" method="POST" enctype="multipart/form-data" target="_blank" action="<?= $pathDirectoryRoot.'/showAllInformationForIncidents.php'; ?>">
			<input type="hidden" name="showAllInformation">
		</form>	
	</div>
	<input id="areaJScript" type="hidden" value="
		//функция скрытой формы
		function shadowForm(element){
			document.showAllInfo.showAllInformation.value = element.value;
			document.showAllInfo.submit();
			event.preventDefault();
		}
		//функция отмечающая все checkbox при выборе основного checkbox
		function choiceAllCheckBox(){
			var majorCheckBox = document.getElementById('majorCheckBox');
			var itemMailDocx = document.getElementsByName('showMailDocx[]');
			if(majorCheckBox.checked == true){
				checked(itemMailDocx, true);
			} else {
				checked(itemMailDocx, false);			
			}
			function checked(itemElements, value){
				for(var i in itemElements){
					itemElements[i].checked = value;
				}
			};
		}
		//функция сортировки значений таблицы
		function sortTableValue(elem){
			var increment;
			var imgUpDown = elem.src.split('/').pop();
			if((imgUpDown.indexOf('up', 0)) == -1){
				elem.src = elem.src.replace('buttonblue_down.png', 'buttonblue_up.png');
				increment = true;
			} else {
				elem.src = elem.src.replace('buttonblue_up.png', 'buttonblue_down.png');
				increment = false;
			}
			var id = elem.getAttribute('orderInTable') - 1;
			//сортируем таблицу
			var elTable = document.getElementById('elTableSearch');
			var arraySort = [];
			for(var b = 1; b < elTable.rows.length; b++){
				arraySort[b-1] = [];
				if(elTable.rows[b].getElementsByTagName('TD').item(id) !== null){
					//получаем содержимое выбранного столбца (текст в теге td)
					arraySort[b-1][0] = elTable.rows[b].getElementsByTagName('TD').item(id).innerHTML;
					//получаем содержимое строки (всю информацию по каждому пользователю)
					arraySort[b-1][1] = elTable.rows[b];
					}
				}
			//сортируем значения
			arraySort.sort();	
			if(!increment){	
				arraySort.reverse();
				}
			//добавляем в таблицу отсортированные значения
			for(var j = 0; j < arraySort.length; j++){
				if(arraySort[j][1] !== undefined){
					//добавляем потомка
					elTable.appendChild(arraySort[j][1]);
					//проверяем на четность
					if(j % 2 === 0){
						arraySort[j][1].setAttribute('bgcolor', '#D1E7F7');
						} else {
						arraySort[j][1].setAttribute('bgcolor', '#B7DCF7');	
						}
					}
				}
			increment = !increment;
		}

		//добавляем обработчики
		function loading(){
			//для вывода страницы с полной информацией по компьютерному воздействию
			var elemShowAllInformation = document.getElementsByName('showAllInformationImg');
			for(var i in elemShowAllInformation){
				if(elemShowAllInformation[i].nodeType == 1 && elemShowAllInformation[i].tagName == 'INPUT'){
					elemShowAllInformation[i].addEventListener('click', function(){shadowForm(this)}, false);
				}
			}
			
			//для выбора всех checkbox
			document.getElementById('majorCheckBox').addEventListener('click', choiceAllCheckBox, false);
			
			//для сортировки значений таблицы
			var elemSortValue = document.getElementsByName('sortValue');
			for(var j in elemSortValue){
				if(elemSortValue[j].nodeType == 1 && elemSortValue[j].tagName == 'IMG'){
					elemSortValue[j].addEventListener('click', function(){sortTableValue(this)}, false);
				}
			}
		}
		">
	<?php
	}

//функция вывода найденной информации
public function viewInformation()
	{
	$page = '';
	//проверяем количества найденных данных
	if($this->countFindResultString() == 0){
		echo ShowMessage::informationNotFound("компьютерных воздействий<br>не найдено", 50);	
		exit();
		} 
	
	//выводим таблицу с данными
	$page .= $this->showTableInformation();
	$page .= '<div style="text-align: center;"><p>всего найдено компьютерных воздействий - '.$this->countFindResultString().'</p></div>';
	return $page;
	}
}
?>