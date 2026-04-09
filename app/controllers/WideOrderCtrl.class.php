<?php
namespace app\controllers;

use core\App;
use core\Utils;
use core\ParamUtils;

class WideOrderCtrl {

    // Strona główna – lista zleceń wielkoformatowych
    public function action_orders_wide() {
        $page = ParamUtils::getFromCleanURL(1, false);
        $page = ($page && ctype_digit($page)) ? intval($page) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            // Pobranie zamówień z etapami wide
            $orders = App::getDB()->select("orders", [
                "[>]clients" => ["client_id" => "id"],
                "[>]order_stages" => ["orders.id" => "order_id"]
            ], [
                "orders.id",
                "orders.name",
                "clients.company_name(client)",
                "orders.price",
                "orders.start_date",
                "orders.end_date",
            ], [
                "order_stages.stage_type" => "wide",
                "orders.archived" => 0,
                "GROUP" => "orders.id",
                "ORDER" => ["orders.start_date" => "DESC"],
                "LIMIT" => [$offset, $limit]
            ]);

            $total = App::getDB()->count("orders", [
                "[>]order_stages" => ["id" => "order_id"]
            ], "orders.id", [
                "order_stages.stage_type" => "wide",
                "orders.archived" => 0
            ]);

            $total_pages = ceil($total / $limit);

            // Sprawdzenie statusu zakończenia etapów
            foreach ($orders as &$order) {
                $stageIds = App::getDB()->select("order_stages", "id", [
                    "order_id" => $order["id"],
                    "stage_type" => "wide"
                ]);

                if (!empty($stageIds)) {
                    $doneStages = App::getDB()->select("wide_print", "done", [
                        "stage_id" => $stageIds
                    ]);

                    $order['all_done'] = !empty($doneStages) && !in_array(0, $doneStages);
                } else {
                    $order['all_done'] = false;
                }
            }

            App::getSmarty()->assign([
                "orders" => $orders,
                "current_page" => $page,
                "total_pages" => $total_pages,
                "total_orders" => $total
            ]);
            App::getSmarty()->display("OrdersWide.tpl");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: " . $e->getMessage());
            App::getSmarty()->display("OrdersWide.tpl");
        }
    }

    // Szczegóły zamówienia wielkoformatowego
    public function action_order_details_wide() {
        $id = ParamUtils::getFromCleanURL(1, true, 'Brak ID zlecenia');

        try {
            // Pobranie zamówienia i klienta
            $order = App::getDB()->get("orders", [
                "[>]clients" => ["client_id" => "id"]
            ], [
                "orders.id",
                "orders.name",
                "orders.description",
                "orders.price",
                "orders.start_date",
                "orders.end_date",
                "clients.company_name(client)"
            ], [
                "orders.id" => $id,
                "orders.archived" => 0
            ]);

            if (!$order) {
                Utils::addErrorMessage("Nie znaleziono zlecenia.");
                App::getRouter()->redirectTo("orders_wide");
                return;
            }

            // Pobranie etapów typu 'wide'
            $stages = App::getDB()->select("order_stages", "*", [
                "order_id" => $id,
                "stage_type" => "wide"
            ]);

            $all_done = true;

            foreach ($stages as &$stage) {
                // Pobranie detali wide_print dla każdego etapu
                $stage['details'] = App::getDB()->select("wide_print", "*", [
                    "stage_id" => $stage['id']
                ]);

                foreach ($stage['details'] as $d) {
                    if ($d['done'] == 0) {
                        $all_done = false;
                    }
                }
            }

            $order['all_done'] = $all_done;

            App::getSmarty()->assign("order", $order);
            App::getSmarty()->assign("stages", $stages);
            App::getSmarty()->display("OrderDetailsWide.tpl");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: " . $e->getMessage());
            App::getRouter()->redirectTo("orders_wide");
        }
    }

    // Zmiana statusu etapu
    public function action_order_toggle_wide() {
        $id = ParamUtils::getFromCleanURL(1, true, 'Brak ID etapu');

        try {
            $stage = App::getDB()->get("wide_print", ["id", "done", "stage_id"], ["id" => $id]);

            if ($stage) {
                $newStatus = $stage["done"] ? 0 : 1;

                // Aktualizacja wide_print
                App::getDB()->update("wide_print", [
                    "done" => $newStatus
                ], ["id" => $id]);

                // Aktualizacja statusu w tabeli order_stages
                App::getDB()->update("order_stages", [
                    "status" => $newStatus ? "skończone" : "w trakcie"
                ], ["id" => $stage["stage_id"]]);

                Utils::addInfoMessage("Status etapu został zmieniony.");

                // Pobranie order_id i przekierowanie
                $order_id = App::getDB()->get("order_stages", "order_id", ["id" => $stage["stage_id"]]);
                App::getRouter()->redirectTo("order_details_wide/".$order_id);
                return;

            } else {
                Utils::addErrorMessage("Nie znaleziono etapu druku wielkoformatowego.");
            }

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: " . $e->getMessage());
        }

        App::getRouter()->redirectTo("orders_wide");
    }

}
