<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		страница формы поиска и просмотра компьютерных воздействий 	*/
						/*											v.0.1 23.03.2015	 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 

//подключаем основную, для дежурного страницу
include($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/analyst/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

?>
<!-- подключаем AJAX -->
<script type="text/javascript" src='/<?= $array_directory[1] ?>/js/objectXMLHttpRequest.js'></script>

<!-- текст "поиск ранее зафиксированных компьютерных атак" -->
	<div style="position: relative; top: 0px; text-align: center; width: 960px; font-size: 20px; font-family: 'Times New Roman', serif; letter-spacing: 2px; margin: 10px 0px;">
		поиск компьютерных воздействий
	</div>
<!-- поле вывода формы -->
	<div style="position: relative; top: 10px; left: 0px; z-index: 10; width: 960px; display: table; vertical-align: middle; border-radius: 3px; background: #F8F8F8;">	
		<?php
		//класс вывода формы поиска информации
		SearchComputerImpactShowForm::showSearchForm();
		?>		
	</div>
<!-- поле вывода основной информации -->
	<div style="position: relative; top: 25px; left: 0; width: 960px; background: #FFF;">
		<div id="fieldInformation">	
		</div>	
	</div><br><br>
<?php
include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>