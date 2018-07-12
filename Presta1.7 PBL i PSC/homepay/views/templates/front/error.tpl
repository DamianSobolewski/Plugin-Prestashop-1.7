{capture name=path}<a href="{$link->getPageLink('order')}">{l s='Twoje zakupy' mod='homepay'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Płać z Homepay.pl' mod='homepay'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}
<h2>{l s='Podsumowanie zamówienia' mod='homepay'}</h2>
{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}
<div class="paiement_block">
<img src="{$this_path}homepay.png" alt="{l s='Płać z Homepay.pl' mod='homepay'}" style="margin-bottom: 5px" />
<p>
{l s='Poniżej znajduje się podsumowanie Twojego zamówienia.' mod='homepay'}
	<br/>
</p>
<p style="margin-top:20px;">
	{l s='Całkowita kwota do zapłaty: ' mod='homepay'}
		<span id="amount" class="price">{displayPrice price=$total}</span>
</p>
<p>
	<b>{l s='Wystąpił błąd podczas generowania płatności Homepay.pl' mod='homepay'}</b>: {$homepayError}
</p>
<p class="cart_navigation">
	<a href="{$backLink}" class="button_large">{l s='Inne metody płatności' mod='homepay'}</a>
</p>
</div>
