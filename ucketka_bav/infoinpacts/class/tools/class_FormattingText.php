<?php

							/*----------------------------------------------------------*/
							/*	  класс форматирования строки под опредеоенный размер	*/
							/*															*/
							/* 	 								 v 0.1 13.05.2015       */
							/*															*/
							/*----------------------------------------------------------*/

class FormattingText
{
/*
	Метод formattingTextLength выполняет форматирование сторки, в результате
	выводится сторка ширина которой не превышает заданного количества символов ($maxStringLength).
	При этом если в строке встречается слово длинна которого больше $maxStringLength, данное слово
	делется на соответствующие сегменты

Входные параметры:
 - $string - входная сторка
 - $maxStringLength - максимальная щирина строки в символах
*/
public static function formattingTextLength($string, $maxStringLength)
	{
	//устанавливаем кодировку скрипта
	mb_internal_encoding('UTF-8');
	//из-за того что кирилица это многобайтовая кодировка и в UTF-8 1 символ это 2 символа
	$stringLength = mb_strlen($string, 'UTF-8');
	//если строка пустая
	if($stringLength <= $maxStringLength || $stringLength == 0) return $string;
	//получаем массив из строк
	$arrayString = explode(' ', $string);
	$countArrayString = count($arrayString);
	$resultString = '';
	for($numArray = 0; $numArray < $countArrayString; $numArray++){
		$lengthArrayString = mb_strlen($arrayString[$numArray], 'UTF-8');
		$searchString = mb_strripos($resultString, '<br>', 0, 'UTF-8');
		if($lengthArrayString < $maxStringLength){
			//получаем длинну строки
			$length = self::searchLengthString($arrayString[$numArray],
										 $resultString, 
										 $searchString);
			//формируем $resultString
			if($length > $maxStringLength){
				$resultString .= '<br>'.$arrayString[$numArray];
				} else {
				$resultString .= ' '.$arrayString[$numArray];
				}
			} else {
			//ищем длинну предыдущего слова
			$lengthWord = self::searchLengtWord($resultString);
			//получаем необходимую для заполнения строки длинну 
			$lengthFirst = $maxStringLength - $lengthWord - 1;
			//формируем дополняем первую строку
			$resultString .= ' '.mb_substr($arrayString[$numArray], 0, $lengthFirst);
			//получаем остаток строки
			$remainderString = mb_substr($arrayString[$numArray], $lengthFirst);
			$lengthRemainderString = mb_strlen($remainderString, 'UTF-8');
			$countString = ceil($lengthRemainderString / $maxStringLength);
			$start = 0;
			for($j = 0; $j < $countString; $j++){
				$resultString .= '<br>'.mb_substr($remainderString, $start, $maxStringLength);
				$start += $maxStringLength;
				}
			}
		}
	return $resultString;
	}

//поиск длинны строки
private static function searchLengthString($elem, $result, $searchString)
	{
	if(!$searchString){
		return mb_strlen($result.' '.$elem, 'UTF-8');
		} else {
		$stringTmp = mb_substr($result, $searchString + 4);
		return mb_strlen($stringTmp.' '.$elem, 'UTF-8');				
		}
	}

//получаем длинну оставшегося слова
private static function searchLengtWord($resultString)
	{
	$searchString = mb_strripos($resultString, '<br>', 0, 'UTF-8');
	return mb_strlen(mb_substr($resultString, $searchString + 4));
	}
}
//$text = 'оченьмногословималоделавсегда привлекали людей тестовыйскриптроверяющийправельностьслов который не хочет работать ох и ах';
//$text = 'очень много слов и малодела всегда привлекали людей тестовый текст для проверки функции и оценки результата';
/*
$text = 'тест один search красивый попугай 33 value + 67 тестовых страниц \\/../..//../..//../..//../..//../..//../..//../..//../..//../../ и еще раз тест 5555544444443333333322222221111111';
echo $text;
echo '<br><br>';
$result = FormattingText::formattingTextLength($text, 22);
echo $result.'<br><br>';
$array = explode('<br>', $result);
foreach($array as $value){
	echo $value.' = '.mb_strlen($value).'<br>';
}
*/
?>