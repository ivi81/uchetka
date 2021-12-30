							

						/*----------------------------------/
						/	скрипты поиска информации: 		/
						/	- по номеру сигнатуры			/
						/	- по IP-адресу источника		/
						/				v0.1 26.08.2014		/
						/----------------------------------*/



//функция конструктор для работы с объектом XMLHttpRequest
function objectXMLHttpRequest(method, path, elem, arg)
	{
	//метод запроса
	this.requestMethod = method;
	//путь к файлу обработчику
	this.requestPathFile = path;
	//аргументы (для GET и POST запросов)
	this.requestArg = arg;
	//идентификатор элемента (для вывода информации)
	this.element = elem;
	//обработчик ответа
	this.requestProcessing = function(request){
								//поиск по
								var argTest = arg.split('=');
								request.onreadystatechange = function(){
									switch(argTest[0]){
										
										//для поиска по номеру сигнатуры
										case 'querySid':
											//получаем TD в котором будем размещать DIV с информацией
											var infoElement = elem.parentNode.parentNode.firstChild.nextSibling.nextSibling.nextSibling;
											//проверяем завершена ли передача
											if(request.readyState == 4){
												var div = infoElement.appendChild(document.createElement('DIV'));
												div.setAttribute('style', 'text-align: center;');
												//проверяем статус ответа сервера
												if(request.status == 200){
													div.innerHTML = request.responseText;
													} else {
													div.innerHTML = 'ошибка при получении ответа, ответ сервера ' + request.status + '<br>';
													}
												} else {
											//вывод индикатора загрузки
											}
										break;
										
										//поиск по IP-адресу
										case 'queryIpSrc':
											//получаем TD в котором будем размещать DIV с информацией
											var infoElement = elem.parentNode.parentNode.firstChild.nextSibling.nextSibling.nextSibling;
											if(request.readyState == 4){
												var div = infoElement.appendChild(document.createElement('DIV'));
												div.setAttribute('style', 'text-align: center;');
												//проверяем статус ответа сервера
												if(request.status == 200){
													div.innerHTML = request.responseText;
													} else {
													div.innerHTML = 'ошибка при получении ответа, ответ сервера ' + request.status + '<br>';
													}
												} else {
												//вывод индикатора загрузки
												}
										break;
										
										//поиск номеров писем (руководство)
										case 'queryMailMajor':
										//получаем DIV в котором будет размещаться элемент
											var div = document.getElementById('divList');
											if(request.readyState == 4){
												//проверяем статус ответа сервера
												if(request.status == 200){
													div.innerHTML = request.responseText;
													} else {
													div.innerHTML = 'ошибка при получении ответа, ответ сервера ' + request.status + '<br>';
													}												
												} else {
												//вывод индикатора загрузки
												}
										break;
										
										//поиск краткой информации по письмам (руководство)
										case 'queryMailMajorInfo':
											//получаем DIV в котором будет размещаться элемент
											var div = document.getElementById('divInform');
											if(request.readyState == 4){
												//проверяем статус ответа сервера
												if(request.status == 200){
													div.innerHTML = request.responseText;
													} else {
													div.innerHTML = 'ошибка при получении ответа, ответ сервера ' + request.status + '<br>';
													}												
												} else {
												//вывод индикатора загрузки
												}
										break;
										
										//поиск краткой информации за выбранный год
										case 'queryShortInformationYear':
											//получаем DIV в котором будет размещаться элемент
											var divChoiceYear = document.getElementById('choiceYear');
											var divAllInformationYear = document.getElementById('allInformationYear');
                                            var divAllAnalyseInformationYear = document.getElementById('allAnalyseInformationYear');
                                            var divAllNotAnalyseInformationYear = document.getElementById('allCountTraffikNotLook');
											var divFalseInformationYear = document.getElementById('falseInformationYear');
											if(request.readyState == 4){
												//проверяем статус ответа сервера
												if(request.status == 200){
													if(typeof request.responseText == 'string'){
														var array = request.responseText.split(':');

														//выбранные пользователем год
														divChoiceYear.innerHTML = '<span style="font-size: 12px;">воздействия за </span><span style="font-size: 20px; color: #3300FF;">' + array[0] + '</span><span style="font-size: 12px;"> год</span>';
														//общая информация за выбранный год
														divAllInformationYear.innerHTML = '<span style="font-size: 12px;">всего: </span><span style="font-size: 20px;">' + array[1] + '</span>';
                                                        //общая информация за выбранный год
                                                        divAllAnalyseInformationYear.innerHTML = '<span style="font-size: 12px;">проанализированных: </span><span style="font-size: 20px;">' + array[2] + '</span>';
                                                        //общая информация за выбранный год
                                                        divAllNotAnalyseInformationYear.innerHTML = '<span style="font-size: 12px;">не рассмотренных: </span><span style="font-size: 20px;">' + array[3] + '</span>';
														//ложные компьютерные воздействия за выбранный год
														divFalseInformationYear.innerHTML = '<span style="font-size: 12px;">ложных: </span><span style="font-size: 20px;">' + array[4] + '</span>';
														}
													} else {
													div.innerHTML = 'ошибка при получении ответа, ответ сервера ' + request.status + '<br>';
													}												
												} else {
												//вывод индикатора загрузки
												}
										break;
										
										//вывод статистики по GeoIp
										case 'queryStatistics':
											//получаем DIV в котором будет размещаться элемент
											var div = document.getElementById('content');
											div.innerHTML = '';
											if(request.readyState == 4){
												//проверяем статус ответа сервера
												if(request.status == 200){
													argGeoIp = (argTest[1].length > 7) ? argTest[1].substr(0,7) : argTest[1];
													switch(argGeoIp){
														case 'GeoIp_1':
														div.innerHTML = request.responseText;
														document.onload = (function(){
															var areaHidden = document.getElementById('elemHidden');
															var areaSendRequest = document.getElementById('areaSendRequest');
															var div = document.getElementById('container');
															var newElem = document.createElement('script');
															newElem.defer = true;
															if(areaHidden != null && areaSendRequest != null){
																newElem.text = areaHidden.value + '; ' + areaSendRequest.value;
																div.appendChild(newElem);
																}
															})();
														break;
														case 'GeoIp_2':
															div.innerHTML = request.responseText;
															document.onload = (function(){
																//вывод интервала времени
																var areaIntervalDate = document.getElementById('areaIntervalDate');
																//отправка запроса
																var areaSendRequest = document.getElementById('areaSendRequest');
																//вывод диаграммы
																var areaHidden = document.getElementById('elemHidden');
																var div = document.getElementById('container');
																var areaScript = document.createElement('script');
																areaScript.defer = true;
																if(areaIntervalDate != null && areaSendRequest != null){
																	if(areaHidden != undefined){
																		areaScript.text = areaIntervalDate.value + areaSendRequest.value + areaHidden.value;
																		} else {
																		areaScript.text = areaIntervalDate.value + areaSendRequest.value;
																		}
																	if(div != null) div.appendChild(areaScript);
																	}
																})()
														break;
														case 'GeoIp_3':
														div.innerHTML = request.responseText;
															document.onload = (function(){
																//отправка запроса
																var areaSendRequest = document.getElementById('areaSendRequest');
																//вывод диаграммы
																var areaHidden = document.getElementById('elemHidden');
																var div = document.getElementById('container');
																var areaScript = document.createElement('script');
																areaScript.defer = true;
																if(areaSendRequest != null){
																	if(areaHidden != undefined){
																		areaScript.text = areaSendRequest.value + areaHidden.value;
																		} else {
																		areaScript.text = areaSendRequest.value;
																		}
																	if(div != null) div.appendChild(areaScript);
																	}
																})()
														break;
														case 'GeoIp_4':
														div.innerHTML = request.responseText;
															document.onload = (function(){
																//отправка запроса
																var areaSendRequest = document.getElementById('areaSendRequest');
																//вывод интервала времени
																var areaIntervalDate = document.getElementById('areaIntervalDate');
																//отправка запроса
																var areaSendRequest = document.getElementById('areaSendRequest');
																//вывод диаграммы
																var areaHidden = document.getElementById('elemHidden');
																var div = document.getElementById('container');
																var areaScript = document.createElement('script');
																areaScript.defer = true;
																if(areaIntervalDate != null && areaSendRequest != null){
																	if(areaHidden != undefined){
																		areaScript.text = areaIntervalDate.value + areaSendRequest.value + areaHidden.value;
																		} else {
																		areaScript.text = areaIntervalDate.value + areaSendRequest.value;
																		}
																	if(div != null) div.appendChild(areaScript);
																	}
															})()
														break;
														}
													} else {
													div.innerHTML = 'ошибка при получении ответа, ответ сервера ' + request.status + '<br>';
													}												
												} else {
												//вывод индикатора загрузки
												}
										break;

										//поиск информации по номеру сенсора
										case 'informationSensorId':
											//получаем DIV в котором будет размещаться элемент
											var divSensorInformation = document.getElementById('sensorInformation');
											if(request.readyState == 4){
												//проверяем статус ответа сервера
												if(request.status == 200){
													divSensorInformation.innerHTML = request.responseText;
													document.onload = (function(){
														var areaJScript = document.getElementById('areaJScript');
														var newElem = document.createElement('script');
														newElem.defer = true;
														if(areaJScript != null || areaJScript != undefined){
															newElem.text = areaJScript.value + ' pageOnload();';
															divSensorInformation.appendChild(newElem);
														}
													})();
													} else {
													div.innerHTML = 'ошибка при получении ответа, ответ сервера ' + request.status + '<br>';
													}												
												} else {
												//вывод индикатора загрузки
												}
										break;

										//поиск информации через форму поиска
										case 'searchStart':
											var fieldInformation = document.getElementById('fieldInformation');
											if(request.readyState == 4){
												//проверяем статус ответа сервера
												if(request.status == 200){
													fieldInformation.innerHTML = request.responseText;
													window.onload = (function(){
														var areaJScript = document.getElementById('areaJScript');
														var newElem = document.createElement('script');
														newElem.defer = true;
														if(areaJScript != null && areaJScript.value != null && areaJScript.value != undefined){
															newElem.text = areaJScript.value;
															areaJScript.parentNode.appendChild(newElem);
															areaJScript.parentNode.removeChild(areaJScript);
														loading();
														}
													})();
												} else {
													div.innerHTML = 'ошибка при получении ответа, ответ сервера ' + request.status + '<br>';
												}												
											} else {
												//вывод индикатора загрузки
												fieldInformation.innerHTML = '<div style="text-align: center; margin-top: 35px; color: #3B5998; font-size: 16px;">Loading...</div>';
											}
										break;
										}

									}
								}
							}

