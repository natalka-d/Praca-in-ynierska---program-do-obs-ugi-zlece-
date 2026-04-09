{include file="header.tpl"}

<link rel="stylesheet" href="{$base_url}assets/css/orders-admin.css" />

<section class="admin-panel-wrapper">

    <div class="admin-panel">
        <div class="admin-header">
            <h2>➕ Dodaj nowe zlecenie</h2>
        </div>

        {if isset($messages) && $messages|count > 0}
            <div style="color:#b22222; margin:8px 0;">
                {foreach $messages as $m}
                    {$m->text}<br>
                {/foreach}
            </div>
        {/if}

        <div style="display:flex; gap:40px; flex-wrap:wrap; margin-top:20px;">

            <!-- Formularz dodawania zlecenia -->
            <form id="order-form" action="{$conf->action_root}order_save_and_add_stages" method="post" style="flex:1; min-width:300px;">
                <h3>Nowe zlecenie</h3>

                <p>
                    <label>Nazwa zlecenia:<br>
                        <input type="text" name="name" value="{$form.name|default:''}" required>
                    </label>
                </p>

                <p>
                    <label>Klient (nazwa lub ID):<br>
                        <input type="text" id="client-input" name="client" value="{$form.client|default:''}" required autocomplete="off">
                    </label>
                </p>

                <!-- Automatycznie wypełniane pola klienta -->
                <div id="client-details" style="display:none; margin-top:15px; padding:10px; border:1px solid #ddd; border-radius:6px; background:#fafafa;">
                    <h4>Dane klienta</h4>
                    <p>
                        <label>Osoba kontaktowa:<br>
                            <input type="text" id="contact_person" name="contact_person" readonly>
                        </label>
                    </p>
                    <p>
                        <label>Email:<br>
                            <input type="email" id="email" name="email" readonly>
                        </label>
                    </p>
                    <p>
                        <label>Telefon:<br>
                            <input type="text" id="phone" name="phone" readonly>
                        </label>
                    </p>
                    <p>
                        <label>Adres:<br>
                            <input type="text" id="address" name="address" readonly>
                        </label>
                    </p>
                    <p>
                        <label>NIP:<br>
                            <input type="text" id="nip" name="nip" readonly>
                        </label>
                    </p>
                </div>

                <p>
                    <label>Opis zlecenia:<br>
                        <textarea name="description" style="width:100%; height:75px;">{$form.description|default:''}</textarea>
                    </label>
                </p>

                <p>
                    <label>Cena:<br>
                        <input type="text" name="price" value="{$form.price|default:''}" step="0.01">
                    </label>
                </p>

                <div class="form-row">
                    <div class="form-half">
                        <label>Data przyjęcia:<br>
                            <input type="date" name="start_date" value="{$form.date_received|default:''}">
                        </label>
                    </div>
                    <div class="form-half">
                        <label>Data zakończenia:<br>
                            <input type="date" name="end_date" value="{$form.date_finished|default:''}">
                        </label>
                    </div>
                </div>

                <p>
                    <input type="submit" class="button button-primary" value="Dodaj zlecenie">
                </p>
            </form>

            <!-- Formularz dodawania klienta -->
            <form action="{$conf->action_root}client_save" method="post" style="flex:1; min-width:300px;">
                <h3>Dodaj nowego klienta</h3>
                <p>
                    <label>Nazwa firmy:<br>
                        <input type="text" name="company_name" required>
                    </label>
                </p>
                <p>
                    <label>Osoba kontaktowa:<br>
                        <input type="text" name="contact_person">
                    </label>
                </p>
                <p>
                    <label>Email:<br>
                        <input type="email" name="email">
                    </label>
                </p>
                <p>
                    <label>Telefon:<br>
                        <input type="text" name="phone">
                    </label>
                </p>
                <p>
                    <label>Adres:<br>
                        <input type="text" name="address">
                    </label>
                </p>
                <p>
                    <label>NIP:<br>
                        <input type="text" name="nip">
                    </label>
                </p>
                <p>
                    <input type="submit" class="button button-primary" value="Dodaj klienta">
                </p>
            </form>

        </div>
    </div>
</section>

<!-- AJAX do automatycznego pobierania danych klienta -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const clientInput = document.getElementById("client-input");
    const clientDetails = document.getElementById("client-details");

    clientInput.addEventListener("input", function() {
        const clientName = clientInput.value.trim();
        if (clientName.length === 0) {
            clientDetails.style.display = "none";
            return;
        }

        fetch("{$conf->action_root}client_lookup?name=" + encodeURIComponent(clientName))
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    document.getElementById("contact_person").value = data.client.contact_person || "";
                    document.getElementById("email").value = data.client.email || "";
                    document.getElementById("phone").value = data.client.phone || "";
                    document.getElementById("address").value = data.client.address || "";
                    document.getElementById("nip").value = data.client.nip || "";
                    clientDetails.style.display = "block";
                } else {
                    clientDetails.style.display = "none";
                }
            })
            .catch(err => {
                console.error("Błąd pobierania danych klienta:", err);
                clientDetails.style.display = "none";
            });
    });
});
</script>

{include file="footer.tpl"}
