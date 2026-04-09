{include file="header.tpl"}

<section class="wrapper login-section">
    <div class="login-box">
        <h2>🔑 Przypominanie hasła</h2>
        <p style="margin-bottom: 20px; color: #666; font-size: 0.9em;">
            Wprowadź swój adres e-mail (login), a wyślemy Ci instrukcje resetowania hasła.
        </p>

        {if $messages}
            {foreach $messages as $msg}
                <div class="message {$msg->type}">
                    {$msg->text}
                </div>
            {/foreach}
        {/if}

        <form action="{$conf->action_root}password_send_link" method="post" class="pure-form pure-form-stacked">
            <fieldset style="display: flex; flex-direction: column; gap: 10px;">
                <label for="email">E-mail / Login</label>
                <input type="email" name="email" id="email" placeholder="Twój adres e-mail" required autofocus>

                <button type="submit" class="button">Wyślij link do resetu</button>
            </fieldset>
        </form>

        <div style="margin-top: 20px; text-align: center;">
            <a href="{$conf->action_root}loginShow" style="font-size: 0.85em; color: #333;">← Powrót do logowania</a>
        </div>
    </div>
</section>

{include file="footer.tpl"}