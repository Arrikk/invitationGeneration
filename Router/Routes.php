<?php

use Core\Router\Router;

Router::get('', 'home@index');
Router::post('process', 'home@process');
Router::post('save', 'home@save');