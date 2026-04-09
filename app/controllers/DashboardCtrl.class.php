<?php
namespace app\controllers;

use core\App;
use core\Utils;

class DashboardCtrl {

    // Po zalogowaniu przekierowuje użytkownika do jego panelu
    public function action_index() {
        if (!isset($_SESSION['user'])) {
            Utils::addErrorMessage("Zaloguj się, aby zobaczyć panel.");
            App::getRouter()->redirectTo('login');
            return;
        }

        $role = $_SESSION['user']['role'];

        switch($role) {
            case 'drukarz':
                App::getRouter()->redirectTo('orders_printer');
                break;
            case 'druk_cyfrowy':
                App::getRouter()->redirectTo('orders_cyfrowy');
                break;
            case 'druk_wielkoformatowy':
                App::getRouter()->redirectTo('orders_wide');
                break;
            case 'introligator':
                App::getRouter()->redirectTo('orders_binding');
                break;
            case 'laser':
                App::getRouter()->redirectTo('orders_laser');
                break;
            case 'admin':
                App::getRouter()->redirectTo('orders_admin');
                break;
            default:
                Utils::addErrorMessage("Nieznana rola użytkownika: $role");
                App::getRouter()->redirectTo('login');
        }
    }
}
