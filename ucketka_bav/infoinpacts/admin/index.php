<?php
header('Content-Type: text/html, charset=utf8');
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Учет компьютерных инцидентов (Администратор) v1.0</title>
		<?php 
		//получаем корневую директорию сайта
		$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 
		?>
		<link rel="stylesheet" type="text/css" href="/<?= $array_directory[1] ?>/css/style_index.css"></link>
		<style>
		ul {
			padding: 0;
			margin-left: 25px;
			}
		a {
			text-decoration: none;
			}

		a:hover {
			text-decoration: underline;
			}

		.listColor {
			color: #0000CD;
			}

		.list {
			line-height: 20px;
			text-align: left;
			font-size: 10px;
			font-weight: bold;
			font-family: 'Times New Ronam', serif;
			text-transform: uppercase;
			letter-spacing: 1px;
			display: block;			
			color: #1C1C1C;
			}
	
		.listOne {
			line-height: 20px;
			text-align: left;
			font-size: 10px;
			font-weight: bold;
			font-family: 'Times New Ronam', serif;
			text-transform: uppercase;
			letter-spacing: 1px;
			}
		
		.listTwo {
			line-height: 15px;
			text-align: left;
			font-size: 8px;
			font-family: 'Times New Ronam', serif;
			text-transform: uppercase;
			display: none;
			}
		</style>
		
		<script type="text/javascript" >
		//функция МЕНЮ присваивания элементам списка изображения + или - (при загрузке страницы) и активации функции aClick по клику
		function setIng(){
			var menu = document.getElementById('menu');
			//получаем список всех элементов с тегом LI 
			var allLIs = menu.getElementsByTagName('LI');
			for(var i = 0; allLIs.length > i; i++){
				//все ul в li
				var allULs = allLIs[i].getElementsByTagName('UL');
				if(allULs.length > 0){
					//если элемент css display пустой +
					if(allLIs[i].style.display == ''){
						allLIs[i].style.listStyleImage = "url('/<?= $array_directory[1] ?>/img/plus.png')";
						}else {
						allLIs[i].style.listStyleImage = "url('/<?= $array_directory[1] ?>/img/minus.png')";
						}
					}
				}
			//все элементы с тего A
			var allA = menu.getElementsByTagName('A');
			//присваиваем им функцию
			for(var i = 0; allA.length > i; i++){
				allA[i].addEventListener('click', aClick);
				}
			}
			
		//функция МЕНЮ сворачивания и разворачивания элементов списка
		function aClick(a){
			//получаем родительский элемент ссылки
			var li = a.srcElement.parentNode;
			//если элемент содержит элементы UL
			if(li.getElementsByTagName('UL').length > 0){
				var uls = li.getElementsByTagName('UL');
				var lis = uls[0].getElementsByTagName('LI');
				for(var i = 0; lis.length > i; i++){
					//если элемент display css пуст присваиваем ему значение 'block' и изображение с минусом
					if(lis[i].style.display == ''){
						lis[i].style.display = 'block';
						li.style.listStyleImage = "url('/<?= $array_directory[1] ?>/img/minus.png')";
						} else {
						lis[i].style.display = '';
						li.style.listStyleImage = "url('/<?= $array_directory[1] ?>/img/plus.png')";
						}
					}
				//запрещаем переход по ссылке (для элементов содержащих список)
				a.preventDefault();
				}
			}
		</script>		
		
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
?>

<!-- основная цветовая подложка -->
	<div class="majorArea">	

<!-- "шапка" страницы -->
		<div class="headArea">
			<span class="headText_1">учет компьютерных инцидентов</span>
			<span style="position: absolute; right: 0px; top: 50px; opacity: 0.9; ">
				<img src="/<?= $array_directory[1] ?>/img/flagrf.jpg" width="1000" height="100" alt="" >
			</span>
		</div>
	
<!-- цветная полоска под основной шапкой -->
		<div class="headMenuArea">
			<span class="headText_2">администратор информационного ресурса</span>
		</div> 

<!-- основной блок -->
		<div class="content">

<!-- общий блок для блока краткой информации и блока основного контента -->
			<div style="position: relative; top: 35px; left: 0px;">

<!-- общий блок (дата и время и меню) -->
				<div style="position: absolute; top: 0px; left: 5px; display: inline-block;">
