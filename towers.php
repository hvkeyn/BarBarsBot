<?php
// Битва в Башнях
function Towers($step_this=500,$Referer,$userAgent,$flog){
header('Content-type: text/html; charset=utf-8');
setlocale(LC_ALL, 'ru_RU.utf8');
date_default_timezone_set('Europe/Moscow');
require_once("config.php"); // Конфиг
include_once("lib.php"); // библиотека функций
include_once("simple_html_dom.php"); // регулярки
// Игровые библиотеки скрипта
include_once("rack.php"); // Работа с рюкзаком

// Для лога - процесс боя
$fLogStr = array(
"Переходим в столицу для отдыха...\n", 
"Активируем Абилку...\n", 
"Атакуем башню!(Жгем энергию у врага!)\n", 
"Добиваем врага!(Лечим союзника!)\n",
"Ищем противника(Союзника для лечения)...\n",
"В локации нет противников! Обновляем...\n");

// КАРТЫ БАШЕНЬ
$TowerMainArray = Array(
"Курган",
"Лагерь орды",
"Устье реки",
"Горное озеро",
"Южная пустошь",
"Мароканд",
"Мертвый город",
"Земли титанов",
"Долина Сражений"
);

// Отдельные объекты по Картам Башень (ЮГ)
// Лагерь
$Tower1Array = Array(
"Лагерь викингов",
"Лагерь орды"
);
// Устье реки
$Tower2Array = Array(
"Дельта реки",
"Левый берег",
"Правый берег",
"Устье реки"
);
// Горное озеро
$Tower3Array = Array(
"Ледник",
"Верхний перевал",
"Ледяные пещеры",
"Нижний перевал",
"Каменные пещеры",
"Горное озеро"
);
// Южная пустошь
$Tower4Array = Array(
"Северная пустошь",
"Северо-западная пустошь",
"Северо-восточная пустошь",
"Западная пустошь",
"Перекрёсток",
"Восточная пустошь",
"Юго-западная пустошь",
"Юго-восточная пустошь",
"Южная пустошь"
);
// Мароканд
$Tower5Array = Array(
"Розенгард",
"Западный Розенгард",
"Железный рудник",
"Восточный Розенгард",
"Большой курган",
"Западный Мароканд",
"Медные копи",
"Восточный Мароканд",
"Мароканд",
"Логово Геррода"
);
// Мертвый город, ЮГ
$Tower6Array = Array(
"Мертвый город (вход)",
"Северо-западная окраина",
"Храм неба",
"Храм воды",
"Северо-восточная окраина",
"Площадь заката",
"Площадь восстания",
"Площадь рассвета",
"Юго-западная окраина",
"Храм огня",
"Храм земли",
"Юго-восточная окраина"
);
// Земли титанов, ЮГ
$Tower7Array = Array(
"Земли титанов, Север",
"Северо-западные горы",
"Северо-восточные горы",
"Западные врата",
"Крепость титанов",
"Восточные врата",
"Юго-западные горы",
"Юго-восточные горы",
"Земли титанов, Юг"
);
// Долина Сражений, ЮГ
$Tower8Array = Array(
"Долина Сражений, Север",
"Северо Западный Форт",
"Северо Восточный Форт",
"Западный Курган",
"Поле вечной Битвы",
"Восточный Курган",
"Юго Западный Форт",
"Юго Восточный Форт",
"Долина Сражений, Юг"
);


// Инициализация переменных
$step_towers = 0;
$EXIT_t = 0;
$rest = 0; // Режим отдыха в столице
$ClearLoc = 0; // В локации нет врагов?
$urls = Array();
$TowersArrayPath = ""; // Имя локации для сравнения
		
$url = "http://barbars.ru/game/towers";
// Идем в БАШНИ
$zapros = get_contents($url, "", $Referer, $userAgent, false);

		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;

		// Ищем ссылку с текстом "Поднять флаг!"
		foreach($html->find('a[class=flhdr]') as $pflag){
		if(strpos($pflag->innertext, "Поднять флаг!") !== false){
        $url = $pflag->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);		
		$zapros = get_contents($url, "", $Referer, $userAgent, false);	
		// !!! Проверяем текст на наличие нужных нам элементов !!!
		$Referer = $url;
		// очищаем буффер
		EraseMemory($html);
	    fputs($flog, "Подняли флаг!\n");
		// Создаем DOM из URL
		$html = str_get_html($zapros);
		break;
		}
		}
		
		// Ищем локацию для входа в дом
		$e = $html->find('a[class=flhdr]',0);
        $url = $e->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		$name = $e->plaintext;
		$TowersArrayPath = trim($name);
	    fputs($flog, "Начинаем бой в локации <<".trim($name).">>!\n");
		
		// очищаем буффер
		EraseMemory($html);
		
