<?php
namespace app\controllers;

use core\App;
use core\Utils;
use core\ParamUtils;

class ClientCtrl {

    public function action_client_save() {
        $company_name   = ParamUtils::getFromRequest('company_name', true, 'Brak nazwy firmy');
        $contact_person = ParamUtils::getFromRequest('contact_person');
        $email          = ParamUtils::getFromRequest('email');
        $phone          = ParamUtils::getFromRequest('phone');
        $address        = ParamUtils::getFromRequest('address');
        $nip            = ParamUtils::getFromRequest('nip');

        if (App::getDB()->has('clients', ['company_name' => $company_name])) {
            Utils::addErrorMessage('Klient o takiej nazwie już istnieje.');
            App::getRouter()->redirectTo('order_add');
            return;
        }

        try {
            App::getDB()->insert('clients', [
                'company_name'   => $company_name,
                'contact_person' => $contact_person,
                'email'          => $email,
                'phone'          => $phone,
                'address'        => $address,
                'nip'            => $nip
            ]);
            Utils::addInfoMessage('Klient został dodany.');
        } catch (\PDOException $e) {
            Utils::addErrorMessage('Błąd zapisu klienta.');
            if (App::getConf()->debug) Utils::addErrorMessage($e->getMessage());
        }

        App::getRouter()->redirectTo('order_add');
    }

    // ===================== AJAX: podpowiadanie danych klienta =====================
    public function action_client_lookup() {
        $name = ParamUtils::getFromRequest('name');

        if (!$name) {
            echo json_encode(["success" => false, "error" => "Brak nazwy klienta"]);
            exit;
        }

        try {
            $client = App::getDB()->get("clients", [
                "id",
                "company_name",
                "contact_person",
                "email",
                "phone",
                "address",
                "nip"
            ], [
                "company_name" => $name
            ]);

            if ($client) {
                echo json_encode([
                    "success" => true,
                    "client" => $client
                ]);
            } else {
                echo json_encode(["success" => false]);
            }
        } catch (\PDOException $e) {
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
        exit;
    }
}
