<?php
namespace app\controllers;

use core\App;
use core\Utils;
use core\RoleUtils;
use core\ParamUtils;
use core\SessionUtils;
use app\forms\LoginForm;
use app\transfer\User;

class LoginCtrl {
    private $form;

    public function __construct() {
        $this->form = new LoginForm();
    }

    public function action_loginShow() {
        App::getSmarty()->assign('form', $this->form);
        App::getSmarty()->display('LoginView.tpl');
    }

    public function action_login() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->form->username = ParamUtils::getFromRequest('username');
        $this->form->password = ParamUtils::getFromRequest('password');

        if (empty($this->form->username) || empty($this->form->password)) {
            Utils::addErrorMessage('Wprowadź login i hasło');
            App::getSmarty()->assign('form', $this->form);
            App::getSmarty()->display('LoginView.tpl');
            return;
        }

        try {
            $user = App::getDB()->get("users", ["id", "username", "password", "role"], [
                "username" => $this->form->username
            ]);

            if ($user && password_verify($this->form->password, $user['password'])) {
                // TWORZENIE OBIEKTU Z KOMPLETEM DANYCH (Login, Rola, ID)
                $userObj = new User($user['username'], $user['role'], $user['id']);
                
                SessionUtils::storeObject('user', $userObj);
                RoleUtils::addRole($user['role']);

                session_write_close();

                // Przekierowania zależne od roli
                if ($user['role'] === 'admin') {
                    App::getRouter()->redirectTo('orders_admin');
                } elseif ($user['role'] === 'drukarz') {
                    App::getRouter()->redirectTo('orders_printer');
                } elseif ($user['role'] === 'cyfrowy') {
                    App::getRouter()->redirectTo('orders_cyfrowy');
                } elseif ($user['role'] === 'wide') {
                    App::getRouter()->redirectTo('orders_wide');
                } elseif ($user['role'] === 'binding') {
                    App::getRouter()->redirectTo('orders_binding');
                } elseif ($user['role'] === 'laser'){
                    App::getRouter()->redirectTo('orders_laser');
                } elseif ($user['role'] === 'dtp'){
                    App::getRouter()->redirectTo('orders_dtp');
                } else {
                    App::getRouter()->redirectTo('loginShow');
                }
                exit;
            } else {
                Utils::addErrorMessage('Nieprawidłowy login lub hasło');
                App::getSmarty()->assign('form', $this->form);
                App::getSmarty()->display('LoginView.tpl');
            }
        } catch (\PDOException $e) {
            Utils::addErrorMessage('Błąd logowania');
            if (App::getConf()->debug) Utils::addErrorMessage($e->getMessage());
            App::getSmarty()->assign('form', $this->form);
            App::getSmarty()->display('LoginView.tpl');
        }
    }
    
    public function action_logout() {
        RoleUtils::clearRoles();
        $_SESSION = [];
        if (session_id() !== '' || isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        session_destroy();
        App::getRouter()->redirectTo("login");
    }
}