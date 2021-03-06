<?php

						/*----------------------------------------------------------------------*/
						/*  класс поиска IP-адреса по таблице "geoip_data" в базе данных MySql	*/
						/* 					 						 	   v.0.11 02.04.2015     */
						/*----------------------------------------------------------------------*/

class GeoIP
{
public $Code;
private $Array_file_flag;
//основная директория сайта
private $directory;
public static $codeCountry = array('AB' => 'Абхазия', 
		  						   'AU' => 'Австралия', 
								   'AT' => 'Австрия', 
								   'AZ' => 'Азербайджан',
								   'AP' => 'Азия',
								   'AL' => 'Албания', 
								   'DZ' => 'Алжир', 
								   'AS' => 'Американское Самоа', 
								   'AI' => 'Ангилья', 
							  	   'AO' => 'Ангола', 
								   'AD' => 'Андорра',
			  					   'A1' => 'Анонимный прокси', 
								   'AQ' => 'Антарктида', 
								   'AG' => 'Антигуа и Барбуда', 
								   'AR' => 'Аргентина', 
								   'AM' => 'Армения', 
								   'AW' => 'Аруба', 
								   'AF' => 'Афганистан', 
								   'BS' => 'Багамы', 
								   'BD' => 'Бангладеш', 
								   'BB' => 'Барбадос', 
								   'BH' => 'Бахрейн', 
								   'BY' => 'Беларусь', 
								   'BZ' => 'Белиз', 
								   'BE' => 'Бельгия', 
								   'BJ' => 'Бенин', 
								   'BM' => 'Бермуды', 
								   'BG' => 'Болгария', 
								   'BO' => 'Боливия, Многонациональное Государство', 
								   'BQ' => 'Бонайре, Саба и Синт-Эстатиус', 
								   'BA' => 'Босния и Герцеговина', 
								   'BW' => 'Ботсвана', 
								   'BR' => 'Бразилия', 
								   'IO' => 'Британская территория в Индийском океане', 
								   'BN' => 'Бруней-Даруссалам', 
								   'BF' => 'Буркина-Фасо', 
								   'BI' => 'Бурунди', 
								   'BT' => 'Бутан', 
								   'VU' => 'Вануату', 
								   'HU' => 'Венгрия', 
								   'VE' => 'Венесуэла Боливарианская Республика', 
								   'VG' => 'Виргинские острова, Британские', 
								   'VI' => 'Виргинские острова, США', 
								   'VN' => 'Вьетнам', 
								   'GA' => 'Габон', 
								   'HT' => 'Гаити', 
								   'GY' => 'Гайана', 
								   'GM' => 'Гамбия ', 
								   'GH' => 'Гана', 
								   'GP' => 'Гваделупа', 
								   'GT' => 'Гватемала', 
								   'GN' => 'Гвинея', 
								   'GW' => 'Гвинея-Бисау', 
								   'DE' => 'Германия', 
								   'GG' => 'Гернси', 
								   'GI' => 'Гибралтар', 
								   'HN' => 'Гондурас', 
								   'HK' => 'Гонконг', 
								   'GD' => 'Гренада', 
								   'GL' => 'Гренландия', 
								   'GR' => 'Греция', 
								   'GE' => 'Грузия', 
								   'GU' => 'Гуам', 
								   'DK' => 'Дания', 
								   'JE' => 'Джерси', 
								   'DJ' => 'Джибути', 
								   'DM' => 'Доминика', 
								   'DO' => 'Доминиканская Республика',
								   'EU' => 'Европа', 
								   'EG' => 'Египет', 
								   'ZM' => 'Замбия', 
								   'EH' => 'Западная Сахара', 
								   'ZW' => 'Зимбабве', 
								   'IL' => 'Израиль', 
								   'IN' => 'Индия', 
								   'ID' => 'Индонезия', 
								   'JO' => 'Иордания', 
								   'IQ' => 'Ирак', 
								   'IR' => 'Иран, Исламская Республика', 
								   'IE' => 'Ирландия', 
								   'IS' => 'Исландия', 
								   'ES' => 'Испания', 
								   'IT' => 'Италия', 
								   'YE' => 'Йемен', 
								   'CV' => 'Кабо-Верде', 
								   'KZ' => 'Казахстан', 
								   'KH' => 'Камбоджа', 
								   'CM' => 'Камерун', 
								   'CA' => 'Канада', 
								   'QA' => 'Катар', 
								   'KE' => 'Кения', 
								   'CY' => 'Кипр', 
								   'KG' => 'Киргизия', 
								   'KI' => 'Кирибати', 
								   'CN' => 'Китай', 
								   'CC' => 'Кокосовые (Килинг) острова', 
								   'CO' => 'Колумбия', 
								   'KM' => 'Коморы', 
								   'CG' => 'Конго', 
								   'CD' => 'Конго, Демократическая Республика', 
								   'KP' => 'Корея, Народно-Демократическая Республика', 
								   'KR' => 'Корея, Республика', 
								   'CR' => 'Коста-Рика', 
								   'CI' => 'Кот д\'Ивуар', 
								   'CU' => 'Куба', 
								   'KW' => 'Кувейт', 
								   'CW' => 'Кюрасао', 
								   'LA' => 'Лаос', 
								   'LV' => 'Латвия', 
								   'LS' => 'Лесото', 
								   'LB' => 'Ливан', 
								   'LY' => 'Ливийская Арабская Джамахирия', 
								   'LR' => 'Либерия', 
								   'LI' => 'Лихтенштейн', 
								   'LT' => 'Литва', 
								   'LU' => 'Люксембург', 
								   'MU' => 'Маврикий', 
								   'MR' => 'Мавритания', 
								   'MG' => 'Мадагаскар', 
								   'YT' => 'Майотта', 
								   'MO' => 'Макао', 
								   'MW' => 'Малави', 
								   'MY' => 'Малайзия', 
								   'ML' => 'Мали', 
								   'UM' => 'Малые Тихоокеанские отдаленные острова Соединенных Штатов', 
								   'MV' => 'Мальдивы', 
								   'MT' => 'Мальта', 
								   'MA' => 'Марокко', 
								   'MQ' => 'Мартиника', 
								   'MH' => 'Маршалловы острова', 
								   'MX' => 'Мексика', 
								   'FM' => 'Микронезия, Федеративные Штаты', 
								   'MZ' => 'Мозамбик', 	
								   'MD' => 'Молдова, Республика', 
								   'MC' => 'Монако', 
								   'MN' => 'Монголия', 
								   'MS' => 'Монтсеррат', 
								   'MM' => 'Мьянма', 
								   'NA' => 'Намибия', 
								   'NR' => 'Науру', 
								   'NP' => 'Непал', 
								   'NE' => 'Нигер', 
								   'NG' => 'Нигерия', 
								   'NL' => 'Нидерланды', 
								   'NI' => 'Никарагуа', 
								   'NU' => 'Ниуэ', 
								   'NZ' => 'Новая Зеландия', 
								   'NC' => 'Новая Каледония', 
								   'NO' => 'Норвегия', 
								   'AE' => 'Объединенные Арабские Эмираты', 
								   'OM' => 'Оман', 
								   'BV' => 'Остров Буве', 
								   'IM' => 'Остров Мэн', 
								   'NF' => 'Остров Норфолк', 
								   'CX' => 'Остров Рождества', 
								   'HM' => 'Остров Херд и острова Макдональд', 
								   'KY' => 'Острова Кайман', 
								   'CK' => 'Острова Кука', 
								   'TC' => 'Острова Теркс и Кайкос', 
								   'PK' => 'Пакистан', 
								   'PW' => 'Палау', 
								   'PS' => 'Палестинская территория, оккупированная', 
								   'PA' => 'Панама', 
								   'VA' => 'Папский Престол (Государство — город Ватикан)', 
								   'PG' => 'Папуа-Новая Гвинея', 
								   'PY' => 'Парагвай', 
								   'PE' => 'Перу', 
								   'PN' => 'Питкерн', 
								   'PL' => 'Польша', 
								   'PT' => 'Португалия', 
								   'PR' => 'Пуэрто-Рико', 
								   'MK' => 'Республика Македония', 
								   'RE' => 'Реюньон', 
								   'RU' => 'Россия', 
								   'RW' => 'Руанда', 
								   'RO' => 'Румыния', 
								   'WS' => 'Самоа', 
								   'SM' => 'Сан-Марино', 
								   'ST' => 'Сан-Томе и Принсипи', 
								   'SA' => 'Саудовская Аравия', 
								   'SZ' => 'Свазиленд', 
								   'SH' => 'Святая Елена, Остров вознесения, Тристан-да-Кунья', 
								   'MP' => 'Северные Марианские острова', 
								   'BL' => 'Сен-Бартельми', 
								   'MF' => 'Сен-Мартен', 
								   'SN' => 'Сенегал', 
								   'VC' => 'Сент-Винсент и Гренадины', 
								   'LC' => 'Сент-Люсия', 
								   'KN' => 'Сент-Китс и Невис', 
								   'PM' => 'Сент-Пьер и Микелон', 
								   'RS' => 'Сербия', 
								   'SC' => 'Сейшелы', 
								   'SG' => 'Сингапур', 
								   'SX' => 'Синт-Мартен', 
								   'SY' => 'Сирийская Арабская Республика', 
								   'SK' => 'Словакия', 
								   'SI' => 'Словения', 
								   'GB' => 'Соединенное Королевство', 
								   'US' => 'Соединенные Штаты', 
								   'SB' => 'Соломоновы острова', 
								   'SO' => 'Сомали', 
								   'SD' => 'Судан', 
								   'SR' => 'Суринам', 
								   'SL' => 'Сьерра-Леоне', 
								   'TJ' => 'Таджикистан', 
								   'TH' => 'Таиланд', 
								   'TW' => 'Тайвань (Китай)', 
								   'TZ' => 'Танзания, Объединенная Республика', 
								   'TL' => 'Тимор-Лесте', 
								   'TG' => 'Того', 
								   'TK' => 'Токелау', 
								   'TO' => 'Тонга', 
								   'TT' => 'Тринидад и Тобаго', 
								   'TV' => 'Тувалу', 
								   'TN' => 'Тунис', 
								   'TM' => 'Туркмения', 
								   'TR' => 'Турция', 
								   'UG' => 'Уганда', 
								   'UZ' => 'Узбекистан', 
								   'UA' => 'Украина', 
								   'WF' => 'Уоллис и Футуна', 
								   'UY' => 'Уругвай', 
								   'FO' => 'Фарерские острова', 
								   'FJ' => 'Фиджи', 
								   'PH' => 'Филиппины', 
								   'FI' => 'Финляндия', 
								   'FK' => 'Фолклендские острова (Мальвинские)', 
								   'FR' => 'Франция', 
								   'GF' => 'Французская Гвиана', 
								   'PF' => 'Французская Полинезия', 
								   'TF' => 'Французские Южные территории', 
							  	   'HR' => 'Хорватия', 
								   'CF' => 'Центрально-Африканская Республика', 
								   'TD' => 'Чад', 
							 	   'ME' => 'Черногория', 
								   'CZ' => 'Чешская Республика', 
								   'CL' => 'Чили', 
								   'CH' => 'Швейцария', 
								   'SE' => 'Швеция', 
								   'SJ' => 'Шпицберген и Ян Майен', 
								   'LK' => 'Шри-Ланка', 
								   'EC' => 'Эквадор', 
								   'GQ' => 'Экваториальная Гвинея', 
								   'AX' => 'Эландские острова', 
								   'SV' => 'Эль-Сальвадор', 
								   'ER' => 'Эритрея', 
								   'EE' => 'Эстония', 
								   'ET' => 'Эфиопия', 
								   'ZA' => 'Южная Африка', 
								   'GS' => 'Южная Джорджия и Южные Сандвичевы острова', 
								   'OS' => 'Южная Осетия', 
								   'SS' => 'Южный Судан', 
								   'JM' => 'Ямайка', 
								   'JP' => 'Япония');

public function __construct() 
	{
	//получаем корневую директорию сайта
	$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));	
	$this->directory = $array_directory[1];
		
	chdir($_SERVER['DOCUMENT_ROOT']."/$this->directory/img/flags/");
	$array_file_flag = scandir($_SERVER['DOCUMENT_ROOT']."/$this->directory/img/flags/");
	if (!$array_file_flag){
		echo MessageErrors::userMessageError(MessageErrors::ERROR_FILE,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage()); 
		}
	$this->Array_file_flag = $array_file_flag;
	}							
							
