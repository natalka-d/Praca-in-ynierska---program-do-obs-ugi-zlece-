{include file="header.tpl"}
<link rel="stylesheet" href="{$base_url}assets/css/orders-admin.css" />

<section class="admin-panel-wrapper">

    <div class="admin-header">
        <h2>📄 Szczegóły zlecenia (drukarz)</h2>
    </div>

    <!-- =======================
         KOMENTARZE NA GÓRZE
    ======================== -->

    {if $order.comments|@count > 0}
        <div style="
            background: rgba(255, 0, 0, 0.12);
            border-left: 4px solid rgba(255,0,0,0.6);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        ">
            <h2 style="margin-top:0;">Uwagi</h2>

            {foreach $order.comments as $c}
                <div style="
                    padding: 10px 0;
                    border-bottom: 1px solid rgba(0,0,0,0.1);
                ">
                    <strong>{$c.user_name}</strong>
                    <span style="color:#555;">({$c.created_at})</span>
                    <p style="margin:5px 0 0;">{$c.comment}</p>
                </div>
            {/foreach}
        </div>
    {/if}


    <!-- PANEL GŁÓWNY -->
    <div class="admin-panel">

        <!-- Nazwa zlecenia -->
        <h1 class="order-title">{$order.name}</h1>

        <!-- Arkusze offsetowe -->
        <h2>Arkusze offsetowe</h2>
        {if $order.worksheets|@count > 0}
            <form method="post" action="{$conf->action_root}order_save_sheets">
                <input type="hidden" name="id" value="{$order.id}">

                <div class="grid-wrapper">
                    {foreach $order.worksheets as $ws}

                        {if $ws.total_sheets > 0}
                            {assign var="percent" value=($ws.printed_sheets/$ws.total_sheets*100)}
                        {else}
                            {assign var="percent" value=0}
                        {/if}

                        {if $percent > 100}
                            {assign var="percent" value=100}
                        {/if}

                        {assign var="barColor" value="#2b6cb0"}
                        {if $percent >= 100}
                            {assign var="barColor" value="#38a169"}
                        {elseif $percent > 0}
                            {assign var="barColor" value="#ecc94b"}
                        {/if}

                        <section class="card stage-card">
                            <div class="stage-header">
                                <h3>{$ws.paper_type|default:"Nie określono"}</h3>
                                {if $ws.printed_sheets >= $ws.total_sheets && $ws.total_sheets > 0}
                                    <span class="status-badge status-done">✅ <span>Skończone</span></span>
                                {elseif $ws.printed_sheets > 0}
                                    <span class="status-badge status-progress">⏳ <span>W trakcie</span></span>
                                {else}
                                    <span class="status-badge status-zero">⌛ <span>Oczekuje</span></span>
                                {/if}
                            </div>

                            <div class="stage-summary">
                                <p><strong>Opis:</strong> {$ws.description|default:"Brak opisu"}</p>
                                <p><strong>Nakład:</strong> {$ws.circulation|default:0}</p>
                                <p><strong>Liczba arkuszy:</strong> {$ws.total_sheets|default:0}</p>
                                <p>
                                    <strong>Data zakończenia druku:</strong>
                                    <input type="date"
                                           name="print_finish_date[{$ws.id}]"
                                           value="{$ws.print_finish_date|date_format:'%Y-%m-%d'|default:''}"
                                           style="padding:4px;">
                                </p>
                                <p>
                                    <strong>Wydrukowane arkusze:</strong>
                                    <input type="number"
                                           name="printed_sheets[{$ws.id}]"
                                           value="{$ws.printed_sheets|default:0}"
                                           min="0"
                                           style="width:80px; padding:4px;">
                                </p>
                                <div style="background:#eee; border-radius:6px; height:12px; margin-top:8px;">
                                    <div style="width:{$percent}%; background:{$barColor}; height:12px; border-radius:6px;"></div>
                                </div>
                            </div>
                        </section>

                    {/foreach}
                </div>

                <button type="submit" class="button button-primary" style="margin-top:16px;">
                    💾 Zapisz arkusze
                </button>
            </form>
        {else}
            <p class="message warning">Brak arkuszy offsetowych.</p>
        {/if}


        <!-- Dane podstawowe -->
        <h2>Dane zlecenia</h2>
        <section class="card">
            <p><strong>Klient:</strong> {$order.client|default:"-"}</p>
            <p><strong>Data przyjęcia:</strong> {$order.start_date|default:"-"}</p>
            <p><strong>Data zakończenia:</strong> {$order.end_date|default:"-"}</p>
        </section>
        
        <div style="height:25px;"></div>
        
        <!-- Formularz dodawania komentarza -->
        <h2>Uwagi</h2>
        <form method="post" action="{$conf->action_root}add_order_comment" style="
            margin-bottom:30px;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        ">
            <input type="hidden" name="order_id" value="{$order.id}">

            <textarea name="comment" rows="3" style="
                width:100%;
                max-width:100%;
                padding:10px;
                border:1px solid #ccc;
                border-radius:6px;
                box-sizing:border-box;
                resize: vertical;
            " placeholder="Dodaj komentarz..."></textarea>

            <button type="submit" class="button button-primary" style="margin-top:16px;">
                    Dodaj komentarz
                </button>
        </form>

        <!-- Powrót -->
        <div class="back-link">
            <a href="{$conf->action_root}orders_printer" class="button">⬅ Powrót do listy</a>
        </div>

    </div> <!-- /.admin-panel -->

</section>

{include file="footer.tpl"}
