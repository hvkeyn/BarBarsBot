<?php
// Этот класс не только разгадывает капчу, но и собирает информацию о герое. 
// На основе полученных данных - алгоритм селективного развития выбирает дальнейшую прокачку персонажа.

function anticapcha($Referer,$userAgent,$flog,$gkey,$capcha_server){
include_once("config.php"); // Конфиг
include_once("lib.php"); // библиотека функций
include_once("simple_html_dom.php"); // регулярки
include_once("antigatecurl.class.php"); // антикапча
$EXIT_a = 0; // Ошибок нет
$ustal = 0; // Устали биться в башнях - делаем перерыв...

// Переходим к герою
$url = "http://barbars.ru/user";
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
		$url = "";
		
		// Ищем ссылку с текстом "Защита от роботов"
		foreach($html->find('a') as $muser){
		if(strpos($muser->innertext, "Защита от роботов") !== false || strpos($muser->innertext, "Защита от ботов") !== false){
		$url = "http://barbars.ru/".$muser->href;	
		}
		// Ищем инф-цию по усталости
		if(strpos($muser->innertext, "усталость") !== false){
		$ustal = 1;
		}
		}
		// очищаем буффер
		EraseMemory($html);
		if($url){
		// Зашли в снаряжение.
		$zapros = get_contents($url, "", $Referer, $userAgent, false);
		// Создаем DOM из URL
		$html = str_get_html($zapros);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
		
				$Referer = $url;
				
		// -- Ищем данные для ввода капчи...		
		// Получаем адрес изображения капчи
		$img_src = $html->find('img[alt=картинка]',0)->src;
		// Получаем адрес формы отправки данных
		$form_action = $html->find('form[method=post]',0)->action;
		// Получаем дополнительное скрытое поле
		$hidden_item = $html->find('input[type=hidden]',0)->name;
		
		$img_src = "http://barbars.ru".$img_src;
		$img_src = str_replace("&amp;", "&", $img_src);	
		
		$answer_captcha = get_contents($img_src, "", $Referer, $userAgent, false);		
		
				// Загружаем изображение на сервер
				$f2 = fopen('captcha.png', 'w');
				fwrite($f2, $answer_captcha);
				fclose($f2);
				
				// -- Работаем с Антикапчей
				// Расшифровывать из файла
				$antigate_code = recognize("./captcha.png",$gkey,true, $capcha_server,$flog);
				// Обработка ошибок антикапчи
				if($antigate_code == "ERROR_ZERO_BALANCE")
				{
				fputs($flog, "Ваш балланс на antigate равен 0. Пополните аккаунт и продолжайте!\n");
				$EXIT_a = 1;
				}
				
				if($antigate_code == "ERROR_NO_SLOT_AVAILABLE")
				{
				$tantigate =0 ;

				while (($antigate_code == "ERROR_NO_SLOT_AVAILABLE") && ($tantigate!=100)){
				fputs($flog, "Попытка: $tantigate . Сервис antigate занят... Переотправляем капчу...\n");
				sleep(3);
				$antigate_code = recognize("./captcha.png",$gkey,true, $capcha_server);

				if($antigate_code != "ERROR_NO_SLOT_AVAILABLE") {
				$EXIT_a = 0;
				}else{
				$EXIT_a = 1;
				$tantigate++;
				}
				}
				}
				
				if($antigate_code == "ERROR_IP_NOT_ALLOWED")
				{
				fputs($flog, "Запрос с этого IP адреса с текущим ключом отклонен.\n");
				$EXIT_a = 1;
				}
				
				if($antigate_code == "ERROR_WRONG_USER_KEY")
				{
				fputs($flog, "Неправильный формат ключа учетной записи (длина не равняется 32 байтам).\n");
				$EXIT_a = 1;
				}
				
				if($antigate_code == "ERROR_KEY_DOES_NOT_EXIST")
				{
				fputs($flog, "Вы использовали неверный captcha ключ в запросе.\n");
				$EXIT_a = 1;
				}
				
				if($EXIT_a  == 1){ 
				fputs($flog, "Ошибка работы с АНТИКАПЧЕЙ!\n");
				} else {
				
				fputs($flog, "Расшифрованный код - ".$antigate_code."\n");
		
		$PostFields = $hidden_item."=&code=".$antigate_code."&%3Asubmit=%D0%AF+%D0%BD%D0%B5+%D0%A0%D0%BE%D0%B1%D0%BE%D1%82%21";
		
        $url = $form_action;
		while($url[0] != "?") {
		$url = substr($url,1);
		}
		$url = "http://barbars.ru/".$url;
		$url = str_replace("&amp;", "&", $url);	
		
		// Отправляем капчу
		$zapros = get_contents($url, $PostFields, $Referer, $userAgent, false);

		// !!! Проверяем текст на наличие нужных нам элементов !!!
				$Referer = $url;
//fputs($flog, "ОТВЕТ_ОТ_СЕРВЕРА:\n$zapros\n");	
	    fputs($flog, "Ввели капчу! Продолжаем работу.\n");	
		}
		// очищаем буффер
		EraseMemory($html);	
		} else fputs($flog, "Капчу не обнаружили!\n");	
	
		return array($Referer, $ustal); 
}
?>