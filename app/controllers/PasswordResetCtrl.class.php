<?php
namespace app\controllers;

use core\App;
use core\Utils;
use core\ParamUtils;

class PasswordResetCtrl {

    public function action_password_forgot() {
        App::getSmarty()->display('PasswordForgot.tpl');
    }

    public function action_password_send_link() {
        $email = ParamUtils::getFromPost('email');

        if (empty($email)) {
            Utils::addErrorMessage('Podaj adres e-mail.');
            App::getRouter()->redirectTo('password_forgot');
            return;
        }

        // Szukamy użytkownika po loginie LUB mailu
        $user = App::getDB()->get("users", ["id", "username", "email"], [
            "OR" => [
                "username" => $email,
                "email" => $email
            ]
        ]);

        if ($user && !empty($user['email'])) {
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            App::getDB()->update("users", [
                "reset_token"   => $token,
                "reset_expires" => $expires
            ], [
                "id" => $user['id']
            ]);

            // Generowanie linku
            $link = App::getConf()->protocol . '://' . App::getConf()->server_name . App::getConf()->app_root . '/password_new_form?token=' . $token;

            // WYSYŁKA MAILEM
            $to = $user['email'];
            $subject = "Resetowanie hasla - Drukarnia";
            $message = "Aby zresetowac haslo, kliknij w ponizszy link:\r\n" . $link;
            $headers = 'From: noreply@' . App::getConf()->server_name . "\r\n" . 'X-Mailer: PHP/' . phpversion();

            mail($to, $subject, $message, $headers);

            // Zostawiamy info o wysyłce (i link dla testów, dopóki nie masz pewności co do maila)
            Utils::addInfoMessage("Instrukcja została wysłana na adres przypisany do konta.");
            Utils::addInfoMessage("DEBUG (link): " . $link); 
            
        } else {
            Utils::addInfoMessage('Jeśli podany adres istnieje w systemie, wysłaliśmy instrukcję.');
        }

        App::getRouter()->redirectTo('login');
    }

    public function action_password_new_form() {
        $token = ParamUtils::getFromRequest('token');
        
        if (empty($token)) {
            Utils::addErrorMessage('Nieprawidłowy link.');
            App::getRouter()->redirectTo('login');
            return;
        }

        $user = App::getDB()->get("users", ["id", "username", "reset_expires"], ["reset_token" => $token]);

        if (!$user || strtotime($user['reset_expires']) < time()) {
            Utils::addErrorMessage('Link wygasł lub jest nieprawidłowy.');
            App::getRouter()->redirectTo('password_forgot');
            return;
        }

        App::getSmarty()->assign('user_name', $user['username']);
        App::getSmarty()->assign('token', $token);
        App::getSmarty()->display('PasswordNew.tpl');
    }

    public function action_password_save_new() {
        $token = ParamUtils::getFromPost('token');
        $password = ParamUtils::getFromPost('password');
        $password_repeat = ParamUtils::getFromPost('password_repeat');

        if (empty($token) || empty($password) || $password !== $password_repeat) {
            Utils::addErrorMessage('Hasła muszą być identyczne i nie mogą być puste.');
            App::getRouter()->redirectTo('password_new_form', ['token' => $token]);
            return;
        }

        $user = App::getDB()->get("users", ["id", "reset_expires"], ["reset_token" => $token]);

        if (!$user || strtotime($user['reset_expires']) < time()) {
            Utils::addErrorMessage('Błąd bezpieczeństwa: sesja wygasła.');
            App::getRouter()->redirectTo('login');
            return;
        }

        // Zapis nowego hasła
        App::getDB()->update("users", [
            "password" => password_hash($password, PASSWORD_DEFAULT),
            "reset_token" => null,
            "reset_expires" => null
        ], ["id" => $user['id']]);

        Utils::addInfoMessage('Hasło zostało zmienione. Możesz się zalogować.');
        App::getRouter()->redirectTo('login');
    }
}