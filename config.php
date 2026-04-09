<?php
$conf->debug = false; # set true during development and use in your code (for instance check if true to send additional message)

# ---- Webapp location
$conf->server_name = 'mdprojekt.eu';   # server address and port
$conf->protocol = 'https';           # http or https
$conf->app_root = '/zlecenia/public';   # project subfolder in domain (relative to main domain)

# ---- Database config - values required by Medoo
$conf->db_type = 'mysql';
$conf->db_server = 'mdprojszlecenia.mysql.db';
$conf->db_name = 'mdprojszlecenia';
$conf->db_user = 'mdprojszlecenia';
$conf->db_pass = 'Zlecenia123';
$conf->db_charset = 'utf8';

# ---- Database config - optional values
$conf->db_port = '3306';
#$conf->db_prefix = '';
$conf->db_option = [ PDO::ATTR_CASE => PDO::CASE_NATURAL, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ];

