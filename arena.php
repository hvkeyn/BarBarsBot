<?php
// Битва на арене
function Arena($colArenaCikl=5,$UseArenaBottle,$Referer,$userAgent,$flog,$gkey,$capcha_server){
require_once("config.php"); // Конфиг
include_once("lib.php"); // библиотека функций
include_once("simple_html_dom.php"); // регулярки
include_once("capcha.php"); // Антикапча

// Для лога - процесс боя
$fLogArena = array(
"Пьем Лекарство!\n", 
"Активируем Абилку...\n", 
"Лечим союзника!\n", 
"Добиваем врага!(Жгем энергию!)\n",
"Бьем противника (Лечим союзников)...\n",
"Ждем окончания боя...\n",
"Мы умерли!:( Выходим...\n",
"Мы Победили!:) Выходим...\n");

$ArenaCikl = 0; // начальный цикл
$DeadTrue = 0; // Признак смерти

// Основной цикл 
while($ArenaCikl < $colArenaCikl){
$dun_step = 0; // шаг боя

fputs($flog, "Входим на Арену!\n");
		
		// Переходим к основному меню
		$url = "http://barbars.ru";
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		
				$Referer = $url;	

		$url = "http://barbars.ru/game/arena";
	
		// Зашли в арену
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);
		
				$Referer = $url;
				
		if(strpos($zapros, "Ваш герой сбежал с поля боя.") !== false || strpos($zapros, "Вход на арену временно закрыт.") !== false) return $Referer;
				

		// Ищем ссылку с текстом "Новый бой"
		foreach($html->find('a') as $dmenu){
		if(strpos($dmenu->innertext, "Новый бой") !== false){
        $url = $dmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);		
		$zapros = get_contents($url, "", $Referer, $userAgent, 111);
		$Referer = $url;
		$url = $zapros;
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		$Referer = $url;
		// очищаем буффер
		EraseMemory($html);
	    fputs($flog, "Готовимся к новому бою на Арене...\n");		
		// Создаем DOM из URL
		$html = str_get_html($zapros);
		break;
		}
		
		// Ищем ссылку с текстом "Встать в очередь"
		if(strpos($dmenu->innertext, "Встать в очередь") !== false){
		$url = $dmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		// Встали в очередь на арене
		$zapros = get_contents($url, "", $Referer, $userAgent, 111);
		$Referer = $url;
		$url = $zapros;
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		$Referer = $url;
		// очищаем буффер
		EraseMemory($html);		
		fputs($flog, "Встали в очередь на Арене!\n");
		// Создаем DOM из URL
		$html = str_get_html($zapros);
		break;
		}
		}
				
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
		if(strpos($html, "Бой начнется через") === false){
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		} else {
		while(($url[0] == "." && $url[1] == ".")||($url[0] == "." && $url[1] == "/")||($url[0] == "/" && $url[1] == ".")||($url[0] == "/")) {
		$url = substr($url,1);
		}
		}		

		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		$cnt++;
		sleep(rand(1,3));	
		}
		if(strpos($dmenu->innertext, "Бить") !== false){
		$url = $dmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		$isWaitR=1;
		fputs($flog, "Бой на Арене начался!\n");
		break 1;
		}
		} else {
		if(strpos($dmenu->innertext, "Покинуть очередь") !== false){
		$url = $dmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		// очищаем буффер
		EraseMemory($html);	
		$zapros = get_contents($url, "", $Referer, $userAgent, 111);
		$Referer = $url;
		$url = $zapros;
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		$Referer = $url;
		
		$isWaitR=0;
		$isMainR=1;
		fputs($flog, "Долго нет подходящих противников для Арены. Уходим...\n");
		break 5;
		}		
		}
		}
		}
		
		// очищаем буффер
		EraseMemory($html);
		// Встали в очередь для боя
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Ошибка пустого ответа - иногда выбрасывает пустую страницу
		if(!$zapros){ 
	    //fputs($flog, $dun_step."[EMPTY_ERROR]: URL=$url ||REFERER=$Referer\n");
		$url = "http://barbars.ru/game/arena";
		$Referer = "http://barbars.ru";
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		}
		// Вдруг появится
		if(strpos($zapros, "Ваш герой сбежал с поля боя.") !== false || strpos($zapros, "Вход на арену временно закрыт.") !== false) return $Referer;
		// Создаем DOM из URL
		$html = str_get_html($zapros);
		
				$Referer = $url;
				
		if($isWaitR == 1){
		// 2 - Ищем жизнь и энергию
		$life = trim($html->find('td[align*=left] span',0)->innertext);
		$energy = trim($html->find('td[align*=left] span',1)->innertext);
		if($life != 0 && $energy != 0)
	    fputs($flog, $dun_step.". У героя ".$life." здоровья и ".$energy." энергии\n");
		
		// Обнуляем массив адресов
		$urls = Array();
		$urlt = "";

		// --------------!!!!! Алгоритм боя !!!!!----------------
		// 3 - Ищем адреса страниц действий
		foreach($html->find('a') as $links){	

		if(($life < 700 || $energy < 5) && $UseArenaBottle == true){
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
		
		// 3.5 - Враг нас убил! Дожидаемся конца боя...
		if(strpos($links->innertext, "Обновить") !== false && strpos($zapros, "Ваш герой погиб") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		fputs($flog, $fLogArena[5]);
		$urls[5] = "http://barbars.ru/".$urlt;//.substr($links->href,2);
		$DeadTrue = 1;
		//break 1;		
		}	
		
		// 3.6 - Победа или поражение! Выходим с арены...
		if(strpos($links->innertext, "Покинуть арену") !== false || strpos($links->innertext, "Новый бой") !== false){
		//$urlt = $links->href;
		//while($urlt[0] != "?") {
		//$urlt = substr($urlt,1);
		//}
		if($DeadTrue == 1)
		fputs($flog, $fLogArena[6]);
		else
		fputs($flog, $fLogArena[7]);
		
		$urls[6] = "http://barbars.ru/";//.$urlt;//.substr($links->href,2);
		break 1;
		}	
		
		}
	/*
		if(strpos($zapros, "Ваш герой погиб") !== false) {
		$urls[6] = "http://barbars.ru/";
		fputs($flog, $fLogArena[5]);
		}
	*/	
		
		// 4 - Определяем последовательность боя. Важность команд по "ценности"
		for($u=0;$u<7;$u++){
		if($urls[6] == "" && $urls[$u] != ""){ 
		   $url = $urls[$u];
		   fputs($flog, $fLogArena[$u]);	
		   break 1;
		} else if($urls[6] != ""){
		// Оп-па, мы победили или проиграли!
		$isMainR = 1;
	    fputs($flog, "Закончили битву на Арене!\n");	
	    break 2;		
		}
		}
	
		$url = str_replace("&amp;", "&", $url);
		
		// очищаем буффер
		EraseMemory($html);
		
		// 1 - подгружаем страницу
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		
		// ЭКСТРЕНЫЙ ВЫХОД по stop
		if(file_exists("stop.txt")){ 
	    fputs($flog, "Аварийное завершение работы! Локация: Арена\n");
		Die();
		}

		sleep(rand(1,3));
		$dun_step++;
		}
		
		}	

		// очищаем буффер
		EraseMemory($html);
		
		if(strpos($zapros, "Ваш герой сбежал с поля боя.") !== false || strpos($zapros, "Вход на арену временно закрыт.") !== false) return $Referer;

		$DeadTrue = 0; // Признак смерти		
		$ArenaCikl++;
		sleep(rand(5,12));	
// C - Антикапча. Проверяем и вводим если нашли.
list($Referer, ) = anticapcha($Referer,$userAgent,$flog,$gkey,$capcha_server);
sleep(rand(2,10));			
		} //End.Основной цикл боя на Арене	
		
		return $Referer;
}

?>