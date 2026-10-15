<?php

/**
 * @file plugins/generic/toggleRequiredMetadata/ToggleRequiredMetadataPlugin.php
 *
 * Copyright (c) 2022-2026 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @class ToggleRequiredMetadataPlugin
 * @ingroup plugins_generic_toggleRequiredMetadata
 *
 */

namespace APP\plugins\generic\toggleRequiredMetadata;

use APP\author\Author;
use APP\core\Application;
use PKP\plugins\GenericPlugin;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\plugins\Hook;
use PKP\db\DAORegistry;
use PKP\config\Config;
use PKP\components\forms\FormComponent;
use PKP\components\forms\publication\ContributorForm;
use PKP\context\Context;
use PKP\orcid\OrcidManager;
use APP\template\TemplateManager;
use APP\plugins\generic\toggleRequiredMetadata\classes\FieldRequiredMark;
use APP\plugins\generic\toggleRequiredMetadata\classes\MetadataChecker;
use APP\plugins\generic\toggleRequiredMetadata\form\ToggleRequiredMetadataSettingsForm;

class ToggleRequiredMetadataPlugin extends GenericPlugin
{
    /**
     * Key the ORCID requirement is reported under when the submission is
     * validated.
     *
     * It is deliberately not `contributors`, which the review step renders as
     * one plain warning per message: the requirement has two halves and reads
     * as two unrelated problems that way. Under a key of its own it still
     * blocks the submission, since the wizard only counts the keys, while the
     * plugin renders it as a single block naming where each contributor stands.
     */
    public const ORCID_REQUIREMENT = 'toggleRequiredMetadataOrcid';

