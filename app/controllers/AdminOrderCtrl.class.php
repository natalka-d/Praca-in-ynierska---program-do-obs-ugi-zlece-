<?php
namespace app\controllers;

use core\App;
use core\Utils;
use core\ParamUtils;
use app\forms\OrderStageForm;

file_put_contents(__DIR__.'/debug_autoload.txt', "AdminOrderCtrl loaded at ".date('Y-m-d H:i:s')."\n", FILE_APPEND);


class AdminOrderCtrl extends BaseOrderCtrl {

    private $stageNames = [
        'digital' => 'Druk cyfrowy',
        'offset'  => 'Druk offsetowy',
        'wide'    => 'Druk wielkoformatowy',
        'binding' => 'Introligatornia',
        'laser'   => 'Laser',
        'dtp'     => 'Studio DTP'
    ];

    // Lista zamówień admina z filtrowaniem
    public function action_orders_admin() {
    $page = ParamUtils::getFromCleanURL(1, false);
    $page = ($page && ctype_digit($page)) ? intval($page) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Pobranie filtrów
    $filters = [
        'status' => ParamUtils::getFromRequest('status'),
        'client' => ParamUtils::getFromRequest('client'),
        'name'   => ParamUtils::getFromRequest('name'),
        'start_date' => ParamUtils::getFromRequest('start_date'),
        'end_date' => ParamUtils::getFromRequest('end_date')
    ];

    // Parametry sortowania
    $sort = ParamUtils::getFromRequest('sort', false, 'created_at');
    $order = strtoupper(ParamUtils::getFromRequest('order', false, 'DESC'));
    if (!in_array($order, ['ASC','DESC'])) $order = 'DESC';

    $sortable_columns = ['name','client','price','start_date','end_date','created_at'];
    if (!in_array($sort, $sortable_columns)) $sort = 'created_at';

    // Przygotowanie linków do sortowania nagłówków (przełączanie ASC/DESC)
    $sort_links = [];
    foreach ($sortable_columns as $col) {
        $sort_links[$col] = ($sort === $col && $order === 'ASC') ? 'DESC' : 'ASC';
    }

    try {
        // Warunki SQL
        $where = ["orders.archived" => 0];

        if (!empty($filters['client'])) $where["clients.company_name[~]"] = $filters['client'];
        if (!empty($filters['name'])) $where["orders.name[~]"] = $filters['name'];
        if (!empty($filters['start_date'])) $where["orders.start_date[>=]"] = $filters['start_date'];
        if (!empty($filters['end_date'])) $where["orders.end_date[<=]"] = $filters['end_date'];

        // Liczba wszystkich rekordów po filtrach (bez statusu)
        $total = App::getDB()->count("orders", ["[>]clients"=>["client_id"=>"id"]], "orders.id", $where);
        $total_pages = ceil($total / $limit);

        // Pobranie zamówień z LIMIT i sort
        $orders = App::getDB()->select("orders", [
            "[>]clients" => ["client_id" => "id"]
        ], [
            "orders.id",
            "orders.name",
            "clients.company_name(client)",
            "orders.start_date",
            "orders.end_date",
            "orders.price",
            "orders.created_at"
        ], [
            "AND" => $where,
            "ORDER" => [$sort => $order],
            "LIMIT" => [$offset, $limit]
        ]);

        // Obliczanie dynamicznego statusu
        foreach ($orders as &$orderItem) {
            $stages = App::getDB()->select('order_stages', ['status'], ['order_id' => $orderItem['id']]);
            if (empty($stages)) {
                $orderItem['computed_status'] = 'oczekuje';
                continue;
            }
            $statuses = array_column($stages, 'status');
            if (!in_array('w trakcie', $statuses) && !in_array('oczekuje', $statuses)) {
                $orderItem['computed_status'] = 'skończone';
            } elseif (!in_array('w trakcie', $statuses) && !in_array('skończone', $statuses)) {
                $orderItem['computed_status'] = 'oczekuje';
            } else {
                $orderItem['computed_status'] = 'w trakcie';
            }
        }

        // Filtr statusu w PHP (bo status dynamiczny)
        if (!empty($filters['status'])) {
            $orders = array_filter($orders, fn($o) => $o['computed_status'] === $filters['status']);
        }

        App::getSmarty()->assign([
            "orders" => $orders,
            "current_page" => $page,
            "total_pages" => $total_pages,
            "total_orders" => $total,
            "filters" => $filters,
            "sort" => $sort,
            "order" => $order,
            "sort_links" => $sort_links
        ]);

        App::getSmarty()->display("OrdersAdmin.tpl");

    } catch (\PDOException $e) {
        Utils::addErrorMessage("Błąd bazy danych: " . $e->getMessage());
        App::getSmarty()->display("OrdersAdmin.tpl");
    }
}


