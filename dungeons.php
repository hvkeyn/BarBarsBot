<?php
// Битва в Пещерах с боссами
function Dungeons($UseDungeonsBottle,$Referer,$userAgent,$flog){
require_once("config.php"); // Конфиг
include_once("lib.php"); // библиотека функций
include_once("simple_html_dom.php"); // регулярки

// Для лога - процесс боя
$fLogDungeon = array(
"Пьем Лекарство!\n", 
"Активируем Абилку...\n", 
"Лечим союзника!\n", 
"Добиваем врага!(Жгем энергию!)\n",
"Бьем противника(Лечим союзников)...\n",
"Мы умерли:( Воскрешаем Героя...\n");

$dMenuLinks = array(
"http://barbars.ru/game/guardian", 
"http://barbars.ru/game/harpy", 
"http://barbars.ru/game/manticora", 
"http://barbars.ru/?wicket:bookmarkablePage=:com.overmobile.combats.wicket.pages.dungeon.minotaur.MinotaurPage",
"http://barbars.ru/game/irondragon");

fputs($flog, "Начинаем цикл битв с Боссами!\n");
		
// Переходим к основному меню
$url = "http://barbars.ru";
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		
				$Referer = $url;
		$int_i=0; 
		while($int_i<count($dMenuLinks)){
		$dun_step = 0;
		
		// очищаем буффер
		EraseMemory($html);	

		$url = "http://barbars.ru/game/dungeons";
	
		// Зашли в пещеры к драконам
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		//$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
				
		$url = $dMenuLinks[$int_i];	
		sleep(1);
		// Зашли в локацию для битвы...
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;

		// Проверяем, были мы тут или нет?
		if(strpos($html->find('h1',0)->innertext, "Пустой грот") !== false || strpos($html->find('h1',0)->innertext, "Пустая пещера") !== false || strpos($html->find('h1',0)->innertext, "Вход закрыт") !== false){
	    fputs($flog, "$int_i грот пуст или вход закрыт...\n");	
		$int_i++;	
		//continue;		
		} else {		
		// ЦИКЛ ПОДГОТОВКИ К БОЮ
		fputs($flog, "Встаем в очередь на босса...\n");
		// Ищем ссылку с текстом "Встать в очередь"
		foreach($html->find('a') as $dmenu){
		if(strpos($dmenu->innertext, "Встать в очередь") !== false){
		$url = $dmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		}
		}
		
		// очищаем буффер
		EraseMemory($html);
		
		// Встали в очередь для боя
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
				
		// Счетчик времени до выхода из	локации	
		$cnt=0;
		// Флаги выхода из циклов
		$isWaitR=0;
		$isMainR=0;
		// ЦИКЛ ОЖИДАНИЯ<->БОЙ
		while($isMainR==0){
		if($isWaitR == 0){
		// Ищем ссылку с текстом "Обновить"
		foreach($html->find('a') as $dmenu){
		if($cnt<=500){
		if(strpos($dmenu->innertext, "Обновить") !== false){
		$url = $dmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		fputs($flog, "$cnt - Ждем...\n");
		$cnt++;
		}
		if(strpos($dmenu->innertext, "Бить") !== false){
		$url = $dmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		$isWaitR=1;
		$isMainR=0;
		fputs($flog, "Бой с Боссом начался!\n");
		break 1;
		}
		// ЭКСТРЕНЫЙ ВЫХОД по stop
		if(file_exists("stop.txt")){ 
	    fputs($flog, "Аварийное завершение работы! Локация: Пещеры и Драконы [Выполнение команды КЛАНА прервано]\n");
		Die();
		}
		} else {
		if(strpos($dmenu->innertext, "Покинуть очередь") !== false){
		$url = $dmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		$isMainR=1;
		$isWaitR=0;
		fputs($flog, "Долго нет подходящей партии на Босса. Идем в другое место...\n");
		break 2;
		}		
		}
		}
		}
		
		// очищаем буффер
		EraseMemory($html);
		sleep(rand(1,3));
		// Встали в очередь для боя
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		if(!$zapros){ 
	    //fputs($flog, $dun_step."[EMPTY_ERROR]: URL=$url ||REFERER=$Referer\n");
		$url = "http://barbars.ru/game/dungeons";
		$Referer = "http://barbars.ru";
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		}		
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
				
		if($isWaitR == 1){
		// 2 - Ищем жизнь и энергию
		$life = trim($html->find('td[align*=left] span',0)->innertext);
		$energy = trim($html->find('td[align*=left] span',1)->innertext);
		
		// Решение проблемы с нулевым здоровьем и энергией(Обновление страницы)
		if($life == "" || $energy == "") {
		  $url = $dMenuLinks[$int_i];
		  $Referer = "http://barbars.ru/game/dungeons";
		  
		// очищаем буффер
		EraseMemory($html);
		
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
		
		// Ищем ссылку для атаки
		$e = $html->find('a[class=flhdr]',0);
        $url = $e->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		$name = $e->plaintext;
	    fputs($flog, "Проблемы с получением данных. Починили. Продолжаем битву!\n");
		
		// очищаем буффер
		EraseMemory($html);
		
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
				
		// 2 - Ищем жизнь и энергию
		$life = trim($html->find('td[align*=left] span',0)->innertext);
		$energy = trim($html->find('td[align*=left] span',1)->innertext);
		} // End.BadRequest
	    fputs($flog, $dun_step.". У героя ".$life." здоровья и ".$energy." энергии\n");
		
		// Обнуляем массив адресов
		$urls = Array();
		$urlt = "";
		
		// --------------!!!!! Алгоритм боя !!!!!----------------
		// 3 - Ищем адреса страниц действий
		foreach($html->find('a[class=flhdr]') as $links){	
		
		if(($life < 400 || $energy < 5) && $UseDungeonsBottle == true){
		// 3.0 - Проверяем наличие готовности бутылочки
		if((strpos($links->innertext, "Пить") !== false) && (strpos($links->innertext, "сек.") !== true)){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[0] = "http://barbars.ru/".$urlt;		
		}		
		}
		
		// 3.1 - Проверяем способности и активируем их, если готовы
		if(strpos($links->innertext, "(гoтoво)") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[1] = "http://barbars.ru/".$urlt;		
		}
		
		// 3.2 - Для мага - лечим если есть...	
		if(strpos($links->innertext, "Лечить") !== false && strpos($links->innertext, "% хп)") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[2] = "http://barbars.ru/".$urlt;//.substr($links->href,2);
		if(strpos($links->plaintext, "<span>200</span>") !== false) $urls[2] = "";
		}
		
		// 3.3 - для Мага - жгем энергию. Для воина - добиваем 
		if(strpos($links->innertext, "Дoбивать") !== false || strpos($links->innertext, "Жечь энергию у") !== false || strpos($links->innertext, "Жечь") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[3] = "http://barbars.ru/".$urlt;//.substr($links->href,2);	
		if(strpos($links->plaintext, "<span>0</span>") !== false) $urls[3] = "";		
		}
		
		// 3.4 - Ищем врага	если ничего не готово...
		if(strpos($links->innertext, "Бить") !== false || strpos($links->innertext, "Лечить союзников") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[4] = "http://barbars.ru/".$urlt;//.substr($links->href,2);
		}	
		
		// 3.5 - Враг нас убил! Выходим из грота
		if(strpos($links->innertext, "Воскреснуть") !== false || strpos($links->innertext, "Обновить") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[5] = "http://barbars.ru/".$urlt;//.substr($links->href,2);
		}	
		
		}
		
		// 4 - Определяем последовательность боя. Важность команд по "ценности"
		for($u=0;$u<6;$u++){
		if($urls[5] == "" && $urls[$u] != ""){ 
		   $url = $urls[$u];
		   fputs($flog, 4);	
		   break 1;
		} else if(strpos($html->find('h1',0)->innertext, "Пустой грот") !== false || strpos($html->find('h1',0)->innertext, "Пустая пещера") !== false){
		// Оп-па, босс повержен! (1 вариант)
		$isMainR = 1;
		$int_i++;
	    fputs($flog, "Закончили бой с Боссом!\n");	
	    break 1;		
		} else if($urls[5] != ""){
		// Оп-па, босс повержен! (2 вариант)
		$isMainR = 1;
		$int_i++;
	    fputs($flog, "Закончили бой с Боссом!\n");	
	    break 1;		
		}
		}
	
		$url = str_replace("&amp;", "&", $url);
		
		// очищаем буффер
		EraseMemory($html);
		
		// 1 - подгружаем страницу
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		
		// ЭКСТРЕНЫЙ ВЫХОД по stop
		if(file_exists("stop.txt")){ 
	    fputs($flog, "Аварийное завершение работы! Локация: Пещеры и драконы\n");
		Die();
		}

		sleep(rand(2,6));
		$dun_step++;
		}
		
		}
		
		} // End.Очеред моснтров
		
		}	

		// очищаем буффер
		EraseMemory($html);
		
		fputs($flog, "Закончили битву с Боссами!\n");
		
		return $Referer;
}

?>