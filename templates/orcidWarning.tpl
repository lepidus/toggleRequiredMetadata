{* v-pre: the page is an in-DOM Vue template, so {{ }} in a name would be evaluated *}
<notification type="warning" style="margin-bottom: 2rem;">
    <span v-pre>{$orcidWarningMessage|escape}</span>
</notification>
