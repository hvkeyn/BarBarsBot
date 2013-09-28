<?php
ini_set('allow_url_fopen',1);
require_once("config.php"); // Конфиг
include_once("lib.php"); // библиотека функций
include_once("simple_html_dom.php"); // регулярки

function repairMain($Referer,$userAgent,$flog){
// Переходим к герою
$url = "http://barbars.ru/user";
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;

		// Ищем ссылку с текстом "Снаряжение"
		foreach($html->find('a') as $muser){
		if(strpos($muser->innertext, "Снаряжение") !== false){
		$url = "http://barbars.ru/".$muser->href;	
		}
		}
		// очищаем буффер
		EraseMemory($html);
		
		// Зашли в снаряжение.
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;

		// Ищем ссылку с текстом починки вещей
		foreach($html->find('a') as $muser){
		if(strpos($muser->innertext, "Починить") !== false){
        $url = $muser->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);	
		$col_zeleza = $muser->find('span',0)->innertext;
	    fputs($flog, "Починили все вещи за $col_zeleza железа!\n");	
		// Чиним вещи...
		$Referer = $url;
		$Referer = get_contents($url, "", $Referer, $userAgent, true);

		break 1;
		//$Referer = $url;		
		}
		}
		// очищаем буффер
		EraseMemory($html);	
	
		return $Referer;
}

// Разбор барахла в рюкзаке на металл
function junkMain($Referer,$userAgent,$flog){
// на всякий случай!
		// очищаем буффер
		EraseMemory($html);
		
$EXIT_RACK = 0; // Для цикла обработки вещей
// Переходим к герою
$url = "http://barbars.ru/user";
		$zapros = get_contents($url, "", $Referer, $userAgent, false);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;

		// Ищем ссылку с текстом "Рюкзак"
		if(strpos($zapros, "Рюкзак") !== false){
		$url = "http://barbars.ru/user/rack";
		}
	
		// Зашли в рюкзак
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
		// Заходим в сам рюкзак(цикл обработки всех вещей)
		while($EXIT_RACK == 0){
		$EXIT_RACK = 1;

		// Ищем ссылку с текстом "разобрать"
		foreach($html->find('a') as $citems){
		if(strpos($citems->innertext, "разобрать") !== false || strpos($citems->innertext, "выкинуть") !== false){
		$url = $citems->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		if($citems->find('span',0))
		$col_zeleza = $citems->find('span',0)->innertext;
		else $col_zeleza = false;
		
		$EXIT_RACK = 0;
		break 1;
		} 
		}	
		
		// Нашли предмет? Переходим по ссылке!
		if($EXIT_RACK == 0){
		// очищаем буффер
		EraseMemory($html);
		$zapros = get_contents($url, "", $Referer, $userAgent, true);
		$Referer = $url;
		$url = $zapros;
		$zapros = get_contents($url, "", $Referer, $userAgent, false);

		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
		if($col_zeleza !== false)
	    fputs($flog, "Разобрали предмет и получили +$col_zeleza железа!\n");
		else
	    fputs($flog, "Выкинули предмет!\n");		
		}

		} //End.While
		
		// Ищем ссылку с текстом "разобрать"
		foreach($html->find('a') as $citems){
		if(strpos($citems->innertext, "Вернуться в бой") !== false){
		$url = $citems->href;
		while(($url[0] == "." && $url[1] == ".")||($url[0] == "." && $url[1] == "/")||($url[0] == "/" && $url[1] == ".")) {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru".$url;
		$url = str_replace("&amp;", "&", $url);
		}
		}
		
		// очищаем буффер
		EraseMemory($html);
		
	    fputs($flog, "Закончили разбор рюкзака!\n");
		return $url;
}

?>