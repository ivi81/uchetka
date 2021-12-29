<?php

						/*++++++++++++++++++++++++++++++++++++++++++++++*/
						/*		страница управления  информацией			*/
						/*		(данные по компьютерным воздействиям,	*/
						/*		сенсорам, 								*/
						/*		DDoS-атакам,							*/
						/*		типам списков IP-адресов и т.д.)		*/
						/*							v.0.1 15.07.2014 	*/
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

<script type="text/javascript">
	
	//изменение изображения	
	function chageImg(elem, flag){
		switch(flag){
			//добавление записи о типе IP-списка
			case '1':
				elem.src = '/<?= $array_directory[1]?>/img/add-to-database.png';
			break;
			//добавление записи о типе IP-списка			
			case '2':
				elem.src = '/<?= $array_directory[1]?>/img/add-to-database_1.png';
			break;
			//удаление записи о типе IP-списка
			case '3':
				elem.src = '/<?= $array_directory[1]?>/img/delete_recording.png';
			break;
			//удаление записи о типе IP-списка			
			case '4':
				elem.src = '/<?= $array_directory[1]?>/img/delete_recording_1.png';
			break;
			}
		}

		//проверка формы добавления новой записи
	function checkIpListAdd(){
		var form = document.formIpListAdd;
		var obj = { type: form.typeListIp,
					info: form.infoListIp };
		//убираем красную рамку
		delRedFrame(obj);
		//проверяем элементы формы на пустоту
		for(var a in obj){
			if(obj[a].value.length == 0){
				obj[a].style['borderColor'] = 'red';
				document.getElementById('message').innerHTML = 'необходимо заполнить все поля';
				return false;
				}
			}
		}

	//подтверждение удаления записи о доменном имени
	function confirmTypeDel(code, type){
		switch(code){
			case '1':
				var testOk = confirm('Подтвердите удаление ... ' + type);	
			break;
			case '2':
				var testOk = confirm('Подтвердите удаление ... ' + type);
			break;
			case '3':
				var testOk = confirm('Удалить список IP-адресов ' + type + '?');
			break;
			}
		return (testOk) ? true : false;
		}

	//убираем красную рамку
	function delRedFrame(elem){
		for(var a in elem){
			elem[a].style['borderColor'] = '';
			}
		}


</script>

