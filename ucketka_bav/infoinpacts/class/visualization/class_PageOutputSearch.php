<?php

							/*--------------------------------------------------------*/
							/*														  */
							/*		класс постраничной навигации найденных значений   */
							/*			навигация основывается на делении массива 	  */
							/*			исходных (найденных) значений	 			  */
							/* 														  */
							/*								v0.1	09.04.2015	 	  */
							/* 														  */		
							/*--------------------------------------------------------*/

class PageOutputSearch
{
private $stringLimit;
private $linkLimit;
private $allNum;
private $array_pages;
	
//функция формирования многомерного массива состоящего из частей (ссылок в каждом состоянии) 
//например, выводим на страницу 5 ссылок по 10 элементов, для 100 элементов будет 2 части (chunk)
//public function giveArrayAllPage()

/* конструктор
получаем значения следующих элементов:
	$stringLimit - количество элементов выводимых на страницу
	$linkLimit - количество ссылок выводимых на страницу
	$allNum - общее количество найденных элементов
и создаем многомерный массив содержащий необходимое смещение и разбитый на части
	$array_pages
*/
public function __construct($stringLimit, $linkLimit, $allNum)
	{
	$this->stringLimit = $stringLimit;
	$this->linkLimit = $linkLimit;
	$this->allNum = $allNum;
	
	//получаем кол-во страниц
	$pages = ceil($allNum / $stringLimit);
	//создаем массив содержащий количество страниц и число смещения $start
	$array_pages = array();
	for($i = 0; $i < $pages; $i++){
		$array_pages[$i+1] = $i * $stringLimit;
		}
	//разбиваем массив на части равные параметру $link_limit
	$this->array_pages = array_chunk($array_pages, $linkLimit, true); //true сохраняет ключи массива
	}

//функция поиска страницы ($need_page) которую необходимо выводить на экран	
public function searchPage($need_page) 
	{
	foreach($this->array_pages as $chunk => $pages){
		if(in_array($need_page, $pages)){
			return $chunk;
			}
		}
	return 0;
	}

//функция вывода на страницу постраничных ссылок
//var_name - имя GET переменной содержащей номер смещения
//$start - текущее смещение элементов  
public function giveLinks($var_name, $start) 
	{
	// ничего не выводить если количество элементов выводимых на страницу ($string_limit) больше или равен
	//	общему количеству найденных элементов ($allNum) и если $stringLimit = 0 
	if($this->stringLimit >= $this->allNum || $this->stringLimit == 0) return null;
	//чистим строку запроса от нашей переменной var_name
	parse_str($_SERVER['QUERY_STRING'], $var_page);	
	if(isset($var_page[$var_name])){
		unset($var_page[$var_name]);
		}
	//формируем такую же ссылку, ведущую на такую же страницу
	$link = $_SERVER['PHP_SELF']."?".http_build_query($var_page);
	//необходимый ключ массива
	$nead_chunk = $this->searchPage($start);
	//выводим ссылки 	
	$linksOut = null;
	//ссылки "в начало" и "предыдущая"
	if($start > 1){
		$linksOut .= "<a href='".$link."&".$var_name."=0'>&laquo;</a>
					  <a href='".$link."&".$var_name."=".($start - $this->stringLimit)."'>&lsaquo;</a>";
		} else {
		$linksOut .= "<span class='pageActive'>&laquo;</span>
					  <span class='pageActive'>&lsaquo;</span>";	
		}
	//вывод основных ссылок	
	foreach($this->array_pages[$nead_chunk] as $pageNum => $ofset)
		{
		//текущая страница не активная
		if($ofset == $start){
			$linksOut .= "<span class='pageActive'>".$pageNum."</span>";
			continue;	
			}
		$linksOut .= "<a href='".$link."&".$var_name."=".$ofset."'>".$pageNum."</a>";
		}
	//ссылки "в конец" и "следующая"
	if(($this->allNum - $this->stringLimit) > $start){
		$linksOut .= "<a href='".$link."&".$var_name."=".($start + $this->stringLimit)."'>&rsaquo;</a>
					  <a href='".$link."&".$var_name."=".array_pop((array_pop($this->array_pages)))."'>&raquo;</a>";
		} else {
		$linksOut .= "<span class='pageActive'>&rsaquo;</span>
					  <span class='pageActive'>&raquo;</span>";
		}
	return $linksOut;
	}

}

?>