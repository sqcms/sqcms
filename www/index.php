<?php

// ini
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);
date_default_timezone_set('Asia/Tokyo');

//execute
$db = new PDO('sqlite:' . __DIR__ . '/.ht.db.sqlite3');
$base_url = substr($_SERVER['SCRIPT_NAME'], 0, -10); // 自動判別させる例
$path = substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), strlen($base_url)); // 自動判別させる例
$header = '';
$title = 'sqcms';
$reponse = '';

$s = $db->prepare("select * from posts where name = ?");
$s->execute(array(trim($path, '/')));
$post = $s->fetch();
if (!$post) {
	$s->execute(array('404 Not Found'));
	$post = $s->fetch();
}
if ($post) {
	$s = $db->prepare("select * from layouts where name = ?");
	$s->execute(array($post['layout']));
	$layout = $s->fetch();
}
if ($post) {
	if ($post['type'] == 'php') {
		ob_start();
		eval('?>' . (convert_url($post['data'] ? $post['data'] : $post['blobdata'])));
		$contents = ob_get_clean();
	} else {
		$contents = ($post['data'] ? $post['data'] : $post['blobdata']);
	}
	$header = $post['header'];
}
if ($layout) {
	if ($layout['type'] == 'php') {
		ob_start();
		eval('?>' . (convert_url($layout['data'])));
		$reponse = ob_get_clean();
	} else {
		$reponse = ($layout['data']);
	}
} else {
	$reponse = $contents;
}

if ($header) {
	foreach (explode("\n", $header) as $v) {
		header(trim($v));
	}
}
echo $reponse;

//functions
function convert_url($s)
{
	return str_replace(
		array('href="/', 'src="/', 'action="/'),
		array('href="<?php echo $base_url ?>/', 'src="<?php echo $base_url ?>/', 'action="<?php echo $base_url ?>/'),
		$s
	);
}