<!-- поле основной информации -->
	<div style="position: relative; top: -1px; left: 210px; display: inline-block; z-index: 10; width: 745px; min-height: 500px; border-radius: 3px; box-shadow: inset 0 0 8px 0px #B7DCF7;">
		<?php
		$id = (int) $_GET['id'];
		switch($id){
			//******************************************************//
			//	управление информацией по компьютерным воздействиям	//
			//******************************************************//
			case 1:
				?>
				<div style="position: relative; top: 2px; text-align: center;">
					<span style="position: relative; top: 0px; font-size: 12px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #000099;">
					редактирование компьютерных воздействий
					</span>
				</div>
<!-- поле вывода формы -->
				<div style="position: relative; top: 5px; left: 5px; z-index: 10; width: 735px; display: table; vertical-align: middle; border-radius: 3px; background: #F0FFFF; box-shadow: inset 0 0 8px 0px #B7DCF7;">	
				<?php
				//класс вывода формы поиска информации
				$SearchIncidentsAdmin = new SearchIncidentsAdmin;
				?>		
				</div>
<!-- поле вывода основной информации -->
				<div style="position: relative; top: 10px; left: 0px; z-index: 10; width: 730px; display: table; vertical-align: middle; ">
					<div style="position: relative; left: 7px;">	
					<?php
					//проверяем наличие хотя бы одного из двух параметров id воздействия или начальное время
					if((isset($_GET['numImpact']) && !empty($_GET['numImpact'])) 
					|| (isset($_GET['dateStart']) && !empty($_GET['dateStart']))){
						//выполняем поиск и выводим в виде таблицы
						$SearchIncidentsAdmin->showSearchIncidents();
						}
					?>
					</div>	
				</div><br><br>
				<?php
			break;
			//******************************************//
			//	управление информацией по DDoS-атакам	//
			//******************************************//
			case 2:
				echo "<br>DDoS-атаки<br>";
			break;
			//******************************************************//
			//	управление информацией о типах списков IP-адресов	//
			//******************************************************//
			case 3:
				?>
<!-- форма добавления новой записи о типе IP-спеска -->
				<div style="position: relative; top: 10px; left: 10px; margin: 10px 0px;">
					<span style="position: relative; top: 0px; left: 200px; font-size: 12px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #990000;">
					добавить информацию о новом типе списков IP-адресов
					</span>
					<form name="formIpListAdd" method="POST" action="/<?= $array_directory[1]?>/admin/process/process_write_xml_setup.php" onsubmit="return checkIpListAdd()">
					<div style="border-width: 1px; border-style: solid; width: 725px; border-color: #B7DCF7;">
					<table id="" border="0" width="725px" cellpadding="2">
						<tr style="background: #FF6A6A;">
							<th style="width: 35px; font-size: 12px; font-family: 'Times New Roman', serif;">
							</th>
							<th style="width: 345px; font-size: 12px; font-family: 'Times New Roman', serif;">
							наименование списка IP-адресов
							</th>
							<th style="width: 345px; font-size: 12px; font-family: 'Times New Roman', serif;">
							краткое описание
							</th>
						</tr>
<!-- иконка 'добавить новую запись' -->
						<tr style="background: #FFC1C1;">
							<td>
							<input onmouseout="chageImg(this,'1')" onmouseover="chageImg(this,'2')" type="image" name="typeIpListAdd" src="/<?= $array_directory[1]?>/img/add-to-database.png" title="добавить данные">
							<input type="hidden" name="id" value="addTypeIpList">
							</td>
<!-- название black list -->
							<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
							<input type="text" name="typeListIp" value="" style="width: 140px; height: 18px">				
							</td>
<!-- краткое описание black list -->
							<td style="text-align: center; font-size: 12px; font-family: 'Times New Roman', serif;">
							<input type="text" name="infoListIp" value="" style="width: 300px; height: 18px">				
							</td>
						</tr>				
					</table>
					</div>
					</form>
					<div style="text-align: center;">
						<span id="message" style="font-size: 12px; font-family: 'Times New Roman', serif; color: #FF0000;"></span>
					</div>
				</div>
<!-- таблица с информацией о типах списков IP-адресов -->
				<div style="position: relative; top: 10px; left: 10px; margin: 10px 0px;">
<!-- текст -->
					<span style="position: relative; top: 0px; left: 230px; font-size: 12px; font-family: 'Times New Roman', serif; letter-spacing: 1px; color: #0000CD;">
					доступны следующие типы списков IP-адресов
					</span>

					<div style="border-width: 1px; border-style: solid; width: 725px; border-color: #B7DCF7;">			
					<table id="" border="0" width="725px" cellpadding="2">
						<tr <?= COLOR_HEADER ?> >
							<th style="width: 35px; font-size: 12px; font-family: 'Times New Roman', serif;"></th>
							<th style="width: 345px; font-size: 12px; font-family: 'Times New Roman', serif;">
							наименование списка IP-адресов
							</th>
							<th style="width: 345px; font-size: 12px; font-family: 'Times New Roman', serif;">
							краткое описание
							</th>
						</tr>
					<?php
					//получаем массив состоящий из существующих записей типов IP-листов
					$typeListIp = $ReadXMLSetup->giveListTypeIpAddress();
					foreach($typeListIp as $key => $value){
						echo "<tr bgcolor='".color()."'>";
						$type = $value[0];
						?>
							<td>
							<form name="formIpListDel" method="POST" action="/<?= $array_directory[1]?>/admin/process/process_write_xml_setup.php" onsubmit="return confirmTypeDel('3', '<?= $type ?>')">
								<input onmouseout="chageImg(this,'3')" onmouseover="chageImg(this,'4')" type="image" src="/<?= $array_directory[1]?>/img/delete_recording.png" name="deleteRecording" title="удалить выбранную запись">
								<input type="hidden" name="id" value="deleteTypeIpList">
								<input type="hidden" name="code" value="<?php echo $key; ?>">
							</form>
							</td>
							<td style="font-size: 14px; font-family: 'Times New Roman', serif; text-align: center;"><?= $type ?></td>
							<td style="font-size: 14px; font-family: 'Times New Roman', serif; text-align: center;"><?= $value[1] ?></td>
						</tr>
						<?php
						}
					?>
					</table>
					</form>
				</div>
			</div>
			<?php
			break;
			//******************************************//
			//	управление информацией по сигнатурам	//
			//******************************************//
			case 4:
				echo "<br>сигнатуры<br>";
			break;
			//**************************************//
			//	управление информацией по сенсорам	//
			//**************************************//
			case 5:
				echo "<br>сенсоры<br>";
			break;
			}
		?>
	</div>
</div><br><br>
<?php
include_once($_SERVER['DOCUMENT_ROOT']."/{$array_directory[1]}/footer.php");
?>