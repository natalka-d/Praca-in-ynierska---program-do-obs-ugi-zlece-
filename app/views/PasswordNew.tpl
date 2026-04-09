{include file="header.tpl"}

<section class="wrapper">
    <div class="card" style="max-width:400px; margin:50px auto; padding:30px; background:#fff; border-radius:12px; box-shadow:0 6px 14px rgba(0,0,0,0.08);">
        <h2 style="text-align:center; color:#2b6cb0;">🆕 Ustaw nowe hasło</h2>
        <p style="text-align:center; font-size:0.9em; color:#666;">Dla użytkownika: <strong>{$user_name}</strong></p>

        <form action="{$conf->action_root}password_save_new" method="post" style="display:flex; flex-direction:column; gap:16px;">
            <input type="hidden" name="token" value="{$token}">

            <label for="pass">Nowe hasło:</label>
            <input type="password" name="password" id="pass" required style="padding:12px; border-radius:8px; border:1px solid #ccc;">

            <label for="pass2">Powtórz hasło:</label>
            <input type="password" name="password_repeat" id="pass2" required style="padding:12px; border-radius:8px; border:1px solid #ccc;">

            <button type="submit" class="button" style="background:#2b6cb0; color:#fff; padding:12px; border:none; border-radius:8px; cursor:pointer;">Zapisz nowe hasło</button>
        </form>
    </div>
</section>

{include file="footer.tpl"}