sleep(rand(1,3));
// 0 - начинаем сканирование до главного цикла
$zapros = get_contents($url, "", $Referer, $userAgent, false);
		
// Цикл основного боя в башнях
while($step_towers < $step_this && $EXIT_t == 0)
{
// Закончились силы или низкий уровень здоровья? Отдыхаем!
if($rest == 1){
fputs($flog, "Отдыхаем около 60 сек.\n");
sleep(rand(40,60));
// Отдохнули? Идем снова на войну!

		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
				
		// Ищем ссылку с текстом "Поднять флаг!"
		foreach($html->find('a[class=flhdr]') as $pflag){
		if(strpos($pflag->innertext, "Поднять флаг!") !== false){
        $url = $pflag->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);		
		$zapros = get_contents($url, "", $Referer, $userAgent, false);	
		// !!! Проверяем текст на наличие нужных нам элементов !!!
		$Referer = $url;
		// очищаем буффер
		EraseMemory($html);
	    fputs($flog, "Подняли флаг!\n");		
		// Создаем DOM из URL
		$html = str_get_html($zapros);
		break;
		}
		}
		
		// Ищем локацию для входа
		$e = $html->find('a[class=flhdr]',0);
        $url = $e->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		$name = $e->plaintext;
		$TowersArrayPath = trim($name);
	    fputs($flog, "Продолжаем бой в локации <<".trim($name).">>!\n");
		
		// очищаем буффер
		EraseMemory($html);
		
$zapros = get_contents($url, "", $Referer, $userAgent, false);

$rest = 0;
}

