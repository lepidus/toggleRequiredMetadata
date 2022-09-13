<?php

/**
 * @file plugins/generic/toggleRequiredMetadata/ToggleRequiredMetadataSettingsForm.inc.php
 *
 * Copyright (c) 2022 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @class ToggleRequiredMetadataSettingsForm
 * @ingroup plugins_generic_toggleRequiredMetadata
 */

import('lib.pkp.classes.form.Form');

class ToggleRequiredMetadataSettingsForm extends Form
{
    public $contextId;
    public $plugin;

    public function __construct($plugin, $contextId)
    {
        $this->contextId = $contextId;
        $this->plugin = $plugin;
        parent::__construct($plugin->getTemplateResource("settingsForm.tpl"));
    }

    public function fetch($request, $template = null, $display = false)
    {
        $orcidProfilePluginEnabled = $this->plugin->isOrcidProfilePluginEnabled();
        $requireOrcid = !$orcidProfilePluginEnabled
            ? $this->plugin->shouldRequireField("requireOrcid")
            : null;
        $requireAffiliation = $this->plugin->shouldRequireField("requireAffiliation");

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign(array(
            "orcidProfilePluginEnabled" => $orcidProfilePluginEnabled,
            "pluginName" => $this->plugin->getName(),
            "requireOrcid" => $requireOrcid,
            "requireAffiliation" => $requireAffiliation
        ));

        return parent::fetch($request, $template, $display);
    }

    public function readInputData()
    {
        $this->readUserVars(["requireOrcid", "requireAffiliation"]);
    }

    public function execute(...$functionArgs)
    {
        $this->updatePluginSettings("requireOrcid");
        $this->updatePluginSettings("requireAffiliation");
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
