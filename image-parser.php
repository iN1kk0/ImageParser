<?php

/*
  Plugin Name: Image Parser
  Description: Парсер изображений
  Version: 1.2
 */

// Вызов функции добавления административных меню
add_action('admin_menu', 'add_pages');

// Функция, вызываемая выше
function add_pages() {
    // Создание нового пункта меню
    add_menu_page('Image Parser', 'Image Parser', 8, 'parser', 'image_parser_page');
}

// image_parser_page() выводит содержимое страницы меню Image Parser
function image_parser_page() {
    include('form.html');
    _parser();
    _image_upload();
}

// curl
function _get_content($url) {
    // Заголовки
    $agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.7.12) Gecko/20050919 Firefox/1.0.7";
    $header[] = "Accept: text/html;q=0.9, text/plain;q=0.8, image/png, */*;q=0.5";
    $header[] = "Accept_charset: windows-1251, utf-8, utf-16;q=0.6, *;q=0.1";
    $header[] = "Accept_encoding: identity";
    $header[] = "Accept_language: en-us,en;q=0.5";
    $header[] = "Connection: close";
    $header[] = "Cache-Control: no-store, no-cache, must-revalidate";
    $header[] = "Keep_alive: 300";
    $header[] = "Expires: Thu, 01 Jan 1970 00:00:01 GMT";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $content = curl_exec($ch);
    curl_close($ch);

    return $content;
}

// Парсер изображений. Вывод изображений и формы с чекбоксами
function _parser() {
    echo "<b>URL:</b>" . $_POST['url'] . "<br/>";
    echo "<b>Images:</b><br />";
    echo "<form action=\"/wp-admin/admin.php?page=parser\" id=\"form2\" method=\"post\">";
    if (isset($_POST['submit']) && !empty($_POST['url'])) {
        $url = $_POST['url']; // Адресс с которого будут парсится картинки
        //$content = file_get_contents($url);
        $content = _get_content($url);
        if (preg_match_all("/<img src=[\'\"](.*?)[\'\"]/", $content, $matches)) {
			foreach($matches as $match){
				$img = $match[1];
				print ' <img src="' . $img . '" alt="" /> ';
				echo "<input type=\"checkbox\" name=\"data[]\" value=\"$img\"><br />";
			}
		}
    }
    echo "<br /><input type=\"submit\" name=\"submit2\" value=\"Save\">";
    echo "</form>";
}

// Загрузка выбранных изображений
function _image_upload() {
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
