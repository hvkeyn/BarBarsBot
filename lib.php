<?php

require_once("config.php"); // Конфиг

//follow on location problems workaround
 
function curl_redir_exec($ch)
{
 
static $curl_loops = 0;
 
static $curl_max_loops = 20;
 
if ($curl_loops++ >= $curl_max_loops)
{
 
$curl_loops = 0;
 
return FALSE;
 
}
 
curl_setopt($ch, CURLOPT_HEADER, true);
 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
$data = curl_exec($ch);
 
list($header, $data) = explode("\n\n", $data, 2);

//echo "DEBUG_header:".$header."<br>DEBUG_data:".$data."<br>";
 
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 
if ($http_code == 301 || $http_code == 302)
 
{
 
$matches = array();
 
preg_match('/Location:(.*?)\n/', $header, $matches);
//echo "HEADER:".$header."<br>";
 
$url = @parse_url(trim(array_pop($matches)));
 
if (!$url)
 
{
 
//couldn't process the url to redirect to
 
$curl_loops = 0;

//echo "FAIL PARCE REDIRECT!<br>".$data."<br>";
 
return $data;
 
}
 
$last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
 
if (!$url['scheme'])
 
$url['scheme'] = $last_url['scheme'];
 
if (!$url['host'])
 
$url['host'] = $last_url['host'];
 
if (!$url['path'])
 
$url['path'] = $last_url['path'];
 
$new_url = $url['scheme'] . '://' . $url['host'] . $url['path'];// . ($url['query']?'?'.$url['query']:'');

//echo "URL:".$new_url."<br>";

$new_url = str_replace("&amp;", "&", $new_url);
$new_url = str_replace("%3a", ":", $new_url);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 
//curl_setopt($ch, CURLOPT_URL, $new_url);
 
return $new_url;//curl_exec($ch);//curl_redir_exec($ch);
 
} else {
 
$curl_loops=0;

//echo "FAIL PARCE REDIRECT!<br>".$data."<br>";
 
return $data;
 
}
}

// функция очищения памяти для simple_html_dom
function EraseMemory($html){
		// очищаем буффер
		if($html)
		{
		$html->clear();
		$html=""; 
		unset($html);
		}
}

 function get_contents($url,$post='',$Referer,$useragent,$followlocation){ 
 $repeat = 1; // Повторения сетевого запроса из-за ошибок
 $repeatCicle = 0; // Повторять запрос, если возникает однотипная ошибка
 // ---- ТЕХНИЧЕСКИЕ ДАННЫЕ ----
$all_useragents = array("Opera/9.23 (Windows NT 5.1; U; ru)", 
"Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.4;MEGAUPLOAD 1.0", 
"Mozilla/5.0 (Windows; U; Windows NT 5.1; Alexa Toolbar; MEGAUPLOAD 2.0; rv:1.8.1.7) Gecko/20070914 Firefox/2.0.0.7;MEGAUPLOAD 1.0", 
"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)", 
"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)", 
"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)", 
"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; Maxthon; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; Media Center PC 5.0; InfoPath.1)", 
"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)", 
"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8", 
"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)", 
"Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8", 
"Opera/9.22 (Windows NT 6.0; U; ru)", 
"Opera/9.22 (Windows NT 6.0; U; ru)", 
"Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/10456466 Firefox/2.0.0.9", 
"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)", 
"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; MRSPUTNIK 1,  8,  0,  17 HW; MRA 4.10 (build 01952); .NET CLR 1.1.4322; .NET CLR 2.0.50727)", 
"Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)",
"Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)",
"Mozilla/5.0 (compatible; YoudaoBot/1.0; http://www.youdao.com/help/webmaster/spider/; )","Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)","Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)",
"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.14) Gecko/2009082707 Firefox/3.0.14 (.NET CLR 3.5.30729)",
"Mozilla/5.0 (compatible; 008/0.83; http://www.80legs.com/webcrawler.html) Gecko/2008032620",
"Mozilla/5.0 (compatible; SiteBot/0.1; +http://www.sitebot.org/robot/)",
"Opera/9.64(Windows NT 5.1; U; en) Presto/2.1.1",
"Mozilla/4.0 (compatible; MSIE 8.0;)",
"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)",
"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.0; DigExt; .NET CLR 1.1.4322)",
"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)",
"Mozilla/0.6 Beta (Windows)",
"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3");
   while($repeat == 1){
    $repeat = 0; // Повторений нет
    // Эмуляция для серверов без CURLOPT_FOLLOWLOCATION
	$emul_curl = true;
	// Использовать прокси? Или работать напрямую?
	$proxyuse = 0;
	$proxy = "";
	$proxy_type = "";
	
    $url=str_replace(' ','+',$url); 
    $ch = curl_init(); 
	
    curl_setopt($ch, CURLOPT_TIMEOUT,12); 
		
    if ($post!=='') 
    { 
    curl_setopt($ch,CURLOPT_POST,TRUE); 
    curl_setopt($ch,CURLOPT_POSTFIELDS,$post); 
    } else curl_setopt($ch,CURLOPT_POST,FALSE);
	
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_HEADER, FALSE); 
    //curl_setopt($ch, CURLOPT_NOBODY, FALSE); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
	

	if($emul_curl !== true)
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	if($Referer!=="") 
	curl_setopt($ch, CURLOPT_REFERER, $Referer);
	
	curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__) . "/cookie.txt");
	curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__) . "/cookie.txt");
	
	if(!$useragent) curl_setopt($ch, CURLOPT_USERAGENT, "User-Agent: ".$all_useragents[rand(0, count($all_useragents)-1)]); 
	else curl_setopt($ch, CURLOPT_USERAGENT, "User-Agent: ".$useragent);
	
	// Активируем GZIP сжатие трафика 
	curl_setopt($ch,CURLOPT_ENCODING,'gzip,deflate');

