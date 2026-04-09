<?php

use core\App;
use core\Utils;

// Domyślna trasa
App::getRouter()->setDefaultRoute('login');

// Trasy logowania
Utils::addRoute('login', 'LoginCtrl');
Utils::addRoute('logout', 'LoginCtrl');

// Trasy zamówień - admin
Utils::addRoute('orders_admin', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('orders_archive', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('order_add', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('order_edit', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('order_update_admin', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('order_details_admin', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('order_add_stages', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('order_save_and_add_stages', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('order_save_stages', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('order_archive', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('order_restore', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('order_save_invoice', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('order_delete', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('order_finish', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('getOrdersForCalendar', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('add_order_comment_admin', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('user_list_ajax', 'AccountCtrl', ['admin']);
Utils::addRoute('user_update_ajax', 'AccountCtrl', ['admin']);

Utils::addRoute('account', 'AccountCtrl', ['admin', 'drukarz', 'cyfrowy', 'wide', 'binding', 'laser', 'dtp']);
Utils::addRoute('account_change_password', 'AccountCtrl', ['admin', 'drukarz', 'cyfrowy', 'wide', 'binding', 'laser', 'dtp']);
// Dodatkowe akcje admina
Utils::addRoute('offset_add', 'OffsetOrderCtrl', ['admin']);
Utils::addRoute('digital_add', 'DigitalOrderCtrl', ['admin']);
Utils::addRoute('wide_add', 'WideOrderCtrl', ['admin']);
Utils::addRoute('binding_add', 'BindingOrderCtrl', ['admin']);
Utils::addRoute('laser_add', 'LaserOrderCtrl', ['admin']);
Utils::addRoute('dtp_add', 'DtpOrderCtrl', ['admin']);
Utils::addRoute('client_lookup', 'AdminOrderCtrl', ['admin']);
Utils::addRoute('client_save', 'ClientCtrl', ['admin']);
Utils::addRoute('client_lookup', 'ClientCtrl', ['admin']);

// --- Nowe trasy dla kalendarza ---
Utils::addRoute('getNotes', 'AdminOrderCtrl', ['admin']);   // GET - pobranie notatek
Utils::addRoute('saveNote', 'AdminOrderCtrl', ['admin']);   // POST - zapis notatki

// Trasy zamówień - drukarz
Utils::addRoute('orders_printer', 'PrinterOrderCtrl', ['drukarz']);
Utils::addRoute('order_done', 'PrinterOrderCtrl', ['drukarz']);
Utils::addRoute('order_details_printer', 'PrinterOrderCtrl', ['drukarz']);
Utils::addRoute('order_save_sheets', 'PrinterOrderCtrl', ['drukarz']);
Utils::addRoute('add_order_comment', 'PrinterOrderCtrl', ['drukarz']);

// Trasy zamówień - druk cyfrowy
Utils::addRoute('orders_cyfrowy', 'DigitalOrderCtrl', ['cyfrowy']);
Utils::addRoute('order_details_cyfrowy', 'DigitalOrderCtrl', ['cyfrowy']);
Utils::addRoute('order_save_cyfrowy', 'DigitalOrderCtrl', ['cyfrowy']);
Utils::addRoute('order_toggle_cyfrowy', 'DigitalOrderCtrl', ['cyfrowy']);

// Trasy zamówień - druk wielkoformatowy
Utils::addRoute('orders_wide', 'WideOrderCtrl', ['wide']);
Utils::addRoute('order_details_wide', 'WideOrderCtrl', ['wide']);
Utils::addRoute('order_save_wide', 'WideOrderCtrl', ['wide']);
Utils::addRoute('order_toggle_wide', 'WideOrderCtrl', ['wide']);

// Trasy zamówień - introligatornia (Binding)
Utils::addRoute('orders_binding', 'BindingOrderCtrl', ['binding']);
Utils::addRoute('order_details_binding', 'BindingOrderCtrl', ['binding']);
Utils::addRoute('order_toggle_binding', 'BindingOrderCtrl', ['binding']);

// Trasy zamówień - laser
Utils::addRoute('orders_laser', 'LaserOrderCtrl', ['laser']);
Utils::addRoute('order_details_laser', 'LaserOrderCtrl', ['laser']);
Utils::addRoute('order_save_laser', 'LaserOrderCtrl', ['laser']);
Utils::addRoute('order_toggle_laser', 'LaserOrderCtrl', ['laser']);

// Trasy zamówień - Studio DTP
Utils::addRoute('orders_dtp', 'DtpOrderCtrl', ['dtp']);
Utils::addRoute('order_details_dtp', 'DtpOrderCtrl', ['dtp']);
Utils::addRoute('order_save_dtp', 'DtpOrderCtrl', ['dtp']);
Utils::addRoute('order_toggle_dtp', 'DtpOrderCtrl', ['dtp']);

Utils::addRoute('password_forgot', 'PasswordResetCtrl');  // bez ról, bo publiczne
Utils::addRoute('password_send_link', 'PasswordResetCtrl');
Utils::addRoute('password_new_form', 'PasswordResetCtrl');
Utils::addRoute('password_save_new', 'PasswordResetCtrl');