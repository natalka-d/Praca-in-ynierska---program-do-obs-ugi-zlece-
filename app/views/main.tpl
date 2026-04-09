<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>{$title|default:"MD-Projekt"}</title>
    <link rel="stylesheet" href="{$conf->app_url}/css/printer.css">
</head>
<body>
    {include file="header.tpl"}

    <main>
        {$content}
    </main>

    {include file="footer.tpl"}
</body>
</html>
