{$subject}
================================================================================
{notification_logo}

{if $payload}
    <p>{lang language=$language}This is the {$payload.attempts}{if $payload.attempts == 1}st{elseif $payload.attempts == 2}nd{elseif $payload.attempts == 3}rd{elseif $payload.attempts >= 4}th{/if} time we were unsuccessful in processing the payment of your last bill{/lang}.</p>
    <p>{lang language=$language}We’ll keep trying to automatically charge your account for the next {$payload.rest_days} days. At the same time, you can simply <a href="{$payload.link}">log in to your workspace</a> and retry the payment yourself{/lang}.</p>
    <p>ActiveCollab</p>
{/if}
