<?php

						/*+++++++++++++++++++++++++++++++++++++++++++*/
						/*		страница статистики (Аналитика)	 	 */
						/*						v0.1 23.12.2014 	 */
						/*+++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));

//подключаем основную, для аналитика страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/analyst/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

?>
<!-- подключаем скрипт для работы с XMLHttpRequest -->
<script type="text/javascript" src='/<?= $array_directory[1] ?>/js/objectXMLHttpRequest.js'></script>
<script type="text/javascript">
	//убрать подчеркивание для выбранного пункта меню
	function delUnderline(num){
		var elem = document.getElementById('item_' + num);
		if(elem.style && (elem.style.background != '')){
			elem.style.background = '';
			} else {
			elem.style.background = '#E6E6FA';
			}
		}
	//для горизонтальных вкладок меню
	function changeSelectTab(){
		var list = document.getElementById('lineTabs');
		var nodeList = list.childNodes;
		for(var i = 0; i < nodeList.length; i++){
			if(nodeList[i].nodeType == 1 && nodeList[i].nodeName == 'LI'){
				nodeList[i].onclick = new Function('selectTab(this)');
				}
			}
		}
	//активируем выделение на вкладке меню и подгружаем данные
	function selectTab(elem){
		unSelectTab(elem.parentNode);
		if(elem.firstChild.className == ''){
			elem.firstChild.className = 'active';
			getInfo(elem);
			}
		}
	//снимаем выделение с выбранной вкладке меню
	function unSelectTab(elem){
		var nodeList = elem.childNodes;
		for(var i = 0; i < nodeList.length; i++){
			if(nodeList[i].nodeType == 1 && nodeList[i].nodeName == 'LI'){
				nodeList[i].firstChild.className = '';
				}
			}
		}
</script>

<!-- ОБЩИЙ БЛОК для блока краткой информации и блока основного контента -->
	<div style="position: relative; top: 0; left: 0; width: 960px;">	
<!-- блок меню -->	
		<div id="menu" style="position: absolute; top: 0; left: 0; z-index: 10; width: 200px; min-height: 410px; background: #F8F8F8; display: inline-block;"><!-- border-radius: 3px; border: 1px solid #00C5Cb; -->
			<div style="width: 200px; text-align: center;">
				<img src="<?php echo '/'.$array_directory[1].'/img/Childish-Chart-Bar.png'; ?>">
				<br><span style="font-size: 16px; font-family: Verdana, serif; text-transform: uppercase; letter-spacing: 1.5px; color: #134345;">статистика</span>
			</div>
			<div style="position: relative; top: 10px; left: 10px; width: 180px;">
				<div id="item_0" style="font-size: 12px; font-family: Verdana, serif; text-transform: lowercase; line-height: 1.7em;">
					<a href="<?php echo '/'.$array_directory[1].'/analyst/process/statistics.php?typeMenu=0'; ?>" class="getInterval"><span style="color: #004345;">Пользователи</span></a></div>
				<div id="item_2" style="font-size: 12px; font-family: Verdana, serif; text-transform: lowercase; line-height: 1.7em;">
					<a href="<?php echo '/'.$array_directory[1].'/analyst/process/statistics.php?typeMenu=1'; ?>" class="getInterval"><span style="color: #004345;">Геопозиционирование</span></a></div>
				<div id="item_3" style="font-size: 12px; font-family: Verdana, serif; text-transform: lowercase; line-height: 1.7em;">
					<a href="<?php echo '/'.$array_directory[1].'/analyst/process/statistics.php?typeMenu=2'; ?>" class="getInterval"><span style="color: #004345;">Типы компьютерных атак</span></a></div>
				<div id="item_4" style="font-size: 12px; font-family: Verdana, serif; text-transform: lowercase; line-height: 1.7em;">
					<a href="<?php echo '/'.$array_directory[1].'/analyst/process/statistics.php?typeMenu=3'; ?>" class="getInterval"><span style="color: #004345;">Кол-во компьютерных атак</span></a></div>
				<div id="item_5" style="font-size: 12px; font-family: Verdana, serif; text-transform: lowercase; line-height: 1.7em;">
					<a href="<?php echo '/'.$array_directory[1].'/analyst/process/statistics.php?typeMenu=4'; ?>" class="getInterval"><span style="color: #004345;">Соотношение компьютерных атак</span></a></div>
			</div>
		</div>
<!-- поле основной информации -->
		<div style="position: relative; top: 0px; left: 205px; width: 755px;">
			<div style="position: relative; top: 0px; z-index: 10; width: 755px; min-height: 590px; border-radius: 3px; background: #FFF;">
			<?php
			$type = (isset($_GET['typeMenu']) && !empty($_GET['typeMenu']) && ($_GET['typeMenu'] != 0)) ? intval($_GET['typeMenu']) : 0;
			switch($type){
				//для пользователей
				case 0:
					?>
<!-- убираем подчеркивание в выбранного пункта меню и меняем его цвет -->
					<script type="text/javascript">delUnderline(0);</script>
<!-- поле диаграмм -->
					<div style="position: relative; left: 4px; top: 0; width: 745px; min-height: 510px; background: #ecf8ff; text-align: center;">
						<div style="padding-top: 10px;">
							<span style="font-size: 15px; font-family: 'Times New Roman', serif;">
							Информация о количестве добавленных компьютерных воздействиях
							</span>
						</div>
					<?php 
					$ShowStatisticsForUsers = new ShowStatisticsForUsers;
					$ShowStatisticsForUsers->showInformation(ShowStatisticsForUsers::ANALYST);
					 ?>
					</div>
					<?php
				break;
				//для геопозиционирования
				case 1:
					//убираем подчеркивание и меняем цвет
					?>
					<script type="text/javascript">delUnderline(2)</script>
<!-- меню со вкладками и информация GeoIp -->
					<div id="wrap">
						<ul id="lineTabs">
							<li><a href="#" name="tab_4" title="отношение ложных срабатываний к общему числу компьютерных воздействий">отношение</a></li>
							<li><a href="#" name="tab_3" title="информация за отрезок времени по странам и IP-адресам назначения">страна/дата</a></li>
							<li><a href="#" name="tab_2" title="информация по странам для IP-адресов назначения">по странам</a></li>
							<li><a href="#" name="tab_1" class="active" title="информация по IP-адресу назначения">по ip</a></li>
						</ul>
<!-- статистическая информация -->

<!-- вывод статистической информации по 'умолчанию' -->
						<div id="content">
							<?php FactoryObjectForAiaxResponse::getObjectForAjaxResponse('statisticsGeoIP')->getStatictics(ShowStatisticsGeoIP::DST_IP); ?>
						</div>
					</div>
					<script type="text/javascript">
					//навешиваем обработчик меню
					changeSelectTab();
					//получаем данные для выбранной вкладке
					function getInfo(elem){
						var content = document.getElementById('content');
						switch(elem.firstChild.name){
							//информация по IP-адресу назначения
							case 'tab_1':
								var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', "/<?= $array_directory[1] ?>/analyst/process/ajax_process.php", elem, 'queryStatistics=GeoIp_1');
								newObjectXMLHttpRequest.sendRequest();
							break;
							//информация по странам для IP-адресов назначения
							case 'tab_2':
								var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', "/<?= $array_directory[1] ?>/analyst/process/ajax_process.php", elem, 'queryStatistics=GeoIp_2');
								newObjectXMLHttpRequest.sendRequest();							
							break;
							//информация за отрезок времени по странам и IP-адресам назначения
							case 'tab_3':
								var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', "/<?= $array_directory[1] ?>/analyst/process/ajax_process.php", elem, 'queryStatistics=GeoIp_3');
								newObjectXMLHttpRequest.sendRequest();
							break;
							//информация по ложным компьютерным атакам
							case 'tab_4':
								var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', "/<?= $array_directory[1] ?>/analyst/process/ajax_process.php", elem, 'queryStatistics=GeoIp_4');
								newObjectXMLHttpRequest.sendRequest();
							break;
							}
						}
					</script>
					<?php
				break;
				//для типов КА
				case 2:
					//убираем подчеркивание и меняем цвет
					?><script type="text/javascript">delUnderline(3);</script>
<!-- меню со вкладками и информация по времени реагирования -->
					<div id="wrap">
						<ul id="lineTabs">
							<li><a href="#" name="tab_4">вкладка №4</a></li>
							<li><a href="#" name="tab_3">вкладка №3</a></li>
							<li><a href="#" name="tab_2">вкладка №2</a></li>
							<li><a href="#" name="tab_1" class="active">вкладка №1</a></li>
						</ul>
						<div id="content">
							<div style="text-align: center; font-size: 20px; font-family: 'Times New Roman', serif;">
								страница в разработке, вкладка №1
							</div>
						</div>
					</div>
					<script type="text/javascript">
					//навешиваем обработчик меню
					changeSelectTab();
					//получаем данные для выбранной вкладке
					function getInfo(elem){
						var content = document.getElementById('content');
						switch(elem.firstChild.name){
							case 'tab_1':
								content.innerHTML = '<div style="text-align: center; font-size: 20px; font-family: \'Times New Roman\', serif;">страница в разработке, вкладка №1</div>';
							break;
							case 'tab_2':
								content.innerHTML = '<div style="text-align: center; font-size: 20px; font-family: \'Times New Roman\', serif;">страница в разработке, вкладка №2</div>';
							break;
							case 'tab_3':
								content.innerHTML = '<div style="text-align: center; font-size: 20px; font-family: \'Times New Roman\', serif;">страница в разработке, вкладка №3</div>';
							break;
							case 'tab_4':
								content.innerHTML = '<div style="text-align: center; font-size: 20px; font-family: \'Times New Roman\', serif;">страница в разработке, вкладка №4</div>';
							break;
							}
						}
					</script>
					<?php
				break;
				//для кол-во КА
				case 3:
					//убираем подчеркивание и меняем цвет
					?>
					<script type="text/javascript">delUnderline(4);</script>
					<div style="text-align: center; font-size: 20px; font-family: 'Times New Roman', serif;">
						страница в разработке
					</div>
					<?php
				break;
				//для соотношения КА
				case 4:
					//убираем подчеркивание и меняем цвет
					?>
					<script type="text/javascript">delUnderline(5);</script>
					<div style="text-align: center; font-size: 20px; font-family: 'Times New Roman', serif;">
						страница в разработке
					</div>
					<?php
				break;
				default:
				//Erroe
				break;
				}
			?>
			</div>
		</div>
	</div>

<?php
include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>