if($ClearLoc == 1 && $TowersArrayPath != ""){
fputs($flog, "Убили всех врагов! Идем дальше...\n");
//sleep(rand(1,2));
// Идем дальше убивать!

		// Создаем DOM из URL
		$html = str_get_html($zapros);
		// !!! Сохраняем старую ссылку
		$Referer = $url;

		// Ищем категорию КАРТ БАШЕНЬ
		for($b=0;$b<count($TowerMainArray);$b++){
		// Применяем массив
	    fputs($flog, "Сравниваем ".no_translit($TowersArrayPath)." с ".$TowerMainArray[$b]."!\n");
		if(strpos(no_translit($TowersArrayPath),$TowerMainArray[$b]) > -1){
	    fputs($flog, "НАШЛИ СОВПАДЕНИЕ $b!\n");	
		$TowerArray = Array();
		switch ($b) {
		case 0: $TowerArray = Array("Курган");
			break 2;
		case 1: $TowerArray = $Tower1Array;
			break 2;
		case 2: $TowerArray = $Tower2Array;
			break 2;
		case 3: $TowerArray = $Tower3Array;
			break 2;
		case 4: $TowerArray = $Tower4Array;
			break 2;
		case 5: $TowerArray = $Tower5Array;
			break 2;
		case 6: $TowerArray = $Tower6Array;
			break 2;
		case 7: $TowerArray = $Tower7Array;
			break 2;
		case 8: $TowerArray = $Tower8Array;
			break 2;
			}
		}
		}
		// Ищем ссылку по базе Башень
		foreach($html->find('a[class=flhdr]') as $ptower){
		for($b=0;$b<count($TowerArray);$b++){
	    fputs($flog, "Сравниваем <<".no_translit(trim($ptower->plaintext)).">> с <<".$TowerArray[$b].">>!\n");
		if(strpos(no_translit(trim($ptower->plaintext)), $TowerArray[$b]) !== false){
        $url = $ptower->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		$name = $ptower->plaintext;
		//$TowersArrayPath = trim($name);
	    fputs($flog, "Продолжаем бой в локации <<".trim($name).">>!\n");
		break 2;
		}
		} //For.End
		}

		// очищаем буффер
		EraseMemory($html);
		
$zapros = get_contents($url, "", $Referer, $userAgent, false);
		
$ClearLoc = 0;
}

		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст локации !!!
		
				$Referer = $url;
				$urls = Array();

		// R - Проверяем, рюкзак заполнен?
		if(strpos($zapros, "bag_full.gif") !== false){
		
		// Для возвращения в бой
		  $Referer = "http://barbars.ru/user/rack";
		// очищаем буффер
		EraseMemory($html);
		
		// Чиним все обмундирование
		$Referer = repairMain($Referer,$userAgent,$flog);
		sleep(rand(1,2));
		// Разбираем все предметы на запчасти(металолом) ! Поправить. Выбор разборки на мифрил
		$url = junkMain($Referer,$userAgent,$flog);
		
		// Возвращаемся в бой
		$zapros = get_contents($url, "", $Referer, $userAgent, false);

		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст локации !!!
		
				$Referer = $url;
		} //End.Рюкзак
				
		// S - Проверяем, есть ли новые предметы, которые можно надеть?
		if(count($html->find('a[class=info]',0))>0){
		$url = $html->find('a[class=info]',0)->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
	    fputs($flog, "Надели новый предмет!\n");
		
		// очищаем буффер
		EraseMemory($html);
		
		// 302 ссылка. Получаем и засовываем в Referer
		$Referer = get_contents($url, "", $Referer, $userAgent, true);
				$url = "http://barbars.ru/game/towers";

		// возвращаемся на поле боя....
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
 
		} //End.Одеть_Предметы

		// 2 - Ищем жизнь и энергию
		$life = trim($html->find('td[align*=left] span',0)->innertext);
		$energy = trim($html->find('td[align*=left] span',1)->innertext);
		
		// Решение проблемы с нулевым здоровьем и энергией(Обновление страницы)
		if($life == "" || $energy == "") {
		  $url = "http://barbars.ru/game/towers";
		  $Referer = "http://barbars.ru/";
		  
		// очищаем буффер
		EraseMemory($html);
		
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
		
		// Ищем ссылку для перехода в локацию или атакуем
		$e = $html->find('a[class=flhdr]',0);
        $url = $e->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		$name = $e->plaintext;
		//$TowersArrayPath = trim($name);
	    fputs($flog, "Проблемы с получением данных. Починили. Продолжаем работу!\n");
		
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
	    fputs($flog, $step_towers.". У героя ".$life." здоровья и ".$energy." энергии\n");
		
		// V - Воскрешение!!!
		if($life == 0 && $energy == 0 && $life != ""  && $energy != ""){
	    fputs($flog, "Героя убили! Воскрешаем его....\n");
		$Referer = $url;	
		
		// Ищем ссылку с текстом воскрешения
		foreach($html->find('a') as $ldeath){
		if(strpos($ldeath->innertext, "Воскреснуть в столице") !== false){
		$url = $ldeath->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);	
		}
		}
		// очищаем буффер
		EraseMemory($html);
		
		// 302. Переадресация при воскрешении.
		$url = get_contents($url, "", $Referer, $userAgent, true);
		
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
		
		// Ищем скрытые поля для формирования запроса
		$e = $html->find('a[class=flhdr]',0);
        $url = $e->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		$name = $e->plaintext;
		$TowersArrayPath = trim($name);
	    fputs($flog, "Воскресли. Продолжаем бой в локации <<".trim($name).">>!\n");
		
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
	    fputs($flog, $step_towers.". У героя ".$life." здоровья и ".$energy." энергии\n");
		
		} // End.Воскрешение
		
		if($life < 400 || $energy < 100){
		// Быстрый цикл поиска последнего значения. Это путь домой...
		foreach($html->find('a[class=flhdr]') as $links) {
		// 3.0 - Алгоритм возврата на базу...	
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[0] = "http://barbars.ru/".$urlt;
		$name = $links->plaintext;
		}
		//$TowersArrayPath = trim($name);
	    fputs($flog, "Возвращаемся для отдыха. Переходим в локацию <<".trim($name).">>\n");
		}
		
		// 3 - Ищем адреса страниц действий
		foreach($html->find('a[class=flhdr]') as $links){
	
		if($life < 400 || $energy < 100 || $step_towers >= ($step_this-1)){
		// 3.0 - Ссылка на БАЗУ если большой дамаг или устал
		if(strpos($links->innertext, "Карaкорум, стoлицa Юга") !== false || strpos($links->innertext, "Мидгард, столица Севера") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[0] = "http://barbars.ru/".$urlt;//.substr($links->href,2);
		$rest = 1;
		} 
		}else {				
		
		// 3.1 - Проверяем способности и активируем их, если готовы
		if(strpos($links->innertext, "(гoтoво)") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[1] = "http://barbars.ru/".$urlt;//.substr($links->href,2);		
		}
		
		// 3.2 - Долбим башню если есть...	
		if(strpos($links->innertext, "башню") !== false || (strpos($links->innertext, "Лечить") !== false && strpos($links->innertext, "% хп)") !== false)){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[2] = "http://barbars.ru/".$urlt;//.substr($links->href,2);
		if(strpos($links->plaintext, "<span>200</span>") !== false) $urls[2] = "";
		}
		
		// 3.3 - Добиваем врага	
		if(strpos($links->innertext, "Добивать") !== false || strpos($links->innertext, "Жечь энергию у") !== false || strpos($links->innertext, "Жечь") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[3] = "http://barbars.ru/".$urlt;//.substr($links->href,2);	
		if(strpos($links->plaintext, "<span>0</span>") !== false) $urls[3] = "";	
		}
		
		// 3.4 - Ищем врага	если ничего не готово...
		if(strpos($links->innertext, "Бить врaгов") !== false || strpos($links->innertext, "Лечить союзников") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[4] = "http://barbars.ru/".$urlt;//.substr($links->href,2);
		}	
		
		}
		
		}

		// 4 - Определяем последовательность боя. Важность команд по "ценности"
		for($u=1;$u<5;$u++){
		if($urls[0] == "" && $urls[$u] != ""){ 
		   $url = $urls[$u];
		   fputs($flog, $fLogStr[$u]);
		  // $ClearLoc = 0;		   
		   break 1;
		} else if($urls[0] != ""){
		   $url = $urls[0];
		   fputs($flog, $fLogStr[0]);
		   //$ClearLoc = 0;		   
		   break 1;		
		}
		}
		if(empty($urls)){ 
		//$url = $Referer;
		fputs($flog, $fLogStr[5]);
		$ClearLoc = 1;
		}
	
		$url = str_replace("&amp;", "&", $url);
		
		// очищаем буффер
		EraseMemory($html);
		
		// 1 - подгружаем страницу
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		
// Ошибка пустого ответа - иногда выбрасывает пустую страницу
if(!$zapros){ 
$url = "http://barbars.ru/game/towers";
$Referer = "http://barbars.ru";
$zapros = get_contents($url, "", $Referer, $userAgent, false);
}

if($step_towers >= $step_this) $EXIT_t = 1;
// ЭКСТРЕНЫЙ ВЫХОД по stop
		if(file_exists("stop.txt")){ 
	    fputs($flog, "Аварийное завершение работы! Локация: Башни\n");
		Die();
		}

$step_towers++;
sleep(rand(2,6));
}

		// очищаем буффер
		EraseMemory($html);
		
		return $Referer;
}

?>