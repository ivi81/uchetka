<?php

						/*+++++++++++++++++++++++++++++++++++++++++++*/
						/*		страница статистики (Руководство)	 */
						/*						v0.2 19.12.2014 	 */
						/*+++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));

//подключаем основную, для аналитика страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/major/index.php"); 

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
				<div id="item_0" style="font-size: 12px; font-family; 'Times New Roman', serif; text-transform: lowercase; line-height: 1.7em;">
					<a href="<?php echo '/'.$array_directory[1].'/major/process/statistics.php?typeMenu=0'; ?>" class="getInterval" title="общая информация">
						<span style="color: #004345;">Пользователи</span></a></div>
				<div id="item_1" style="font-size: 12px; font-family; 'Times New Roman', serif; text-transform: lowercase; line-height: 1.7em;">
					<a href="<?php echo '/'.$array_directory[1].'/major/process/statistics.php?typeMenu=1'; ?>" class="getInterval" title="статистика по дежурным и типам компьютерных атак">
						<span style="color: #004345;">Дежурные (доп. информация)</span></a></div>
				<div id="item_2" style="font-size: 12px; font-family; 'Times New Roman', serif; text-transform: lowercase; line-height: 1.7em;">
					<a href="<?php echo '/'.$array_directory[1].'/major/process/statistics.php?typeMenu=2'; ?>" class="getInterval" title="статистика времени реогирования на компьютерные воздействия">
						<span style="color: #004345;">Время реагирования</span></a></div>
				<div id="item_3" style="font-size: 12px; font-family; 'Times New Roman', serif; text-transform: lowercase; line-height: 1.7em;">
					<a href="<?php echo '/'.$array_directory[1].'/major/process/statistics.php?typeMenu=3'; ?>" class="getInterval" title="статистика по геопозиционированию">
						<span style="color: #004345;">Геопозиционирование</span></a></div>
				<div id="item_4" style="font-size: 12px; font-family; 'Times New Roman', serif; text-transform: lowercase; line-height: 1.7em;">
					<a href="<?php echo '/'.$array_directory[1].'/major/process/statistics.php?typeMenu=4'; ?>" class="getInterval" title="статистика по типам компьютерных атак">
						<span style="color: #004345;">Типы компьютерных атак</span></a></div>
				<div id="item_5" style="font-size: 12px; font-family; 'Times New Roman', serif; text-transform: lowercase; line-height: 1.7em;">
					<a href="<?php echo '/'.$array_directory[1].'/major/process/statistics.php?typeMenu=5'; ?>" class="getInterval" title="">
						<span style="color: #004345;">Кол-во компьютерных атак</span></a></div>
				<div id="item_6" style="font-size: 12px; font-family; 'Times New Roman', serif; text-transform: lowercase; line-height: 1.7em;">
					<a href="<?php echo '/'.$array_directory[1].'/major/process/statistics.php?typeMenu=6'; ?>" class="getInterval" title="">
						<span style="color: #004345;">Соотношение компьютерных атак</span></a></div>
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
					$GetCharts = new GetChartsConstructionMajor();
					?>
<!-- убираем подчеркивание в выбранного пункта меню и меняем его цвет -->
					<script type="text/javascript">delUnderline(0);</script>
<!-- поле диаграмм -->
					<div style="position: relative; left: 4px; top: 0; width: 745px; background: #ecf8ff;">
<!-- две верхние диаграммы -->

<!-- круговая диаграмма количества пользователей по группам -->
						<div style="display: inline-block; margin: 5px; text-align: center; width: 360px; min-height: 200px;">
							<span style="color: #000; font-size: 11px; font-family; 'Times New Roman', serif;">количество пользователей</span><br>
							<?php $GetCharts->getCountWorker(); ?>
						</div>
<!-- диаграмма типа 'бублик' количество посещений по фамилиям -->
						<div style="display: inline-block; margin: 5px; text-align: center; width: 360px; min-height: 200px;">
							<span style="color: #000; font-size: 11px; font-family; 'Times New Roman', serif;">количество посещений</span><br>
							<?php $GetCharts->getCountVisitation(); ?>
						</div>
<!-- две нижние диаграммы -->

<!-- диаграмма типа 'бублик' количество добавленных компьютерных воздействий -->
						<div style="display: inline-block; margin: 5px; text-align: center; width: 360px; min-height: 200px;">
							<span style="color: #000; font-size: 11px; font-family; 'Times New Roman', serif;">количество добавленных компьютерных воздействий</span><br>
							<?php $GetCharts->getCountAddComputerImpact(); ?>
						</div>
<!-- круговая диаграмма количество подготовленных дежурными писем -->
						<div style="display: inline-block; margin: 5px; text-align: center; width: 360px; min-height: 200px;">
							<span style="color: #000; font-size: 11px; font-family; 'Times New Roman', serif;">количество компьютерных воздействий признанных ложными</span><br>
							<?php $GetCharts->getCountMail(); ?>
						</div>
					</div><br>
<!-- таблица с краткой информацией о пользователях -->
					<div onclick="(function(elem){ var table = getElementById('table_1'); if((table.nodeType == 1) && (table.style.display == 'none')){ table.style.display = 'block'; elem.childNodes[1].firstChild.nextSibling.style.textDecoration = ''; } else { table.style.display = 'none'; elem.childNodes[1].firstChild.nextSibling.style.textDecoration = 'underline'; }})(this)" style="cursor: pointer;">
						<span style"font-size: 12px; font-family; 'Times New Roman', serif;">
							<div style="position: relative; left: 10px; text-decoration: underline; color: #000080">Таблица (общая информация о пользователях)</div></span>
					</div>
					<div id="table_1" style="padding-top: 10px; padding-left: 3px; width: 747px; display: none;">
						<?php TablesIndexInformation::showTableCountIncidentForUsers(); ?>
					</div><br>
					<?php
				break;
				//для дежурных (статистика по типам компьютерных атак)
				case 1:
					//убираем подчеркивание и меняем цвет
					?>
<!-- статистическая информация о предпочитаемых дежурными типов компьютерных атак -->
					<script type="text/javascript">delUnderline(1);</script>
					<div id="content" style="width: 740px; overflow: auto;">
						<?php (new ShowStatisticsWorkerAndTypeKa())->showInformation(); ?>
					</div>
					<?php
				break;
				//для времени реагирования
				case 2:
					//убираем подчеркивание и меняем цвет
					?>
					<script type="text/javascript">delUnderline(2);</script>
<!-- статистическая информация -->

<!-- вывод статистической информации по 'умолчанию' -->
					<div id="content" style="width: 740px;">
						<div style="padding-bottom: 15px; text-align: center;">
							<span style="font-size: 14px; font-family; 'Times New Roman', serif; color: #363636;">
							среднее время реагирования на компьютерные воздействия
							</span>
						</div>
						<?php $ShowStatisticsTimeReact = new ShowStatisticsTimeReact ?>
<!-- диаграмма с информацией о времени реагирования дежурных -->
						<div style="width: 370px; float: left;">
							<?php $ShowStatisticsTimeReact->showInformation($ShowStatisticsTimeReact::WORKER); ?>
						</div>
<!-- диаграмма с информацией о времени реагирования аналитиков -->
						<div style="width: 370px; float: left;">
							<?php $ShowStatisticsTimeReact->showInformation($ShowStatisticsTimeReact::ANALYST); ?>
						</div>
<!-- информация о общем времени реагирования -->
						<div style="width: 760px; text-align: center;">
							<span style="font-size: 14px; font-family; 'Times New Roman', serif; color: #363636;">
								общее среднее время реагирования на компьютерные воздействия
							</span><br>
						 	<span style="font-size: 26px; font-family; 'Times New Roman', serif; color: #0040ff;">
								<?php $ShowStatisticsTimeReact->showInformation($ShowStatisticsTimeReact::COMMON); ?>
							</span>
						</div>
					</div>					
					<?php
				break;
				//для геопозиционирования
				case 3:
					//убираем подчеркивание и меняем цвет
					?>
					<script type="text/javascript">delUnderline(3)</script>
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
								var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', "/<?= $array_directory[1] ?>/major/process/ajax_process.php", elem, 'queryStatistics=GeoIp_1');
								newObjectXMLHttpRequest.sendRequest();
							break;
							//информация по странам для IP-адресов назначения
							case 'tab_2':
								var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', "/<?= $array_directory[1] ?>/major/process/ajax_process.php", elem, 'queryStatistics=GeoIp_2');
								newObjectXMLHttpRequest.sendRequest();							
							break;
							//информация за отрезок времени по странам и IP-адресам назначения
							case 'tab_3':
								var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', "/<?= $array_directory[1] ?>/major/process/ajax_process.php", elem, 'queryStatistics=GeoIp_3');
								newObjectXMLHttpRequest.sendRequest();
							break;
							//информация по ложным компьютерным атакам
							case 'tab_4':
								var newObjectXMLHttpRequest = new objectXMLHttpRequest('POST', "/<?= $array_directory[1] ?>/major/process/ajax_process.php", elem, 'queryStatistics=GeoIp_4');
								newObjectXMLHttpRequest.sendRequest();
							break;
							}
						}
					</script>
					<?php
				break;
				//для типов КА
				case 4:
					//убираем подчеркивание и меняем цвет
					?><script type="text/javascript">delUnderline(4);</script>
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
				case 5:
					//убираем подчеркивание и меняем цвет
					?>
					<script type="text/javascript">delUnderline(5);</script>
					<div style="text-align: center; font-size: 20px; font-family: 'Times New Roman', serif;">
						страница в разработке
					</div>
					<?php
				break;
				//для соотношения КА
				case 6:
					//убираем подчеркивание и меняем цвет
					?>
					<script type="text/javascript">delUnderline(6);</script>
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