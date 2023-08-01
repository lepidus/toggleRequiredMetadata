<?php

/**
 * @file plugins/generic/toggleRequiredMetadata/ToggleRequiredMetadataSettingsForm.php
 *
 * Copyright (c) 2022 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @class ToggleRequiredMetadataSettingsForm
 * @ingroup plugins_generic_toggleRequiredMetadata
 */

namespace APP\plugins\generic\toggleRequiredMetadata\form;

use APP\template\TemplateManager;
use PKP\form\Form;
use PKP\form\validation\FormValidatorCSRF;
use APP\plugins\generic\toggleRequiredMetadata\ToggleRequiredMetadataPlugin;

class ToggleRequiredMetadataSettingsForm extends Form
{
    public ToggleRequiredMetadataPlugin $plugin;
    public $contextId;

    public function __construct(ToggleRequiredMetadataPlugin $plugin, int $contextId)
    {
        parent::__construct($plugin->getTemplateResource("settingsForm.tpl"));
        $this->contextId = $contextId;
        $this->plugin = $plugin;
        $this->addCheck(new FormValidatorCSRF($this));
    }

    public function fetch($request, $template = null, $display = false)
    {
        $orcidProfilePluginEnabled = $this->plugin->isOrcidProfilePluginEnabled();
        $requireOrcid = !$orcidProfilePluginEnabled
            ? $this->plugin->shouldRequireField("requireOrcid")
            : null;
        $requireAffiliation = $this->plugin->shouldRequireField("requireAffiliation");
        $requireBiography = $this->plugin->shouldRequireField("requireBiography");

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign(array(
            "orcidProfilePluginEnabled" => $orcidProfilePluginEnabled,
            "pluginName" => $this->plugin->getName(),
            "requireOrcid" => $requireOrcid,
            "requireAffiliation" => $requireAffiliation,
            "requireBiography" => $requireBiography
        ));

        return parent::fetch($request, $template, $display);
    }

    public function readInputData()
    {
        $this->readUserVars(["requireOrcid", "requireAffiliation", "requireBiography"]);
    }

    public function execute(...$functionArgs)
    {
        $this->updatePluginSettings("requireOrcid");
        $this->updatePluginSettings("requireAffiliation");
        $this->updatePluginSettings("requireBiography");
        parent::execute(...$functionArgs);
    }

    private function updatePluginSettings($setting)
    {
        $this->plugin->updateSetting(
            $this->contextId,
            $setting,
            $this->getData($setting)
        );
    }
}