//вывод индикатора загрузки
// elem - элемент куда надо вставить индикатор
// request - объект XMLHttpRequest
function showLoadingDetector(elem, request){
	//получаем элемент в котором будем выводить индикатор
	var infoElement = elem.parentNode.parentNode.firstChild.nextSibling.nextSibling.nextSibling;
	//создаем новый элемент DIV
	var div = infoElement.appendChild(document.createElement('DIV'));
	div.setAttribute('style', 'text-align: center;');
	//получаем имя директории сайта
	var nameSite = window.location.href.split('/');
	//создаем элемент с изображением
	var img = div.appendChild(document.createElement('IMG'));
	img.setAttribute('src', '/' + nameSite[3] + '/img/712.gif');
	}

//создание объекта XMLHttpRequest
objectXMLHttpRequest.prototype.createRequest = function(){
/*
	методы объекта XMLHttpRequest
abort() - отмена текущего запроса к серверу
getAllResponseHeaders() - получить все заголовки ответа от сервера
getResponseHeader(<имя_заголовка>) - получить указанный заголовок
open(<типа_запроса>, <URL>, <асинхронный (true, false)>, <имя_пользователя>, <пароль>) - инициализация запроса к серверу
send(<содержимое>) - послать HTTP запрос на сервер и получить ответ
setRequestHeader(<имя_заголовка>, <значение>) - установить значение заголовка запроса

	свойства объекта XMLHttpRequest
onreadystatechange - свойство задающее обработчик вызываемый при смене статуса объекта
readyState - число, обозначающее статус объекта
responseText - представление ответа от сервера в виде текста
responseXML - представление ответа от сервера в виде XML
status - состояние ответа от сервера
statusText - текст состояния ответа от сервера
*/
											var request = false;
											//для стандартных браузеров
											if(window.XMLHttpRequest){
												request = new XMLHttpRequest();
												}
											//для IE
											else if(window.ActiveXObject){
												request = new ActiveXObject('Microsoft.XMLHTTP');
												}
											if(!request){
												alert('невозможно создать объект XMLHttpRequest');
												}
											return request;
											}

//создаем запрос
objectXMLHttpRequest.prototype.sendRequest = function(){
										var request = this.createRequest();	
										if(!request){
											alert('невозможно создать объект XMLHttpRequest');
											}
										//если обмен данными завершен
/*
свойство readyState объекта XMLHttpRequest
0 - объект не инициализирован
1 - объект загружает данные
2 - объект закончил загрузку данных
3 - объект не полностью загружен но может взаимодействовать с пользователем
4 - объект полностью инициализирован, получен ответ от сервера
*/
										if(request.readyState == 4){
											//передаем данные обработчику
											this.requestProcessing(request);
											}
										//если GET запрос
										if(this.requestMethod.toUpperCase() == 'GET' && this.requestMethod.length > 0){
											this.requestPathFile += '?' + requestArg;
											}
										//инициализируем соединение
										request.open(this.requestMethod, this.requestPathFile, true);
										//если POST запрос
										if(this.requestMethod.toUpperCase() == 'POST' && this.requestMethod.length > 0){
											//устанавливаем заголовок
											request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=utf-8", 
																	 "Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");							
											request.send(this.requestArg);
											} else {
											request.send(null);
											}
										this.requestProcessing(request);
										}
