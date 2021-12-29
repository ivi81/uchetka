<?php
header('Content-Type: text/html, charset=utf8');
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Учет компьютерных воздействий (Дежурный) v1.1</title>
		<?php 
		//получаем корневую директорию сайта
		$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
		?>
		<link rel="stylesheet" type="text/css" href="/<?= $array_directory[1] ?>/css/style_index.css"></link>
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

//объект для работы с поставленными задачами
$TaskProcessWorker = new TaskProcessWorker(new DBOlink(), new ReadXMLSetup);
TablesIndexInformation::$directory = $array_directory[1];
?>

<!-- основная цветовая подложка -->
	<div id="majorArea" class="majorArea">	

<!-- "шапка" страницы -->
		<div class="headArea">
			<span class="headText_1">учет компьютерных воздействий</span>
			<span style="position: absolute; right: 0px; top: 50px; opacity: 0.9; ">
				<img src="/<?= $array_directory[1] ?>/img/flagrf.jpg" width="1000" height="100" alt="" >
			</span>
		</div>
	
<!-- цветная полоска под основной шапкой -->
		<div class="headMenuArea">
			<span class="headText_2"><!-- оперативный дежурный --></span>
		</div> 
<!-- основное меню страницы -->
		<div style="position: relative; left: -15px; top: -16px; z-index: 3;">	
			<?php $Menu = new GetMenu($menu_worker); ?>
		</div><br> 

<!-- /// блок с информацией о дежурном с информационными иконками /// -->
		<div style="position: relative; top: 0px; left: 5px; width: 990px; height: 60px; clean: both; border-radius: 3px; background: #FFF;">
			<?php
			$InfoPanelUsers = new InfoPanelUsers('оперативный дежурный', $checkAuthorization->userLogin, $array_directory[1]);
			//количество непрочитанных сообщений
			$count_message = ReviewMessage::showReviewMessage();
			//проверяем есть ли новая задача
			$newTask = $TaskProcessWorker->checkNewTask();
			$InfoPanelUsers->setIconsName(array('messageChat' => array('count' => intval($count_message), 'active' => '/worker/process/simply_chat.php', 'img' => '/img/mail_alert.png', 'message' => 'новое сообщение'),
												'messageTask' => array('count' => intval($newTask), 'active' => '/worker/process/solve_problem.php', 'img' => '/img/tasks.png', 'message' => 'новая задача')));
			//получаем информационную панель
			$InfoPanelUsers->showInfoPanel();
			?>
		</div>
				
