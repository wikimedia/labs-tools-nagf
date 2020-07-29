<?php

require_once __DIR__ . '/../inc/autoload.php';

header('content-type: text/html; charset=utf-8');

try {
	$app = new Nagf();
	$view = $app->getView();
	echo $view->output();
} catch (Exception $e) {
	NagfView::error($e);
}
