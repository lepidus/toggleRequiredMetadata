<?php

/**
 * @file plugins/generic/toggleRequiredMetadata/ToggleRequiredMetadataPlugin.inc.php
 *
 * Copyright (c) 2022 Lepidus Tecnologia
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
use PKP\plugins\PluginRegistry;
use PKP\plugins\Hook;
use PKP\db\DAORegistry;
use PKP\config\Config;
use PKP\components\forms\FormComponent;
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
            Hook::add('TemplateManager::display', [$this, 'addOrcidWarning']);
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

        if ($this->shouldRequireField("requireOrcid")) {
            if ($this->isOrcidProfilePluginEnabled()) {
                if (!$metadataChecker->checkOrcidsOrAuthorizationRequested($authors)) {
                    $contributorsErrors[] = __('plugins.generic.toggleRequiredMetadata.stepValidation.error.orcidAuthorization');
                }
            } elseif (!$metadataChecker->checkOrcids($authors)) {
                $contributorsErrors[] = __('plugins.generic.toggleRequiredMetadata.stepValidation.error.orcid');
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
        if (!defined('FORM_CONTRIBUTOR') || $form->id !== FORM_CONTRIBUTOR) {
            return;
        }

        if ($this->shouldRequireField("requireOrcid") and !$this->isOrcidProfilePluginEnabled()) {
            $orcidField = $form->getField('orcid');
            $orcidField->isRequired = true;

            $form->removeField('orcid');
            $form->addField($orcidField, [FIELD_POSITION_AFTER, 'url']);
        }

        if ($this->shouldRequireField("requireBiography")) {
            $biographyField = $form->getField('biography');
            $biographyField->isRequired = true;

            $form->removeField('biography');
            $form->addField($biographyField, [FIELD_POSITION_AFTER, 'orcid']);
        }

        if ($this->shouldRequireField("requireAffiliation")) {
            $affiliationField = $form->getField('affiliation');
            $affiliationField->isRequired = true;

            $form->removeField('affiliation');
            $form->addField($affiliationField, [FIELD_POSITION_AFTER, 'biography']);
        }

        return false;
    }

    public function addOrcidWarning($hookName, $params)
    {
        $templateMgr = $params[0];
        $template = $params[1];

        if (
            $template != 'workflow/workflow.tpl'
            && $template != 'authorDashboard/authorDashboard.tpl'
        ) {
            return false;
        }

        $submission = $templateMgr->getTemplateVars('submission');
        $publication = $submission->getCurrentPublication();
        $authors = $publication->getData('authors')->toArray();

        $metadataChecker = new MetadataChecker();
        if ($metadataChecker->checkContributorsOrcidAuthentication($submission, $authors)) {
            return false;
        }

        $orcidWarningMessage = $template === 'workflow/workflow.tpl'
            ? __('plugins.generic.toggleRequiredMetadata.notification.workflow.orcidWarning')
            : __('plugins.generic.toggleRequiredMetadata.notification.authorDashboard.orcidWarning');

        $templateMgr->assign('orcidWarningMessage', $orcidWarningMessage);
        $templateMgr->registerFilter('output', [$this, 'orcidWarningFilter']);
    }

    public function orcidWarningFilter($output, $templateMgr)
    {
        if (preg_match('/<tabs[^>]*>/', $output, $matches, PREG_OFFSET_CAPTURE)) {
            $match = $matches[0][0];
            $offset = $matches[0][1];
            $newOutput = substr($output, 0, $offset);
            $newOutput .= $templateMgr->fetch($this->getTemplateResource('orcidWarning.tpl'));
            $newOutput .= substr($output, $offset);
            $output = $newOutput;
            $templateMgr->unregisterFilter('output', [$this, 'orcidWarningFilter']);
        }
        return $output;
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

    public function isOrcidProfilePluginEnabled()
    {
        PluginRegistry::loadCategory('generic');
        $orcidProfilePlugin = PluginRegistry::getPlugin('generic', 'orcidprofileplugin');

        if (is_null($orcidProfilePlugin)) {
            return false;
        }

        return $orcidProfilePlugin->getEnabled();
    }
}
