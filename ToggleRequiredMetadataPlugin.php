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
use APP\template\TemplateManager;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\plugins\PluginRegistry;
use PKP\plugins\Hook;
use PKP\db\DAORegistry;
use PKP\config\Config;
use PKP\form\validation\FormValidatorLocale;
use PKP\components\forms\FormComponent;

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
            Hook::add('authorform::display', array($this, 'editAuthorFormTemplate'));
            Hook::register('Form::config::before', array($this, 'editAuthorFormDataFields'));
            if ($this->shouldRequireField("requireBiography")) {
                Hook::add('authorform::Constructor', array($this, 'validateBiography'));
            }
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

    public function validateBiography($hookName, $params)
    {
        $authorForm = & $params[0];
        $authorForm->addCheck(new FormValidatorLocale($authorForm, 'biography', 'required', 'plugins.generic.toggleRequiredMetadata.error'));
    }

    public function editAuthorFormTemplate($hookName, $params)
    {
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->registerFilter("output", array($this, 'biographyFilter'));
        $templateMgr->registerFilter("output", array($this, 'affiliationFilter'));
        $templateMgr->registerFilter("output", array($this, 'orcidFilter'));

        return false;
    }

    public function editAuthorFormDataFields(string $hookName, FormComponent $form)
    {
        if (!defined('FORM_CONTRIBUTOR') || $form->id !== FORM_CONTRIBUTOR) {
            return;
        }

        if($this->shouldRequireField("requireOrcid") and !$this->isOrcidProfilePluginEnabled()) {
            $orcidField = $form->getField('orcid');
            $orcidField->isRequired = true;

            $form->removeField('orcid');
            $form->addField($orcidField, [FIELD_POSITION_AFTER, 'url']);
        }

        if($this->shouldRequireField("requireBiography")) {
            $biographyField = $form->getField('biography');
            $biographyField->isRequired = true;

            $form->removeField('biography');
            $form->addField($biographyField, [FIELD_POSITION_AFTER, 'orcid']);
        }

        if($this->shouldRequireField("requireAffiliation")) {
            $affiliationField = $form->getField('affiliation');
            $affiliationField->isRequired = true;

            $form->removeField('affiliation');
            $form->addField($affiliationField, [FIELD_POSITION_AFTER, 'biography']);
        }

        return false;
    }

    public function toggleRequiredField($output, $templateMgr, $fieldName, $tag)
    {
        $selectFieldInput = '/<' . $tag . '[^>]+id="' . $fieldName . '[^>]*>/';
        if (preg_match_all($selectFieldInput, $output, $matches, PREG_OFFSET_CAPTURE)) {
            $output = $this->setRequiredOnInputFields($output, $matches, $tag);
            $output = $this->addRequiredFieldSpanToLabel($output);
            $templateMgr->unregisterFilter('output', array($this, $fieldName . "Filter"));
        }
        return $output;
    }

    public function affiliationFilter($output, $templateMgr)
    {
        if ($this->shouldRequireField("requireAffiliation")) {
            return $this->toggleRequiredField($output, $templateMgr, "affiliation", "input");
        }
        return $output;
    }

    public function biographyFilter($output, $templateMgr)
    {
        if ($this->shouldRequireField("requireBiography")) {
            return $this->toggleRequiredField($output, $templateMgr, "biography", "textarea");
        }
        return $output;
    }

    public function orcidFilter($output, $templateMgr)
    {
        if ($this->shouldRequireField("requireOrcid") and !$this->isOrcidProfilePluginEnabled()) {
            return $this->toggleRequiredField($output, $templateMgr, "orcid", "input");
        }
        return $output;
    }

    private function setRequiredOnInputFields($output, $inputFieldMatches, $tag)
    {
        $timesEditedOutput = 0;
        foreach ($inputFieldMatches[0] as $match) {
            $matchedText = $match[0];
            $posMatch = $match[1];

            $requiredParam = " required=\"required\" ";
            $inputTagStart = "<" . $tag . " ";
            $editionsOffset = $timesEditedOutput * strlen($requiredParam);

            $output = substr_replace($output, $requiredParam, $posMatch + strlen($inputTagStart) + $editionsOffset, 0);
            $timesEditedOutput++;
        }

        return $output;
    }

    private function addRequiredFieldSpanToLabel($output)
    {
        $selectFieldLabelOpening = "/<label *class=\"sub_label\" *for=\"[^>]+>/";
        preg_match($selectFieldLabelOpening, $output, $matches, PREG_OFFSET_CAPTURE);
        $posStartContentLabel = $matches[0][1] + strlen($matches[0][0]);

        $selectFieldLabelEnding = '/<\/label>/';
        preg_match($selectFieldLabelEnding, $output, $matches, PREG_OFFSET_CAPTURE, $posStartContentLabel);
        $posEndContentLabel = $matches[0][1];

        $requiredFieldSpan = "<span class=\"req\">*</span> ";
        return substr_replace($output, $requiredFieldSpan, $posEndContentLabel, 0);
    }

    public function getActions($request, $actionArgs)
    {
        $router = $request->getRouter();
        import('lib.pkp.classes.linkAction.request.AjaxModal');
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

    public function shouldRequireField($settingName)
    {
        $contextId = Application::get()->getRequest()->getContext()->getId();
        if (!$this->settingExists($contextId, $settingName)) {
            $this->updateSetting($contextId, $settingName, 'on');
        };
        return $this->getSetting($contextId, $settingName);
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

        if(is_null($orcidProfilePlugin)) {
            return false;
        }

        return $orcidProfilePlugin->getEnabled();
    }
}
