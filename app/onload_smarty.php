<?php

function url($params, $smarty) {
    $action = $params['action'] ?? '';
    unset($params['action']);
    return \core\Utils::URL($action, $params);
}

function rel_url($params, $smarty) {
    $action = $params['action'] ?? '';
    unset($params['action']);
    return \core\Utils::relURL($action, $params);
}

\core\App::getSmarty()->registerPlugin("function","url", "url");
\core\App::getSmarty()->registerPlugin("function","rel_url", "rel_url");

# Zmienna globalna dla ścieżek
\core\App::getSmarty()->assign("base_url", "/drukarnia/");
