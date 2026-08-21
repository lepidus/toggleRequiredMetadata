{literal}
<style>
    .trmOrcidNotice {
        margin: 1rem 0 0.25rem;
        padding: 0.7rem 0.9rem;
        border-left: 3px solid #007ab2;
        background: #f4f8fb;
        font-size: 0.82rem;
        line-height: 1.45;
    }

    .trmOrcidNotice__status {
        display: block;
        margin-bottom: 0.35rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #24303c;
    }

    .trmOrcidNotice__list {
        margin: 0;
        padding-left: 1.1rem;
        color: #3d4b57;
    }

    .trmOrcidNotice__list li + li {
        margin-top: 0.2rem;
    }

    .trmOrcidNotice--disabled {
        border-left-color: #7a8b99;
        background: #f4f6f8;
    }
</style>

<script>
    $(function() {
        $('#toggleRequiredMetadataSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
    });
</script>
{/literal}

<div id="plnSettings">
    <div id="description">{translate key="plugins.generic.toggleRequiredMetadata.settings.description"}</div>
    <br>
    <form class="pkp_form" id="toggleRequiredMetadataSettingsForm" method="post"
        action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
        {csrf}
        {include file="controllers/notification/inPlaceNotification.tpl" notificationId="toggleRequiredMetadataSettingsFormNotification"}

        {fbvFormArea id="toggleRequiredMetadataSettingsFormArea"}

        {fbvFormSection list="true"}

        {fbvElement type="checkbox" name="requireAffiliation" id="requireAffiliation" checked=$requireAffiliation label="plugins.generic.toggleRequiredMetadata.settings.requireAffiliation"}

        <br>
        {fbvElement type="checkbox" name="requireBiography" id="requireBiography" checked=$requireBiography label="plugins.generic.toggleRequiredMetadata.settings.requireBiography"}

        <br>
        {fbvElement type="checkbox" name="requireOrcid" id="requireOrcid" checked=$requireOrcid disabled=$orcidDisabled label="plugins.generic.toggleRequiredMetadata.settings.requireOrcid"}
        {/fbvFormSection}

        <div class="trmOrcidNotice{if !$orcidEnabled} trmOrcidNotice--disabled{/if}" data-cy="trm-orcid-notice">
            {if $orcidEnabled}
                <span class="trmOrcidNotice__status">{translate key="plugins.generic.toggleRequiredMetadata.settings.orcid.enabled.status"}</span>
                <ul class="trmOrcidNotice__list">
                    <li>{translate key="plugins.generic.toggleRequiredMetadata.settings.orcid.enabled.effect.submission"}</li>
                </ul>
            {else}
                <span class="trmOrcidNotice__status">{translate key="plugins.generic.toggleRequiredMetadata.settings.orcid.disabled.status"}</span>
                <ul class="trmOrcidNotice__list">
                    <li>{translate key="plugins.generic.toggleRequiredMetadata.settings.orcid.disabled.effect"}</li>
                </ul>
            {/if}
        </div>

        {fbvFormButtons id="toggleRequiredMetadataSettingsFormSubmit" submitText="common.save"}
        {/fbvFormArea}
    </form>
</div>
