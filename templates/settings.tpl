<script>
	$(function() {ldelim}
		$('#toggleRequiredMetadataSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<div id="plnSettings">
    <div id="description">{translate key="plugins.generic.toggleRequiredMetadata.settings.description"}</div>
    <br>
	<form class="pkp_form" id="toggleRequiredMetadataSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
		{include file="controllers/notification/inPlaceNotification.tpl" notificationId="toggleRequiredMetadataSettingsFormNotification"}

		{fbvFormArea id="toggleRequiredMetadataSettingsFormArea"}
			
            {fbvFormSection title="plugins.generic.toggleRequiredMetadata.requiredMetadata" required="true"}
            {fbvElement type="checkbox" id="requireOrcid" value=$requireOrcid}    
            {fbvElement type="checkbox" id="requireAffiliation" value=$requireOrcid}
            {/fbvFormSection}

			{fbvFormButtons id="toggleRequiredMetadataSettingsFormSubmit" submitText="common.save" hideCancel=false}
		{/fbvFormArea}
	</form>
</div>