    // Szczegóły zamówienia admin
    public function action_order_details_admin() {
        $id = ParamUtils::getFromCleanURL(1, true, 'Błędne wywołanie aplikacji');

        try {
            $order = App::getDB()->get('orders', [
                "[>]clients" => ["client_id" => "id"]
            ], [
                "orders.id",
                "orders.name",
                "orders.description",
                "orders.price",
                "orders.invoice_number",
                "orders.start_date",
                "orders.end_date",
                "clients.company_name(client_company)",
                "clients.contact_person",
                "clients.email",
                "clients.phone",
                "clients.address",
                "clients.nip"
            ], ["orders.id" => $id]);
            
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

            if (!$order) {
                Utils::addErrorMessage("Nie znaleziono zlecenia o ID: $id");
                App::getRouter()->redirectTo('orders_admin');
                return;
            }

            // Pobranie etapów
            $stages = App::getDB()->select('order_stages', '*', ['order_id' => $id]);
            foreach ($stages as &$stage) $stage = $this->loadStageDetails($stage);
            $order['stages'] = $stages;

            App::getSmarty()->assign("order", $order);
            App::getSmarty()->display("OrderDetailsAdmin.tpl");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: " . $e->getMessage());
            App::getRouter()->redirectTo('orders_admin');
        }
    }

    // Dodawanie zamówienia
    public function action_order_add() {
        $today = date('Y-m-d');
        $form = [
            'name' => '',
            'client' => '',
            'description' => '',
            'price' => '',
            'start_date' => $today,
            'end_date' => '',
            'description_printer' => ''
        ];

        App::getSmarty()->assign('form', $form);
        App::getSmarty()->assign('messages', App::getMessages()->getMessages());
        App::getSmarty()->display('OrderAdd.tpl');
    }

