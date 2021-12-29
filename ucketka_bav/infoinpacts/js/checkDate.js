function checkDate(directory){
	var objDate = new Date();
	if((objDate.getMonth() == 11 && objDate.getDate() > 24) || (objDate.getMonth() == 0 && objDate.getDate() < 15)){
		setNewYearImg();
		}

	//плюшки к новому году
	function setNewYearImg(){		
		//снеговик
		createImgElement({ id: 'snowman', src: '/' + directory + '/img/holiday/snowman_1.png', style: 'position: absolute; top: 30px; left: 20px;' });
		
		//ёлка
		createImgElement({ id: 'tree', src: '/' + directory + '/img/holiday/tree_1.png', style: 'position: absolute; top: 25px; left: 850px;' });
		
		//надпись 'с новым годом'
		createImgElement({ id: 'stringNewYear', src: '/' + directory + '/img/holiday/new_year_2.png', style: 'position: absolute; top: 40px; left: 220px;' });

		//проверяем на какой страницы мы находимся и на основании этого размещаем элементы
		var pathname = window.location.pathname;

		//если главная страница
		if(pathname.indexOf('index') !== -1 && pathname.indexOf('major') === -1){
			//подарок
			createImgElement({ id: 'gift', src: '/' + directory + '/img/holiday/gift_1.png', style: 'position: absolute; top: 340px; left: 895px; z-index: 70;' });
			//ёлочный шар
			createImgElement({ id: 'tree_ballon_4', src: '/' + directory + '/img/holiday/tree_ballon_4.png', style: 'position: absolute; top: 215px; left: 10px; z-index: 70;' });
			}
		//если страница редактирования компьютерного воздействия (дежурный и аналитик)
		if(pathname.indexOf('edit_incidents') > 0){
			//ёлочные игрушки
			createImgElement({ id: 'tree_ballon_1', src: '/' + directory + '/img/holiday/xmas.png', style: 'position: absolute; top: 265px; left: 883px; z-index: 70;' });
			}	
		//если страница поиска
		if(pathname.indexOf('search_incidents') > 0 && pathname.indexOf('major') === -1){
			//снегирь
			createImgElement({ id: 'snegir', src: '/' + directory + '/img/holiday/snegir.png', style: 'position: absolute; top: 235px; left: 40px; z-index: 70;' });			
			//ёлочная игрушка снеговик
			createImgElement({ id: 'snowmanIcon', src: '/' + directory + '/img/holiday/Snowman-icon.png', style: 'position: absolute; top: 435px; left: 931px; z-index: 70;' });
			}
		//если страница анализа компьютерного воздействия
		if(pathname.indexOf('analysis_incident') > 0){
			//ёлочные игрушки (3 красных шара)
			createImgElement({ id: 'tree_ballon_3', src: '/' + directory + '/img/holiday/tree_ballon_3.png', style: 'position: absolute; top: 669px; left: 785px; z-index: 70;' });			
			}
		}
	//создаем новое изображение
	//функция принимает объект в котором id: имя div елемента, src: местоположение изображения, style: css стиль
	function createImgElement(objElements){
		if(typeof objElements !== 'object') return false;
		//родительский элемент
		var parentDiv = document.getElementById('majorArea');		
		var divNew = document.createElement('DIV');
		divNew.id = objElements.id;
		var img = document.createElement('IMG');
		img.src = objElements.src;
		divNew.appendChild(img);
		divNew.setAttribute('style', objElements.style);
		parentDiv.appendChild(divNew);
		}
	}

//проверка даты и вывод картинки для окошка авторизации
function checkDateAuthorization(directory){
	var objDate = new Date();
	if((objDate.getMonth() == 11 && objDate.getDate() > 24) || (objDate.getMonth() == 0 && objDate.getDate() < 15)){
		var div = document.getElementById('author');
		//добавляем сниговика
		var divNew_1 = document.createElement('DIV');
		var img_1 = document.createElement('IMG');
		img_1.src = '/' + directory + '/img/holiday/snowman_2.png';
		divNew_1.appendChild(img_1);
		divNew_1.setAttribute('style', 'position: absolute; top: -55px; left: 25px; z-index: 70;');
		div.appendChild(divNew_1);
		//добавляем сосульки
		var divNew_2 = document.createElement('DIV');
		var img_2 = document.createElement('IMG');
		img_2.src = '/' + directory + '/img/holiday/icicles_2.png';
		divNew_2.appendChild(img_2);
		divNew_2.setAttribute('style', 'position: absolute; top: 165px; left: 5px; z-index: 70;');
		div.appendChild(divNew_2);
		}
	}