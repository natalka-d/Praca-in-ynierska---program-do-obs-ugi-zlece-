<?php
namespace app\controllers;

use core\App;
use core\Utils;
use core\ParamUtils;

class DtpOrderCtrl {

    // Lista zleceń DTP
    public function action_orders_dtp() {
        $page = ParamUtils::getFromCleanURL(1, false);
        $page = ($page && ctype_digit($page)) ? intval($page) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            // Liczenie wszystkich zleceń z etapem DTP
            $total = App::getDB()->count("orders", [
                "[>]order_stages" => ["id" => "order_id"],
                "[>]dtp" => ["order_stages.id" => "stage_id"]
            ], ["orders.id"], [
                "order_stages.stage_type" => "dtp"
            ]);

            $total_pages = ceil($total / $limit);
            if ($page > $total_pages && $total_pages > 0) {
                App::getRouter()->redirectTo("orders_dtp/$total_pages");
                return;
            }

            // Pobranie listy zleceń z etapami DTP
            $orders = App::getDB()->select("orders", [
                "[>]clients" => ["client_id" => "id"],
                "[>]order_stages" => ["orders.id" => "order_id"],
                "[>]dtp" => ["order_stages.id" => "stage_id"]
            ], [
                "orders.id",
                "orders.name",
                "clients.company_name(client)",
                "orders.start_date",
                "orders.end_date",
                "dtp.id(stage_id)",
                "dtp.done",
                "order_stages.status"
            ], [
                "order_stages.stage_type" => "dtp",
                "ORDER" => ["orders.created_at" => "DESC"],
                "LIMIT" => [$offset, $limit]
            ]);

            App::getSmarty()->assign([
                "orders" => $orders,
                "current_page" => $page,
                "total_pages" => $total_pages,
                "total_orders" => $total
            ]);
            App::getSmarty()->display("OrdersDtp.tpl");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: " . $e->getMessage());
            App::getSmarty()->display("OrdersDtp.tpl");
        }
    }

    // Szczegóły zlecenia DTP
    public function action_order_details_dtp() {
        $id = ParamUtils::getFromCleanURL(1, true, 'Brak ID zlecenia');

        try {
            $order = App::getDB()->get("orders", [
                "[>]clients" => ["client_id" => "id"],
                "[>]order_stages" => ["orders.id" => "order_id"],
                "[>]dtp" => ["order_stages.id" => "stage_id"]
            ], [
                "orders.id",
                "orders.name",
                "orders.description",
                "orders.price",
                "orders.start_date",
                "orders.end_date",
                "clients.company_name(client)",
                "dtp.id(stage_id)",
                "dtp.description(dtp_description)",
                "dtp.done",
                "order_stages.status"
            ], [
                "orders.id" => $id,
                "order_stages.stage_type" => "dtp"
            ]);

            if (!$order) {
                Utils::addErrorMessage("Nie znaleziono zlecenia.");
                App::getRouter()->redirectTo("orders_dtp");
                return;
            }

            App::getSmarty()->assign("order", $order);
            App::getSmarty()->display("OrderDetailsDtp.tpl");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: " . $e->getMessage());
            App::getRouter()->redirectTo("orders_dtp");
        }
    }

    // Toggle status etapu DTP
    public function action_order_toggle_dtp() {
        $stage_id = ParamUtils::getFromCleanURL(1, true, 'Brak ID etapu');
        $order_id = ParamUtils::getFromCleanURL(2, false);

        try {
            $dtp = App::getDB()->get("dtp", ["id", "done", "stage_id"], ["id" => $stage_id]);

            if ($dtp) {
                $newDone = $dtp["done"] ? 0 : 1;

                // Aktualizacja statusu w dtp
                App::getDB()->update("dtp", ["done" => $newDone], ["id" => $stage_id]);

                // Synchronizacja ze stage
                $newStatus = $newDone ? "skończone" : "w trakcie";
                App::getDB()->update("order_stages", ["status" => $newStatus], ["id" => $dtp["stage_id"]]);

                Utils::addInfoMessage("Status Studio DTP został zmieniony.");
                App::getRouter()->redirectTo("order_details_dtp/$order_id");
                return;
            } else {
                Utils::addErrorMessage("Nie znaleziono etapu DTP.");
            }

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: " . $e->getMessage());
        }

        App::getRouter()->redirectTo("orders_dtp");
    }
}
