<?php

										/*--------------------------*/
										/*	класс склонение слов	*/
										/*		v0.1 05.03.2014		*/
										/*--------------------------*/

class DeclansionWord
{
//склонение слов перед с числами
public static function declansionNum($number, $array_word) 
	{
	if($number > 0){
		$number = abs($number) % 100;
		$n1 = $number % 10;
		if($number > 10 && $number < 20) return $array_word[2];
		if($n1 > 1 && $n1 < 5) return $array_word[1];
		if($n1 == 1) return $array_word[0];
		}	
	return $array_word[2];	
	}
}
?>