//определения страны по IP-адресу
public function countryIP($DBO, $ip_src)
		{
		//поиск IP-адреса в базе geoip
		try{
			$result = $DBO->connectionDB()->prepare("SELECT `country`,`code` FROM `geoip_data` WHERE start<=:ip_src AND end>=:ip_src");
			$result->bindParam(':ip_src', $ip_src);
			$result->execute();
			if($row = $result->fetch(PDO::FETCH_OBJ)){
				$Country = $row->country;
				$this->Code = $row->code;
				} else {
				$Country = "<strong>IP-адрес не найден</strong>";
				$this->Code = "10"; 				
				}  
			}
		//обрабатываем ошибки
		catch(PDOException $e){
			$Country = "Error";
			echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
			}
		return $Country;
		}
		
//получение пути расположения картинки с флагом
public function flags() 
	{
	foreach($this->Array_file_flag as $file){
		if(($file !=".") && ($file !="..")){
 			if(preg_match("/^[a-zA-Z0-9]{2}[.][p][n][g]$/", $file)){
				if(strcmp($this->Code, substr($file, 0, 2)) == 0){
					return $file;
					}			
				}	
			}
		}
	}

//получение названия и флага страны по коду страны 	
public function nameAndFlagsOfCode($code, $flag = "") 
	{
	$path = "/{$this->directory}/img/flags/";
	foreach($this->Array_file_flag as $file){
		if(($file != ".") && ($file != "..")){
			if(preg_match("/^[a-zA-Z0-9]{2}[.][p][n][g]$/", $file)){
				if(strcmp($code, substr($file, 0, 2)) == 0){
					$flag = $file;						
					}			
				}
			}
		}
	if(($code == "10") || ($code == "20") || ($code == "")){
		return '<strong>IP-адрес не найден</strong> <img src="'.$path.'10.png" />';
		} else {
		return self::$codeCountry[$code].' <img src="'.$path.$flag.'" />';
		}
	}	
	
//поиск среди IP-адресов источников адреса принадлежащее определенной стране
public function seachCountryByIP($DBO, $ip_src, $countryCode) 
	{
	try{
		$result = $DBO->connectionDB()->prepare("SELECT `country`,`code` FROM `geoip_data` 
												 WHERE start<=:ip_src AND end>=:ip_src AND `code`=:countryCode");
		$result->bindParam(':countryCode', $countryCode);		
		$result->bindParam(':ip_src', $ip_src);
		$result->execute();
		if($row = $result->fetch(PDO::FETCH_OBJ)){
			return $ip_src;
			}
		return null; 
		}
	//обрабатываем ошибки
	catch(PDOException $e){
		$Country = "Error";
		echo MessageErrors::userMessageError(MessageErrors::ERROR_SQL,"\t файл: ".$e->getFile()."\t линия: ".$e->getLine()."\t ошибка: ".$e->getMessage());
		}
	}
}
?>