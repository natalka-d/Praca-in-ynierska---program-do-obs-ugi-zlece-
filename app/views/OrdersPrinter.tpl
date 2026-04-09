{include file="header.tpl"}
<link rel="stylesheet" href="{$base_url}assets/css/orders-admin.css" />

<section class="wrapper orders-admin">

    <!-- Nagłówek panelu -->
    <div class="header-panel">
        <h2>📑 Panel drukarza</h2>
        <a href="{$conf->action_root}logout" class="logout-btn">🚪 Wyloguj</a>
    </div>

    <!-- Komunikaty -->
    {if isset($messages) && $messages|count > 0}
        <div>
            {foreach $messages as $m}
                <div class="message info">{$m->text}</div>
            {/foreach}
        </div>
    {/if}

    {if isset($orders) && $orders|count > 0}
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nazwa</th>
                        <th>Klient</th>
                        <th>Data przyjęcia</th>
                        <th>Data zakończenia</th>
                        <th>Status</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $orders as $order}
                        {assign var="percent" value=$order.status|replace:'%':''}
                        <tr>
                            <td data-label="Nazwa">{$order.name}</td>
                            <td data-label="Klient">{$order.client|default:"Brak"}</td>
                            <td data-label="Data przyjęcia">{$order.date_received|default:"-"}</td>
                            <td data-label="Data zakończenia">{$order.date_finished|default:"-"}</td>
                            <td data-label="Status">
                                {if $percent >= 100}
                                    <span class="status-badge status-done">✅ {$order.status}</span>
                                {elseif $percent == 0}
                                    <span class="status-badge status-zero">⌛ {$order.status}</span>
                                {else}
                                    <span class="status-badge status-progress">⏳ {$order.status}</span>
                                {/if}
                            </td>
                            <td class="actions-cell" data-label="Akcje">
                                <a href="{$conf->action_root}order_details_printer/{$order.id}" class="action-link">🔍 Szczegóły</a>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>

        {if $total_pages > 1}
            <div class="pagination">
                {if $current_page > 1}
                    <a href="{$conf->action_root}orders_printer/{$current_page-1}" class="page-link">⬅ Poprzednia</a>
                {/if}
                <span class="current-page">Strona {$current_page} z {$total_pages}</span>
                <span style="color:#666;">(Łącznie zleceń: {$total_orders})</span>
                {if $current_page < $total_pages}
                    <a href="{$conf->action_root}orders_printer/{$current_page+1}" class="page-link">Następna ➡</a>
                {/if}
            </div>
        {/if}

    {else}
        <p class="message warning">Brak zleceń do wyświetlenia na tej stronie.</p>
    {/if}

</section>

{include file="footer.tpl"}
