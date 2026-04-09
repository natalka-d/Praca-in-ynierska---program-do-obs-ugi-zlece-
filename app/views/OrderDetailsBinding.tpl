{include file="header.tpl"}
<link rel="stylesheet" href="{$base_url}assets/css/orders-admin.css" />

<section class="admin-panel-wrapper">

    <div class="admin-header">
        <h2>📚 Szczegóły zlecenia (Introligatornia)</h2>
    </div>

    <div class="admin-panel">

        <!-- Nazwa zlecenia -->
        <h1 class="order-title">{$order.name}</h1>

        <!-- Jeden etap introligatorni -->
        <h2>Etap introligatorni</h2>
        {if isset($order.done)}
            <section class="card stage-card">
                <h4>{$order.description|default:"Brak opisu"}</h4>
                <p>
                    <strong>Status:</strong>
                    {if $order.done == 1}
                        <span class="status-badge status-done">✅ Zakończone</span>
                        <a href="{$conf->action_root}order_toggle_binding/{$order.stage_id}/{$order.id}" class="button button-revert">↩ Cofnij</a>
                    {else}
                        <span class="status-badge status-progress">⌛ W trakcie</span>
                        <a href="{$conf->action_root}order_toggle_binding/{$order.stage_id}/{$order.id}" class="button button-complete">✔ Oznacz jako skończone</a>
                    {/if}
                </p>
            </section>
        {else}
            <p class="message warning">Brak etapu dla tego zlecenia.</p>
        {/if}

        <!-- Dane podstawowe zlecenia -->
        <h2>Dane zlecenia</h2>
        <section class="card">
            <p><strong>Klient:</strong> {$order.client|default:"-"}</p>
            <p><strong>Data startu:</strong> {$order.start_date|default:"-"}</p>
            <p><strong>Data zakończenia:</strong> {$order.end_date|default:"-"}</p>
        </section>

        <!-- Powrót -->
        <div class="back-link">
            <a href="{$conf->action_root}orders_binding" class="button">⬅ Powrót do listy</a>
        </div>

    </div> <!-- /.admin-panel -->

</section> <!-- /.admin-panel-wrapper -->

{include file="footer.tpl"}
