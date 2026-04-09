{include file="header.tpl"}

<section class="wrapper orders-admin">

    <div class="header-panel">
        <h2>🛠️ Panel administracyjny</h2>

        <!-- Mini kalendarz -->
        <div id="adminCalendarMini" style="width: 250px; font-size: 12px; cursor:pointer;"></div>

        <a href="{$conf->action_root}account">👤 Moje konto</a>

        <a href="{$conf->action_root}logout" class="logout-btn">🚪 Wyloguj</a>
    </div>
    

    <!-- Modal (pełny kalendarz) -->
    <div id="calendarOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
        background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
        <div style="background:#fff; padding:20px; border-radius:8px; max-width:900px; width:90%;
            max-height:90%; overflow:auto; position:relative;">
            
            <!-- 🔽 FILTROWANIE W KALENDARZU -->
            <div style="margin-bottom: 10px;">
                <label for="calendarFilter">Filtruj zlecenia:</label>
                <select id="calendarFilter" style="padding:4px;">
                    <option value="">Wszystkie</option>
                    <option value="w trakcie">W trakcie</option>
                    <option value="skończone">Skończone</option>
                    <option value="oczekuje">Oczekuje</option>
                    <option value="archiwalne">Zarchiwizowane</option>
                </select>
            </div>
            <!-- 🔼 KONIEC FILTRA -->

            <button id="closeCalendar" style="position:absolute; top:10px; right:10px; z-index:101;">✖ Zamknij</button>
            <div id="adminCalendarFull"></div>
        </div>
    </div>

    <!-- Filtry -->
    <div style="margin:16px 0;">
        <button type="button" id="toggleFilterBtn" class="button filter-toggle">Filtruj</button>
        <form method="get" id="filterForm" style="display:none; margin-top:10px;" class="filter-form">
            <input type="text" name="name" placeholder="Nazwa" value="{$filters.name|default:''}">
            <input type="text" name="client" placeholder="Klient" value="{$filters.client|default:''}">
            <input type="date" name="start_date" value="{$filters.start_date|default:''}">
            <input type="date" name="end_date" value="{$filters.end_date|default:''}">
            <select name="status">
                <option value="">Wszystkie statusy</option>
                <option value="w trakcie" {if $filters.status=='w trakcie'}selected{/if}>W trakcie</option>
                <option value="skończone" {if $filters.status=='skończone'}selected{/if}>Skończone</option>
                <option value="oczekuje" {if $filters.status=='oczekuje'}selected{/if}>Oczekuje</option>
            </select>
            <button type="submit" class="button apply-filters">Zastosuj filtry</button>
        </form>
    </div>

    <div style="margin:16px 0;">
        <a href="{$conf->action_root}order_add" class="button add-order">➕ Dodaj nowe zlecenie</a>
        <a href="{$conf->action_root}orders_archive" class="button archive-order">🗄️ Archiwum</a>
    </div>

    {if isset($orders) && $orders|count>0}
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nazwa</th>
                        <th>Klient</th>
                        <th>Cena</th>
                        <th>Data przyjęcia</th>
                        <th>Data zakończenia</th>
                        <th>Status</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $orders as $order}
                    <tr data-href="{$conf->action_root}order_details_admin/{$order.id}">
                        <td>{$order.name}</td>
                        <td>{$order.client|default:"Brak"}</td>
                        <td>{$order.price|default:"-"}</td>
                        <td>{$order.start_date|default:"-"}</td>
                        <td>{$order.end_date|default:"-"}</td>
                        <td>
                            {if $order.computed_status=="skończone"}
                                <span class="status-badge status-done">✅ Skończone</span>
                            {elseif $order.computed_status=="w trakcie"}
                                <span class="status-badge status-progress">⏳ W trakcie</span>
                            {else}
                                <span class="status-badge status-zero">⌛ Oczekuje</span>
                            {/if}
                        </td>
                        <td class="actions-cell">
                            <a href="{$conf->action_root}order_edit/{$order.id}" class="action-link button-blue">Edytuj</a>
                            {if $order.computed_status != 'skończone'}
                                <a href="{$conf->action_root}order_finish/{$order.id}" class="action-link finish">✅ Zakończ</a>
                            {/if}
                            <a href="{$conf->action_root}order_archive/{$order.id}" class="action-link archive" onclick="return confirm('Przenieść do archiwum?');">🗄️ Archiwizuj</a>
                            <a href="{$conf->action_root}order_delete/{$order.id}" class="action-link delete" onclick="return confirm('Usunąć to zlecenie?');">🗑️ Usuń</a>
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
                
        </div>
    {else}
        <p class="message warning">Brak zleceń do wyświetlenia.</p>
    {/if}
    
    {if $total_pages > 1}
