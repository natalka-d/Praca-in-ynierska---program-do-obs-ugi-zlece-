{include file="header.tpl"}
<section class="wrapper" id="login">
    <div class="card" style="max-width:400px; margin:50px auto; padding:30px; text-align:center; background:#fff; border-radius:12px; box-shadow:0 6px 14px rgba(0,0,0,0.08);">
        <h2 style="margin-bottom:20px; font-size:28px; font-weight:800; color:#2b6cb0;">🔐 Logowanie</h2>

        {* Obsługa wiadomości systemowych (Info/Error) *}
        {if $msgs->isMessage()}
            <div class="messages" style="margin-bottom:20px;">
                {foreach $msgs->getMessages() as $msg}
                    <div class="message {if $msg->isInfo()}info{else}warning{/if}" style="background:{if $msg->isInfo()}#d1ecf1{else}#fff3cd{/if}; color:{if $msg->isInfo()}#0c5460{else}#856404{/if}; padding:12px 16px; border-radius:8px; margin-bottom:10px; font-weight:600; text-align:left; font-size:0.9em;">
                        {$msg->text}
                    </div>
                {/foreach}
            </div>
        {/if}

        {if isset($user) && isset($user->login)}
            <p class="logged-in" style="margin-top:20px; font-weight:600;">
                Zalogowano jako: <strong>{$user->login}</strong>
            </p>
            <a href="{$conf->action_root}logout" class="button logout-btn" style="display:inline-block; margin-top:10px; padding:10px 20px; border-radius:8px; background:#e3342f; color:#fff; font-weight:700; text-decoration:none;">
                🚪 Wyloguj
            </a>
        {else}
            <form method="post" action="{$conf->action_root}login" class="login-form" style="display:flex; flex-direction:column; gap:16px;">
                <input type="text" name="username" placeholder="Login" value="{$form->username|default:''}" required style="padding:12px 14px; border-radius:8px; border:1px solid #ccc; font-size:16px;">
                <input type="password" name="password" placeholder="Hasło" required style="padding:12px 14px; border-radius:8px; border:1px solid #ccc; font-size:16px;">
                <button type="submit" class="button login-btn" style="padding:12px 0; border-radius:8px; background:#2b6cb0; color:#fff; font-weight:700; font-size:16px; border:none; cursor:pointer;">
                    Zaloguj
                </button>
            </form>

            <a href="{$conf->action_root}password_forgot" style="display:block; margin-top:10px; font-size: 0.8em; color: #2b6cb0;">
                Zapomniałeś hasła?
            </a>
        {/if}
    </div>
</section>
{include file="footer.tpl"}