{* v-pre: the page is an in-DOM Vue template, so {{ }} in a name would be evaluated *}
<notification type="warning" style="margin-bottom: 2rem;">
    {foreach from=$orcidWarningMessages item=orcidWarningMessage name=orcidWarning}
        <p class="trmOrcidWarning__line" style="margin: {if $smarty.foreach.orcidWarning.first}0{else}0.5rem 0 0{/if};"><span v-pre>{$orcidWarningMessage|escape}</span></p>
    {/foreach}
</notification>