// работаем с прокси
// Если используем прокси
if($proxyuse == 1){
$cntprx=1;
// Открываем файл и берем прокси + стираем его
$fprx = file("proxy.txt"); // Считываем весь файл в массив
if(count($fprx) == 1){
//echo "Последняя ПРОКСИ. Завершаем работу...";
//fputs($flog, "Последняя ПРОКСИ. Завершаем работу...\n");
$in_cikl = 0;
$EXIT = 1;
}
if($fprx[0] == ""){ 
//fputs($flog, "Прокси больше нет. Аварийное завершение!\n");
Die("Прокси больше нет. Аварийное завершение!");
}
if($in_cikl > 1 ) $in_cikl = 0;
if($in_cikl == 0){
for($i=0;$i<count($fprx);$i++) {
    if($i==0) {
        $proxy = $fprx[$i]; // это первая строка
        $full_file = ""; 
		//echo "Используем прокси:".$proxy."<br>";
		fputs($flog, "Используем прокси:".$proxy."\n");
    }
    else {
        $full_file.=$fprx[$cntprx]; //а здесь все кроме первой строки будет в итоге
		$cntprx++;
    }
}
file_put_contents("proxy.txt",$full_file); //записываем все остальное в файл
}
		$in_cikl++;
}	

    if ($proxy!=="") 
    { 
        if ($proxy_type == "socks") curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5); 
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
        curl_setopt($ch, CURLOPT_PROXY, $proxy); 
    } 

	if($followlocation !== true){
    $result = curl_exec($ch); 
	} else {
	if($emul_curl !== true) $result = curl_exec($ch); 
	else $result = curl_redir_exec($ch);
	}
	
    if (curl_errno($ch)!==0)  
    { 
        $er=curl_error($ch); 
        //return "Error load page  -> $url ($er)"; 
        $result = "Error load page  -> $url ($er)";//false; 
    }; 
    curl_close($ch);
	if(!$result || strpos($result, "Bad Gateway") !== false || strpos($result, "Ошибка") !== false || strpos($result, "Internal Server Error") !== false){ 
	$repeat = 1;
	$repeatCicle++;
	sleep(1);
	} else{ $repeat = 0; }
	
	// Повторяем несколько запросов, если с первой попытки ничего не получили
	if($repeatCicle >=3) break 1;
    } //End.Repeat = 0
    return $result;	
}

function fast_srch($theArray, $searchElement)
{
    if (!is_array($searchElement))
    {
        $e = array($searchElement);
    } else {
        $e = $searchElement;
    }
 
    $a1 = array_merge($theArray, $e);
    $a2 = array_diff($a1, $theArray);
 
    if (count($a2))
    {
        return false;
    } else {
        return true;
    }
}

function replace_spec($text_spec)
{
		// Блок замены спец-символов
		$text_spec = str_replace("&nbsp;", " ", $text_spec);	
		$text_spec = str_replace("&quot;", "'", $text_spec);
		$text_spec = str_replace("&amp;", "&", $text_spec); 
		$text_spec = str_replace("&lt;", "<", $text_spec); 
		$text_spec = str_replace("&gt;", ">", $text_spec); 
		$text_spec = str_replace("&raquo;", "»", $text_spec);
		$text_spec = str_replace("&laquo;", "«", $text_spec);	
		
		$text_spec  = strip_tags($text_spec);
		$text_spec  = trim($text_spec);
		
		return $text_spec;
}

// Бореся с заменой русских букв на английские в словах
function no_translit($str)
{
    $tr = array(
    "A"=>"А","O"=>"О","E"=>"Е","P"=>"Р","C"=>"С","Y"=>"У","K"=>"К","B"=>"В","X"=>"Х","M"=>"М","T"=>"Т","H"=>"Н"
	,"a"=>"а","o"=>"о","e"=>"е","p"=>"р","c"=>"с","y"=>"у","k"=>"к","x"=>"х","m"=>"м","t"=>"т","h"=>"н"		
    );
    return strtr($str,$tr);
}


function get_balance_antigate($gkey,$capcha_server)
{
$rez = get_contents("http://".$capcha_server."/res.php?key=$gkey&action=getbalance");
return $rez;
}

  
?>