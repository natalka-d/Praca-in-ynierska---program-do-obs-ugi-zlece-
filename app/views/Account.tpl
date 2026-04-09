{include file="header.tpl"}

<section class="wrapper account">

    <h2>👤 Moje konto</h2>
    <div style="margin-bottom: 20px;">
        {if $user.role == 'admin'}
            <a href="{$conf->action_root}orders_admin" class="button" style="background: #718096; text-decoration: none; display: inline-block;">⬅️ Powrót do panelu admina</a>
        {elseif $user.role == 'drukarz'}
            <a href="{$conf->action_root}orders_printer" class="button" style="background: #718096; text-decoration: none; display: inline-block;">⬅️ Powrót do zleceń</a>
        {elseif $user.role == 'cyfrowy'}
            <a href="{$conf->action_root}orders_cyfrowy" class="button" style="background: #718096; text-decoration: none; display: inline-block;">⬅️ Powrót do zleceń</a>
        {elseif $user.role == 'wide'}
            <a href="{$conf->action_root}orders_wide" class="button" style="background: #718096; text-decoration: none; display: inline-block;">⬅️ Powrót do zleceń</a>
        {elseif $user.role == 'binding'}
            <a href="{$conf->action_root}orders_binding" class="button" style="background: #718096; text-decoration: none; display: inline-block;">⬅️ Powrót do zleceń</a>
        {elseif $user.role == 'laser'}
            <a href="{$conf->action_root}orders_laser" class="button" style="background: #718096; text-decoration: none; display: inline-block;">⬅️ Powrót do zleceń</a>
        {elseif $user.role == 'dtp'}
            <a href="{$conf->action_root}orders_dtp" class="button" style="background: #718096; text-decoration: none; display: inline-block;">⬅️ Powrót do zleceń</a>
        {else}
            <a href="{$conf->action_root}login" class="button" style="background: #718096; text-decoration: none; display: inline-block;">⬅️ Powrót</a>
        {/if}
    </div>
    {if $messages}
        {foreach $messages as $msg}
            <div class="message {$msg->type}">
                {$msg->text}
            </div>
        {/foreach}
    {/if}

    <div class="account-box">

        <h3>📌 Informacje o koncie</h3>

        <p><strong>Login:</strong> {$user.username}</p>
        <p><strong>Rola:</strong> {$user.role}</p>

        <hr>

        <h3>🔐 Zmiana hasła</h3>

        <form method="post" action="{$conf->action_root}account_change_password" class="account-form">

            <label>Stare hasło</label>
            <input type="password" name="old_password" required>

            <label>Nowe hasło</label>
            <input type="password" name="new_password" required>

            <label>Powtórz nowe hasło</label>
            <input type="password" name="repeat_password" required>

            <button type="submit" class="button">Zmień hasło</button>

        </form>
        
        <hr>
        <h3>👥 Zarządzanie użytkownikami</h3>
        <div id="user-management-zone">
            <button type="button" class="button" onclick="loadUserList()" id="load-users-btn">Pokaż listę użytkowników</button>
            <div id="user-list-container" style="margin-top:20px; display:none;">
                </div>
        </div>

        <script>
        function loadUserList() {
            const container = document.getElementById('user-list-container');
            const btn = document.getElementById('load-users-btn');

            fetch('{$conf->action_root}user_list_ajax')
                .then(response => response.text())
                .then(html => {
                    container.innerHTML = html;
                    container.style.display = 'block';
                    btn.style.display = 'none';
                });
        }

        function saveUser(id) {
            const email = document.getElementById('email-'+id).value;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('email', email);

            fetch('{$conf->action_root}user_update_ajax', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'ok') {
                    alert('Zapisano pomyślnie!');
                } else {
                    alert('Błąd: ' + data.message);
                }
            });
        }
        </script>

    </div>

</section>

<style>
.account {
    max-width: 750px;
    margin: 30px auto;
}

.account-box {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
}

.account-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.account-form input {
    padding: 6px;
}
.button:hover {
    filter: brightness(1.1);
}
</style>

{include file="footer.tpl"}
