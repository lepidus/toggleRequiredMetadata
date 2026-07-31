<div class="pkp_notification">
	{foreach from=$missingMetadata item=missingMetadataMessage}
		<div class="notifyWarning">
			<span class="description" v-pre>{$missingMetadataMessage|escape}</span>
		</div>
	{/foreach}
</div>
