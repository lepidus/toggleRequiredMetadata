<div class="pkpNotification pkpNotification--warning" style="margin-bottom: 2rem;" v-pre>
    {foreach from=$orcidWarningMessages item=orcidWarningMessage name=orcidWarning}
        <p class="trmOrcidWarning__line" style="margin: {if $smarty.foreach.orcidWarning.first}0{else}0.5rem 0 0{/if};">{$orcidWarningMessage|escape}</p>
    {/foreach}
</div>
