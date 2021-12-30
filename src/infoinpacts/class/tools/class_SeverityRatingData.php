<?php

						/*----------------------------------------------*/
						/*  		класс оценки критичности данных		*/
						/* 	 						v.0.31 08.10.2014   */
						/*----------------------------------------------*/

class SeverityRatingData
{
private $ReadXMLSetup;
function __construct()
	{
	//объект для чтения файла setup_site.xml
	$this->ReadXMLSetup = new ReadXMLSetup;
	}
//оценка критичности IP-адресов назначения
public function getSeverityRatingIpDst($ipDst)
	{
	//массив критичных IP-адресов
	$arrayCriticalIp = $this->ReadXMLSetup->giveCriticalDomainName();
	if(in_array($ipDst, $arrayCriticalIp)){
		return true;
		}
	return false;
	}	
}
?>