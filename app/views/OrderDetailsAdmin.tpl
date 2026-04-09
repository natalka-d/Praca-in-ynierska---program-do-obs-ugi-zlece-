{include file="header.tpl"}

<section class="admin-panel-wrapper">

    <div class="admin-header">
        <h2>📄 Szczegóły zlecenia</h2>
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

        <!-- Etapy -->
        <h2>Postęp zlecenia</h2>
        {if $order.stages|@count > 0}
            <div class="grid-wrapper">
                {foreach $order.stages as $stage}
                    <section class="card stage-card toggle-card" data-stage="{$stage.id}">
                        <div class="stage-header">
                            <h3>{$stage.stage_name|default:"Brak nazwy"}</h3>
                            {if $stage.status == 'skończone'}
                                <span class="status-badge status-done">✅ <span>Skończone</span></span>
                            {elseif $stage.status == 'w trakcie'}
                                <span class="status-badge status-progress">⏳ <span>W trakcie</span></span>
                            {else}
                                <span class="status-badge status-zero">⌛ <span>Oczekuje</span></span>
                            {/if}
                        </div>

                        <div class="stage-summary">
                            {if $stage.stage_type == 'offset'}
                                <p><strong>Opis:</strong> {$stage.description|default:"Brak"}</p>
                            {elseif $stage.stage_type == 'digital'}
                                <p><strong>Kopie:</strong> {$stage.copies|default:0}</p>
                                <p><strong>Opis:</strong> {$stage.description|default:"Brak"}</p>
                            {elseif in_array($stage.stage_type, ['wide','binding','laser'])}
                                <p><strong>Opis:</strong> {$stage.description|default:"Brak"}</p>
                            {elseif $stage.stage_type == 'dtp'}
                                <p><strong>Opis:</strong> {$stage.description|default:"Brak"}</p>
                            {elseif $stage.stage_type == 'subcontract'}
                                <p><strong>Firma:</strong> {$stage.company_name|default:"Brak"}</p>
                                <p><strong>Kontakt:</strong> {$stage.contact|default:"Brak"}</p>
                                <p><strong>Opis:</strong> {$stage.description|default:"Brak"}</p>
                            {else}
                                <em>Brak szczegółów</em>
                            {/if}
                        </div>

                        {if $stage.stage_type == 'offset'}
                            <div class="stage-details details-hidden">
                                {if isset($stage.worksheets) && $stage.worksheets|@count > 0}
                                    {foreach $stage.worksheets as $ws}
                                        <p><strong>Papier:</strong> {$ws.paper_type|default:"Nie określono"}</p>
                                        <p><strong>Status:</strong>
                                            {if $ws.printed_sheets >= $ws.circulation}
                                                ✅ Wykonane ({$ws.printed_sheets}/{$ws.total_sheets})
                                            {elseif $ws.printed_sheets > 0}
                                                ⏳ W trakcie ({$ws.printed_sheets}/{$ws.total_sheets})
                                            {else}
                                                ⌛ Oczekuje ({$ws.printed_sheets|default:0}/{$ws.total_sheets|default:0})
                                            {/if}
                                        </p>
                                        <p><strong>Opis:</strong> {$ws.description|default:"Brak"}</p>
                                        <hr>
                                    {/foreach}
                                    <div style="font-weight:bold; margin-top:6px;">
                                        Postęp całego etapu: {$stage.printed_sheets|default:0} / {$stage.total_sheets|default:0} arkuszy
                                    </div>
                                {else}
                                    <em>Brak arkuszy roboczych</em>
                                {/if}
                            </div>
                        {/if}
                    </section>
                {/foreach}
            </div>
        {else}
            <p class="message warning">Brak etapów dla tego zlecenia.</p>
        {/if}

        <!-- Szczegóły zlecenia -->
        <h2>Szczegóły</h2>
        <section class="card">
            <p><strong>Cena:</strong> {$order.price|default:"0"} zł</p>
            <form method="post" action="{$conf->action_root}order_save_invoice" class="invoice-form">
                <input type="hidden" name="order_id" value="{$order.id|escape}">
                <label>
                    <strong>Numer faktury:</strong>
                    <input type="text" name="invoice_number" value="{$order.invoice_number|default:''|escape}">
                </label>
                <button type="submit" class="button button-primary">💾 Zapisz</button>
            </form>
            <p><strong>Start:</strong> {$order.start_date|default:"-"}</p>
            <p><strong>Koniec:</strong> {$order.end_date|default:"-"}</p>
        </section>

        <!-- Dane klienta -->
        <h2>Dane klienta</h2>
        <section class="card">
            <p><strong>Firma:</strong> {$order.client_company|default:"-"}</p>
            <p><strong>Osoba do kontaktu:</strong> {$order.contact_person|default:"-"}</p>
            <p><strong>Email:</strong> {$order.email|default:"-"}</p>
            <p><strong>Telefon:</strong> {$order.phone|default:"-"}</p>
            <p><strong>Adres:</strong> {$order.address|default:"-"}</p>
            <p><strong>NIP:</strong> {$order.nip|default:"-"}</p>
        </section>

        <!-- Opis -->
        <h2>Opis</h2>
        <section class="card">
            <p>{$order.description|default:"Brak opisu"}</p>
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
            <a href="{$conf->action_root}orders_admin" class="button">⬅ Powrót do listy</a>
        </div>

    </div> <!-- /.admin-panel -->

</section> <!-- /.admin-panel-wrapper -->

{literal}
<script>
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".toggle-card").forEach(card => {
        card.addEventListener("click", e => {
            const details = card.querySelector(".stage-details");
            if(details){
                details.classList.toggle("details-hidden");
            }
        });
    });
});
</script>
{/literal}

{include file="footer.tpl"}
