<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Учет компьютерных воздействий (Аналитик) v1.1</title>
		<?php 
		//получаем корневую директорию сайта
		$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));
		?>
<!-- подключаем общий файл стилей -->
		<link rel="stylesheet" type="text/css" href="/<?= $array_directory[1] ?>/css/style_index.css"></link>
<!-- подключение файла стилей постраничных ссылок -->
		<link rel="stylesheet" type="text/css" href="/<?= $array_directory[1] ?>/css/style_searchPageOutput.css"></link>
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
$TaskProcessWorker = new TaskProcessWorker(new DBOlink, new ReadXMLSetup);
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
			<span class="headText_2"><!-- аналитик --></span>
		</div> 

<!-- основное меню страницы -->
		<div style="position: relative; left: 80px; top: -16px; width: 800x; z-index: 3;">	
			<?php $Menu = new GetMenu($menu_analyst); ?>
		</div><br>
		
<!-- /// блок с информацией об аналитике с информационными иконками /// -->
		<div style="position: relative; top: 0px; left: 5px; width: 990px; height: 60px; clean: both; border-radius: 3px; background: #FFF;">
			<?php
			$InfoPanelUsers = new InfoPanelUsers('аналитик', $checkAuthorization->userLogin, $array_directory[1]);
			//количество непрочитанных сообщений
			$count_message = ReviewMessage::showReviewMessage();
			//проверяем есть ли новая задача
			$newTask = $TaskProcessWorker->checkNewTask();
			$InfoPanelUsers->setIconsName(array('messageChat' => array('count' => intval($count_message), 'active' => '/analyst/process/simply_chat.php', 'img' => '/img/mail_alert.png', 'message' => 'новое сообщение'),
												'messageTask' => array('count' => intval($newTask), 'active' => '/analyst/process/solve_problem.php', 'img' => '/img/tasks.png', 'message' => 'новая задача')));
			//получаем информационную панель
			$InfoPanelUsers->showInfoPanel();
			?>
		</div> 

<!-- рамка для поля "информация о аналитике" -->	
			<style>
			.informationWorker {
		 		position: absolute; top: 20px; left: 20px; z-index: 10;
		 		width: 745px;
		 		height: 100px;
		 		vertical-align: middle;
	 	 		border-radius: 3px;
		 		background: #FFF; 
		 		box-shadow: inset 0 0 8px 0px #B7DCF7; }
			</style>

<!-- ОСНОВНОЙ КОНТЕНТ -->
	<div class="content">
		<?php						 
			//
		//если страница соответствует основной странице выводим дополнительную информацию
			//
		if($_SERVER['REQUEST_URI'] === "/{$array_directory[1]}/analyst/index.php"){
		?>
<!-- ОБЩИЙ БЛОК для блока краткой информации и блока основного контента -->
		<div style="position: relative; top: 0px; left: 0px; display: table;">		
	
<!-- ***** поле основных таблиц ***** -->
			<div style="position: relative; top: 0px; left: 0px; width: 755px; float: left; min-height: 300px; display: table; vertical-align: middle;	border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">
<!-- таблица компьютерных воздействий ожидающих анализа -->
				<div style="position: relative; top: 10px; left: 15px; margin: 10px 0px;">
					<?php TablesIndexInformation::showTableWaitAnalysis($GeoIP, $checkAuthorization->userId); ?>
				</div>
<!-- таблица 10 последних проанализированных инцидентов -->
				<div style="position: relative; top: 10px; left: 15px; margin: 10px 0px;">
					<?php TablesIndexInformation::showTableAnalysisIncident($GeoIP); ?>
				</div><br>
			</div>

<!-- /// общий блок для краткой информации /// -->
			<div style="position: relative; top: 0px; left: 5px; float: left;">
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