<div class="pagination">

    {* Poprzednia strona *}
    {if $current_page > 1}
        <a class="page-btn" href="{$conf->action_root}orders_admin/{$current_page-1}">&laquo; Poprzednia</a>
    {/if}

    {* Numery stron *}
    {section name=i start=1 loop=$total_pages+1}
        {if $smarty.section.i.index == $current_page}
            <span class="page-current">{$smarty.section.i.index}</span>
        {else}
            <a class="page-btn" href="{$conf->action_root}orders_admin/{$smarty.section.i.index}">
                {$smarty.section.i.index}
            </a>
        {/if}
    {/section}

    {* Następna strona *}
    {if $current_page < $total_pages}
        <a class="page-btn" href="{$conf->action_root}orders_admin/{$current_page+1}">Następna &raquo;</a>
    {/if}

</div>
{/if}


</section>

{literal}
<style>
#adminCalendarMini { font-size: 12px; max-width: 250px; }
#calendarOverlay { display: none; justify-content: center; align-items: center; }
#adminCalendarFull { max-height: 80vh; }
#closeCalendar { z-index: 101; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const base = window.location.origin + '/zlecenia/public/';
    const miniEl = document.getElementById('adminCalendarMini');
    const fullEl = document.getElementById('adminCalendarFull');
    const overlay = document.getElementById('calendarOverlay');
    const closeBtn = document.getElementById('closeCalendar');
    const filterSelect = document.getElementById('calendarFilter');

    if (!miniEl || !fullEl) return;

    const mini = new FullCalendar.Calendar(miniEl, {
        initialView: 'dayGridMonth',
        firstDay: 1,
        height: 180,
        headerToolbar: false,
        dateClick: function() {
            overlay.style.display = 'flex';
            setTimeout(() => full.updateSize(), 250);
        }
    });
    mini.render();

    const full = new FullCalendar.Calendar(fullEl, {
        initialView: 'dayGridMonth',
        firstDay: 1,
        selectable: true,
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        dateClick: function(info) {
            const note = prompt('Dodaj notatkę do ' + info.dateStr);
            if (note) {
                fetch(base + "saveNote", {
                    method: 'POST',
                    credentials: 'include',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `date=${info.dateStr}&note=${encodeURIComponent(note)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        full.addEvent({
                            id: data.id,
                            title: note,
                            start: info.dateStr,
                            allDay: true,
                            color: '#28a745'
                        });
                    } else {
                        alert('Błąd zapisu: ' + data.message);
                    }
                })
                .catch(err => alert('Błąd połączenia z serwerem.'));
            }
        }
    });
    full.render();

    // 🔽 Funkcja wczytująca zlecenia z filtrem
    function loadOrders(status = '') {
        full.getEvents().forEach(e => {
            if (!e.id.startsWith('note-')) e.remove(); // zostaw notatki
        });

        fetch(base + "getOrdersForCalendar" + (status ? '?status=' + encodeURIComponent(status) : ''), 
            { credentials: 'include' })
            .then(res => res.json())
            .then(data => {
                data.forEach(o => {
                    full.addEvent({
                        id: o.id,
                        title: o.title,
                        start: o.start,
                        end: o.end,
                        color: o.color,
                        url: o.url
                    });
                });
            })
            .catch(err => console.error('Błąd wczytywania zleceń:', err));
    }

    // 🔼 Wczytaj zlecenia na start
    loadOrders();

    // 🔽 Obsługa zmiany filtra
    filterSelect.addEventListener('change', function() {
        loadOrders(this.value);
    });

    // Wczytaj notatki (raz)
    fetch(base + "getNotes", { credentials: 'include' })
        .then(res => res.json())
        .then(data => {
            data.forEach(n => {
                full.addEvent({
                    id: 'note-' + n.id,
                    title: n.note_text,
                    start: n.note_date,
                    allDay: true,
                    color: '#28a745'
                });
            });
        })
        .catch(err => console.error('Błąd wczytywania notatek:', err));

    closeBtn.addEventListener('click', () => overlay.style.display = 'none');
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.style.display = 'none'; });
});

// === Filtry tabeli ===
const toggleBtn = document.getElementById('toggleFilterBtn');
const filterForm = document.getElementById('filterForm');
if (toggleBtn && filterForm) {
    toggleBtn.addEventListener('click', () => {
        filterForm.style.display = (filterForm.style.display === 'none' || filterForm.style.display === '') ? 'flex' : 'none';
    });
}

// === Kliknięcie w wiersz ===
document.querySelectorAll('.table tbody tr').forEach(row => {
    row.addEventListener('click', e => {
        if (!e.target.closest('a')) window.location = row.dataset.href;
    });
});
</script>
{/literal}

{include file="footer.tpl"}
