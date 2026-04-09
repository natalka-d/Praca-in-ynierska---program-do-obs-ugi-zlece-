{include file="header.tpl"}
<link rel="stylesheet" href="{$base_url}assets/css/orders-admin.css" />

<section class="admin-panel-wrapper">

    <div class="admin-header">
        <h2>📄 Szczegóły zlecenia (druk cyfrowy)</h2>
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

    <div class="admin-panel">

        <!-- Nazwa zlecenia -->
        <h1 class="order-title">{$order.name}</h1>

        <!-- Etapy druku cyfrowego -->
        <h2>Etapy druku cyfrowego</h2>
        {if $stages|@count > 0}
            <div class="grid-wrapper">
                {foreach $stages as $stage}
                    {if $stage.stage_type == 'digital'}
                        <section class="card stage-card toggle-card" data-stage="{$stage.id}">

                            <div class="stage-summary">
                                {if $stage.details|@count > 0}
                                    {foreach $stage.details as $d}
                                        <p><strong>Opis:</strong> {$d.description|default:"Brak opisu"}</p>
                                        <p><strong>Kopie:</strong> {$d.copies|default:0}</p>
                                        <p>
                                            <strong>Status:</strong>
                                            {if ($d.done|default:0) == 1}
                                                <span class="status-badge status-done">✅ Zakończone</span>
                                                <a href="{url action='order_toggle_cyfrowy' id=$d.id}" class="button button-revert">↩ Cofnij</a>
                                            {else}
                                                <span class="status-badge status-progress">⌛ Oczekuje</span>
                                                <a href="{url action='order_toggle_cyfrowy' id=$d.id}" class="button button-complete">✔ Oznacz jako skończone</a>
                                            {/if}
                                        </p>
                                        <hr>
                                    {/foreach}
                                {else}
                                    <em>Brak szczegółów dla tego etapu</em>
                                {/if}
                            </div>
                        </section>
                    {/if}
                {/foreach}
            </div>
        {else}
            <p class="message warning">Brak etapów dla tego zlecenia.</p>
        {/if}

        <!-- Dane podstawowe zlecenia -->
        <h2>Dane zlecenia</h2>
        <section class="card">
            <p><strong>Klient:</strong> {$order.client|default:"-"}</p>
            <p><strong>Data startu:</strong> {$order.start_date|default:"-"}</p>
            <p><strong>Data zakończenia:</strong> {$order.end_date|default:"-"}</p>
            <p><strong>Cena:</strong> {$order.price|default:"0"} zł</p>
        </section>
        
        <div style="height:25px;"></div>
        
        <!-- =======================
             FORMULARZ KOMENTARZA
        ======================== -->

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
            <a href="{$conf->action_root}orders_cyfrowy" class="button">⬅ Powrót do listy</a>
        </div>

    </div> <!-- /.admin-panel -->

</section> <!-- /.admin-panel-wrapper -->

{literal}
<script>
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".toggle-card").forEach(card => {
        card.addEventListener("click", () => {
            const details = card.querySelector(".stage-summary");
            if(details){
                details.classList.toggle("details-hidden");
            }
        });
    });
});
</script>
{/literal}

{include file="footer.tpl"}
