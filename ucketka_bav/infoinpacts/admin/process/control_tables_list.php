<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		страница обновление таблиц 				*/
						/*		содержащих базу сигнатур СОА Snort, 	*/
						/*		GeoIP и списки	IP-адресов				*/
						/*							v.0.1 21.07.2014 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 

//подключаем основную страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/admin/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

?>
<!-- поле основной информации -->
	<div style="position: relative; top: -1px; left: 210px; display: inline-block; z-index: 10; width: 745px; min-height: 502px; border-radius: 3px; box-shadow: inset 0 0 8px 0px #B7DCF7;">
		<?php
		$id = (int) $_GET['id'];
		switch($id) {
			//управление информацией по компьютерным воздействиям
			case 1:
				?>
				<div style="position: relative; top: 10px;">
					<div style="width: 745px; text-align: center;">
						<span style="text-align: center; font-size: 16px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #000099;">
						Управление списком "плохих" IP-адресов
						</span>
					</div>
<!-- управление списками IP-адресов принадлежащих сети Tor, Bot-net и т.д. -->
					<div style="position: relative; left: 5px; width: 735px; min-height: 130px;">
						<table border="0" style="width: 735px;">
							<tr>
								<td style="width: 400px;">
								<?php
								$ControlBlackIpList = new ControlBlackIpList();
								//краткая информация о списках IP-адресов 
								$ControlBlackIpList->showBDInfo();
								?>
								</td>
								<td style="width: 335px; text-align: center;">
								<?php 
								//вывод формы загрузки файла с IP-адресами
								$ControlBlackIpList->loadFile();
								?>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<?php 								
				//загрузка списка IP-адресов
				$ControlBlackIpList->updateIpList();
				//вывод информации о бинарной БД
				$ReadBinaryDBIpAddress = new ReadBinaryDBBlackList;
				$ReadBinaryDBIpAddress->showInfoBinaryBD();
				?>
				<div id="message" style="position: relative; top: 0px; width: 745px; text-align: center; display: none; font-size: 10px; color: #FF0000; font-family: 'Times New Roman', serif;"></div>
				<br><br>
				<div style="position: relative; top: 0px; width: 745px;">
<!-- вывод таблицы с подробной информацией о списках IP-адресах -->
					<?php $ControlBlackIpList->showIpListInfoDetails(); ?>
				</div>
				<?php
			break;
			//управление информацией по DDoS-атакам
			case 2:
				?>
				<div style="position: relative; top: 10px; width: 745px; text-align: center;">
					<span style="text-align: center; font-size: 16px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #000099;">
					Обновление базы данных GeoIP
					</span>
				</div>
<!-- работа с БД GeoIP -->
				<div style="position: relative; top: 10px; width: 745px;">
					<?php
					$ListGeoIP = new ControlIpListGeoIP();
					//краткая информация о БД GeoIP 
					$ListGeoIP->showBDInfo();
					//вывод формы загрузки файла с IP-адресами
					$ListGeoIP->loadFile();
					?>
					<div style=" position: relative; left: 22px; top: 10px; width: 700px; text-align: center;">
						<span style="font-style: italic; font-family: 'Times New Roman', serif; letter-spacing: 1px;">
						<span style="font-size: 14px; color: #CD0000;">Внимание!!!</span><br>
						<span style="font-size: 12px; color: #000;">Для обновления базы данных IP-адресов геопозиционирования необходимо использовать только файлы в формате CSV, полученные с Web-сайта maxmind.com 
						URL <spam style="text-decoration: underline; color: #0000CD;">http://dev.maxmind.com/geoip/legacy/geolite/</spam></span>
						</span>
					</div>
					<?php $ListGeoIP->updateIpList(); ?>
				</div>
				<?php
			break;
			//управление информацией по сигнатурам
			case 3:
				echo "<br>правила СОА Snort<br>";
			break;
			//управление информацией по сенсорам
			case 4:
				echo "<br>управление информацией по сенсорам<br>";
			break;
			}
		?>
		<br>
	</div>
</div><br><br>
<?php
include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>