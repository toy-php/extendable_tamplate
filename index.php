<?php

include 'vendor/autoload.php';

$data = [
    'title' => 'test',
    'content' => 'тут содержимое'
];

$layout = new \View\Template(__DIR__ . '/layout.php', $data);

$layout->registerFunction('eachBlock', function (array $elements) {
    foreach ($elements as $element) {
        if (is_callable($element)) {
            echo $element();
        }
    }
});


$layout->sidebar = [
    \View\Template::lazyLoad(__DIR__ . '/sidebar_menu.php'),
    \View\Template::lazyLoad(__DIR__ . '/top_news.php'),
    \View\Template::lazyLoad(__DIR__ . '/sidebar_ads.php')
];


echo $layout;





