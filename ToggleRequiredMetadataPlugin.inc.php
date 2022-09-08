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

import('lib.pkp.classes.plugins.GenericPlugin');

class ToggleRequiredMetadataPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);

        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) {
            return true;
        }
        if ($success && $this->getEnabled($mainContextId)) {
            HookRegistry::register('authorform::display', array($this, 'editAuthorFormTemplate'));
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

    public function editAuthorFormTemplate($hookName, $params)
    {
        $request = PKPApplication::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->registerFilter("output", array($this, 'affiliationFilter'));
        $templateMgr->registerFilter("output", array($this, 'orcidFilter'));

        return false;
    }

    public function toggleRequiredField($output, $templateMgr, $fieldName)
    {
        $selectFieldInput = '/<input[^>]+id="' . $fieldName . '[^>]*>/';
        if (preg_match_all($selectFieldInput, $output, $matches, PREG_OFFSET_CAPTURE)) {
            $output = $this->setRequiredOnInputFields($output, $matches);
            $output = $this->addRequiredFieldSpanToLabel($output);
            $templateMgr->unregisterFilter('output', array($this, $fieldName . "Filter"));
        }
        return $output;
    }

    public function affiliationFilter($output, $templateMgr)
    {
        if ($this->shouldRequireField("requireAffiliation")) {
            return $this->toggleRequiredField($output, $templateMgr, "affiliation");
        }
        return $output;
    }

    public function orcidFilter($output, $templateMgr)
    {
        if ($this->shouldRequireField("requireOrcid")) {
            return $this->toggleRequiredField($output, $templateMgr, "orcid");
        }
        return $output;
    }

    private function setRequiredOnInputFields($output, $inputFieldMatches)
    {
        $timesEditedOutput = 0;
        foreach ($inputFieldMatches[0] as $match) {
            $matchedText = $match[0];
            $posMatch = $match[1];

            $requiredParam = " required=\"true\" ";
            $inputTagStart = "<input ";
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
                $this->import('form.ToggleRequiredMetadataSettingsForm');
                $form = new ToggleRequiredMetadataSettingsForm($this, $context->getId());

                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        return new JSONMessage(true);
                    }
                }

                return new JSONMessage(true, $form->fetch($request));
            default:
                return parent::manage($verb, $args, $message, $messageParams);
        }
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
}
