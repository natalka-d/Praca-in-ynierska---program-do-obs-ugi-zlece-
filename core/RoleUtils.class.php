<?php

namespace core;

/**
 * Wrapper class for role utility functions
 *
 * Obsługuje dodawanie, usuwanie i sprawdzanie ról użytkownika.
 * Teraz wspiera zarówno pojedynczą rolę (string), jak i tablicę ról.
 */
class RoleUtils {

    private static function ensureSession() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function addRole($role) {
        self::ensureSession();

        // Inicjalizacja, jeśli pusta
        if (!isset(App::getConf()->roles) || !is_array(App::getConf()->roles)) {
            App::getConf()->roles = [];
        }

        // Jeśli podano tablicę ról
        if (is_array($role)) {
            foreach ($role as $r) {
                App::getConf()->roles[$r] = true;
            }
        } else {
            // Pojedyncza rola
            App::getConf()->roles[$role] = true;
        }

        $_SESSION['_amelia_roles'] = serialize(App::getConf()->roles);
    }

    public static function removeRole($role) {
        self::ensureSession();

        if (isset(App::getConf()->roles[$role])) {
            unset(App::getConf()->roles[$role]);
            $_SESSION['_amelia_roles'] = serialize(App::getConf()->roles);
        }
    }

    public static function inRole($role) {
        self::ensureSession();
        return isset(App::getConf()->roles[$role]);
    }

    public static function requireRole($role, $fail_action = null) {
        if (!self::inRole($role)) {
            if (isset($fail_action)) {
                App::getRouter()->forwardTo($fail_action);
            } else {
                App::getRouter()->forwardTo(App::getRouter()->getLoginRoute());
            }
        }
    }
    public static function clearRoles() {
        self::ensureSession();
        App::getConf()->roles = [];
        $_SESSION['_amelia_roles'] = serialize([]);
}

}