<!-- вывод области даты и времени -->
					<div style="position: relative; top: 0px; left: 0px; width: 200px; z-index: 10;">
						<?php $ShowUserDate = new ShowUserDate(); ?>
					</div>
					
<!-- блок меню -->	
					<div id="menu" style="position: relative; top: 5px; left: 0px; z-index: 10; width: 200px; min-height: 360px; border-radius: 3px; background: #F0FFFF; box-shadow: inset 0 0 8px 0px #B7DCF7; ">
					<br>
					<ul style="margin-left: 35px;">
<!-- главная -->
						<li class="list">
							<a href="/<?= $array_directory[1] ?>/admin/index.php">
								<span style="color: #990000;">главная</span>
							</a>
						</li>
<!-- сенсора -->
						<li class="list listColor">
							<a href="/<?= $array_directory[1] ?>/admin/process/sensors.php" type="добавление, удаление пользователей и изменение их данных">
								сенсора
							</a>
						</li>
<!-- пользователи -->
						<li class="list listColor">
							<a href="/<?= $array_directory[1] ?>/admin/process/users.php" type="добавление, удаление пользователей и изменение их данных">
								пользователи
							</a>
						</li>
<!-- доменные имена -->
						<li class="list listColor">
							<a href="/<?= $array_directory[1] ?>/admin/process/domain_name.php" type="добавление и удаление доменных имен">
								доменные имена
							</a>
						</li>
<!-- компьютерные атаки -->
						<li class="list listColor">
							<a href="/<?= $array_directory[1] ?>/admin/process/control_information.php?id=1">
								компьютерные атаки
							</a>
						</li>					
<!-- информация -->				
						<li class="listOne"><a href="#">информация</a>
							<ul>
								<li class="listTwo">
									<a href="/<?= $array_directory[1] ?>/admin/process/control_information.php?id=3">
										о типах IP списках
									</a>
								</li>
								<li class="listTwo">
									<a href="/<?= $array_directory[1] ?>/admin/process/control_information.php?id=4">
										о сигнатурах
									</a>
								</li>
								<li class="listTwo">
									<a href="/<?= $array_directory[1] ?>/admin/process/control_information.php?id=5">
										о сенсорах
									</a>
								</li>
							</ul>
						</li>
<!-- обновление таблиц -->				
						<li class="listOne"><a href="#">обновление таблиц</a>
							<ul>
								<li class="listTwo">
									<a href="/<?= $array_directory[1] ?>/admin/process/control_tables_list.php?id=1">
										списки IP-адресов
									</a>
								</li>
								<li class="listTwo">
									<a href="/<?= $array_directory[1] ?>/admin/process/control_tables_list.php?id=2">
										данные GeoIP
									</a>
								</li>
								<li class="listTwo">
									<a href="/<?= $array_directory[1] ?>/admin/process/control_tables_list.php?id=3">
										правила СОА Snort
									</a>
								</li>
								<li class="listTwo">
									<a href="/<?= $array_directory[1] ?>/admin/process/control_tables_list.php?id=4">
										сенсора
									</a>
								</li>
							</ul>
						</li>
<!-- выход -->
						<li class="list">
							<a href="/<?= $array_directory[1] ?>/index.php?Quit=quit">
								<span style="color: #990000;">выход</span>
							</a>
						</li> 
					</ul>
					<br>
					</div>
				</div>
			<script> window.onload = setIng; </script>  
			<?php
			if($_SERVER['REQUEST_URI'] === "/{$array_directory[1]}/admin/index.php"){
			?>			
<!-- поле основной информации -->
				<div style="position: relative; top: 0px; left: 210px; display: inline-block; z-index: 10; width: 745px; min-height: 500px; border-radius: 3px; box-shadow: inset 0 0 8px 0px #B7DCF7;">
					<span style="position: relative; top: 0px; left: 10px;">
					<?php
					$AdministrationUsers = new AdministrationUsers();
					$AdministrationUsers->infoUsers();
					?>
					</span>
					<br>
				</div>
			</div><br><br> 
			<?php	
				include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
				} 
			?>
<!-- кнопка вверх вниз -->
	<div id="updown"></div>	
<!-- кнопка вверх вниз -->	
	<script type="text/javascript" src="/<?= $array_directory[1] ?>/js/scrollUp.js"></script>

	</body>
</html>