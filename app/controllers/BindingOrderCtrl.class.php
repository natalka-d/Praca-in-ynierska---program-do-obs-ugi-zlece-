<?php
namespace app\controllers;

use core\App;
use core\Utils;
use core\ParamUtils;

class BindingOrderCtrl {

    // Lista zleceń dla introligatorni
    public function action_orders_binding() {
        $page = ParamUtils::getFromCleanURL(1, false);
        $page = ($page && ctype_digit($page)) ? intval($page) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            $total = App::getDB()->count("orders", [
                "[>]order_stages" => ["id" => "order_id"],
                "[>]binding" => ["order_stages.id" => "stage_id"]
            ], ["orders.id"], [
                "order_stages.stage_type" => "binding"
            ]);

            $total_pages = ceil($total / $limit);
            if ($page > $total_pages && $total_pages > 0) {
                App::getRouter()->redirectTo("orders_binding/$total_pages");
                return;
            }

            $orders = App::getDB()->select("orders", [
                "[>]clients" => ["client_id" => "id"],
                "[>]order_stages" => ["orders.id" => "order_id"],
                "[>]binding" => ["order_stages.id" => "stage_id"]
            ], [
                "orders.id",
                "orders.name",
                "clients.company_name(client)",
                "orders.price",
                "orders.start_date",
                "orders.end_date",
                "binding.id(stage_id)",
                "binding.description",
                "binding.done",
                "order_stages.status"
            ], [
                "order_stages.stage_type" => "binding",
                "ORDER" => ["orders.created_at" => "DESC"],
                "LIMIT" => [$offset, $limit]
            ]);

            App::getSmarty()->assign([
                "orders" => $orders,
                "current_page" => $page,
                "total_pages" => $total_pages,
                "total_orders" => $total
            ]);
            App::getSmarty()->display("OrdersBinding.tpl");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: ".$e->getMessage());
            App::getSmarty()->display("OrdersBinding.tpl");
        }
    }

    // Szczegóły zlecenia introligatorni
    public function action_order_details_binding() {
        $id = ParamUtils::getFromCleanURL(1, true, 'Brak ID zlecenia');

        try {
            $order = App::getDB()->get("orders", [
                "[>]clients" => ["client_id" => "id"],
                "[>]order_stages" => ["orders.id" => "order_id"],
                "[>]binding" => ["order_stages.id" => "stage_id"]
            ], [
                "orders.id",
                "orders.name",
                "orders.description",
                "orders.price",
                "orders.start_date",
                "orders.end_date",
                "clients.company_name(client)",
                "binding.id(stage_id)",
                "binding.description",
                "binding.done",
                "order_stages.status"
            ], [
                "orders.id" => $id,
                "order_stages.stage_type" => "binding"
            ]);

            if (!$order) {
                Utils::addErrorMessage("Nie znaleziono zlecenia.");
                App::getRouter()->redirectTo("orders_binding");
                return;
            }

            App::getSmarty()->assign("order", $order);
            App::getSmarty()->display("OrderDetailsBinding.tpl");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: ".$e->getMessage());
            App::getRouter()->redirectTo("orders_binding");
        }
    }

    // Toggle status etapu (binding)
    public function action_order_toggle_binding() {
        $stage_id = ParamUtils::getFromCleanURL(1, true, 'Brak ID etapu');
        $order_id = ParamUtils::getFromCleanURL(2, false);

        try {
            $binding = App::getDB()->get("binding", ["id", "done", "stage_id"], ["id" => $stage_id]);

            if ($binding) {
                $newDone = $binding["done"] ? 0 : 1;

                // Aktualizacja statusu w binding
                App::getDB()->update("binding", ["done" => $newDone], ["id" => $stage_id]);

                // Synchronizacja ze stage
                $newStatus = $newDone ? "skończone" : "w trakcie";
                App::getDB()->update("order_stages", ["status" => $newStatus], ["id" => $binding["stage_id"]]);

                Utils::addInfoMessage("Status introligatorni został zmieniony.");

                App::getRouter()->redirectTo("order_details_binding/$order_id");
                return;
            } else {
                Utils::addErrorMessage("Nie znaleziono etapu introligatorni.");
            }

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: ".$e->getMessage());
        }

        App::getRouter()->redirectTo("orders_binding");
    }
}
