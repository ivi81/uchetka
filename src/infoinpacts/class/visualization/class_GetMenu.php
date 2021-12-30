<!-- прямое меню -->

	<style>
		#menu {
			margin:0 auto; 
			width:100%; }
		#menu ul {
			list-style:none; }
		#menu li {
			list-style:none;
			display:block;
			float:left;
			line-height:30px;
			border:solid #296C9B; /* окантовка */
			border-width:0 2px 0 2px;
			 /* Цвет фона меню */
		    background: -moz-linear-gradient(top, #7398B3 0%,#296C9B 100%); /* FF3.6+ */
			background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#7398B3), color-stop(100%,#296C9B)); /* Chrome,Safari4+ */
			background: -webkit-linear-gradient(top, #7398B3 0%,#296C9B 100%); /* Chrome10+,Safari5.1+ */
			background: -o-linear-gradient(top, #7398B3 0%,#296C9B 100%); /* Opera 11.10+ */
			background: -ms-linear-gradient(top, #7398B3 0%,#296C9B 100%); /* IE10+ */
			background: linear-gradient(top, #7398B3 0%,#296C9B 100%); /* W3C */
			height:31px; /* высота меню */
			margin:0 1px 0 0; }
		#menu li a {
			display:block;
			float:left;
			color:#fff;
			text-transform:uppercase;
			font-size:11px;
			font-weight:bold;
			text-decoration:none;
			padding:0 25px; /* интервал в ячейках меню */
			height:30px; } /* высота меню при выделении */					
		#menu li a:hover {
			color:#fff;
			/* Цвет фона меню при наведении курсора */
		    background: -moz-linear-gradient(top, #296C9B 0%,#7398B3 100%); /* FF3.6+ */
			background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#7398B3), color-stop(100%,#296C9B)); /* Chrome,Safari4+ */
			background: -webkit-linear-gradient(top, #296C9B 0%,#7398B3 100%); /* Chrome10+,Safari5.1+ */
			background: -o-linear-gradient(top, #296C9B 0%,#7398B3 100%); /* Opera 11.10+ */
			background: -ms-linear-gradient(top, #296C9B 0%,#7398B3 100%); /* IE10+ */
			background: linear-gradient(top, #296C9B 0%,#7398B3 100%); /* W3C */ }
		#menu li a.current {
			display:block;
			float:left;
			color:#fff;
			text-transform:uppercase;
			font-size:11px;
			font-weight:bold;
			text-decoration:none;
			padding:0 25px; }
		#menu li a:hover.current {
			color:#fff; }
	</style>

<?php

							/*--------------------------------------------------*/
							/*	класс формирующий меню для страницы index.php	*/
							/*								v.0.1 27.11.2013	*/
							/*--------------------------------------------------*/

class GetMenu
{
public function __construct(array $array)
	{
	?>
	<div id="menu">
		<ul>
		<?php
		foreach($array as $key => $value){		
			?><li class="current"><a href= <?php echo $value.">".$key; ?></a></li><?php
			}
		?>	
		</ul>
	</div>
	<?php	
	}
}

?>
