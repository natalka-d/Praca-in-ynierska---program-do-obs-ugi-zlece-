<table style="width:100%; font-size: 0.85em; border-collapse: collapse;">
    <thead>
        <tr style="border-bottom: 2px solid #eee; text-align: left;">
            <th>Login</th>
            <th>Email</th>
            <th>Akcja</th>
        </tr>
    </thead>
    <tbody>
    {foreach $users as $u}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 8px 0;"><strong>{$u.username}</strong></td>
            <td><input type="email" id="email-{$u.id}" value="{$u.email}" style="width:100%; padding:4px;"></td>
            <td>
                <button type="button" onclick="saveUser({$u.id})" style="background:#28a745; color:#fff; border:none; padding:4px 8px; cursor:pointer; border-radius:4px;">💾</button>
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>