{extends file=$layout}
{block name='content'}


<h2>{l s='całkowita cena' mod='homepay'}</h2><h2<strong>{$total} pln</strong></h2>
<img src="{$image}">
<p>
	<b>{l s='Potwierdź zamówienie klikając \'Potwierdzam zamówienie\'' mod='homepay'}.</b>
</p>

<form name="transfer" action="https://homepay.pl/przelew/" method="post">
   {foreach $data as $field => $value}
      <input type="hidden" name="{$field}" value="{$value}" >  
   {/foreach}
      <input type="submit" class="submit_payment"
         style="padding: 10px;
                background: #00b6ce;                
                border: 0px;
                border-radius: 4px;
                color: white;"
         value="Potwierdzam zamówienie" />
</form>
<br>
<form name="PSC" action="https://homepay.pl/paysafecard/" method="post">
   {foreach $dataPSC as $field => $value}
      <input type="hidden" name="{$field}" value="{$value}" >  
   {/foreach}
      <input type="submit" class="submit_payment"
         style="padding: 10px;
                background: #00b6ce;                
                border: 0px;
                border-radius: 4px;
                color: white;"
         value="Płace za pomocą PSC" />
</form>
<br>
<p class="cart_navigation">
	<a href="{$backLink}" class="button_large">{l s='Inne metody płatności' mod='homepay'}</a>
</p>


{/block}