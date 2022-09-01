<?php

/**
 * @file plugins/generic/toggleRequiredMetadata/ToggleRequiredMetadataSettingsFormPlugin.inc.php
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
        parent::__construct($plugin->getTemplateResource('settings.tpl'));
    }

    public function fetch($request, $template = null, $display = false)
    {
        return parent::fetch($request, $template, $display);
    }

    public function readInputData()
    {
    }

    public function execute(...$functionArgs)
    {
        parent::execute(...$functionArgs);
    }
}