    /** What each requirement answered in this request, by context. */
    private array $requiredFields = [];

    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path);

        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) {
            return true;
        }

        if ($success && $this->getEnabled()) {
            Hook::add('Submission::validateSubmit', [$this, 'validateSubmissionFields']);
            Hook::add('Form::config::before', [$this, 'editAuthorFormDataFields']);
            Hook::add('TemplateManager::display', [$this, 'addBackendAssets']);
            Hook::add('Template::SubmissionWizard::Section::Review::Contributors', [$this, 'addOrcidRequirementToReviewStep']);
        }

        return $success;
    }

    public function getDisplayName()
    {
        return __('plugins.generic.toggleRequiredMetadata.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.toggleRequiredMetadata.description');
    }

    /**
     * The context is the one the hook hands over, and not the one of the
     * request: the submission being validated is what the requirements are
     * about, and reading the request instead leaves them to be silently
     * dropped wherever it carries no context.
     */
    public function validateSubmissionFields($hookName, $params)
    {
        $errors = &$params[0];
        $submission = $params[1];
        $context = $params[2];
        $publication = $submission->getCurrentPublication();
        $authors = $publication->getData('authors')->toArray();

        $contextId = $context->getId();
        $contributorsErrors = $errors['contributors'] ?? [];
        $metadataChecker = new MetadataChecker();

        if ($this->shouldRequireField("requireOrcid", $contextId) && $this->isOrcidEnabled($context)) {
            $orcidRequirement = $this->describeOrcidRequirement($authors);

            if (!is_null($orcidRequirement)) {
                $errors[self::ORCID_REQUIREMENT] = $orcidRequirement;
            }
        }

        if ($this->shouldRequireField("requireAffiliation", $contextId) && !$metadataChecker->checkAffiliations($authors)) {
            $contributorsErrors[] = __('plugins.generic.toggleRequiredMetadata.stepValidation.error.affiliation');
        }

        if ($this->shouldRequireField("requireBiography", $contextId) && !$metadataChecker->checkBiographies($authors)) {
            $contributorsErrors[] = __('plugins.generic.toggleRequiredMetadata.stepValidation.error.biography');
        }

        if (!empty($contributorsErrors)) {
            $errors['contributors'] = $contributorsErrors;
        }
    }

    public function editAuthorFormDataFields(string $hookName, FormComponent $form)
    {
        if ($form->id !== ContributorForm::FORM_CONTRIBUTOR) {
            return;
        }

        if ($this->shouldRequireField("requireBiography")) {
            $biographyField = $form->getField('biography');

            if (!is_null($biographyField)) {
                $biographyField->isRequired = true;
            }
        }

        if ($this->shouldRequireField("requireAffiliation")) {
            $this->markFieldAsRequired($form, 'affiliations', 'plugins.generic.toggleRequiredMetadata.contributorForm.affiliationRequired');
        }

        if ($this->shouldRequireField("requireOrcid") && $this->isOrcidEnabled()) {
            $this->markFieldAsRequired($form, 'orcid', 'plugins.generic.toggleRequiredMetadata.contributorForm.orcidRequired');
        }

        return false;
    }

    /**
     * Marks a field as required, which the affiliations and the ORCID fields
     * cannot be told to do: they render their own label and ignore the one
     * configured for them, so the required mark cannot come from the form
     * config. The plugin field added here carries it through a CSS sibling
     * rule, and states the requirement for screen readers.
     *
     * The marked field is deliberately NOT flagged with `isRequired`. The
     * affiliations field pushes onto its value in place without emitting a
     * change event (FieldAffiliations.vue, handleInsertToAffiliationsList), so
     * the form never clears an error raised for it, and the save button of the
     * single-page contributor form would stay disabled even after the
     * contributor filled the affiliation in. The ORCID field holds no value the
     * contributor can type at all. Both requirements are enforced when the
     * submission is completed instead.
     */
    private function markFieldAsRequired(FormComponent $form, string $fieldName, string $requiredLabelKey): void
    {
        if (is_null($form->getField($fieldName))) {
            return;
        }

        $form->addField(
            new FieldRequiredMark('trmRequired' . ucfirst($fieldName), [
                'markedField' => $fieldName,
                'requiredLabel' => __($requiredLabelKey),
            ]),
            [FIELD_POSITION_BEFORE, $fieldName]
        );
    }

    /**
     * Loads the plugin components on the two pages that mount the contributor
     * form and the workflow: the submission wizard and the dashboard.
     */
    public function addBackendAssets(string $hookName, array $args): bool
    {
        $templateMgr = $args[0];
        $template = $args[1];

        if (!in_array($template, ['submission/wizard.tpl', 'dashboard/editors.tpl'], true)) {
            return false;
        }

        $this->registerVueAssets($templateMgr);

        if ($template === 'dashboard/editors.tpl') {
            $this->addOrcidWarningState($templateMgr);
        }

        return false;
    }

    private function registerVueAssets(TemplateManager $templateMgr): void
    {
        $templateMgr->addJavaScript(
            'toggleRequiredMetadata',
            $this->assetUrl('toggleRequiredMetadata.iife.js'),
            [
                'inline' => false,
                'contexts' => ['backend'],
                'priority' => TemplateManager::STYLE_SEQUENCE_LAST,
            ]
        );

        $templateMgr->addStyleSheet(
            'toggleRequiredMetadata',
            $this->assetUrl('toggleRequiredMetadata.css'),
            [
                'inline' => false,
                'contexts' => ['backend'],
                'priority' => TemplateManager::STYLE_SEQUENCE_LAST,
            ]
        );
    }

    /**
     * The URL of a built asset, carrying the version of the plugin.
     *
     * The application would otherwise stamp its own version on it, which does
     * not change when the plugin alone is updated: the browser would go on
     * serving the bundle of the previous release from its cache, and the
     * components and the required marks would quietly be the old ones.
     */
    private function assetUrl(string $fileName): string
    {
        $request = Application::get()->getRequest();
        $url = "{$request->getBaseUrl()}/{$this->getPluginPath()}/public/build/{$fileName}";
        $installedVersion = $this->getCurrentVersion();

        return $installedVersion
            ? "{$url}?v={$installedVersion->getVersionString()}"
            : $url;
    }

    private function addOrcidWarningState(TemplateManager $templateMgr): void
    {
        if (!$this->shouldRequireField("requireOrcid") || !$this->isOrcidEnabled()) {
            return;
        }

        $pageInitConfig = $templateMgr->getState('pageInitConfig');
        if (!is_array($pageInitConfig)) {
            return;
        }

        // Nested under componentForms because the workflow is handed the
        // dashboard component's props, and Vue only passes on declared ones: a
        // key of our own at the top level never reaches the workflow.
        $pageInitConfig['componentForms']['toggleRequiredMetadataOrcidWarning'] = [
            'editorMessage' => __('plugins.generic.toggleRequiredMetadata.notification.workflow.pendingVerification'),
            'authorMessage' => __('plugins.generic.toggleRequiredMetadata.notification.authorDashboard.pendingVerification'),
        ];

        $templateMgr->setState(['pageInitConfig' => $pageInitConfig]);
    }

    /**
     * Describes what the ORCID requirement is still waiting for, or null once
     * it is met.
     *
     * The description carries the rule, how far the contributors are from it
     * and where each of them stands, so that the review step can show it as one
     * block instead of two warnings that read as unrelated problems. It is
     * built here, and not in the browser, because the wizard holds summarized
     * contributors and `orcidVerificationRequested` is not a summary property:
     * the two unverified states cannot be told apart there. The wizard
     * revalidates whenever the review step is opened, so this stays current
     * while the author acts on it.
     *
     * A submission with no contributors at all is left to the application,
     * which has its own warning for it: the review step drops the list this
     * block is rendered in, so a requirement reported here would block the
     * submission with nothing on screen to explain what for.
     */
    private function describeOrcidRequirement(array $authors): ?array
    {
        if (empty($authors)) {
            return null;
        }

        $metadataChecker = new MetadataChecker();
        $verifiedAuthors = $metadataChecker->getAuthorsWithVerifiedOrcid($authors);
        $authorsPendingRequest = $metadataChecker->getAuthorsWithoutRequestedOrcidAuthorization($authors);

        if (!empty($verifiedAuthors) && empty($authorsPendingRequest)) {
            return null;
        }

        return [
            'summary' => __(
                'plugins.generic.toggleRequiredMetadata.stepValidation.orcid.summary',
                ['verified' => count($verifiedAuthors), 'total' => count($authors)]
            ),
            'rule' => __('plugins.generic.toggleRequiredMetadata.stepValidation.orcid.rule'),
            'contributors' => array_map(
                fn (Author $author) => $this->describeAuthorOrcidStatus($author),
                array_values($authors)
            ),
        ];
    }

    private function describeAuthorOrcidStatus(Author $author): array
    {
        if ($author->hasVerifiedOrcid()) {
            $status = 'verified';
        } elseif ($author->getData('orcidVerificationRequested')) {
            $status = 'awaitingVerification';
        } else {
            $status = 'notRequested';
        }

        return [
            'name' => $author->getFullName(),
            'status' => $status,
            'note' => __("plugins.generic.toggleRequiredMetadata.stepValidation.orcid.status.{$status}"),
        ];
    }

    /**
     * Renders the ORCID requirement inside the contributors panel of the review
     * step, where the Edit button that resolves it already is.
     *
     * The hook is called at the end of the contributor list, so the block
     * carries the CSS that lifts it to the top, where the errors the wizard
     * raises itself are: warnings belong together, whichever end they are at.
     *
     * The block is bound to the wizard's own `errors`, so it follows every
     * revalidation without a page reload, and leaves the list empty-handed as
     * soon as the requirement is met.
     */
    public function addOrcidRequirementToReviewStep(string $hookName, array $args): bool
    {
        if (!$this->shouldRequireField("requireOrcid") || !$this->isOrcidEnabled()) {
            return false;
        }

        $requirement = 'errors.' . self::ORCID_REQUIREMENT;
        $args[2] .= "<trm-orcid-requirement :requirement=\"{$requirement}\"></trm-orcid-requirement>";

        return false;
    }

    public function getActions($request, $actionArgs)
    {
        $router = $request->getRouter();
        return array_merge(
            $this->getEnabled() ? array(
                new LinkAction(
                    'settings',
                    new AjaxModal(
                        $router->url(
                            $request,
                            null,
                            null,
                            'manage',
                            null,
                            array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')
                        ),
                        $this->getDisplayName()
                    ),
                    __('manager.plugins.settings'),
                    null
                ),
            ) : array(),
            parent::getActions($request, $actionArgs)
        );
    }

    public function manage($args, $request)
    {
        switch ($request->getUserVar('verb')) {
            case 'settings':
                $context = $request->getContext();
                if (is_null($context)) {
                    return new JSONMessage(false);
                }

                $form = new ToggleRequiredMetadataSettingsForm($this, $context->getId());

                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        return new JSONMessage(true);
                    }
                } else {
                    $form->initData();
                }
                return new JSONMessage(true, $form->fetch($request));
        }
        return parent::manage($args, $request);
    }

    public function getCanEnable()
    {
        return ((bool) Application::get()->getRequest()->getContext());
    }

    public function getCanDisable()
    {
        return ((bool) Application::get()->getRequest()->getContext());
    }


    /**
     * Whether the given metadata is required, in the given context or, for the
     * hooks that only ever run inside one, in the context of the request.
     *
     * Answering is a read, and stays one: the default is not written to the
     * database on the way past. What tells a requirement nobody ever chose
     * from one that was turned off is the absence of the setting, since an
     * unticked checkbox is saved as an empty setting and not as a missing one.
     */
    public function shouldRequireField(string $settingName, ?int $contextId = null): bool
    {
        $contextId ??= Application::get()->getRequest()->getContext()?->getId();

        if (is_null($contextId)) {
            return false;
        }

        return $this->requiredFields[$contextId][$settingName]
            ??= $this->readRequirement($contextId, $settingName);
    }

    private function readRequirement(int $contextId, string $settingName): bool
    {
        if (!$this->settingExists($contextId, $settingName)) {
            return true;
        }

        return (bool) $this->getSetting($contextId, $settingName);
    }

    public function settingExists($contextId, $name): bool
    {
        $pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
        return $pluginSettingsDao->settingExists($contextId, $this->getName(), $name);
    }

    public function isOrcidEnabled(?Context $context = null): bool
    {
        return OrcidManager::isEnabled($context);
    }
}