    // Zapis zamówienia i dodawanie etapów
    public function action_order_save_and_add_stages() {
        $name = ParamUtils::getFromRequest('name', true, 'Błędna nazwa');
        $clientInput = ParamUtils::getFromRequest('client', true, 'Błędny klient');
        $description = ParamUtils::getFromRequest('description');
        $price = ParamUtils::getFromRequest('price');
        $start_date = ParamUtils::getFromRequest('start_date', false, null);
        $end_date = ParamUtils::getFromRequest('end_date', false, null);

        $client_id = $this->resolveClientId($clientInput);

        try {
            App::getDB()->insert('orders', [
                'name' => $name,
                'client_id' => $client_id,
                'description' => $description,
                'price' => $price,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $order_id = App::getDB()->id();
            Utils::addInfoMessage('Zlecenie zostało dodane.');
            App::getRouter()->redirectTo("order_add_stages/$order_id");

        } catch (\PDOException $e) {
            Utils::addErrorMessage('Błąd zapisu zlecenia: ' . $e->getMessage());
            App::getRouter()->redirectTo('orders_admin');
        }
    }

    // Dodawanie etapów
    public function action_order_add_stages() {
        $orderId = ParamUtils::getFromCleanURL(1, true, 'Brak ID zamówienia');
        try {
            $order = App::getDB()->get('orders', [
                "[>]clients" => ["client_id" => "id"]
            ], [
                "orders.id",
                "orders.name",
                "orders.description",
                "orders.price",
                "orders.start_date",
                "orders.end_date",
                "clients.company_name(client_name)"
            ], ["orders.id" => $orderId]);

            if (!$order) {
                Utils::addErrorMessage("Nie znaleziono zamówienia o ID: $orderId");
                App::getRouter()->redirectTo('orders_admin');
                return;
            }

            App::getSmarty()->assign('order', $order);
            App::getSmarty()->display('OrderAddStages.tpl');

        } catch (\PDOException $e) {
            Utils::addErrorMessage('Błąd bazy danych: ' . $e->getMessage());
            App::getRouter()->redirectTo('orders_admin');
        }
    }

    public function action_order_save_stages() {
        $form = new OrderStageForm();
        $form->order_id = ParamUtils::getFromRequest('order_id', true, 'Brak ID zlecenia');
        $form->stages = $_POST['stages'] ?? [];

        // Debug — zapisze strukturę tego, co faktycznie przychodzi
        file_put_contents('debug_stages.txt', print_r($form->stages, true));

        if (!is_array($form->stages) || count($form->stages) === 0) {
            Utils::addErrorMessage('Nie wybrano żadnego etapu (formularz pusty).');
            App::getRouter()->redirectTo("order_add_stages/{$form->order_id}");
            return;
        }

        try {
            foreach ($form->stages as $stageType => $stageList) {
                if (!is_array($stageList)) continue;

                foreach ($stageList as $stage) {
                    $stage_type = $stage['stage_type'] ?? $stageType;

                    // Wstawienie do order_stages
                    App::getDB()->insert('order_stages', [
                        'order_id'    => $form->order_id,
                        'stage_type'  => $stage_type,
                        'description' => $stage['description'] ?? '',
                        'status'      => 'w trakcie'
                    ]);

                    $stage_id = App::getDB()->id();
                    if (!$stage_id) continue;

                    // Wstawienie do tabeli zależnej od typu
                    switch ($stage_type) {
                        case 'digital':
                            App::getDB()->insert('digital_print', [
                                'stage_id'    => $stage_id,
                                'copies'      => $stage['copies'] ?? 0,
                                'description' => $stage['description'] ?? '',
                                'done'        => 0
                            ]);
                            break;

                        case 'offset':
                            if (!empty($stage['worksheets']) && is_array($stage['worksheets'])) {
                                foreach ($stage['worksheets'] as $ws) {
                                    App::getDB()->insert('offset_print', [
                                        'order_id'       => $form->order_id,
                                        'stage_id'       => $stage_id,
                                        'circulation'    => $ws['circulation'] ?? 0,
                                        'total_sheets'   => $ws['total_sheets'] ?? '',
                                        'paper_type'     => $ws['paper_type'] ?? '',
                                        'description'    => $ws['description'] ?? '',
                                        'printed_sheets' => 0,
                                        'done'           => 0
                                    ]);
                                }
                            }
                            break;

                        case 'wide':
                            App::getDB()->insert('wide_print', [
                                'stage_id'    => $stage_id,
                                'description' => $stage['description'] ?? '',
                                'done'        => 0
                            ]);
                            break;

                        case 'binding':
                        case 'laser':
                        case 'dtp':
                            App::getDB()->insert($stage_type, [
                                'stage_id'    => $stage_id,
                                'description' => $stage['description'] ?? '',
                                'done'        => 0
                            ]);
                            break;
                        case 'subcontract': // ✅ dodaj zapis podzlecenia
                            App::getDB()->insert('subcontract', [
                                'stage_id'     => $stage_id,
                                'company_name' => $stage['company_name'] ?? '',
                                'contact'      => $stage['contact'] ?? '',
                                'description'  => $stage['description'] ?? '',
                                'done'         => 0
                            ]);
                            break;
                    }
                }
            }

            Utils::addInfoMessage('Etapy zostały zapisane.');
            App::getRouter()->redirectTo("order_details_admin/{$form->order_id}");

        } catch (\PDOException $e) {
            Utils::addErrorMessage('Błąd zapisu etapów: ' . $e->getMessage());
            App::getRouter()->redirectTo("order_add_stages/{$form->order_id}");
        }
    }

    private function loadStageDetails($stage) {
        switch ($stage['stage_type'] ?? '') {
            case 'offset':
                $worksheets = App::getDB()->select('offset_print', '*', ['stage_id' => $stage['id']]);
                $stage['worksheets'] = $worksheets;

                $totalSheets = 0;
                $printedSheets = 0;

                foreach ($worksheets as $ws) {
                    // parsowanie total_sheets
                    $sheets = 0;
                    if (isset($ws['total_sheets'])) {
                        if (is_numeric($ws['total_sheets'])) {
                            $sheets = (int)$ws['total_sheets'];
                        } else {
                            if (preg_match('/(\d+)\s*x\s*(\d+)/i', $ws['total_sheets'], $matches)) {
                                $sheets = intval($matches[1]) * intval($matches[2]);
                            }
                        }
                    }

                    $totalSheets += $sheets;
                    $printedSheets += min(intval($ws['printed_sheets']), $sheets);
                }

                $stage['total_sheets']   = $totalSheets;
                $stage['printed_sheets'] = $printedSheets;

                if ($totalSheets === 0) {
                    $stage['status'] = 'oczekuje';
                } elseif ($printedSheets >= $totalSheets) {
                    $stage['status'] = 'skończone';
                } else {
                    $stage['status'] = 'w trakcie';
                }
                break;

            case 'digital':
                $data = App::getDB()->get('digital_print', '*', ['stage_id' => $stage['id']]);
                $stage['status'] = (!empty($data['done']) && $data['done'] == 1) ? 'skończone' : 'w trakcie';
                break;

            case 'wide':
                $data = App::getDB()->get('wide_print', '*', ['stage_id' => $stage['id']]);
                $stage['status'] = (!empty($data['done']) && $data['done'] == 1) ? 'skończone' : 'w trakcie';
                break;
            case 'binding':
            case 'laser':
                $data = App::getDB()->get($stage['stage_type'], '*', ['stage_id' => $stage['id']]);
                $stage['status'] = (!empty($data['done']) && $data['done'] == 1) ? 'skończone' : 'w trakcie';
                break;
            case 'dtp':
                $data = App::getDB()->get('dtp', '*', ['stage_id' => $stage['id']]);
                $stage['status'] = (!empty($data['done']) && $data['done'] == 1) ? 'skończone' : 'w trakcie';
                break;
             case 'subcontract': // ✅ PODZLECENIE
                $data = App::getDB()->get('subcontract', '*', ['stage_id' => $stage['id']]);
                $stage['company_name'] = $data['company_name'] ?? '';
                $stage['contact'] = $data['contact'] ?? '';
                $stage['description'] = $data['description'] ?? '';
                break;
        }

        $stage['stage_name'] = $this->stageNames[$stage['stage_type']] ?? $stage['stage_type'];
        return $stage;
    }

    protected function resolveClientId($clientInput) {
        if (ctype_digit($clientInput)) return (int)$clientInput;

        $client = App::getDB()->get("clients", "id", ["company_name" => $clientInput]);
        if ($client) return $client;

        App::getDB()->insert("clients", ["company_name" => $clientInput]);
        return App::getDB()->id();
    }

    // Edycja zamówienia
    public function action_order_edit() {
        $id = ParamUtils::getFromCleanURL(1, true, 'Brak ID zlecenia');
        try {
            $order = App::getDB()->get("orders", ["[>]clients"=>["client_id"=>"id"]],
                ["orders.id","orders.name","orders.description","orders.price","orders.start_date","orders.end_date","clients.company_name(client)"],
                ["orders.id"=>$id]);
            if (!$order) {
                Utils::addErrorMessage("Nie znaleziono zlecenia o ID $id.");
                App::getRouter()->redirectTo("orders_admin");
                return;
            }

            // Pobranie etapów dla edycji
            $stages = App::getDB()->select('order_stages', '*', ['order_id' => $id]);
            foreach ($stages as &$stage) $stage = $this->loadStageDetails($stage);
            $order['stages'] = $stages;

            App::getSmarty()->assign("order",$order);
            App::getSmarty()->display("OrderEdit.tpl");
        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: ".$e->getMessage());
            App::getRouter()->redirectTo("orders_admin");
        }
    }

    public function action_order_update_admin() {
        $id          = ParamUtils::getFromRequest('id', true, 'Brak ID');
        $name        = ParamUtils::getFromRequest('name', true, 'Brak nazwy');
        $clientInput = ParamUtils::getFromRequest('client', true, 'Brak klienta');
        $description = ParamUtils::getFromRequest('description');
        $price       = ParamUtils::getFromRequest('price');
        $start_date  = ParamUtils::getFromRequest('start_date');
        $end_date    = ParamUtils::getFromRequest('end_date');
        $stages      = ParamUtils::getFromRequest('stages', []);

        try {
            $client_id = $this->resolveClientId($clientInput);

            // Aktualizacja zamówienia
            App::getDB()->update("orders", [
                "name"       => $name,
                "client_id"  => $client_id,
                "description"=> $description,
                "price"      => $price,
                "start_date" => $start_date,
                "end_date"   => $end_date
            ], ["id" => $id]);

            // Aktualizacja istniejących i dodawanie nowych etapów
            foreach ($stages as $stage) {
                // Istniejący etap
                if (!empty($stage['id'])) {
                    App::getDB()->update('order_stages', [
                        'description' => $stage['description'] ?? null
                    ], ['id' => $stage['id']]);

                    // Digital
                    if ($stage['stage_type'] === 'digital') {
                        App::getDB()->update('digital_print', [
                            'copies'      => intval($stage['copies'] ?? 0),
                            'description' => $stage['description'] ?? null
                        ], ['stage_id' => $stage['id']]);
                    }

                    // Offset
                    if ($stage['stage_type'] === 'offset' && !empty($stage['worksheets'])) {
                        foreach ($stage['worksheets'] as $ws) {
                            if (!empty($ws['id'])) {
                                // Aktualizacja istniejącego arkusza
                                App::getDB()->update('offset_print', [
                                    'circulation'  => intval($ws['circulation'] ?? 0),
                                    'total_sheets' => intval($ws['total_sheets'] ?? 0),
                                    'paper_type'   => $ws['paper_type'] ?? '',
                                    'description'  => $ws['description'] ?? ''
                                ], ['id' => $ws['id']]);
                            } else {
                                // Nowy arkusz
                                App::getDB()->insert('offset_print', [
                                    'order_id'      => $id,
                                    'stage_id'      => $stage['id'],
                                    'circulation'   => intval($ws['circulation'] ?? 0),
                                    'total_sheets'  => intval($ws['total_sheets'] ?? 0),
                                    'paper_type'    => $ws['paper_type'] ?? '',
                                    'description'   => $ws['description'] ?? '',
                                    'printed_sheets'=> 0,
                                    'done'          => 0
                                ]);
                            }
                        }
                    }

                    // Inne typy
                    if (in_array($stage['stage_type'], ['wide','binding','laser','dtp'])) {
                        App::getDB()->update($stage['stage_type'], [
                            'description' => $stage['description'] ?? null
                        ], ['stage_id' => $stage['id']]);
                    }

                } else {
                    // Dodawanie nowego etapu
                    $stageType = $stage['stage_type'] ?? '';
                    if (empty($stageType)) continue;

                    // Wstawienie etapu
                    App::getDB()->insert('order_stages', [
                        'order_id'   => $id,
                        'stage_type' => $stageType,
                        'description'=> $stage['description'] ?? null,
                    ]);

                    $newStageId = App::getDB()->id();
                    if (!$newStageId) continue;

                    // Digital
                    if ($stageType === 'digital') {
                        App::getDB()->insert('digital_print', [
                            'stage_id'    => $newStageId,
                            'copies'      => intval($stage['copies'] ?? 0),
                            'description' => $stage['description'] ?? null,
                            'done'        => 0
                        ]);
                    }

                    // Offset
                    if ($stageType === 'offset' && !empty($stage['worksheets'])) {
                        foreach ($stage['worksheets'] as $ws) {
                            App::getDB()->insert('offset_print', [
                                'order_id'      => $id,
                                'stage_id'      => $newStageId,
                                'circulation'   => intval($ws['circulation'] ?? 0),
                                'total_sheets'  => intval($ws['total_sheets'] ?? 0),
                                'paper_type'    => $ws['paper_type'] ?? '',
                                'description'   => $ws['description'] ?? '',
                                'printed_sheets'=> 0,
                                'done'          => 0
                            ]);
                        }
                    }

                    // Inne typy
                    if (in_array($stageType, ['wide','binding','laser','dtp'])) {
                        App::getDB()->insert($stageType, [
                            'stage_id'    => $newStageId,
                            'description' => $stage['description'] ?? null,
                            'done'        => 0
                        ]);
                    }
                }
            }

            Utils::addInfoMessage("Zlecenie i etapy zostały zaktualizowane.");
            App::getRouter()->redirectTo("order_details_admin/$id");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd zapisu: " . $e->getMessage());
            App::getRouter()->redirectTo("order_edit/$id");
        }
    }

    // Archiwizacja
    public function action_order_archive() {
        $id = ParamUtils::getFromCleanURL(1,true,'Brak ID zlecenia');
        try {
            App::getDB()->update('orders',['archived'=>1],['id'=>$id]);
            Utils::addInfoMessage("Zlecenie zostało przeniesione do archiwum.");
        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd archiwizacji zlecenia: ".$e->getMessage());
        }
        App::getRouter()->redirectTo('orders_admin');
    }

    public function action_order_restore() {
        $id = ParamUtils::getFromCleanURL(1,true,'Brak ID zlecenia');
        try {
            App::getDB()->update('orders',['archived'=>0],['id'=>$id]);
            Utils::addInfoMessage("Zlecenie zostało przywrócone do aktywnych.");
        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd przywracania zlecenia: ".$e->getMessage());
        }
        App::getRouter()->redirectTo('orders_archive');
    }

    // Lista zleceń w archiwum
    public function action_orders_archive() {
        $page = ParamUtils::getFromCleanURL(1,false);
        $page = ($page && ctype_digit($page)) ? intval($page) : 1;
        $limit = 10;
        $offset = ($page-1)*$limit;

        try {
            $total = App::getDB()->count('orders',['archived'=>1]);
            $total_pages = ceil($total/$limit);

            $orders = App::getDB()->select('orders',["[>]clients"=>["client_id"=>"id"]],[
                "orders.id",
                "orders.name",
                "clients.company_name(client)",
                "orders.price",
                "orders.start_date",
                "orders.end_date",
            ], [
                "AND"=>["orders.archived"=>1],
                "ORDER"=>["orders.created_at"=>"DESC"],
                "LIMIT"=>[$offset,$limit]
            ]);

            App::getSmarty()->assign("orders",$orders);
            App::getSmarty()->assign("current_page",$page);
            App::getSmarty()->assign("total_pages",$total_pages);
            App::getSmarty()->assign("total_orders",$total);
            App::getSmarty()->display("OrdersArchive.tpl");

        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd bazy danych: ".$e->getMessage());
            App::getSmarty()->display("OrdersArchive.tpl");
        }
    }
    // Zapis numeru faktury
    public function action_order_save_invoice() {
        $order_id = ParamUtils::getFromRequest('order_id', true, 'Brak ID zlecenia');
        $invoice_number = ParamUtils::getFromRequest('invoice_number', false, '');

        try {
            App::getDB()->update('orders', ['invoice_number' => $invoice_number], ['id' => $order_id]);
            Utils::addInfoMessage('Numer faktury zapisany.');
        } catch (\PDOException $e) {
            Utils::addErrorMessage('Błąd zapisu faktury: ' . $e->getMessage());
        }

        App::getRouter()->redirectTo("order_details_admin/$order_id");
    }
    // Usuwanie zamówienia
    // Usuwanie zamówienia i powiązanych etapów
    public function action_order_delete() {
        $id = ParamUtils::getFromCleanURL(1, true, 'Brak ID zlecenia');

        try {
            // Pobierz wszystkie etapy jako tablice
            $stages = App::getDB()->select('order_stages', ['id', 'stage_type'], ['order_id' => $id]);

            foreach ($stages as $stage) {
                $stageId = $stage['id'];
                $type = $stage['stage_type'] ?? '';

                // Usuń powiązane tabele w zależności od typu
                switch ($type) {
                    case 'digital':
                        App::getDB()->delete('digital_print', ['stage_id' => $stageId]);
                        break;
                    case 'offset':
                        App::getDB()->delete('offset_print', ['stage_id' => $stageId]);
                        break;
                    case 'wide':
                        App::getDB()->delete('wide_print', ['stage_id' => $stageId]);
                        break;
                    case 'binding':
                        App::getDB()->delete('binding', ['stage_id' => $stageId]);
                        break;
                    case 'laser':
                        App::getDB()->delete('laser', ['stage_id' => $stageId]);
                        break;
                    case 'dtp': 
                        App::getDB()->delete('dtp', ['stage_id' => $stageId]); 
                        break;
                    case 'subcontract': // ✅ PODZLECENIE
                        App::getDB()->delete('subcontract', ['stage_id' => $stageId]);
                        break;
                }
            }

            // Usuń same etapy
            App::getDB()->delete('order_stages', ['order_id' => $id]);

            // Usuń zamówienie
            App::getDB()->delete('orders', ['id' => $id]);

            Utils::addInfoMessage("Zlecenie zostało usunięte.");
        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd przy usuwaniu zlecenia: " . $e->getMessage());
        }

        App::getRouter()->redirectTo('orders_admin');
    }
        // Zakończenie zlecenia (ręczne przez admina)
    public function action_order_finish() {
        $id = ParamUtils::getFromCleanURL(1, true, 'Brak ID zlecenia');

        try {
            // 1️⃣ Ustaw datę zakończenia na dzisiejszy dzień
            $today = date('Y-m-d');
            App::getDB()->update('orders', [
                'end_date' => $today
            ], ['id' => $id]);

            // 2️⃣ Zmień status wszystkich etapów na skończone
            $stages = App::getDB()->select('order_stages', ['id', 'stage_type'], ['order_id' => $id]);
            foreach ($stages as $stage) {
                App::getDB()->update('order_stages', ['status' => 'skończone'], ['id' => $stage['id']]);

                switch ($stage['stage_type']) {
                    case 'digital':
                        App::getDB()->update('digital_print', ['done' => 1], ['stage_id' => $stage['id']]);
                        break;
                    case 'offset':
                        App::getDB()->update('offset_print', ['done' => 1], ['stage_id' => $stage['id']]);
                        break;
                    case 'wide':
                        App::getDB()->update('wide_print', ['done' => 1], ['stage_id' => $stage['id']]);
                        break;
                    case 'binding':
                        App::getDB()->update('binding', ['done' => 1], ['stage_id' => $stage['id']]);
                        break;
                    case 'laser':
                        App::getDB()->update('laser', ['done' => 1], ['stage_id' => $stage['id']]);
                        break;
                    case 'dtp':
                        App::getDB()->update('dtp', ['done' => 1], ['stage_id' => $stage['id']]);
                        break;
                    case 'subcontract': // ✅ PODZLECENIE
                        App::getDB()->update('subcontract', ['done' => 1], ['stage_id' => $stage['id']]);
                        break;
                }
            }

            // 3️⃣ (Opcjonalnie) Archiwizuj po zakończeniu
            // App::getDB()->update('orders', ['archived' => 1], ['id' => $id]);

            Utils::addInfoMessage("Zlecenie zostało oznaczone jako zakończone.");
        } catch (\PDOException $e) {
            Utils::addErrorMessage("Błąd przy zakończaniu zlecenia: " . $e->getMessage());
        }

        App::getRouter()->redirectTo('orders_admin');
    }
    public function action_saveNote() {
        // Debug: zapisz POST do pliku w katalogu kontrolera
        file_put_contents(__DIR__.'/debug_note.txt', print_r($_POST, true), FILE_APPEND);

        //$this->checkAdmin(); // zabezpieczenie tylko dla admina

        $date = $_POST['date'] ?? null;
        $note = $_POST['note'] ?? null;

        header('Content-Type: application/json');

        if(!$date || !$note) {
            echo json_encode(['success' => false, 'message' => 'Brak daty lub notatki']);
            exit;
        }

        try {
            App::getDB()->insert("admin_calendar_notes", [
                'note_date' => $date,
                'note_text' => $note
            ]);

            $id = App::getDB()->id(); // pobranie ostatniego ID
            echo json_encode(['success' => true, 'id' => $id]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function action_getNotes() {
        header('Content-Type: application/json');
        try {
            $notes = App::getDB()->select("admin_calendar_notes", "*");
            echo json_encode($notes);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    public function action_getOrdersForCalendar() {
        if (!isset($_SESSION['user'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Brak dostępu']);
            exit;
        }

        // 🔽 pobranie filtra statusu z requestu
        $statusFilter = ParamUtils::getFromRequest('status');

        try {
            // 🔽 pobranie zleceń (bez statusu – liczony dynamicznie)
            $orders = App::getDB()->select("orders", [
                "[>]clients" => ["client_id" => "id"]
            ], [
                "orders.id",
                "orders.name",
                "orders.archived",
                "clients.company_name(client)",
                "orders.start_date",
                "orders.end_date"
            ], [
                "orders.archived" => 0
            ]);

            // 🔽 obliczanie dynamicznego statusu (TAK SAMO jak w tabeli)
            foreach ($orders as &$order) {

                $stages = App::getDB()->select(
                    'order_stages',
                    ['status'],
                    ['order_id' => $order['id']]
                );

                if (empty($stages)) {
                    $order['computed_status'] = 'oczekuje';
                    continue;
                }

                $statuses = array_column($stages, 'status');

                if (!in_array('w trakcie', $statuses) && !in_array('oczekuje', $statuses)) {
                    $order['computed_status'] = 'skończone';
                } elseif (!in_array('w trakcie', $statuses) && !in_array('skończone', $statuses)) {
                    $order['computed_status'] = 'oczekuje';
                } else {
                    $order['computed_status'] = 'w trakcie';
                }
            }
            unset($order);

            // 🔽 filtr statusu (PHP, bo status dynamiczny)
            if (!empty($statusFilter)) {
                $orders = array_filter($orders, function ($o) use ($statusFilter) {
                    return $o['computed_status'] === $statusFilter;
                });
            }

            // 🔽 mapowanie kolorów wg statusu
            $colorMap = [
                'w trakcie' => '#ffc107',
                'skończone' => '#28a745',
                'oczekuje'  => '#dc3545'
            ];

            // 🔽 budowanie eventów dla FullCalendar
            $events = [];

            foreach ($orders as $o) {

                if (!$o['start_date'] || $o['start_date'] === '0000-00-00') {
                    continue;
                }

                $title = $o['name'];
                if (!empty($o['client'])) {
                    $title .= ' - ' . $o['client'];
                }

                $event = [
                    'id'    => $o['id'],
                    'title' => $title,
                    'start' => $o['start_date'],
                    'color' => $colorMap[$o['computed_status']] ?? '#007bff',
                    'url'   => $this->conf->action_root . 'order_details_admin/' . $o['id']
                ];

                if ($o['end_date'] && $o['end_date'] !== '0000-00-00') {
                    $event['end'] = $o['end_date'];
                }

                $events[] = $event;
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array_values($events));

        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd bazy: ' . $e->getMessage()]);
        }
        exit;
    }

    public function action_add_order_comment_admin() {
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
            header("Location: https://mdprojekt.eu/zlecenia/public/order_details_admin/$orderId?msg=comment_added");
            exit;
        } catch (\Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
