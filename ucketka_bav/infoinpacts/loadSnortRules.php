<?php
/*
 * Скрипт загрузки правил Снорт в таблицу 'signature_tables'  БД
 *
 * */

/*******************************************
Класс подключение к БД
 ********************************************/
class LinkConnectionDB
{
//данные для подключения к БД
    private static $dbHost = "localhost";
    private static $dbName = "data_on_KA";
    private static $dbUser = "analyst_connect";
    private static $dbPassword = "BG5&*VCYi12_";

//соединение с БД
    public function connectionDB () {
        try{
            //Создание переменной $DBO (Database Handle)
            $DBO = new PDO("mysql:host=".self::$dbHost."; dbname=".self::$dbName."", self::$dbUser, self::$dbPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
            $DBO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $err){
            echo "файл: ".$e->getFile()." линия: ".$e->getLine()." ошибка: ".$e->getMessage();
        }
        return $DBO;
    }
}

/*******************************************
Класс загрузки правил Snort
 ********************************************/
class LoadSnortRues
{
    private static $linkDB;

    //фабрика для подключения к БД
    private static function getLinkDB(){
        if(empty(self::$linkDB)){
            $linkConnectionDB = new LinkConnectionDB;
            self::$linkDB = $linkConnectionDB->connectionDB();
        }
        return self::$linkDB;
    }

    public function __construct(){
        try {
            self::getLinkDB()->query("CREATE TABLE IF NOT EXISTS `signature_tables` (
											  sid INT(10) UNSIGNED UNIQUE NOT NULL,
											  short_message VARCHAR(200),
											  snort_rules TEXT) ENGINE=MyISAM DEFAULT CHARSET=utf8");
        }
        catch(PDOException $err) {
            echo "файл: ".$err->getFile()." линия: ".$err->getLine()." ошибка: ".$err->getMessage();
        }
    }

    //загрузка правил в БД
    public function loadRules(){
        $path = '/var/www/html/infoinpacts/tmp/rules/';
        $arrayFileName = $this->getArrayListFiles($path);
        for($i = 0; $i < count($arrayFileName); $i++){
            $this->readFiles($path.$arrayFileName[$i]);
        }
    }

    //чтение файла
    private function readFiles($file){
        $handle = fopen($file, 'r');
        while(($buffer = fgets($handle)) !== false){
            if((substr($buffer, 0, 5) == 'alert') || (substr($buffer, 2, 5) == 'alert')){
                //echo '\n'.$buffer;
                list(, $string) = explode("(msg:\"", $buffer);
                $array = explode(";", $string);
                //краткое описание правила
                $short_message = htmlspecialchars(substr($array[0], 0, -1));
                //полное правило
                $snort_rules = htmlspecialchars($buffer);
                // загрузка в БД
                try{
                    $query = self::getLinkDB()->prepare("INSERT IGNORE `signature_tables` (
																`sid`,
																`short_message`,
																`snort_rules`)
																 VALUE (
																 '".intval(substr($array[(count($array) - 3)], 5))."',
															 	 :short_message,
												 				 :snort_rules)");
                    $query->execute(array(':short_message' => $short_message, ':snort_rules' => $snort_rules));
                }
                catch(PDOException $err){
                    echo "файл: ".$err->getFile()." линия: ".$err->getLine()." ошибка: ".$err->getMessage();
                }
            }
        }
        fclose($handle);
        unlink($file);
    }

    //получаем список файлов правил из DIR_ROOT/tmp/rules
    private function getArrayListFiles($pathDirName){
        //переходим в директорию с загруженными файлами
        chdir($pathDirName);
        //обработка ошибок открытия директории
        if (!$directory = opendir($pathDirName)) {
            echo "невозможно открыть директорию ".$pathDirName;
        }

        //чтение файлов
        while($files = readdir($directory)){
            if(($files != '.') && ($files != '..')){
                if(preg_match("/[\.rules]$/", $files)){
                    $arrayFiles[] = $files;
                }
            }
        }
        closedir($directory);
        return $arrayFiles;
    }
}

$loadSnortRules = new LoadSnortRues();
$loadSnortRules->loadRules();
?>