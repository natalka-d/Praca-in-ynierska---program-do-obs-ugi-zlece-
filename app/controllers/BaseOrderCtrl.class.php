<?php
namespace app\controllers;

use core\App;
use core\Utils;
use core\ParamUtils;
use core\RoleUtils;

abstract class BaseOrderCtrl {

    protected function isValidDateOrEmpty($value) {
        if ($value === null || $value === '') return true;
        $dt = \DateTime::createFromFormat('Y-m-d', $value);
        return $dt && $dt->format('Y-m-d') === $value;
    }

    protected function currentRole(): string {
        if (class_exists('\\core\\RoleUtils') && RoleUtils::inRole('admin')) return 'admin';
        if (class_exists('\\core\\RoleUtils') && RoleUtils::inRole('drukarz')) return 'drukarz';
        return 'guest';
    }

    protected function resolveClientId($clientInput) {
        $clientInput = trim((string)$clientInput);
        if ($clientInput === '') return null;

        if (ctype_digit($clientInput)) {
            $id = (int)$clientInput;
            $exists = App::getDB()->has('clients', ['id' => $id]);
            if ($exists) return $id;
            Utils::addErrorMessage('Podane ID klienta nie istnieje.');
            return null;
        }

        $existing = App::getDB()->get('clients', ['id'], ['company_name' => $clientInput]);
        if ($existing && isset($existing['id'])) {
            return (int)$existing['id'];
        }

        App::getDB()->insert('clients', ['company_name' => $clientInput]);
        $newId = App::getDB()->id();
        if ($newId) return (int)$newId;

        Utils::addErrorMessage('Nie udało się zapisać klienta.');
        return null;
    }
}