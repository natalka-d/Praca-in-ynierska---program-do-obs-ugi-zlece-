{include file="header.tpl"}
<link rel="stylesheet" href="{$base_url}assets/css/orders-admin.css" />

<section class="wrapper orders-admin">

    <div class="header-panel">
        <h2> Archiwum zleceń</h2>
        <a href="{$conf->action_root}logout" class="logout-btn">🚪 Wyloguj</a>
    </div>


    <!-- Powrót do panelu -->
    <div style="margin:16px 0;">
        <a href="{$conf->action_root}orders_admin" class="button add-order">⬅ Powrót do panelu</a>
    </div>

    {if isset($orders) && $orders|count > 0}
        <!-- Tabela w stylu panelu głównego -->
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr style="background:#d1d5db;"> {* szary pasek nagłówków *}
                        <th>Nazwa</th>
                        <th>Klient</th>
                        <th>Cena</th>
                        <th>Data przyjęcia</th>
                        <th>Data zakończenia</th>
                        {*<th>Status</th>*}
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $orders as $order}
                        <tr>
                            <td data-label="Nazwa">{$order.name}</td>
                            <td data-label="Klient">{$order.client|default:"Brak"}</td>
                            <td data-label="Cena">{$order.price|default:"-"}</td>
                            <td data-label="Data przyjęcia">{$order.start_date|default:"-"}</td>
                            <td data-label="Data zakończenia">{$order.end_date|default:"-"}</td>
                            {*<td>Status wyłączony</td>*}
                            <td class="actions-cell" data-label="Akcje">
                                <a href="{$conf->action_root}order_details_admin/{$order.id}" class="action-link">Szczegóły</a>
                                <a href="{$conf->action_root}order_restore/{$order.id}" class="action-link edit">♻ Przywróć</a>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>

        {if $total_pages > 1}
            <div class="pagination">
                {if $current_page > 1}
                    <a href="{$conf->action_root}orders_archive/{$current_page-1}" class="page-link">⬅ Poprzednia</a>
                {/if}
                <span class="current-page">Strona {$current_page} z {$total_pages} (Łącznie zleceń: {$total_orders})</span>
                {if $current_page < $total_pages}
                    <a href="{$conf->action_root}orders_archive/{$current_page+1}" class="page-link">Następna ➡</a>
                {/if}
            </div>
        {/if}

    {else}
        <p class="message warning">Brak zleceń w archiwum.</p>
    {/if}

</section>

{include file="footer.tpl"}
