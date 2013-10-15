<?php
// Поиск Комманд управления
function FindClanCommand($Referer,$userAgent,$flog){
include_once("lib.php"); // библиотека функций
include_once("simple_html_dom.php"); // регулярки
// Данные главной страницы
$url = "http://barbars.ru";
$zapros = get_contents($url, "", $Referer, $userAgent, false);
$html = str_get_html($zapros);
$Referer = $url;

$clanCommTrue = -1;

foreach($html->find('span[class=info]') as $links){
if((strpos($links->innertext, "миф") !== false) || (strpos($links->innertext, "мифа") !== false) || (strpos($links->innertext, "Мифа") !== false) || (strpos($links->innertext, "Миф") !== false) || (strpos($links->innertext, "МИФ") !== false) || (strpos($links->innertext, "МИФА") !== false)){
fputs($flog, "Обнаружили команду КЛАНА на битву с Мифическим Драконом!\n");
$clanCommTrue = 0; // Активируем работу по мифическому дракону
break;
}

if((strpos($links->innertext, "велы") !== false) || (strpos($links->innertext, "велов") !== false) || (strpos($links->innertext, "Великаны") !== false) || (strpos($links->innertext, "великаны") !== false) || (strpos($links->innertext, "великанов") !== false) || (strpos($links->innertext, "веллы") !== false) || (strpos($links->innertext, "веллов") !== false)){
fputs($flog, "Обнаружили команду КЛАНА на битву с Великанами!\n");
$clanCommTrue = 1; // Активируем работу по великанам
break;
}

if((strpos($links->innertext, "тролль") !== false) || (strpos($links->innertext, "Тролль") !== false) || (strpos($links->innertext, "троль") !== false) || (strpos($links->innertext, "троллем") !== false)){
fputs($flog, "Обнаружили команду КЛАНА на битву с Каменным троллем!\n");
$clanCommTrue = 2; // Активируем работу по троллю
break;
}

if((strpos($links->innertext, "немезида") !== false) || (strpos($links->innertext, "Немезида") !== false) || (strpos($links->innertext, "немезиду") !== false) || (strpos($links->innertext, "немезидой") !== false)){
fputs($flog, "Обнаружили команду КЛАНА на битву с Немезидой!\n");
$clanCommTrue = 3; // Активируем работу по немезиде
break;
}

if((strpos($links->innertext, "троф") !== false) || (strpos($links->innertext, "Троф") !== false)  || (strpos($links->innertext, "трофа") !== false)){
fputs($flog, "Обнаружили команду КЛАНА на битву с Трофейным драконом!\n");
$clanCommTrue = 4; // Активируем работу по трофу
break;
}

if((strpos($links->innertext, "зодиак") !== false) || (strpos($links->innertext, "Зодиак") !== false) || (strpos($links->innertext, "зодиаком") !== false)){
fputs($flog, "Обнаружили команду КЛАНА на битву с Зодиаком!\n");
$clanCommTrue = 5; // Активируем работу по зодиаку
break;
}

if((strpos($links->innertext, "святилище") !== false) || (strpos($links->innertext, "Святилище") !== false) ||
(strpos($links->innertext, "предков") !== false) || (strpos($links->innertext, "Предков") !== false)){
fputs($flog, "Обнаружили команду КЛАНА на битву в Святилище предков!\n");
$clanCommTrue = 6; // Активируем работу по святилищу
break;
}

if((strpos($links->innertext, "легион") !== false) || (strpos($links->innertext, "Легион") !== false) || (strpos($links->innertext, "легионом") !== false)){
fputs($flog, "Обнаружили команду КЛАНА на битву с Легионом!\n");
$clanCommTrue = 7; // Активируем работу по легиону
break;
}
}

// очищаем буффер
EraseMemory($html);

return array($Referer, $clanCommTrue);
} // Конец поиска ключевых комманд клана 


// Скрываем старое объявления клана
function hideAd($Referer,$userAgent,$flog){
include_once("lib.php"); // библиотека функций
include_once("simple_html_dom.php"); // регулярки
// Данные главной страницы
$url = "http://barbars.ru";
$zapros = get_contents($url, "", $Referer, $userAgent, false);
$html = str_get_html($zapros);
$Referer = $url;
// Ищем ссылку на сокрытие мифа
foreach($html->find('a') as $links){
if(strpos($links->innertext, "скрыть") !== false){
fputs($flog, "Скрыли старое объявление на мифа!\n");
 $url = $links->href;
 while($url[0] != "?") {
	$url = substr($url,1);
 }
 $url = "http://barbars.ru/".$url;
 $url = str_replace("&amp;", "&", $url);

$zapros = get_contents($url, "", $Referer, $userAgent, false);
$Referer = $url;
 break;
 }
 }
 // очищаем буффер
EraseMemory($html);
 
 return $Referer;
 } // Конец функции сокрытия объявления

