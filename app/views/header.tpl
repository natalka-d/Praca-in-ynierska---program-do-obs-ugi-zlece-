<!DOCTYPE html>
<html lang="pl">
<head>
    <title>MD-Projekt</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {** Ładowanie CSS z folderu assets **}
    <link rel="stylesheet" href="{$conf->server_url}/zlecenia/assets/css/orders-admin.css" />

  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Twój main.js -->
   <!--<script src="{$conf->server_url}/zlecenia/assets/js/main.js"></script>-->

    <style>
        /* ===== Header ===== */
        #header {
            background-color: #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            padding: 10px 30px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }

        #header_logo a {
            display: inline-block;
        }

        #header_logo .logo {
            max-width: 100%;
            height: auto;
            display: block;
        }
    </style>
</head>
<body>
<header id="header">
    <div id="header_logo">
        <a href="{$conf->app_url}" title="MD-Projekt">
            <img class="logo" 
                 src="{$conf->server_url}/zlecenia/assets/images/md-logo-3.png" 
                 alt="MD-Projekt" 
                 width="300" />
        </a>
    </div>
</header>
