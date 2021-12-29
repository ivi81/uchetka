<?php

							/*------------------------------------------------------------------*/
							/*  класс-фабрика для выбора объекта используемого при Ajax запросе */
							/* 	 											 v.0.1 21.10.2014   */
							/*------------------------------------------------------------------*/

class FactoryObjectForAiaxResponse
{
public static function getObjectForAjaxResponse($query)
	{
	//для подготовленных писем
	if($query == 'queryMailMajor' || $query == 'queryMailMajorInfo') return new ShowInformationPreparedMail;
	//для статистики по GeoIP
	elseif($query == 'statisticsGeoIP') return new ShowStatisticsGeoIP;
	else return false;
	}
}
?>