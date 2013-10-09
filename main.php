<?php
header("Content-type: text/html; charset=utf-8");
setlocale(LC_ALL, 'ru_RU.utf8');
date_default_timezone_set('Europe/Moscow');

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
include_once("towers.php"); // Бой в башнях
include_once("dungeons.php"); // Драконы и пещеры
include_once("capcha.php"); // Антикапча

$html = new simple_html_dom(); // создаем объект

// Инициализация переменных
$all_step = 0;
$EXIT = 0;
$ustal = 0; // Усталость

// Логируем все действия
$flog = fopen('log.txt', 'w');

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

// Супербольшой цикл обработки игровой логики ---> !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//(в данный момент - бой\лечение, подбор\разбор\починка вещей, заклинания,
// ввод капчи, получение подарка из колодца, битвы в пещерах с Боссами и отдых)
while($all_step < $step_main && $EXIT == 0)
{

// C - Антикапча. Проверяем и вводим если нашли. Также получаем параметр "усталость"
list($Referer, $ustal) = anticapcha($Referer,$userAgent,$flog,$gkey,$capcha_server);
sleep(rand(2,10));	

if($pUstal == 0) $ustal = 0; // Игнорируем проверку усталости если в конфиге 0
if($ustal == 0){
// T - Битва в башнях - основной цикл накрутки опыта
$Referer = Towers($step_tower, $Referer, $userAgent, $flog);
} else fputs($flog, "Герой устал! Делаем перерыв до снятия усталости...\n");
sleep(rand(2,10));

// C - Антикапча. Проверяем и вводим если нашли.
list($Referer, $ustal) = anticapcha($Referer,$userAgent,$flog,$gkey,$capcha_server);
sleep(rand(2,10));	

// G - Проверяем подарок в колодце удачи
$Referer = wellGift($Referer,$userAgent,$flog);
sleep(rand(2,10));
		
// D - Битва с Боссами в пещерах и гротах
$Referer = Dungeons($Referer,$userAgent,$flog);

if($all_step >= $step_main) $EXIT = 1;
// ЭКСТРЕНЫЙ ВЫХОД по stop
if(file_exists("stop.txt")) $EXIT = 1;

$all_step++;
sleep(rand(2,10));
}

		// очищаем буффер
		EraseMemory($html);

		fputs($flog, "ЗАВЕРШИЛИ РАБОТУ!\n\n");
fclose($flog);
//Die();
?>