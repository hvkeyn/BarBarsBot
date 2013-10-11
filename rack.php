<?php
function repairMain($Referer,$userAgent,$flog){
ini_set('allow_url_fopen','1');
require_once("config.php"); // Конфиг
include_once("lib.php"); // библиотека функций
include_once("simple_html_dom.php"); // регулярки
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

// --!! ПРОФЕССИИ !!--
// Если у нас есть профессия(пока только поддерживается "антиквар"), переводим весь стафф в камни
function CreateStones($Referer,$userAgent,$flog){	
//$EXIT_STONES = 0; // Для цикла создания камней
// Переходим к герою
$url = "http://barbars.ru/user";
		$zapros = get_contents($url, "", $Referer, $userAgent, false);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;

		// Ищем ссылку с текстом "антиквар"
		if(strpos($zapros, "антиквар") !== false){
		$url = "http://barbars.ru/user/profession";
		// Зашли в профессии
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
				
		// Ищем кнопку "Сделать камень" 
		$url = $html->find('a[class=ml5]',0)->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		// Очищаем память
		EraseMemory($html);
		
		// Получаем страницу с меню
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
/*		
		// Начинаем производство камней(цикл переработки всех вещей)
		while($EXIT_STONES == 0){
		//$EXIT_STONES = 1;
		//!!!ТЕСТОВЫЙ ЦИКЛ: Отключил обработку пунктов меню. достаточно прокачиваться...
 
		// Ищем ссылки на меню антиквара
		foreach($html->find('a') as $citems){
		if(strpos($citems->innertext, "необычный") !== false || strpos($citems->innertext, "редкий") !== false || strpos($citems->innertext, "эпический") !== false || strpos($citems->innertext, "легендарный") !== false || strpos($citems->innertext, "мифический") !== false){
		if($citems->class != "minor"){
		$url = $citems->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		
		break 2;
		} 
		
		}
		
		} 
	
		EraseMemory($html);
		$zapros = get_contents($url, "", $Referer, $userAgent, true);
			
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
*/
		while($int_i<6)
		{ // !!! - закончить цикл обработки подменю --------------!!!!!

		$int_i = 0; // Переменная подсчета пройденных камней с начала
		// Ищем ссылки на меню с элементами для превращения в камни
		foreach($html->find('div') as $smenu){
		if(strpos($smenu->find('a',0)->innertext, "Обсидиан") !== false || strpos($smenu->find('a',0)->innertext, "Оникс") !== false || strpos($smenu->find('a',0)->innertext, "Сапфир") !== false || strpos($smenu->find('a',0)->innertext, "Корунд") !== false || strpos($smenu->find('a',0)->innertext, "Изумруд") !== false || strpos($smenu->find('a',0)->innertext, "Случайный") !== false){
		$url = $smenu->find('a',0)->href;
		if(count($smenu->find('span[class=minor]',0))>0){
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		
		break 1;
		} else {$int_i++;
		}
		$url = "";
		} 
		}
		
		EraseMemory($html);	
		
		if($int_i>=5){ 
		$EXIT_STONES=1; // Проверили один пункт меню. Переходим к обработке другого
		break 1; // Выходим из цикла обработки подменю...Больше камней нет в меню
		} // else $EXIT_STONES=0;

		$zapros = get_contents($url, "", $Referer, $userAgent, false);

		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
		// Ищем предмет для переработки
		foreach($html->find('a') as $pmenu){
		if(strpos($pmenu->innertext, "выбрать") !== false){
		$url = $pmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		}
		}
		
		EraseMemory($html);	
		sleep(1);	
		// 302 переадресация при переработка предмета
		$zapros = get_contents($url, "", $Referer, $userAgent, true);
		$Referer = $url;	
		$url = $zapros;
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
				fputs($flog, "TEST:$url\n\n$zapros\n");
				die();
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;

		// Да, подтверждаем переработку предмета!
		foreach($html->find('a') as $pmenu){
		if(strpos($pmenu->innertext, "Да, подтверждаю") !== false){
		$url = $pmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		}
		}
		
		EraseMemory($html);	
		sleep(1);
		// 302 переадресация при переработка предмета
		$zapros = get_contents($url, "", $Referer, $userAgent, true);
		$Referer = $url;
		$url = $zapros;
		$zapros = get_contents($url, "", $Referer, $userAgent, false);

		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
				fputs($flog, "Шаг9: Создали камень! Переходим к следующему...\n");

		// Создали камень! Переходим к следующему...
		foreach($html->find('a') as $pmenu){
		if(strpos($pmenu->innertext, "Создать еще камень") !== false){
		$url = $pmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		}
		}
		
		EraseMemory($html);	
		sleep(1);
		// 302 переадресация при переработка предмета
		$zapros = get_contents($url, "", $Referer, $userAgent, true);
		$Referer = $url;
		$url = $zapros;
		$zapros = get_contents($url, "", $Referer, $userAgent, false);

		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
	
	    fputs($flog, "Создали новый Камень!$url\n");
		} //End.While int_i<6
		
		//} //End.MAIN.While 
		
		// Переходим к герою
		$url = "http://barbars.ru/user";
		$zapros = get_contents($url, "", $Referer, $userAgent, false);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;

		// Переходим в профессию
		$url = "http://barbars.ru/user/profession";
		
		// Зашли в профессии
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
				
		// Ищем кнопку "Повысить уровень" 
		foreach($html->find('a') as $pmenu){
		if(strpos($pmenu->innertext, "Повысить уровень") !== false){
		$url = $pmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		}
		}
		// Очищаем память
		EraseMemory($html);
		
		// Зашли в профессии
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
				
		// Проверяем, есть ли камни уровень которыхъ может повысить опыт профессии?		
		if(count($html->find('div[class=major]',0)) == 0){
				
		// Ищем кнопку "Сдать все камни" 
		foreach($html->find('a') as $pmenu){
		if(strpos($pmenu->innertext, "Сдать все камни") !== false){
		$url = $pmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		}
		}
		// Очищаем память
		EraseMemory($html);	
		
		sleep(1);
		// 302 переадресация при повышении уровня профессии
		$zapros = get_contents($url, "", $Referer, $userAgent, true);
		$Referer = $url;
		$url = $zapros;
		$zapros = get_contents($url, "", $Referer, $userAgent, false);

		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;

		// Да, подтверждаем повышение профессии!
		foreach($html->find('a') as $pmenu){
		if(strpos($pmenu->innertext, "Да, подтверждаю") !== false){
		$url = $pmenu->href;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);
		break 1;
		}
		}
		
		EraseMemory($html);	
		sleep(1);
		// 302 переадресация при переработка предмета
		$zapros = get_contents($url, "", $Referer, $userAgent, true);
		$Referer = $url;
		$url = $zapros;
		$zapros = get_contents($url, "", $Referer, $userAgent, false);

		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
				
		$rez_prof = $html->find('div[class=info]',0)->plaintext;

		fputs($flog, $rez_prof." камней осталось сдать мастеру!\n");					

		} else 	fputs($flog, "В рюкзаке нет нужного камня для сдачи мастеру!\n");	
		
		}// End.Profession
		
		return $Referer;
}

?>