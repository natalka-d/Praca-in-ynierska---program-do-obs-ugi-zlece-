<?php
namespace app\controllers;

use core\App;
use core\Utils;
use core\ParamUtils;

class LaserOrderCtrl {

    // Lista zleceń laser
    public function action_orders_laser() {
        $page = ParamUtils::getFromCleanURL(1, false);
        $page = ($page && ctype_digit($page)) ? intval($page) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            $total = App::getDB()->count("orders", [
                "[>]order_stages" => ["id" => "order_id"],
                "[>]laser" => ["order_stages.id" => "stage_id"]
            ], ["orders.id"], [
                "order_stages.stage_type" => "laser"
            ]);

            $total_pages = ceil($total / $limit);
            if ($page > $total_pages && $total_pages > 0) {
                App::getRouter()->redirectTo("orders_laser/$total_pages");
                return;
            }

            $orders = App::getDB()->select("orders", [
                "[>]clients" => ["client_id" => "id"],
                "[>]order_stages" => ["orders.id" => "order_id"],
                "[>]laser" => ["order_stages.id" => "stage_id"]
            ], [
                "orders.id",
                "orders.name",
                "clients.company_name(client)",
                "orders.start_date",
                "orders.end_date",
                "laser.id(stage_id)",
                "laser.done",
                "order_stages.status"
            ], [
                "order_stages.stage_type" => "laser",
                "ORDER" => ["orders.created_at" => "DESC"],
                "LIMIT" => [$offset, $limit]
            ]);

            App::getSmarty()->assign([
                "orders" => $orders,
                "current_page" => $page,
                "total_pages" => $total_pages,
                "total_orders" => $total
            ]);
            App::getSmarty()->display("OrdersLaser.tpl");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: ".$e->getMessage());
            App::getSmarty()->display("OrdersLaser.tpl");
        }
    }

    // Szczegóły zlecenia laser
    public function action_order_details_laser() {
        $id = ParamUtils::getFromCleanURL(1, true, 'Brak ID zlecenia');

        try {
            $order = App::getDB()->get("orders", [
                "[>]clients" => ["client_id" => "id"],
                "[>]order_stages" => ["orders.id" => "order_id"],
                "[>]laser" => ["order_stages.id" => "stage_id"]
            ], [
                "orders.id",
                "orders.name",
                "orders.description",
                "orders.price",
                "orders.start_date",
                "orders.end_date",
                "clients.company_name(client)",
                "laser.id(stage_id)",
                "laser.description(laser_description)", // <-- alias
                "laser.done",
                "order_stages.status"
            ], [
                "orders.id" => $id,
                "order_stages.stage_type" => "laser"
            ]);

            if (!$order) {
                Utils::addErrorMessage("Nie znaleziono zlecenia.");
                App::getRouter()->redirectTo("orders_laser");
                return;
            }

            App::getSmarty()->assign("order", $order);
            App::getSmarty()->display("OrderDetailsLaser.tpl");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: ".$e->getMessage());
            App::getRouter()->redirectTo("orders_laser");
        }
    }

    // Toggle status etapu laser
    public function action_order_toggle_laser() {
        $stage_id = ParamUtils::getFromCleanURL(1, true, 'Brak ID etapu');
        $order_id = ParamUtils::getFromCleanURL(2, false);

        try {
            $laser = App::getDB()->get("laser", ["id", "done", "stage_id"], ["id" => $stage_id]);

            if ($laser) {
                $newDone = $laser["done"] ? 0 : 1;

                // Aktualizacja statusu w laser
                App::getDB()->update("laser", ["done" => $newDone], ["id" => $stage_id]);

                // Synchronizacja ze stage
                $newStatus = $newDone ? "skończone" : "w trakcie";
                App::getDB()->update("order_stages", ["status" => $newStatus], ["id" => $laser["stage_id"]]);

                Utils::addInfoMessage("Status lasera został zmieniony.");

                App::getRouter()->redirectTo("order_details_laser/$order_id");
                return;
            } else {
                Utils::addErrorMessage("Nie znaleziono etapu lasera.");
            }

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: ".$e->getMessage());
        }

        App::getRouter()->redirectTo("orders_laser");
    }
}
