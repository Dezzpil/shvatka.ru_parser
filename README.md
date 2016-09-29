# shvatka.ru_parser
simplest html parser from shvatka.ru

Use: [PHP Simple HTML DOM Parser] (http://simplehtmldom.sourceforge.net/) and regular expressions

Screept work result simple:
```php
array (
	...
18 => 
  array (
    'id' => '<GAME ID>',
    'title' => '<GAME NAME HEADER>',
    'link' => '<GAME ON SHVATKA.RU URL>',
    'scenic' => 
    array (
      0 => 
      array (
        'id' => '<LEVEL ID>',
        'key' => '<LEVEL KEY>',
        'bkey' => '<LEVEL BRAIN KEY>',
        'text' => '<LEVEL TEXT>',
        'tips' => 
        array (
          0 => 
          array (
            'time' => '<TIP HOLD TIME>',
            'text' => '<TIP TEXT>',
          ),
          ...
        ),
      ),
	  ...
	 )
	)
	...
}
```
