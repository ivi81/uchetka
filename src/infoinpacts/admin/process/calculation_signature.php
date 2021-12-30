<?php

						/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*			страница учета дежурными сигнатур СОА snort		*/
						/*		вызывающих большое количество ложных срабатываний	*/
						/*													v.0.1 06.12.2013 	*/
						/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//подключаем основную, для дежурного страницу
include($_SERVER['DOCUMENT_ROOT']."/new_project/worker/index.php"); 

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkPageUserId(__DIR__);

?>
<!-- основная цветовая подложка -->	
	<div class="majorArea">
	
<!-- шаблон загрузки файлов с правилами СОА snort START -->
		<div style="">
			<span>загрузка файлов с правилами snort</span>
			<form name="loadFile" method="POST" enctype="multipart/form-data" action="calculation_signature.php?load=1">
				<input type="file" name="loadFileSnort[]" multiple="true">
				<input type="submit" value="загрузить">
			</form>
		</div>
<?php
	if(isset($_GET['load']) && $_GET['load'] == 1) 
		{
		echo "loading";
		$ReadSnortRules = new ReadSnortRules();
		$ReadSnortRules->loadRules();
		echo "<br>loading complite";
		}
?>
<!-- шаблон загрузки файлов с правилами СОА snort FINISH -->

	
	</div>