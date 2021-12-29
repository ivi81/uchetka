<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>информация по сигнатуре №<?php echo $_GET['showRule'] ?></title>
		<link rel="stylesheet" type="text/css" href=""></link>
			<style type="text/css">
				html, body { height: 100%; margin: 0; }
				body {
   					background: -webkit-linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent);
   					background: -moz-linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent);
   					background: -o-linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent);
   					background: -ms-linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent);
					background: linear-gradient(left, #7EC0EE 0%, #BFEFFF 50%, #7EC0EE 100%, transparent); }
			</style>	
	</head>
	<body>
<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		скрипт вывода на страницу информации по коду сигнатуры	*/
						/*										v.0.1 13.02.2014	 	*/
						/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));

//подключаем config
require_once ($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/config/config.php");

session_start();

//проверяем авторизацию
$checkAuthorization = new CheckAuthorization();
$checkAuthorization->checkUserData();
$checkAuthorization->checkPageUserId(__DIR__);

//объект для подключения к БД
$DBO = new DBOlink();

//объект БД GeoIP
$GeoIP = new GeoIP();

//объект для чтения файла setup_site.xml
$ReadXMLSetup = new ReadXMLSetup;

//показывать в новом окне всю имеющуюся информацию по поставленной задаче 	
if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['showRule']) && !empty($_GET['showRule']))
	{
	$numSid = (int) $_GET['showRule'];

		?>
<!-- основная цветовая подложка -->
		<div style="position: relative; top: 10px; left: 10px; z-index: 10; width: 490px; min-height: 180px; display: table; vertical-align: middle;	border-radius: 3px; background: #FFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">	
			<br>			
			<div style="width: 510px; text-align: center;">				
			<span style="font-weight: bold; font-size: 16px; font-family: 'Times New Roman', serif; letter-spacing: 2px; border-radius: 3px; color: #0000CC;">
				информация по сигнатуре № <?php echo $numSid; ?>			
			</span>
			</div><br>
			<?php
			try{
				$query = $DBO->connectionDB()->query("SELECT `short_message`, `snort_rules` FROM `signature_tables` WHERE `sid`='".$numSid."'");
				$row = $query->fetch(PDO::FETCH_OBJ);
				if(empty($row->short_message)){
					?>
					<br><div style="width: 510px; text-align: center;">
						<span style="font-weight: bold; font-size: 16px; font-family: 'Times New Roman', serif;">
							описание данной сигнатуры отсутствует
						</span>					
					</div>
					<?php
					} else {
					?>
					<table border="0" width="480px" align="center">
						<tr>
<!-- краткое описание сигнатуры -->
							<th style=" text-align: center; width: 480px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
							краткое описание
							</th>
						</tr>
						<tr>
							<td style=" text-align: center; width: 480px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFF;">
								<?= $row->short_message ?>
							</td>
						</tr>
						<tr>
<!-- правило -->
							<th style=" text-align: center; width: 480px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFEDB9;">
							правило
							</th>
						</tr>
						<tr>						
							<td style=" text-align: justify; width: 480px; font-size: 14px; font-family: 'Times New Roman', serif; background: #FFF;">
							<?php		
							//проверяем есть ли в строке пробел, если нет режем строку на подстроки
							if(!strpbrk($row->snort_rules, " ")){
								for($i = 0; $i < abs(strlen($row->snort_rules) / 40); $i++){
									echo substr($row->snort_rules, ($i * 40), 40)."\n";
									}
								} else {
								echo $row->snort_rules;
								}
							?>
							</td>
						</tr>
					</table><br>
					<?php
					}
				}
			catch(PDOException $e){
				echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
				}	
			?>
			
		</div><br>
		<?php

	}
//закрываем соединения с БД
$DBO->onConnectionDB();
?>
	</body>
</html>