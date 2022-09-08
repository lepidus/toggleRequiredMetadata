<script>
    $(function() {ldelim}
    $('#toggleRequiredMetadataSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
    {rdelim});
</script>

<div id="plnSettings">
    <div id="description">{translate key="plugins.generic.toggleRequiredMetadata.settings.description"}</div>
    <br>
    <form class="pkp_form" id="toggleRequiredMetadataSettingsForm" method="post"
        action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
        {include file="controllers/notification/inPlaceNotification.tpl" notificationId="toggleRequiredMetadataSettingsFormNotification"}

        {fbvFormArea id="toggleRequiredMetadataSettingsFormArea"}

        {fbvFormSection list="true"}

        {fbvElement type="checkbox" name="requireOrcid" id="requireOrcid" checked=$requireOrcid label="plugins.generic.toggleRequiredMetadata.settings.requireOrcid"}
        {fbvElement type="checkbox" name="requireAffiliation" id="requireAffiliation" checked=$requireAffiliation label="plugins.generic.toggleRequiredMetadata.settings.requireAffiliation"}
        {/fbvFormSection}

        {fbvFormButtons id="toggleRequiredMetadataSettingsFormSubmit" submitText="common.save"}
        {/fbvFormArea}
    </form>
</div>