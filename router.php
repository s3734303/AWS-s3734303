<?php
ini_set('allow_url_fopen',1);

switch (@parse_url($_SERVER['REQUEST_URI'])['path']){

    case '/login':
    case '/':
        require 'index.php';
        break;
    case '/main':
        require 'main.php';
        break;
    case '/logout':
        require 'logout.php';
        break;
    case '/register':
        require 'register.php';
        break;
}
