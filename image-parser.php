<?php

/*
  Plugin Name: Image Parser
  Description: Парсер изображений
  Version: 1.0
  Author: Nikolay Bychko
  Author URI:
  Plugin URI:
 */

// Вызов функции добавления административных меню
add_action('admin_menu', 'add_pages');

// Сама функция, вызываемая выше
function add_pages() {
    // Создание нового пункта меню верхнего уровня:
    add_menu_page('Image Parser', 'Image Parser', 8, 'parser', 'image_parser_page');
}

// image_parser_page() выводит содержимое страницы меню Image Parser
function image_parser_page() {
    echo "<h2>Парсер изображений</h2>";
    _form();
    _parser();
    image_upload();
}

// Форма для ввода URL
function _form() {
    echo "Введите URL: <br />";
    echo "<form action=\"/wp-admin/admin.php?page=parser\" id=\"form1\" method=\"post\">
          <input type=\"text\" name=\"url\" >
          <input type=\"submit\" name=\"submit\" value=\"Go!\"></form><br/>";
    echo "<br /><b>URL:</b>" . $_POST['url'] . "<br/>";
}

// Парсер изображений. Вывод изображений и формы с чекбоксами
function _parser() {
    echo "<b>Images:</b><br />";
    echo "<form action=\"/wp-admin/admin.php?page=parser\" id=\"form2\" method=\"post\">";
    if (isset($_POST['submit']) && !empty($_POST['url'])) {
        $url = $_POST['url']; //Адресс с которого будут парсится картинки
        $content = file_get_contents($url);
        if ($c = preg_match_all("/<img src=[\'\"](.*?)[\'\"]/", $content, $matches)) {
            $x = 1;
            $i = 0;
            while ($x != "") {
                $x = $matches[1][$i];
                if ($x != "") {
                    print '<img src="' . $x . '" alt="" /> ';
                    echo "<input type=\"checkbox\" name=\"data[]\" value=\"$x\"><br />";
                }
                $i++;
            }
        }
    }
    echo "<br /><input type=\"submit\" name=\"submit2\" value=\"Save\">";
    echo "</form>";
}

// Загрузка выбранных изображений
function image_upload() {
    //$image_url = "http://s.wordpress.org/images/thememarkets/mojo-banner.jpg";

    $data = $_POST['data'];
    $count = count($data);
    for ($i = 0; $i < $count; $i++) {
        $image_url = $data[$i];

        if (isset($image_url)) {
            $upload_dir = wp_upload_dir();
            $image_data = file_get_contents($image_url);
            $filename = basename($image_url);
            if (wp_mkdir_p($upload_dir['path']))
                $file = $upload_dir['path'] . '/' . $filename;
            else
                $file = $upload_dir['basedir'] . '/' . $filename;
            file_put_contents($file, $image_data);

            $wp_filetype = wp_check_filetype($filename, null);
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment($attachment, $file, $post_id);

            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
            wp_update_attachment_metadata($attach_id, $attach_data);

            set_post_thumbnail($post_id, $attach_id);
        }
    }
}
