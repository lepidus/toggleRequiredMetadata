{* One notification per contributor requirement still missing on step 3 *}
<div class="pkp_notification">
	{foreach from=$missingMetadata item=missingMetadataMessage}
		<div class="notifyWarning">
			<span class="description">{$missingMetadataMessage|escape}</span>
		</div>
	{/foreach}
</div>