// Команды управления для синхронного боя клана
function Commands($MainClanCommand,$UseClansBottle,$Referer,$userAgent,$flog){
require_once("config.php"); // Конфиг
include_once("lib.php"); // библиотека функций
include_once("simple_html_dom.php"); // регулярки

// Для лога - процесс боя
$fLogDungeon = array(
"Пьем лекарство!\n", 
"Лечим себя!\n", 
"Активируем Абилку...\n", 
"Лечим союзника!\n", 
"Добиваем врага!(Жгем энергию!)\n",
"Бьем противника(Лечим союзников)...\n",
"Мы умерли:(\n");

$dMenuDungeonLinks = array(
"http://barbars.ru/game/dragonDungeon", 
"http://barbars.ru/game/giantvalley", 
"http://barbars.ru/game/stoneTroll", 
"http://barbars.ru/game/nemezida",
"http://barbars.ru/game/dragonTrophy",
"http://barbars.ru/game/zodiac", 
"http://barbars.ru/game/greatBoss", 
"http://barbars.ru/?wicket:bookmarkablePage=:com.overmobile.combats.wicket.pages.game.legion.LegionPage");

fputs($flog, "ВНИМАНИЕ! Обнаружена команда КЛАНА. Выполняем...\n");
		
// Переходим к основному меню
$url = "http://barbars.ru";
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		
				$Referer = $url;
		//$int_i=0; 
		//while($int_i<count($dMenuLinks)){
		$dun_step = 0;
		
		if($MainClanCommand != -1){

		$url = "http://barbars.ru/game/dungeons";
	
		// Зашли в пещеры к драконам
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		//$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
				
		$url = $dMenuDungeonLinks[$MainClanCommand];	
		sleep(1);
		// Зашли в локацию для битвы...
		$zapros = get_contents($url, "", $Referer, $userAgent, 111);
		$Referer = $url;
		$url = $zapros;
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		$Referer = $url;
		// Создаем DOM из URL
		$html = str_get_html($zapros);
	    //fputs($flog, "TEST:$html\n");	
		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;

		// Проверяем, открыт ли бой для нас?
		if((strpos($html->find('h1',0)->innertext, "Пустой грот") !== false) || (strpos($html->find('h1',0)->innertext, "Пустая пещера") !== false) || (strpos($html->find('h1',0)->innertext, "Вход закрыт") !== false)){
	    fputs($flog, "Доступ к бою закрыт ограничениями. В другой раз...\n");	
		continue;		
		} else {		
		// ЦИКЛ ПОДГОТОВКИ К БОЮ
		fputs($flog, "Переходим в режим ожидания Старта Битвы с Клан-Боссом...\n");
				
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
		if($cnt<=10000){
		if(strpos($dmenu->innertext, "Обновить") !== false){
		$url = $dmenu->href;
		/*
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		*/
		while(($url[0] == "." && $url[1] == ".")||($url[0] == "." && $url[1] == "/")||($url[0] == "/" && $url[1] == ".")||($url[0] == "/")) {
		$url = substr($url,1);
		}
		
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		fputs($flog, "$cnt - Ждем...\n");
		$cnt++;
		}
		if((strpos($dmenu->innertext, "Бить") !== false) || (strpos($dmenu->innertext, "Лечить") !== false)){
		$url = $dmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		$isWaitR=1;
		$isMainR=0;
		fputs($flog, "Бой с Клан Боссом начался!\n");
		}
		} else {
		// Ждали долго. Выходим.
		$url = "http://barbars.ru/";
		// очищаем буффер
		EraseMemory($html);
		// Выходим
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		$isWaitR=0;
		$isMainR=1;
		fputs($flog, "Команда не подтверждена. Слишком долго ждали. Продолжаем выполнять прерванный алгоритм...\n");
		break 2;
		}
		// ЭКСТРЕНЫЙ ВЫХОД по stop
		if(file_exists("stop.txt")){ 
	    fputs($flog, "Аварийное завершение работы! Локация: Пещеры и Драконы [Выполнение команды КЛАНА прервано]\n");
		Die();
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
		if($isWaitR == 0){
		//$url = "http://barbars.ru/game/survival";
		//$url = $dMenuDungeonLinks[$MainClanCommand];	
		// Зашли в локацию для битвы...
		$zapros = get_contents($url, "", $Referer, $userAgent, 111);
		$Referer = $url;
		$url = $zapros;
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		$Referer = $url;
		} else {
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		$Referer = $url;		
		}
		}
		// Создаем DOM из URL
		$html = str_get_html($zapros);
		
				$Referer = $url;
				
		if($isWaitR == 1){
		// 2 - Ищем жизнь и энергию
		$life = trim($html->find('td[align*=left] span',0)->innertext);
		$energy = trim($html->find('td[align*=left] span',1)->innertext);
	    fputs($flog, $dun_step.". У героя ".$life." здоровья и ".$energy." энергии\n");
		
		// Обнуляем массив адресов
		$urls = Array();
		$urlt = "";
		
		// --------------!!!!! Алгоритм боя !!!!!----------------
		// 3 - Ищем адреса страниц действий
		foreach($html->find('a') as $links){	
		
		if(($life < 400 || $energy < 5) && $UseClansBottle == true){
		// 3.0 - Проверяем наличие готовности бутылочки
		if((strpos($links->innertext, "Пить") !== false) && (strpos($links->innertext, "сек.") !== true)){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[0] = "http://barbars.ru/".$urlt;		
		}		
		}
		
		// 3.0.1 - Лечим себя, если очень ранены...
		if(strpos($links->innertext, "Лечить себя") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[1] = "http://barbars.ru/".$urlt;		
		}
		
		// 3.1 - Проверяем способности и активируем их, если готовы
		if(strpos($links->innertext, "(гoтoво)") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[2] = "http://barbars.ru/".$urlt;		
		}
		
		// 3.2 - Для мага - лечим если есть...	
		if(strpos($links->innertext, "Лечить") !== false && strpos($links->innertext, "% хп)") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[3] = "http://barbars.ru/".$urlt;//.substr($links->href,2);
		if(strpos($links->plaintext, "<span>200</span>") !== false) $urls[3] = "";
		}
		
		// 3.3 - для Мага - жгем энергию. Для воина - добиваем 
		if(strpos($links->innertext, "Дoбивать") !== false || strpos($links->innertext, "Жечь энергию у") !== false || strpos($links->innertext, "Жечь") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[4] = "http://barbars.ru/".$urlt;//.substr($links->href,2);	
		if(strpos($links->plaintext, "<span>0</span>") !== false) $urls[4] = "";		
		}
		
		// 3.4 - Ищем врага	если ничего не готово...
		if(strpos($links->innertext, "Бить") !== false || strpos($links->innertext, "Лечить союзников") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[5] = "http://barbars.ru/".$urlt;//.substr($links->href,2);
		}	
		
		if($life == 0 && $energy == 0){
		$urls[6] = "http://barbars.ru/";	
	    fputs($flog, $fLogDungeon[6]);
		}
		
		// 3.5 - Враг нас убил! Выходим из грота
		if((strpos($links->innertext, "Воскреснуть") !== false) || (strpos($links->innertext, "Обновить") !== false)){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[6] = "http://barbars.ru/".$urlt;//.substr($links->href,2);
		}	
		
		}
		
		// 4 - Определяем последовательность боя. Важность команд по "ценности"
		for($u=0;$u<7;$u++){
		if($urls[6] == "" && $urls[$u] != ""){ 
		   $url = $urls[$u];
		   fputs($flog, $fLogDungeon[$u]);	
		   break 1;
		} else if(strpos($html->find('h1',0)->innertext, "Пустой грот") !== false || strpos($html->find('h1',0)->innertext, "Пустая пещера") !== false){
		// Оп-па, босс повержен! (1 вариант)
		$isMainR = 1;
		//$int_i++;
	    fputs($flog, "Закончили бой с Клан Боссом!\n");	
	    break 1;		
		} else if($urls[6] != ""){
		// Оп-па, босс повержен! (2 вариант)
		$isMainR = 1;
		//$int_i++;
	    fputs($flog, "Закончили бой с Клан Боссом!\n");	
	    break 1;		
		}
		}
	
		$url = str_replace("&amp;", "&", $url);
		
		// очищаем буффер
		EraseMemory($html);
		
		// 1 - подгружаем страницу
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		//$html = str_get_html($zapros);
		
		// ЭКСТРЕНЫЙ ВЫХОД по stop
		if(file_exists("stop.txt")){ 
	    fputs($flog, "Аварийное завершение работы! Локация: Пещеры и Драконы [Выполнение команды КЛАНА прервано]\n");
		Die();
		}

		sleep(rand(2,10));
		$dun_step++;
		}
		
		}
		
		} // End.Очеред моснтров
		
		} // End.Идем на монстров клана	

		// очищаем буффер
		EraseMemory($html);
		
		fputs($flog, "Мы выполнили команду КЛАНА! Продолжаем работать...\n");
		
		return $Referer;
}

?>