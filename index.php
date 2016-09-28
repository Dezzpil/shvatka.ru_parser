<?php
//header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
include('simple_html_dom.php');

$db = mysql_connect('localhost', 'root', '');
mysql_select_db('game');
mysql_query("SET NAMES utf8");

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
					preg_match_all( '#Подсказка №.*? \((.?.) мин.\)</b>.*?<br>(.*?)<br><b>#is', $value[4]."<br><b>"/*Костыль*/, $matches1, PREG_SET_ORDER);
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
	//if($game['id'] > 20 ) break; //debug
}

// Выводим весь массив
/* получаем массив вида:
array (
	...
18 => 
  array (
    'id' => '19',
    'title' => 'Свартальвхейм',
    'link' => 'http://www.shvatka.ru/index.php?act=module&module=shgames&cmd=disp&id=19',
    'scenic' => 
    array (
      0 => 
      array (
        'id' => '1',
        'key' => 'SHШИФРОВКАИЗЦЕНТРА',
        'bkey' => '',
        'text' => 'Легенда<br /><br />Вам остается лишь расслабиться и слушать. Кому надо – тот поймет, о чем идет речь. Найти скрытые мысли несложно, если поверить в старую легенду о городе Свартальвхейм. Прошло уже почти тысячелетие, мало кто верит в эту разбитую временем историю, но остались те, чью каменную веру не сломать. Трудолюбивые гномы начали постройку города, не оставляя веры в успех. Удачи им было не занимать, чего и вам желаем в поисках сокровищ маленьких человечков.',
        'tips' => 
        array (
          0 => 
          array (
            'time' => '10',
            'text' => 'не пугайтесь, это шифровка',
          ),
          1 => 
          array (
            'time' => '20',
            'text' => 'в расшифровке вам помогут цифры 1 и 6',
          ),
          2 => 
          array (
            'time' => '35',
            'text' => 'читаем первое слово, затем шесть слов пропускаем',
          ),
          3 => 
          array (
            'time' => '50',
            'text' => 'камнедробильный завод у кристалла',
          ),
          4 => 
          array (
            'time' => '60',
            'text' => 'SHШИФРОВКАИЗЦЕНТРА',
          ),
        ),
      ),
	  ...
	 )
	)
	...
}
var_export($games); // debug
//==================================================================================
?>