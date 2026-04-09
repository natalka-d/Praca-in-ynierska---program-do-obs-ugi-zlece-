{include file="header.tpl"}
<link rel="stylesheet" href="{$base_url}assets/css/orders-admin.css" />

<section class="admin-panel-wrapper">

    <div class="admin-header">
        <h2>🔦 Szczegóły zlecenia (Laser)</h2>
    </div>

    <div class="admin-panel">

        <!-- Nazwa zlecenia -->
        <h1 class="order-title">{$order.name}</h1>

        <!-- Etap Lasera -->
        <h2>Etap Lasera</h2>
        <section class="card stage-card toggle-card" data-stage="{$order.stage_id}">
            <div class="stage-summary">
                <p><strong>Opis:</strong> {$order.laser_description|default:"Brak opisu"}</p>
                <p>
                    <strong>Status:</strong>
                    {if ($order.done|default:0) == 1}
                        <span class="status-badge status-done">✅ Zakończone</span>
                        <a href="{url action='order_toggle_laser' stage_id=$order.stage_id id=$order.id}" class="button button-revert">↩ Cofnij</a>
                    {else}
                        <span class="status-badge status-progress">⌛ W trakcie</span>
                        <a href="{url action='order_toggle_laser' stage_id=$order.stage_id id=$order.id}" class="button button-complete">✔ Oznacz jako skończone</a>
                    {/if}
                </p>
            </div>
        </section>

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
            <a href="{$conf->action_root}orders_laser" class="button">⬅ Powrót do listy</a>
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
