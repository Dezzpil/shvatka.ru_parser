<?php
//header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
include('simple_html_dom.php');

$db = mysql_connect('localhost', 'root', '');
mysql_select_db('game');
mysql_query("SET NAMES utf8");

set_time_limit(10000000);

// Ваши куки с схватка.ру
$Cookie = "";

// Инициализируем переменные.
$games = null;
$html  = null;
$item  = null;
//==================================================================================
// Получаем список игр
//==================================================================================
$html = file_get_html('http://www.shvatka.ru/index.php?act=module&module=shstat&cmd=games');
// Проверяем загрузилась ли страничка
if (is_object($html))  {
	// Ищем нужную таблицу
	$table = $html->find('table.borderwrap', 0);
	if (is_object($table)) {
		// Перебераем строки таблицы
		foreach($table->find('tr.ipbtable') as $tr) {
			if (is_object($tr))  {
				// Первый столбец - номер игры
				if (is_object($tr->find('td.row2', 0)))
				{ 
					$item['id'] = $tr->find('td.row2', 0)->plaintext;
					// Второй столбец название игры и...
					$title = $tr->find('td.row1', 0);
					if (is_object($title))  {
						$a = $title->find('a', 0);
						
						if (is_object($title))  {
							$item['title'] = $a->plaintext;
							$item['title'] = htmlspecialchars_decode(iconv('windows-1251', 'utf-8', $item['title']));
							// ... ссылка на сценарий и данные по игре
							$item['link'] = $a->href; 
							// Исправляем ссылки на игры после 18ой
							If(!strcmp(substr($item['link'],0,2),"./")) {
								$item['link'] = "http://www.shvatka.ru".substr($item['link'],1);
							}
						}
					}
					
					// Добавляем данные по игре в массив
					$games[] = $item;
				}
			}
		}
	} else {
		echo ("Чёт таблички нема");
	}
}
else {
	echo ("Чёт заглючило");
}

$html->clear(); // подчищаем за собой
unset($html);
//==================================================================================

//==================================================================================
// Парсим сценарии
//==================================================================================ъ
// todo: Проверять масив на наличие данных is not empty
foreach($games as &$game){
	$html=$scenic=$t=null;
	
	// Берём только игры до 19ой!
	if($game['id'] > 0 ) {
		
		
		if ($curl = curl_init()) {
			curl_setopt($curl, CURLOPT_ENCODING ,"");
			curl_setopt($curl, CURLOPT_URL, $game['link']);
			curl_setopt($curl, CURLOPT_COOKIE, $Cookie);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$out = curl_exec($curl);
			curl_close($curl);
		}
		$html = str_get_html( $out);
		// Проверяем загрузилась ли страница
		if (is_object($html)) {
			$t = $html->find('div.postcolor',0);
			if (is_object($t)) {
				//  Получаем содержимое строки, и меняем кодировку на utf-8
				$str = iconv('windows-1251', 'utf-8', $t->innertext)."<b>";
				// Парсим уровни по патерну
				if ($game['id'] == 1){
					preg_match_all( '#Уровень (.*?)\..*?<br />(.*?)<br />Ключ: (.*?)<br /><br />(.*?)<b>#is',$str,$matches, PREG_SET_ORDER);
					// Перебераем результаты
					foreach ( $matches as $value ){
						$level=$tips=null;
						$level['id'] = $value[1];
						$level['key'] = $value[2];
						$level['text'] = $value[3];
						// Парсим подсказки по патерну
						preg_match_all( '#Подсказка №.*? \((.?.) мин.\)<br />(.*?)<br /><br />#is', $value[4]."<br><b>", $matches1, PREG_SET_ORDER);
						
						// Перебераем результат
						foreach ( $matches1 as $value1 ){
							$tip=null;
							$tip['time'] = $value1[1];
							$tip['text'] = $value1[2];
							$tips[] = $tip;
						}
						$level['tips'] = $tips;
						// добавляем уровень в сценарий
						$scenic[] = $level;
					}
					$game['scenic'] = $scenic;
				} else {
					preg_match_all( '#Уровень (.*?)\..*?Ключ: (.*?)<br /><br />(.*?)<br /><br />(.*?)<b>#is',$str,$matches, PREG_SET_ORDER);
					// Перебераем результаты
					foreach ( $matches as $value ){
						$level=$tips=null;
						$level['id'] = $value[1];
						$level['key'] = $value[2];
						$level['text'] = $value[3];
						// Парсим подсказки по патерну
						preg_match_all( '#Подсказка №.*? \((.?.) мин.\)<br />(.*?)<br /><br />#is', $value[4]."<br><b>", $matches1, PREG_SET_ORDER);
						
						// Перебераем результат
						foreach ( $matches1 as $value1 ){
							$tip=null;
							$tip['time'] = $value1[1];
							$tip['text'] = $value1[2];
							$tips[] = $tip;
						}
						$level['tips'] = $tips;
						// добавляем уровень в сценарий
						$scenic[] = $level;
					}
					$game['scenic'] = $scenic;
				}
			}
		}
	}
	
	$html=$scenic=$t=null;
	// Берём только игры после 18ой!
	if($game['id'] > 18 ) {
		$html = file_get_html($game['link']);
		// Проверяем загрузилась ли страница
		if (is_object($html)) {
			// Ищем нужную нам строку таблицы
			$t = $html->find('td.row1', 0);
			if (is_object($t)) {
				//  Получаем содержимое строки, и меняем кодировку на utf-8
				$str = iconv('windows-1251', 'utf-8', $t->innertext); 
				$str = substr($str, 0, strlen($str)-9)."<br>"; // Костыль
				// Парсим уровни по патерну
				preg_match_all( '#<center><b>Уровень (.*?)\. Ключ: </b>(.*?)</center><br>(.*?)<br><b>(.*?)<br><br>#is',$str,$matches, PREG_SET_ORDER);
				// Перебераем результаты
				foreach ( $matches as $value ){
					$level=$tips=null;
					$level['id'] = $value[1];
					// Проверка на наличие мозгового ключа
					if(($pos = strpos($value[2], "Мозговой ключ:")) !== False){
						$level['key'] = substr($value[2],0,strpos($value[2], "<b>"));
						$level['bkey'] = substr($value[2],strpos($value[2], "</b>")+4);
					} 
					else{
						$level['key'] = $value[2];
						$level['bkey'] = "";
					}
					$level['text'] = $value[3];
					// Парсим подсказки по патерну
					preg_match_all( '#Подсказка №.*? \((.?.) мин.\)</b>.*?<br>(.*?)<br><b>#is', $value[4]."<br><b>", $matches1, PREG_SET_ORDER);
					// Перебераем результат
					foreach ( $matches1 as $value1 ){
						$tip=null;
						$tip['time'] = $value1[1];
						$tip['text'] = $value1[2];
						$tips[] = $tip;
					}
					$level['tips'] = $tips;
					// добавляем уровень в сценарий
					$scenic[] = $level;
				}
				$game['scenic'] = $scenic;
				//print_r($scenic);
			}
		}
	}

	
	//if($game['id'] == 3 ) break; //debug
}

// Выводим весь массив

var_export($games); // debug
//==================================================================================
?>