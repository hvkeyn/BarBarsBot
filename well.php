<?php
// Получаем ежедневный подарок
function wellGift($Referer,$userAgent,$flog){
require_once("config.php"); // Конфиг
include_once("lib.php"); // библиотека функций
include_once("simple_html_dom.php"); // регулярки
$gift_d = 0; // нет подарка
		
// Переходим к герою
$url = "http://barbars.ru/user";
		$zapros = get_contents($url, "", $Referer, $userAgent, false);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;

		$url = "http://barbars.ru/trade/well";
	
		// Зашли в колодец
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;

		// Ищем ссылку с текстом "подарок"
		foreach($html->find('a') as $gifts){
		if(strpos($gifts->innertext, "подарок") !== false){
		$url = $gifts->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		$gift_d = 1;
		}
		}	
		
		// Нашли предмет? Переходим по ссылке!
		if($gift_d == 1){
		// очищаем буффер
		EraseMemory($html);
		$zapros = get_contents($url, "", $Referer, $userAgent, true);
		$Referer = $url;
		$url = $zapros;
		$zapros = get_contents($url, "", $Referer, $userAgent, false);

		// Создаем DOM из URL
		//$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
	    fputs($flog, "Получили ежедневный подарок!\n");		
		} 

		// очищаем буффер
		EraseMemory($html);
		
		return $Referer;
}

?>