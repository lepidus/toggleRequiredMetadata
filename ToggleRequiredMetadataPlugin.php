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
use PKP\orcid\OrcidManager;
use APP\template\TemplateManager;
use APP\plugins\generic\toggleRequiredMetadata\classes\FieldAffiliationsRequired;
use APP\plugins\generic\toggleRequiredMetadata\classes\MetadataChecker;
use APP\plugins\generic\toggleRequiredMetadata\form\ToggleRequiredMetadataSettingsForm;

class ToggleRequiredMetadataPlugin extends GenericPlugin
{
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

    public function validateSubmissionFields($hookName, $params)
    {
        $errors = &$params[0];
        $submission = $params[1];
        $publication = $submission->getCurrentPublication();
        $authors = $publication->getData('authors')->toArray();

        $contributorsErrors = $errors['contributors'] ?? [];
        $metadataChecker = new MetadataChecker();

        if ($this->shouldRequireField("requireOrcid") and $this->isOrcidEnabled()) {
            if (!$metadataChecker->checkAnyAuthenticatedOrcid($authors)) {
                $contributorsErrors[] = __('plugins.generic.toggleRequiredMetadata.stepValidation.error.noAuthenticatedOrcid');
            }

            $authorsPendingRequest = $metadataChecker->getAuthorsWithoutRequestedOrcidAuthorization($authors);
            if (!empty($authorsPendingRequest)) {
                $contributorsErrors[] = __(
                    'plugins.generic.toggleRequiredMetadata.stepValidation.error.orcidAuthorization',
                    ['pendingAuthors' => $this->getAuthorNames($authorsPendingRequest)]
                );
            }
        }

        if ($this->shouldRequireField("requireAffiliation") and !$metadataChecker->checkAffiliations($authors)) {
            $contributorsErrors[] = __('plugins.generic.toggleRequiredMetadata.stepValidation.error.affiliation');
        }

        if ($this->shouldRequireField("requireBiography") and !$metadataChecker->checkBiographies($authors)) {
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
            $this->requireAffiliations($form);
        }

        return false;
    }

    /**
     * Marks the affiliations field as required.
     *
     * The field renders its own label and ignores the one configured for it, so
     * the required mark cannot come from the form config. The plugin field added
     * here carries it through a CSS sibling rule, and states the requirement for
     * screen readers.
     *
     * The field is deliberately NOT flagged with `isRequired`: adding an
     * affiliation pushes onto the value in place without emitting a change
     * event (FieldAffiliations.vue, handleInsertToAffiliationsList), so the form
     * never clears an error raised for it, and the save button of the
     * single-page contributor form stays disabled even after the contributor
     * fills the affiliation in. The requirement is enforced when the submission
     * is completed instead.
     */
    private function requireAffiliations(FormComponent $form): void
    {
        if (is_null($form->getField('affiliations'))) {
            return;
        }

        $form->addField(
            new FieldAffiliationsRequired('trmAffiliationsRequired', [
                'requiredLabel' => __('plugins.generic.toggleRequiredMetadata.contributorForm.affiliationRequired'),
            ]),
            [FIELD_POSITION_BEFORE, 'affiliations']
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
        $request = Application::get()->getRequest();
        $basePluginUrl = "{$request->getBaseUrl()}/{$this->getPluginPath()}";

        $templateMgr->addJavaScript(
            'toggleRequiredMetadata',
            "{$basePluginUrl}/public/build/toggleRequiredMetadata.iife.js",
            [
                'inline' => false,
                'contexts' => ['backend'],
                'priority' => TemplateManager::STYLE_SEQUENCE_LAST,
            ]
        );

        $templateMgr->addStyleSheet(
            'toggleRequiredMetadata',
            "{$basePluginUrl}/public/build/toggleRequiredMetadata.css",
            [
                'inline' => false,
                'contexts' => ['backend'],
                'priority' => TemplateManager::STYLE_SEQUENCE_LAST,
            ]
        );
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

    private function getAuthorNames(array $authors): string
    {
        $names = array_map(function ($author) {
            return $author->getFullName();
        }, $authors);

        return implode(', ', $names);
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


    public function shouldRequireField($settingName)
    {
        $context = Application::get()->getRequest()->getContext();
        if (!is_null($context)) {
            $contextId = $context->getId();
            if (!$this->settingExists($contextId, $settingName)) {
                $this->updateSetting($contextId, $settingName, 'on');
            }
            return $this->getSetting($contextId, $settingName);
        }
    }

    public function settingExists($contextId, $name): bool
    {
        $pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
        return $pluginSettingsDao->settingExists($contextId, $this->getName(), $name);
    }

    public function isOrcidEnabled(): bool
    {
        return OrcidManager::isEnabled(Application::get()->getRequest()->getContext());
    }
}
