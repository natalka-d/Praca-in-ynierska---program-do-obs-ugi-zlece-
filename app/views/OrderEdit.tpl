{include file="header.tpl"}

<div class="admin-panel-wrapper">
    <section class="admin-panel">
        <div class="admin-header">
            <h2>✏️ Edytuj zlecenie: {$order.name}</h2>
        </div>

        <form action="{$conf->action_root}order_update_admin" method="post">
            <input type="hidden" name="id" value="{$order.id}">

            <div class="form-group">
                <label>Nazwa:</label>
                <input type="text" name="name" value="{$order.name}" required>
            </div>

            <div class="form-group">
                <label>Klient (firma):</label>
                <input type="text" name="client" value="{$order.client|default:''}" required>
            </div>

            <div class="form-group">
                <label>Opis:</label>
                <textarea name="description">{$order.description|default:''}</textarea>
            </div>

            <div class="form-group">
                <label>Cena:</label>
                <input type="text" name="price" value="{$order.price|default:''}">
            </div>

            <div class="form-row">
                <div class="form-group form-half">
                    <label>Data rozpoczęcia:</label>
                    <input type="date" name="start_date" value="{$order.start_date|default:''}">
                </div>
                <div class="form-group form-half">
                    <label>Data zakończenia:</label>
                    <input type="date" name="end_date" value="{$order.end_date|default:''}">
                </div>
            </div>

            <h3>Etapy zlecenia</h3>

            {if !empty($order.stages)}
                {foreach $order.stages as $i => $stage}
                    <div class="stage-card">
                        <h4>{$stage.stage_name}</h4>
                        <input type="hidden" name="stages[{$i}][id]" value="{$stage.id}">
                        <input type="hidden" name="stages[{$i}][stage_type]" value="{$stage.stage_type}">

                        <div class="form-group">
                            <label>Opis etapu:</label>
                            <textarea name="stages[{$i}][description]">{$stage.description|default:''}</textarea>
                        </div>

                        {if $stage.stage_type == 'digital'}
                            <div class="form-group">
                                <label>Liczba kopii:</label>
                                <input type="number" name="stages[{$i}][copies]" value="{$stage.copies|default:0}">
                            </div>
                        {/if}

                        {if $stage.stage_type == 'offset'}
                            {if !empty($stage.worksheets)}
                                <h5>Arkusze:</h5>
                                {foreach $stage.worksheets as $j => $ws}
                                    <div class="worksheet-card">
                                        <input type="hidden" name="stages[{$i}][worksheets][{$j}][id]" value="{$ws.id}">
                                        <div class="form-row">
                                            <div class="form-group form-half">
                                                <label>Nakład:</label>
                                                <input type="number" name="stages[{$i}][worksheets][{$j}][circulation]" value="{$ws.circulation|default:0}">
                                            </div>
                                            <div class="form-group form-half">
                                                <label>Ilość arkuszy:</label>
                                                <input type="text" name="stages[{$i}][worksheets][{$j}][total_sheets]" value="{$ws.total_sheets|default:''}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-group">
                                                <label>Rodzaj papieru:</label>
                                                <select name="stages[{$i}][worksheets][{$j}][paper_type]">
                                                    <option value="">-- wybierz --</option>
                                                    <option value="90 g/m2" {if $ws.paper_type == '90 g/m2'}selected{/if}>90 g/m²</option>
                                                    <option value="115 g/m2" {if $ws.paper_type == '115 g/m2'}selected{/if}>115 g/m²</option>
                                                    <option value="130 g/m2" {if $ws.paper_type == '130 g/m2'}selected{/if}>130 g/m²</option>
                                                    <option value="170 g/m2" {if $ws.paper_type == '170 g/m2'}selected{/if}>170 g/m²</option>
                                                    <option value="200 g/m2" {if $ws.paper_type == '200 g/m2'}selected{/if}>200 g/m²</option>
                                                    <option value="250 g/m2" {if $ws.paper_type == '250 g/m2'}selected{/if}>250 g/m²</option>
                                                    <option value="300 g/m2" {if $ws.paper_type == '300 g/m2'}selected{/if}>300 g/m²</option>
                                                    <option value="350 g/m2" {if $ws.paper_type == '350 g/m2'}selected{/if}>350 g/m²</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Opis:</label>
                                            <input type="text" name="stages[{$i}][worksheets][{$j}][description]" value="{$ws.description|default:''}">
                                        </div>
                                    </div>
                                {/foreach}
                            {/if}
                        {/if}
                    </div>
                {/foreach}
            {else}
                <p class="message warning">Brak etapów do edycji.</p>
            {/if}

            <!-- Kontener dla nowych etapów -->
            <div id="new-stages-container"></div>

            <!-- Przycisk dodawania nowych etapów -->
            <div class="form-actions">
                <button type="button" class="button button-secondary" id="add-stage-btn">➕ Dodaj nowy etap</button>
            </div>

            <div class="form-actions">
                <button type="submit" class="button button-primary">💾 Zapisz zmiany</button>
                <a href="{$conf->action_root}orders_admin" class="button button-secondary">⬅ Powrót</a>
            </div>
        </form>
    </section>
