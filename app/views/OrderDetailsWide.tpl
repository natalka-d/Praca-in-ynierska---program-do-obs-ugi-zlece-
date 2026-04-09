{include file="header.tpl"}
<link rel="stylesheet" href="{$base_url}assets/css/orders-admin.css" />

<section class="admin-panel-wrapper">

    <div class="admin-header">
        <h2>📐 Szczegóły zlecenia (druk wielkoformatowy)</h2>
    </div>

    <div class="admin-panel">

        <!-- Nazwa zlecenia -->
        <h1 class="order-title">{$order.name}</h1>

        <!-- Etapy druku wielkoformatowego -->
        <h2>Etapy druku wielkoformatowego</h2>
            {if $stages|@count > 0}
                <div class="grid-wrapper">
                    {foreach $stages as $stage}
                        {if $stage.stage_type == 'wide' && $stage.details|@count > 0} {* tylko jeśli są detale *}
                            <section class="card stage-card toggle-card" data-stage="{$stage.id}">

                                <div class="stage-summary">
                                    {foreach $stage.details as $d}
                                        <p><strong>Opis:</strong> {$d.description|default:"Brak opisu"}</p>
                                        <p><strong>Kopie:</strong> {$d.copies|default:0}</p>
                                        <p>
                                            <strong>Status:</strong>
                                            {if ($d.done|default:0) == 1}
                                                <span class="status-badge status-done">✅ Zakończone</span>
                                                <a href="{url action='order_toggle_wide' id=$d.id}" class="button button-revert">↩ Cofnij</a>
                                            {else}
                                                <span class="status-badge status-progress">⌛ Oczekuje</span>
                                                <a href="{url action='order_toggle_wide' id=$d.id}" class="button button-complete">✔ Oznacz jako skończone</a>
                                            {/if}
                                        </p>
                                        <hr>
                                    {/foreach}
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

        <!-- Powrót -->
        <div class="back-link">
            <a href="{$conf->action_root}orders_wide" class="button">⬅ Powrót do listy</a>
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
