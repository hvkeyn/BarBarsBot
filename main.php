<?php

// Ошибки
ini_set('display_errors', '1');
ini_set('allow_url_fopen','1'); // разрешаем указывать пустые ссылки в парсинге
error_reporting(E_ALL);
//error_reporting(-1);

set_time_limit(0); 
ignore_user_abort(1);   // Игнорировать обрыв связи с браузером 

require_once("config.php"); // Конфиг
include_once("lib.php"); // библиотека функций
include_once("simple_html_dom.php"); // регулярки
include_once("antigatecurl.class.php"); // антикапча
// Игровые библиотеки скрипта
include_once("rack.php"); // Работа с рюкзаком
include_once("well.php"); // Колодец удачи
include_once("dungeons.php"); // Драконы и пещеры
include_once("capcha.php"); // Антикапча

$html = new simple_html_dom(); // создаем объект

// Для лога - процесс боя
$fLogStr = array(
"Переходим в столицу для отдыха...\n", 
"Активируем Абилку...\n", 
"Атакуем башню!(Жгем энергию у врага!)\n", 
"Добиваем врага!(Лечим союзника!)\n",
"Ищем противника(Союзника для лечения)...\n");

// Логируем все действия
$flog = fopen('log.txt', 'w');

// Инициализация переменных
$all_step = 0;
$EXIT = 0;
$rest = 0; // Режим отдыха в столице?
$urls = Array();

// Удаляем stop.txt
if(file_exists("stop.txt")) unlink("stop.txt");

// Логинимся на сервер...
fputs($flog, "Логинимся на сервер игры barbars.ru...\n");
		
$userAgent = $all_useragents[rand(0, count($all_useragents)-1)];

// Устанавливаем данные для логина
$url = "http://barbars.ru/login";
$PostFields = "loginForm_hf_0=&login=".$game_login."&password=".$game_password."&%3Asubmit=%D0%92%D0%BE%D0%B9%D1%82%D0%B8";
$Referer = "http://barbars.ru/";

$zapros = get_contents($url, "", $Referer, $userAgent, false);

		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
		// Ищем скрытые поля для формирования запроса
		$url = "http://barbars.ru/". $html->find('form',0)->action;
	    fputs($flog, "Переадресовываем на $url\n");

		// очищаем буффер
		EraseMemory($html);

// Если все ок, в $url вернется ссылка на страницу героя
$Referer = get_contents($url, $PostFields, $Referer, $userAgent, true);

fputs($flog, "Вошли в аккаунт!\n");

// начинаем с главной страницы(есил вдруг уже залогинены)
$url = "http://barbars.ru";
sleep(rand(1,2));

// получаем главную страницу операций героя
$zapros = get_contents($url, "", $Referer, $userAgent, false);

$Referer = $url;

		// C - Антикапча. Проверяем и вводим если нашли.
		$Referer = anticapcha($Referer,$userAgent,$flog,$gkey,$capcha_server);
		// G - Проверяем подарок в колодце удачи
		$Referer = wellGift($Referer,$userAgent,$flog);

		
$url = "http://barbars.ru/game/towers";
// Идем в БАШНИ
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
	    fputs($flog, "Начинаем бой в локации <<$name>>!\n");
		
		// очищаем буффер
		EraseMemory($html);
		
sleep(rand(1,3));
// 0 - начинаем сканирование до главного цикла
$zapros = get_contents($url, "", $Referer, $userAgent, false);
		
// Супербольшой цикл обработки игровой логики ---> !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//(в данный момент - бой\лечение, подбор\разбор\починка вещей, заклинания,
// ввод капчи, получение подарка из колодца, битвы в пещерах с Боссами и отдых)
while($all_step < $step_main && $EXIT == 0)
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
		
		// Ищем скрытые поля для формирования запроса
		$e = $html->find('a[class=flhdr]',0);
        $url = $e->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		$name = $e->plaintext;
	    fputs($flog, "Продолжаем бой в локации <<".trim($name).">>!\n");
		
		// очищаем буффер
		EraseMemory($html);
		
$zapros = get_contents($url, "", $Referer, $userAgent, false);

$rest = 0;
}

		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст локации !!!
		
				$Referer = $url;
				$urls = Array();

		// R - Проверяем, рюкзак заполнен?
		if(strpos($zapros, "bag_full.gif") !== false){
		
		// P - Проверяем, есть-ли у персонажа пррофессия? Если есть - повышаем!
		// !!! Поддерживает только аниквара !!! 
		//$Referer = CreateStones($Referer,$userAgent,$flog);	
		
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
	    fputs($flog, $all_step.". У героя ".$life." здоровья и ".$energy." энергии\n");
		
		
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
	    fputs($flog, "Воскресли. Продолжаем бой в локации <<$name>>!\n");
		
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
	    fputs($flog, $all_step.". У героя ".$life." здоровья и ".$energy." энергии\n");
		
		} // End.Воскрешение
		
		// 3 - Ищем адреса страниц действий
		foreach($html->find('a[class=flhdr]') as $links){
		
		if($life < 50 || $energy < 5 || $all_step >= ($step_main-1)){
		// 3.0 - Ссылка на БАЗУ если большой дамаг или устал
		if(strpos($links->innertext, "Карaкорум, стoлицa Юга") !== false || strpos($links->innertext, "Мидгард, столица Севера") !== false){
		$urlt = $links->href;
		while($urlt[0] != "?") {
		$urlt = substr($urlt,1);
		}
		$urls[0] = "http://barbars.ru/".$urlt;//.substr($links->href,2);
		$rest = 1;
		}
		} else {				
		
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
		if(strpos($links->innertext, "Дoбивать") !== false || strpos($links->innertext, "Жечь энергию у") !== false || strpos($links->innertext, "Жечь") !== false){
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
		   break 1;
		} else if($urls[0] != ""){
		   $url = $urls[0];
		   fputs($flog, $fLogStr[0]);	
		   break 1;		
		}
		}
	
		$url = str_replace("&amp;", "&", $url);
		
		// очищаем буффер
		EraseMemory($html);
		
		// 1 - подгружаем страницу
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		
if($all_step==1000 || $all_step==2000 || $all_step==3000 || $all_step==4000 || $all_step==5000 || $all_step==6000 || $all_step==7000 || $all_step==8000 || $all_step==9000 || $all_step==10000)
{
		// C - Антикапча. Проверяем и вводим если нашли.
		$Referer = anticapcha($Referer,$userAgent,$flog,$gkey,$capcha_server);
		// G - Проверяем подарок в колодце удачи
		$Referer = wellGift($Referer,$userAgent,$flog);
		// D - Битва с Боссами в пещерах и гротах
		$Referer = Dungeons($Referer,$userAgent,$flog);
		$url = "http://barbars.ru/game/towers";
		// Идем в БАШНИ
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
}
		
// Ошибка пустого ответа - иногда выбрасывает пустую страницу
if(!$zapros){ 
$url = "http://barbars.ru/game/towers";
$Referer = "http://barbars.ru";
$zapros = get_contents($url, "", $Referer, $userAgent, false);
}

if($all_step >= $step_main) $EXIT = 1;
// ЭКСТРЕНЫЙ ВЫХОД по stop
if(file_exists("stop.txt")) $EXIT = 1;

$all_step++;
sleep(rand(2,6));
}

		// очищаем буффер
		EraseMemory($html);

		fputs($flog, "ЗАВЕРШИЛИ РАБОТУ!\n\n");
fclose($flog);
//Die();
?>