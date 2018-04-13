<?php

require_once __DIR__ . '/../vendor/autoload.php';

header('content-type: text/html; charset=utf-8');

try {
	$app = new Nagf();
	$view = $app->getView();
	$html = $view->output();
} catch (Exception $e) {
	$html = NagfView::error($e);
}

echo $html;
