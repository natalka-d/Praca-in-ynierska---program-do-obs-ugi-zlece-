<?php
namespace app\controllers;

use core\App;
use core\Utils;
use core\ParamUtils;
use core\SessionUtils;

class AccountCtrl {

    public function action_account() {
        if (!isset($_SESSION['user'])) {
            App::getRouter()->redirectTo('login');
            return;
        }

        $userObj = SessionUtils::loadObject('user');

        $userData = [
            'username' => $this->getUserProperty($userObj, ['login', 'username'], 'Nieznany'),
            'role'     => $this->getUserProperty($userObj, ['role'], 'user'),
            'id'       => $this->getUserProperty($userObj, ['id'], null)
        ];

        App::getSmarty()->assign('user', $userData);
        App::getSmarty()->display('Account.tpl');
    }

    public function action_account_change_password() {
        // 1. WERYFIKACJA SESJI (Bezpieczeństwo)
        if (!isset($_SESSION['user'])) {
            Utils::addErrorMessage('Sesja wygasła. Zaloguj się ponownie.');
            App::getRouter()->redirectTo('login');
            return;
        }

        $userObj = SessionUtils::loadObject('user');
        $userId = $this->getUserProperty($userObj, ['id'], null);
        $userId = ($userId !== null) ? (int)$userId : null;

        if (!$userId) {
            Utils::addErrorMessage('Błąd systemowy: brak identyfikatora użytkownika.');
            App::getRouter()->redirectTo('account');
            return;
        }

        $oldPassword    = ParamUtils::getFromPost('old_password');
        $newPassword    = ParamUtils::getFromPost('new_password');
        $repeatPassword = ParamUtils::getFromPost('repeat_password');

        // Walidacja podstawowa
        if (empty($oldPassword) || empty($newPassword) || empty($repeatPassword)) {
            Utils::addErrorMessage('Wypełnij wszystkie pola.');
            App::getRouter()->redirectTo('account');
            return;
        }

        if ($newPassword !== $repeatPassword) {
            Utils::addErrorMessage('Nowe hasła nie są identyczne.');
            App::getRouter()->redirectTo('account');
            return;
        }

        try {
            // 2. SPRAWDZENIE STAREGO HASŁA W BAZIE
            $dbData = App::getDB()->get("users", ["password"], ["id" => $userId]);

            if (!$dbData || !password_verify($oldPassword, $dbData['password'])) {
                Utils::addErrorMessage('Podane stare hasło jest niepoprawne.');
                App::getRouter()->redirectTo('account');
                return;
            }

            // 3. AKTUALIZACJA HASŁA
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            App::getDB()->update("users", [
                "password" => $newHash
            ], [
                "id" => $userId
            ]);

            Utils::addInfoMessage('Twoje hasło zostało zmienione pomyślnie.');

        } catch (\Exception $e) {
            Utils::addErrorMessage('Błąd bazy danych: ' . $e->getMessage());
        }

        App::getRouter()->redirectTo('account');
    }

    private function getUserProperty($user, array $keys, $default = null) {
        foreach ($keys as $key) {
            if (is_object($user)) {
                if (isset($user->$key)) return $user->$key;
                $method = 'get' . ucfirst($key);
                if (method_exists($user, $method)) return $user->$method();
            }
            if (is_array($user) && isset($user[$key])) return $user[$key];
        }
        return $default;
    }
    public function action_user_list_ajax() {
        // Pobierz wszystkich użytkowników (możesz wykluczyć zalogowanego admina)
        $users = App::getDB()->select("users", ["id", "username", "email", "role"]);

        App::getSmarty()->assign('users', $users);
        App::getSmarty()->display('UserListAjax.tpl');
    }

    public function action_user_update_ajax() {
        $id = ParamUtils::getFromPost('id');
        $email = ParamUtils::getFromPost('email');

        $updateData = [
            "email" => $email
        ];
        
        App::getDB()->update("users", $updateData, ["id" => $id]);

        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
        exit;
    }
}