<!-- ОСНОВНОЙ КОНТЕНТ -->
		<div class="content">
		<?php						 
			//
		//если страница соответствует основной странице выводим дополнительную информацию
			//
		if($_SERVER['REQUEST_URI'] === "/{$array_directory[1]}/worker/index.php"){
		?>
<!-- ОБЩИЙ БЛОК для блока краткой информации и блока основного контента -->
		<div style="position: relative; top: 0px; left: 0px; display: table;">		
<!-- ***** поле основных таблиц ***** -->
			<div style="position: relative; top: 0px; left: 0px; width: 755px; float: left;  display: table; vertical-align: middle; border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">

<!-- таблица КОМПЬЮТЕРНЫЕ АТАКИ которые необходимо дополнить доп. информацией -->
				<div style="position: relative; top: 10px; left: 10px; margin: 10px 0px;">
					<?php TablesIndexInformation::showTableEmptySpaceIncident($GeoIP, $checkAuthorization->userId); ?> 
				</div>

<!-- таблица компьютерных воздействий сетевой трафик которых не обнаружен -->
				<div style="position: relative; top: 10px; left: 10px; margin: 10px 0px;">
					<?php TablesIndexInformation::showTableNoNetTraffic($GeoIP, $checkAuthorization->userId); ?> 
				</div>

<!-- таблица компьютерных воздействий ожидающих анализа -->
				<div style="position: relative; top: 10px; left: 10px; margin: 10px 0px;">
<!-- текст -->			
					<span style="position: relative; top: 0px; left: 220px; font-size: 12px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #000099;">
						компьютерные воздействия ожидающие анализа			
					</span>
					<div style="border-width: 1px; border-style: solid; width: 735px; border-color: #B7DCF7;">				
						<table border="0" width="735px" cellpadding="2">
							<?php echo "<tr ".COLOR_HEADER.">"; ?>
								<th style="width: 40px; font-size: 12px; font-family: 'Times New Roman', serif;"></th>
								<th style="width: 125px; font-size: 12px; font-family: 'Times New Roman', serif;">начальное<br>дата/время</th>
								<th style="width: 125px; font-size: 12px; font-family: 'Times New Roman', serif;">конечное<br>дата/время</th>
								<th style="width: 130px; font-size: 12px; font-family: 'Times New Roman', serif;">IP-адреса источники</th>
								<th style="width: 185px; font-size: 12px; font-family: 'Times New Roman', serif;">страна</th>
								<th style="width: 130px; font-size: 12px; font-family: 'Times New Roman', serif;">IP-адрес назначения</th>
							</tr>
							<?php TablesIndexInformation::showTableWaitInformation($GeoIP); ?>
						</table>
					</div>
				</div>

<!-- таблица 10 последних ложных воздействий -->
				<div style="position: relative; top: 10px; left: 10px; margin: 10px 0px;">
<!-- текст -->
					<span style="position: relative; top: 0px; left: 165px; font-size: 12px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #000099;">
						10 последних компьютерных воздействий признанных ложными			
					</span>
					<div style="border-width: 1px; border-style: solid; width: 735px; border-color: #B7DCF7;">				
						<table border="0" width="735px" cellpadding="2">
							<?php echo "<tr ".COLOR_HEADER.">"; ?>
								<th style="width: 20px; font-size: 12px; font-family: 'Times New Roman', serif;">№</th>
								<th style="width: 75px; font-size: 12px; font-family: 'Times New Roman', serif;">начальное<br>дата/время</th>
								<th style="width: 75px; font-size: 12px; font-family: 'Times New Roman', serif;">конечное<br>дата/время</th>
								<th style="width: 100px; font-size: 12px; font-family: 'Times New Roman', serif;">IP-адреса источники</th>
								<th style="width: 180px; font-size: 12px; font-family: 'Times New Roman', serif;">страна</th>
								<th style="width: 100px; font-size: 12px; font-family: 'Times New Roman', serif;">IP-адрес назначения</th>
								<th style="width: 70px; font-size: 12px; font-family: 'Times New Roman', serif;">№ сигнатуры</th>
								<th style="width: 120px; font-size: 12px; font-family: 'Times New Roman', serif;">Ф.И.О.</th>
							</tr>
							<?php TablesIndexInformation::showTableFalseIncident($GeoIP); ?>
						</table>
					</div><br>
				</div>
			</div>

<!-- /// общий блок для краткой информации /// -->
			<div style="position: relative; top: 0; left: 5px; float: left;">
<!-- вывод области даты и времени -->
				<div style="position: relative; top: 0; left: 0; width: 200px; z-index: 10;">
					<?php $ShowUserDate = new ShowUserDate(); ?>
				</div>
<!-- блок с КРАТКОЙ информацией по воздействиям и присутствующим на сайте пользователям -->	
				<?php
				$BlockShortInformation = new BlockShortInformation(__DIR__);
				$BlockShortInformation->showBlockShortInformation();
				?>
			</div>
		</div><br>
	</div>

	<?php
	include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
	}
//закрываем соединения с БД
$DBO->onConnectionDB();
?>

<!-- кнопка вверх вниз -->
	<div id="updown"></div>	
<!-- кнопка вверх вниз -->	
	<script type="text/javascript" src='/<?= $array_directory[1] ?>/js/scrollUp.js'></script>
<!-- проверка даты (вывод плюшек к праздникам) -->		
	<script src="/<?= $array_directory[1] ?>/js/checkDate.js"></script>
	<script type="text/javascript"> window.onload = checkDate('<?= $array_directory[1] ?>'); </script>	
	</body>
</html>