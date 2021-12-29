<?php
header('Content-Type: text/html, charset=utf8');
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Учет компьютерных воздействий (Руководство) v1.1</title>
		<?php 
		//получаем корневую директорию сайта
		$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 
		?>
		<link rel="stylesheet" type="text/css" href="/<?= $array_directory[1] ?>/css/style_index.css"></link>
		<style>
		ul{	padding: 0;
			margin-left: 25px;
			}
		</style>
	</head>
	<body>
<?php
session_start();

//подключаем config
require_once ($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/config/config.php");

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

//проверяем наличие необходимых таблиц БД (если их нет создаем)
$CreateTables = new CreateTables();

//объект для подключения к БД
$DBO = new DBOlink();

//объект БД GeoIP
$GeoIP = new GeoIP();

//объект для чтения файла setup_site.xml
$ReadXMLSetup = new ReadXMLSetup;
TablesIndexInformation::$directory = $array_directory[1];
?>

<!-- основная цветовая подложка -->
	<div id="particl" class="majorArea">	

<!-- "шапка" страницы -->
		<div id="majorArea" class="headArea">
			<span class="headText_1">учет компьютерных воздействий</span>
			<span style="position: absolute; right: 0px; top: 50px; opacity: 0.9; ">
				<img src="/<?= $array_directory[1] ?>/img/flagrf.jpg" width="1000" height="100" alt="" >
			</span>
		</div>
	
<!-- цветная полоска под основной шапкой -->
		<div class="headMenuArea">
			<span class="headText_2">руководство</span>
		</div> 

<!-- основное меню страницы -->
		<div style="position: relative; left: 120px; top: -16px; z-index: 3;">	
			<?php $Menu = new GetMenu($menu_major); ?>
		</div><br> 

<!-- основной блок -->
		<div class="content">
	<?php
		if($_SERVER['REQUEST_URI'] === "/{$array_directory[1]}/major/index.php"){
	?>
<!-- общий блок для блока краткой информации и блока основного контента -->
			<div style="position: relative; top: 0px; left: 0px;">
							
<!-- общий блок для краткой информации -->
				<div style="position: absolute; top: 2px; left: 755px; display: inline-block;">
<!-- вывод области даты и времени -->
					<div style="position: relative; top: 0px; left: 0px; width: 200px; z-index: 10;">
						<?php $ShowUserDate = new ShowUserDate(); ?>
					</div>
<!-- блок с КРАТКОЙ информацией по воздействиям и присутствующим на сайте пользователям -->	
					<?php 
					$BlockShortInformation = new BlockShortInformation(__DIR__);
					$BlockShortInformation->showBlockShortInformation();
					?>				
				</div> 
<!-- поле основной информации -->
				<div style="position: relative; top: 0; left: 5px; display: inline-block; z-index: 10; width: 745px; min-height: 485px; border-radius: 3px; box-shadow: inset 0 0 8px 0px #B7DCF7;">
<!-- таблица неподготовленных писем по компьютерным атакам -->
					<div style="position: relative; top: 0; left: 10px; width: 735px; margin-top: 15px;">
						<?php TablesIndexInformation::showTableEmptySpaceIncident($GeoIP, $checkAuthorization->userId); ?>
					</div>
<!-- таблица компьютерных воздействий ожидающих анализа -->
					<div style="position: relative; top: 0; left: 10px; width: 735px; margin-top: 10px;">
						<?php TablesIndexInformation::showTableWaitAnalysis($GeoIP, $checkAuthorization->userId); ?>
					</div>
<!-- таблица компьютерных воздействий сетевой трафик которых не обнаружен -->
					<div style="position: relative; top: 0; left: 10px; width: 735px; margin-top: 10px;">
						<?php TablesIndexInformation::showTableNoNetTraffic($GeoIP, $checkAuthorization->userId); ?> 
					</div>
<!-- таблица последних подготовленных писем (количество задается пользователем) -->
					<div style="position: relative; top: 0; left: 10px; width: 735px; margin-top: 10px;">
						<?php 
						function showPrepareMail($dir)
							{
							?>
							<div style="position: relative; top: 0px; left: 11px; font-size: 14px; font-family: 'Times New Roman', serif;"> 
								<span name="l10" onclick="getMailListNumber(this, 10)" style="font-size: 14px; font-family: 'Times New Roman', serif; text-decoration: underline; cursor: pointer; color: #000080;">10</span>
								<span name="l20" onclick="getMailListNumber(this, 20)" style="font-size: 14px; font-family: 'Times New Roman', serif; text-decoration: underline; cursor: pointer; color: #000080;">20</span>
								<span name="l30" onclick="getMailListNumber(this, 30)" style="font-size: 14px; font-family: 'Times New Roman', serif; text-decoration: underline; cursor: pointer; color: #000080;">30</span>
								номера последних подготовленных писем
							</div>
							<div id="mailListNumber" style="display: none;">
<!-- DIV для списка -->
								<div id="divList" style="text-align: left; float: left; width: 165px; font-family: 'Times New Roman', serif;"></div>
<!-- DIV для дополнительной информации -->
								<div id="divInform" style="float: left; width: 555px; padding-bottom: 7px;"></div><br>
							</div>
<!-- подключаем скрипт для работы с XMLHttpRequest -->
							<script type="text/javascript" src='/<?= $dir ?>/js/objectXMLHttpRequest.js'></script>
							<script type="text/javascript">
							function getMailListNumber(elem, countMail){
								var div = document.getElementById('mailListNumber');
								delChild = function(){
									var divInform = document.getElementById('divInform');
									if(divInform.childNodes.length > 0){
										var divChild = divInform.childNodes;
										for(var a in divChild){
											if(divChild[a].nodeType == 1 && divChild[a].nodeName == 'DIV'){
												divChild[a].parentNode.removeChild(divChild[a]);
												}
											}
										}
									}
								if(div.nodeType == 1 && div.nodeName == 'DIV'){
									delChild();
									//Ajax запрос
									var newObjectXMLHttpRequest = new objectXMLHttpRequest("POST", "/<?= $dir ?>/major/process/ajax_process.php", elem, 'queryMailMajor=' + countMail);
									newObjectXMLHttpRequest.sendRequest();	
									div.style.display = 'block';
									//убираем подчеркивание для выбранного элемента
									elem.style.textDecoration = '';
									//подчеркиваем все остальные
									var span = div.parentNode.firstChild.nextSibling.childNodes;
									for(var s in span){
										if(span[s].nodeType == 1 && span[s].nodeName == 'SPAN'){
											if(span[s] != elem){
												span[s].style.textDecoration = 'underline';
											}
										}
									}
								}
							}
							</script>
							<?php
							}
						showPrepareMail($array_directory[1]);
						?> 
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
	}
	?>
	
<!-- кнопка вверх вниз -->
	<div id="updown"></div>	
<!-- кнопка вверх вниз -->	
	<script type="text/javascript" src="/<?= $array_directory[1] ?>/js/scrollUp.js"></script>
<!-- проверка даты (вывод плюшек к праздникам) -->		
	<script src="/<?= $array_directory[1] ?>/js/checkDate.js"></script>
	<script type="text/javascript"> window.onload = checkDate('<?= $array_directory[1] ?>'); </script>

	</body>
</html>