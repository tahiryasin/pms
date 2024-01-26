{$subject}
================================================================================
{notification_logo}

{if $payload}
    <p>Hi,</p>
    <p>You have requested cancellation of your ActiveCollab account.</p>
    <p>You can confirm the request by clicking on <a href="{$payload.link}">this link</a></p>
    <p>If you don't want to cancel your account, just ignore this email.</p>
    <p>If you didn't request this change, you may want to contact our support.</p>
{/if}