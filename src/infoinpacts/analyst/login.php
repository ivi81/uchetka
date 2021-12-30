<?php
//получаем корневую директорию сайта
$array_directory = explode('/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); 

header("Location:/{$array_directory[1]}/analyst/index.php");
?>