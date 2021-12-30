/* кнопка вверх вниз */	

		var updownElem = document.getElementById('updown');
		var pageYLabel = 0;

		updownElem.onclick = function()
			{
			window.scrollTo(0,0);
			var pageY = window.pageYOffset || document.documentElement.scrollTop;
			switch(this.className)
				{
				case 'up':
					pageYLabel = pageY;
					window.scrollTo(0,0);
					this.className = 'down';
				break;
				case 'down':
					window.scrollTo(0, pageYLabel);
					this.className = 'up';
				}
			}
			
		window.onscroll = function()
			{
			var pageY = window.pageYOffset || document.documentElement.scrollTop;
			var innerHeight = document.documentElement.clientHeight;
			switch(updownElem.className)
				{
				case '':
					if(pageY > innerHeight)
						{
						updownElem.className = 'up';
						}
				break;
				case 'up':
					if(pageY < innerHeight)
						{
						updownElem.className = '';
						}
				break;
				case 'down':
					if(pageY > innerHeight)
						{
						updownElem.className = 'up';
						}
				break;
				}
			}
