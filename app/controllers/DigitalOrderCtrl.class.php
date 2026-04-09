<?php
namespace app\controllers;

use core\App;
use core\Utils;
use core\ParamUtils;

class DigitalOrderCtrl {

    public function action_orders_cyfrowy() {
        $page = ParamUtils::getFromCleanURL(1, false);
        $page = ($page && ctype_digit($page)) ? intval($page) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            // Liczymy zlecenia cyfrowe
            $total = App::getDB()->count("orders", [
                "[>]order_stages" => ["id" => "order_id"]
            ], ["orders.id"], [
                "order_stages.stage_type" => "digital",
                "orders.archived" => 0
            ]);
            $total_pages = ceil($total / $limit);
            if ($page > $total_pages && $total_pages > 0) {
                App::getRouter()->redirectTo("orders_cyfrowy/$total_pages");
                return;
            }

            // Pobranie zleceń cyfrowych
            $orders = App::getDB()->select("orders", [
                "[>]clients" => ["client_id" => "id"],
                "[>]order_stages" => ["orders.id" => "order_id"]
            ], [
                "orders.id",
                "orders.name",
                "clients.company_name(client)",
                "orders.start_date",
                "orders.end_date",
                "order_stages.status"
            ], [
                "order_stages.stage_type" => "digital",
                "orders.archived" => 0,
                "GROUP" => "orders.id",
                "ORDER" => ["orders.created_at" => "DESC"],
                "LIMIT" => [$offset, $limit]
            ]);
            
            // Pobranie komentarzy
            $comments = App::getDB()->select('order_comments', [
                '[>]users' => ['user_id' => 'id']
            ], [
                'order_comments.id',
                'order_comments.comment',
                'order_comments.created_at',
                'users.username(user_name)'
            ], [
                'order_id' => $id,
                'ORDER' => ['order_comments.id' => 'ASC']
            ]);

            $order['comments'] = $comments;

            App::getSmarty()->assign([
                "orders" => $orders,
                "current_page" => $page,
                "total_pages" => $total_pages,
                "total_orders" => $total
            ]);
            App::getSmarty()->display("OrdersDigital.tpl");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: ".$e->getMessage());
            App::getSmarty()->display("OrdersDigital.tpl");
        }
    }

    public function action_order_details_cyfrowy() {
        $id = ParamUtils::getFromCleanURL(1, true, 'Brak ID zlecenia');

        try {
            // Pobranie zlecenia i klienta
            $order = App::getDB()->get("orders", [
                "[>]clients" => ["client_id" => "id"],
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
                App::getRouter()->redirectTo("orders_cyfrowy");
                return;
            }

            // Pobranie etapów cyfrowych i ich rekordów
            $digitalStages = App::getDB()->select("order_stages", "*", [
                "order_id" => $id,
                "stage_type" => "digital"
            ]);

            foreach ($digitalStages as &$stage) {
                $stage['details'] = App::getDB()->select("digital_print", [
                    "id",
                    "description",
                    "done",
                    "copies"
                ], ["stage_id" => $stage['id']]);
            }

            App::getSmarty()->assign("order", $order);
            App::getSmarty()->assign("stages", $digitalStages);
            App::getSmarty()->display("OrderDetailsDigital.tpl");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: ".$e->getMessage());
            App::getRouter()->redirectTo("orders_cyfrowy");
        }
    }

    public function action_order_toggle_cyfrowy() {
        $id = ParamUtils::getFromCleanURL(1, true, 'Brak ID etapu');

        try {
            // Pobranie rekordu digital_print
            $stageDetail = App::getDB()->get("digital_print", ["id", "done", "stage_id"], ["id" => $id]);

            if ($stageDetail) {
                $newStatus = $stageDetail["done"] ? 0 : 1;

                // Aktualizacja statusu w digital_print
                App::getDB()->update("digital_print", ["done" => $newStatus], ["id" => $id]);

                // Jeżeli etap został oznaczony jako skończony, sprawdzamy czy wszystkie detale etapu są skończone
                if ($newStatus == 1) {
                    $allDone = App::getDB()->count("digital_print", [
                        "stage_id" => $stageDetail["stage_id"],
                        "done" => 0
                    ]) === 0; // jeśli brak nieukończonych rekordów -> wszystkie done

                    if ($allDone) {
                        // Aktualizacja statusu etapu w order_stages
                        App::getDB()->update("order_stages", ["status" => "skończone"], ["id" => $stageDetail["stage_id"]]);
                    }
                } else {
                    // Cofamy status etapu, jeśli zmieniono done na 0
                    App::getDB()->update("order_stages", ["status" => "w trakcie"], ["id" => $stageDetail["stage_id"]]);
                }

                Utils::addInfoMessage("Status druku cyfrowego został zmieniony.");

                // Pobranie order_id powiązanego z etapem
                $order_id = App::getDB()->get("order_stages", "order_id", ["id" => $stageDetail["stage_id"]]);

                App::getRouter()->redirectTo("order_details_cyfrowy/".$order_id);
                return;
            } else {
                Utils::addErrorMessage("Nie znaleziono etapu druku cyfrowego.");
            }
        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: " . $e->getMessage());
        }

        App::getRouter()->redirectTo("orders_cyfrowy");
    }
    public function action_add_order_comment_digital() {
        try {
            $orderId = ParamUtils::getFromRequest('order_id');
            $comment = ParamUtils::getFromRequest('comment');

            if (!$orderId || empty(trim($comment))) {
                die("Invalid input: Missing order_id or comment.");
            }

            $orderId = intval($orderId);
            $comment = trim($comment);

            // Manual insert to bypass Medoo (this part works)
            $pdo = App::getDB()->pdo;
            $stmt = $pdo->prepare("INSERT INTO order_comments (order_id, comment) VALUES (?, ?)");
            $stmt->execute([$orderId, $comment]);

            // Redirect back to order details with success message
            header("Location: https://mdprojekt.eu/zlecenia/public/order_details_printer/$orderId?msg=comment_added");
            exit;
        } catch (\Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
