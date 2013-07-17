<?php

/*
  Plugin Name: Image Parser
  Description: Image Parser
  Version: 1.0
  Author: Nikolay Bychko
  Author URI:
  Plugin URI:
 */

// Вызов функции добавления административных меню
add_action('admin_menu', 'mt_add_pages');

// Сама функция, вызываемая выше
function mt_add_pages() {
    // Создание нового пункта меню верхнего уровня:
    add_menu_page('Image Parser', 'Image Parser', 8, __FILE__, 'mt_toplevel_page');
}

// mt_toplevel_page() выводит содержимое страницы меню Image Parser
function mt_toplevel_page() {
    echo "<h2>Hello, world</h2>";
    _form();
    _parser();
}

function _form() {
    echo "Введите URL:<br/>";
    echo "<form action=\"$_SERVER[PHP_SELF]?page=image-parser/image-parser.php\" id=\"form\" method=\"post\">
          <input type=\"text\" name=\"url\" >
          <input type=\"submit\" name=\"submit\" value=\"Go!\"><form><br/>";
    echo "<br/>URL: " . $_POST['url'] . "<br/>";
}

function _parser() {
    echo "Images:<br/>";
    if (isset($_POST['submit'])) {
        $url = $_POST['url']; //Адресс с которого будут парсится картинки
        $txt = file_get_contents($url);
        if ($c = preg_match_all("/<img src=[\'\"](.*?)[\'\"]/", $txt, $matches)) {
            $x = 1;
            $i = 0;
            while ($x != "") {
                $x = $matches[1][$i];
                if ($x != "") {
                    print '<img src="' . $x . '" alt="" /><br />';
                }
                $i++;
            }
        }
    }
}

