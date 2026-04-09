<?php
namespace app\controllers;

use core\App;
use core\Utils;
use core\ParamUtils;

class PrinterOrderCtrl extends BaseOrderCtrl {

    public function action_orders_printer() {
        $page = ParamUtils::getFromCleanURL(1, false);
        $page = ($page && ctype_digit($page)) ? intval($page) : 1;

        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            $total = App::getDB()->query("
                SELECT COUNT(DISTINCT o.id) as cnt
                FROM orders o
                INNER JOIN offset_print op ON o.id = op.order_id
                WHERE o.archived = 0
            ")->fetchColumn();

            $total_pages = ceil($total / $limit);
            if ($page > $total_pages && $total_pages > 0) {
                App::getRouter()->redirectTo("orders_printer/$total_pages");
                return;
            }

            $orders = App::getDB()->query("
                SELECT o.id, o.name, c.company_name AS client, o.price,
                       o.start_date AS date_received,
                       o.end_date   AS date_finished,
                       COALESCE(SUM(op.total_sheets), 0) AS total_sheets,
                       COALESCE(SUM(op.printed_sheets), 0) AS printed_sheets
                FROM orders o
                INNER JOIN offset_print op ON o.id = op.order_id
                LEFT JOIN clients c ON o.client_id = c.id
                WHERE o.archived = 0
                GROUP BY o.id
                ORDER BY o.id DESC
                LIMIT :limit OFFSET :offset
            ", [
                "limit" => $limit,
                "offset" => $offset
            ])->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($orders as &$order) {
                $totalSheets = max(0, (int)$order['total_sheets']);
                $printedSheets = max(0, (int)$order['printed_sheets']);
                $order['status'] = $totalSheets > 0 ? round(($printedSheets / $totalSheets) * 100) . "%" : "0%";
            }

            App::getSmarty()->assign("orders", $orders);
            App::getSmarty()->assign("current_page", $page);
            App::getSmarty()->assign("total_pages", $total_pages);
            App::getSmarty()->assign("total_orders", $total);

            App::getSmarty()->display("OrdersPrinter.tpl");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: ".$e->getMessage());
            App::getSmarty()->display("OrdersPrinter.tpl");
        }
    }

    public function action_order_details_printer() {
        $id = ParamUtils::getFromCleanURL(1, true, 'Błędne wywołanie aplikacji');

        try {
            $order = App::getDB()->get('orders', ['id', 'name', 'price', 'description', 'client_id', 'start_date', 'end_date'], [
                'id' => $id,
                'archived' => 0
            ]);
            if (!$order) {
                Utils::addErrorMessage("Nie znaleziono zlecenia o ID: $id");
                App::getRouter()->redirectTo('orders_printer');
                return;
            }

            $client = App::getDB()->get('clients', ['company_name'], ['id' => $order['client_id']]);
            $order['client'] = $client['company_name'] ?? '-';

            $worksheets = App::getDB()->select('offset_print', '*', ['order_id' => $id]);
            foreach ($worksheets as &$ws) {
                $ws['total_sheets'] = (int)$ws['total_sheets'];
                $ws['printed_sheets'] = (int)$ws['printed_sheets'];
            }
            $order['worksheets'] = $worksheets;

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

            App::getSmarty()->assign("order", $order);
            App::getSmarty()->display("OrderDetailsPrinter.tpl");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: " . $e->getMessage());
            App::getRouter()->redirectTo('orders_printer');
        }
    }

    public function action_order_save_sheets() {
        $orderId = ParamUtils::getFromRequest('id');
        $printedSheets = ParamUtils::getFromRequest('printed_sheets', []);
        $finishDates = ParamUtils::getFromRequest('print_finish_date', []); // <--- nowe pole

        try {
            // --- Zapis wydrukowanych arkuszy i dat zakończenia druku ---
            foreach ($printedSheets as $wsId => $value) {
                $value = max(0, intval($value));

                // sprawdź, czy arkusz ma określoną łączną liczbę arkuszy
                $totalSheets = App::getDB()->get("offset_print", "total_sheets", ["id" => $wsId]);
                $done = ($value >= $totalSheets && $totalSheets > 0) ? 1 : 0;

                // pobierz i przygotuj datę zakończenia druku
                $date = $finishDates[$wsId] ?? null;
                $date = !empty($date) ? $date : null;

                // aktualizacja rekordu
                App::getDB()->update("offset_print", [
                    "printed_sheets" => $value,
                    "done" => $done,
                    "print_finish_date" => $date
                ], [
                    "id" => $wsId
                ]);
            }

            // --- Aktualizacja statusów etapów ---
            $stages = App::getDB()->select("order_stages", "*", ["order_id" => $orderId]);

            foreach ($stages as $stage) {
                if ($stage["stage_type"] === "offset") {
                    $worksheets = App::getDB()->select("offset_print", ["total_sheets", "printed_sheets"], ["stage_id" => $stage["id"]]);

                    $total = 0;
                    $doneCount = 0;

                    foreach ($worksheets as $ws) {
                        $sheets = 0;

                        if (isset($ws['total_sheets'])) {
                            if (is_numeric($ws['total_sheets'])) {
                                $sheets = (int)$ws['total_sheets'];
                            } else {
                                // parsowanie np. "100 x 3"
                                if (preg_match('/(\d+)\s*x\s*(\d+)/i', $ws['total_sheets'], $matches)) {
                                    $sheets = intval($matches[1]) * intval($matches[2]);
                                }
                            }
                        }

                        $total += $sheets;
                        $doneCount += min($ws["printed_sheets"], $sheets);
                    }

                    $percent = $total > 0 ? round(($doneCount / $total) * 100) : 0;

                    if ($percent >= 100) $status = "skończone";
                    elseif ($percent > 0) $status = "w trakcie";
                    else $status = "oczekuje";

                    App::getDB()->update("order_stages", ["status" => $status], ["id" => $stage["id"]]);
                }
            }

            Utils::addInfoMessage("Arkusze, daty i statusy etapów zostały zaktualizowane.");
        } catch (\Exception $e) {
            Utils::addErrorMessage("Błąd podczas zapisywania: " . $e->getMessage());
        }

        App::getRouter()->redirectTo("order_details_printer/" . $orderId);
    }
    
    public function action_add_order_comment() {
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
