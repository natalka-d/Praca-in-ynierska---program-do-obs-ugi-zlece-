{include file="header.tpl"}

<link rel="stylesheet" href="{$base_url}assets/css/orders-admin.css" />

<section class="admin-panel-wrapper">
    <div class="admin-panel">
        <div class="admin-header">
            <h2>➕ Dodaj etapy do zlecenia: {$order.name}</h2>
        </div>

        <form id="order_stages_form" action="{$conf->action_root}order_save_stages" method="post">
            <input type="hidden" name="order_id" value="{$order.id}">

            <div id="stages_container" style="margin-top:20px;"></div>

            <div class="buttons" style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
                <button type="button" class="button button-primary" id="btn_digital">➕ Druk cyfrowy</button>
                <button type="button" class="button button-primary" id="btn_offset">➕ Druk offsetowy</button>
                <button type="button" class="button button-primary" id="btn_wide">➕ Druk wielkoformatowy</button>
                <button type="button" class="button button-primary" id="btn_binding">➕ Introligatornia</button>
                <button type="button" class="button button-primary" id="btn_laser">➕ Laser</button>
                <button type="button" class="button button-primary" id="btn_dtp">➕ Studio DTP</button>
                <button type="button" class="button button-primary" id="btn_subcontract">➕ Podzlecenie</button>
            </div>

            <hr style="margin-bottom:20px;">

            <button type="submit" class="button button-primary">💾 Zapisz etapy</button>
        </form>
    </div>
</section>

{literal}
<script>
let digitalIndex = 0;
let offsetIndex = 0;
let wideIndex = 0;
let bindingIndex = 0;
let laserIndex = 0;
let dtpIndex = 0;
let subcontractIndex = 0;

// ---- Druk cyfrowy ----
document.getElementById('btn_digital').addEventListener('click', function() {
    const container = document.getElementById('stages_container');
    const div = document.createElement('div');
    div.classList.add('stage-card');
    div.style.marginBottom = '15px';
    div.innerHTML = `
        <h3>Druk cyfrowy</h3>
        <input type="hidden" name="stages[digital][${digitalIndex}][stage_type]" value="digital">
        <label>Ilość kopii:
            <input type="number" name="stages[digital][${digitalIndex}][copies]" min="1" value="1">
        </label><br>
        <label>Opis:
            <textarea name="stages[digital][${digitalIndex}][description]"></textarea>
        </label>
    `;
    addRemoveButton(div);
    container.appendChild(div);
    digitalIndex++;
});

// ---- Druk offsetowy ----
document.getElementById('btn_offset').addEventListener('click', function() {
    const container = document.getElementById('stages_container');
    const div = document.createElement('div');
    div.classList.add('stage-card');
    div.id = `offset_stage_${offsetIndex}`;
    div.innerHTML = `
        <h3>Druk offsetowy</h3>
        <input type="hidden" name="stages[offset][${offsetIndex}][stage_type]" value="offset">
        <div id="offset_worksheets_${offsetIndex}" class="offset-worksheets" style="margin-top:10px;"></div>
        <button type="button" class="button button-secondary" onclick="addOffsetWorksheet(${offsetIndex})">➕ Dodaj arkusz</button>
    `;
    addRemoveButton(div);
    container.appendChild(div);
    addOffsetWorksheet(offsetIndex);
    offsetIndex++;
});

function addOffsetWorksheet(stageIndex) {
    const container = document.getElementById(`offset_worksheets_${stageIndex}`);
    const count = container.children.length;
    const div = document.createElement('div');
    div.classList.add('worksheet-block');
    div.style.marginBottom = '10px';
    div.style.border = '1px solid #ddd';
    div.style.padding = '8px';
    div.innerHTML = `
        <label>Nakład:
            <input type="number" name="stages[offset][${stageIndex}][worksheets][${count}][circulation]" min="1" value="1">
        </label><br>
        <label>Ilość arkuszy:
            <input type="text" name="stages[offset][${stageIndex}][worksheets][${count}][total_sheets]" value="">
        </label><br>
        <label>Gramatura papieru:
            <select name="stages[offset][${stageIndex}][worksheets][${count}][paper_type]">
                <option value="">-- wybierz --</option>
                <option value="90 g/m2">90 g/m²</option>
                <option value="115 g/m2">115 g/m²</option>
                <option value="130 g/m2">130 g/m²</option>
                <option value="170 g/m2">170 g/m²</option>
                <option value="200 g/m2">200 g/m²</option>
                <option value="250 g/m2">250 g/m²</option>
                <option value="300 g/m2">300 g/m²</option>
                <option value="350 g/m2">350 g/m²</option>
            </select>
        </label><br>
        <label>Opis:
            <textarea name="stages[offset][${stageIndex}][worksheets][${count}][description]"></textarea>
        </label>
    `;
    addRemoveButton(div, 'arkusz');
    container.appendChild(div);
}