</div>

<!-- Szablon nowego etapu -->
<script type="text/template" id="stage-template">
    <div class="stage-card new-stage">
        <h4>Nowy etap</h4>
        <div class="form-group">
            <label>Typ etapu:</label>
            <select name="stages[__INDEX__][stage_type]" class="stage-type-select">
                <option value="digital">Druk cyfrowy</option>
                <option value="offset">Druk offsetowy</option>
                <option value="wide">Druk wielkoformatowy</option>
                <option value="binding">Introligatornia</option>
                <option value="laser">Laser</option>
                <option value="dtp">Studio DTP</option>
                <option value="subcontract">Podzlecenie</option> <!-- nowa opcja -->
            </select>
        </div>

        <div class="form-group">
            <label>Opis etapu:</label>
            <textarea name="stages[__INDEX__][description]"></textarea>
        </div>

        <!-- Pola dla digital -->
        <div class="form-group digital-only" style="display:none;">
            <label>Liczba kopii:</label>
            <input type="number" name="stages[__INDEX__][copies]" value="0">
        </div>

        <!-- Pola dla offset -->
        <div class="form-group offset-only" style="display:none;">
            <h5>Arkusze:</h5>
            <div class="worksheet-card">
                <div class="form-row">
                    <div class="form-group form-half">
                        <label>Nakład:</label>
                        <input type="text" name="stages[__INDEX__][worksheets][0][circulation]" value="">
                    </div>
                    <div class="form-group form-half">
                        <label>Ilość arkuszy:</label>
                        <input type="text" name="stages[__INDEX__][worksheets][0][total_sheets]" value="">
                    </div>
                </div>
                <div class="form-group">
                    <label>Rodzaj papieru:</label>
                    <input type="text" name="stages[__INDEX__][worksheets][0][paper_type]" value="">
                </div>
                <div class="form-group">
                    <label>Opis:</label>
                    <input type="text" name="stages[__INDEX__][worksheets][0][description]" value="">
                </div>
            </div>
        </div>

        <!-- Pola dla Podzlecenie -->
        <div class="form-group subcontract-only" style="display:none;">
            <label>Nazwa firmy:</label>
            <input type="text" name="stages[__INDEX__][company_name]" value="">
            <label>Dane kontaktowe:</label>
            <input type="text" name="stages[__INDEX__][contact]" value="">
            <label>Opis zakresu:</label>
            <textarea name="stages[__INDEX__][description]"></textarea>
        </div>

        <div class="form-actions">
            <button type="button" class="button button-danger remove-stage-btn">🗑 Usuń etap</button>
        </div>
    </div>
</script>

<script>
document.addEventListener('change', function(e){
    if(e.target && e.target.classList.contains('stage-type-select')){
        const stageCard = e.target.closest('.stage-card');
        const digitalFields = stageCard.querySelectorAll('.digital-only');
        const offsetFields = stageCard.querySelectorAll('.offset-only');
        const subcontractFields = stageCard.querySelectorAll('.subcontract-only');

        // reset
        digitalFields.forEach(f => f.style.display = 'none');
        offsetFields.forEach(f => f.style.display = 'none');
        subcontractFields.forEach(f => f.style.display = 'none');

        // pokaz odpowiednie pola
        if(e.target.value === 'digital') digitalFields.forEach(f => f.style.display = 'block');
        else if(e.target.value === 'offset') offsetFields.forEach(f => f.style.display = 'block');
        else if(e.target.value === 'subcontract') subcontractFields.forEach(f => f.style.display = 'block');
    }
});
</script>


<!-- JavaScript -->
<script>
let stageIndex = 1000;

document.getElementById('add-stage-btn').addEventListener('click', () => {
    const template = document.getElementById('stage-template').innerHTML;
    const newStageHtml = template.replace(/__INDEX__/g, stageIndex++);
    document.getElementById('new-stages-container').insertAdjacentHTML('beforeend', newStageHtml);
});

document.addEventListener('click', function(e){
    if(e.target && e.target.classList.contains('remove-stage-btn')){
        e.target.closest('.stage-card').remove();
    }
});

document.addEventListener('change', function(e){
    if(e.target && e.target.classList.contains('stage-type-select')){
        const stageCard = e.target.closest('.stage-card');
        const digitalFields = stageCard.querySelectorAll('.digital-only');
        const offsetFields = stageCard.querySelectorAll('.offset-only');

        if(e.target.value === 'digital'){
            digitalFields.forEach(f => f.style.display = 'block');
            offsetFields.forEach(f => f.style.display = 'none');
        } else if(e.target.value === 'offset'){
            digitalFields.forEach(f => f.style.display = 'none');
            offsetFields.forEach(f => f.style.display = 'block');
        } else {
            digitalFields.forEach(f => f.style.display = 'none');
            offsetFields.forEach(f => f.style.display = 'none');
        }
    }
});
</script>

{include file="footer.tpl"}