// ---- Druk wielkoformatowy ----
document.getElementById('btn_wide').addEventListener('click', function() {
    const container = document.getElementById('stages_container');
    const div = document.createElement('div');
    div.classList.add('stage-card');
    div.style.marginBottom = '15px';
    div.innerHTML = `
        <h3>Druk wielkoformatowy</h3>
        <input type="hidden" name="stages[wide][${wideIndex}][stage_type]" value="wide">
        <label>Opis:
            <textarea name="stages[wide][${wideIndex}][description]"></textarea>
        </label>
    `;
    addRemoveButton(div);
    container.appendChild(div);
    wideIndex++;
});

// ---- Introligatornia ----
document.getElementById('btn_binding').addEventListener('click', function() {
    const container = document.getElementById('stages_container');
    const div = document.createElement('div');
    div.classList.add('stage-card');
    div.style.marginBottom = '15px';
    div.innerHTML = `
        <h3>Introligatornia</h3>
        <input type="hidden" name="stages[binding][${bindingIndex}][stage_type]" value="binding">
        <label>Opis:
            <textarea name="stages[binding][${bindingIndex}][description]"></textarea>
        </label>
    `;
    addRemoveButton(div);
    container.appendChild(div);
    bindingIndex++;
});

// ---- Laser ----
document.getElementById('btn_laser').addEventListener('click', function() {
    const container = document.getElementById('stages_container');
    const div = document.createElement('div');
    div.classList.add('stage-card');
    div.style.marginBottom = '15px';
    div.innerHTML = `
        <h3>Laser</h3>
        <input type="hidden" name="stages[laser][${laserIndex}][stage_type]" value="laser">
        <label>Opis:
            <textarea name="stages[laser][${laserIndex}][description]"></textarea>
        </label>
    `;
    addRemoveButton(div);
    container.appendChild(div);
    laserIndex++;
});

// ---- Studio DTP ----
document.getElementById('btn_dtp').addEventListener('click', function() {
    const container = document.getElementById('stages_container');
    const div = document.createElement('div');
    div.classList.add('stage-card');
    div.style.marginBottom = '15px';
    div.innerHTML = `
        <h3>Studio DTP</h3>
        <input type="hidden" name="stages[dtp][${dtpIndex}][stage_type]" value="dtp">
        <label>Opis:
            <textarea name="stages[dtp][${dtpIndex}][description]"></textarea>
        </label>
    `;
    addRemoveButton(div);
    container.appendChild(div);
    dtpIndex++;
});

// ---- Podzlecenie ----
document.getElementById('btn_subcontract').addEventListener('click', function() {
    const container = document.getElementById('stages_container');
    const div = document.createElement('div');
    div.classList.add('stage-card');
    div.style.marginBottom = '15px';
    div.innerHTML = `
        <h3>Podzlecenie</h3>
        <input type="hidden" name="stages[subcontract][${subcontractIndex}][stage_type]" value="subcontract">
        <label>Nazwa firmy:
            <input type="text" name="stages[subcontract][${subcontractIndex}][company_name]">
        </label><br>
        <label>Dane kontaktowe:
            <input type="text" name="stages[subcontract][${subcontractIndex}][contact]">
        </label><br>
        <label>Opis zakresu:
            <textarea name="stages[subcontract][${subcontractIndex}][description]"></textarea>
        </label>
    `;
    addRemoveButton(div);
    container.appendChild(div);
    subcontractIndex++;
});

// --- Funkcja dodająca przycisk usuń ---
function addRemoveButton(div, type = 'etap') {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.classList.add('button', 'button-danger');
    btn.style.marginTop = '10px';
    btn.textContent = type === 'arkusz' ? '❌ Usuń arkusz' : '❌ Usuń etap';
    btn.addEventListener('click', () => div.remove());
    div.appendChild(btn);
}
</script>
{/literal}

{include file="footer.tpl"}
