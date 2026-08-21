<?php

/**
 * @file plugins/generic/toggleRequiredMetadata/classes/FieldRequiredMark.php
 *
 * Copyright (c) 2022-2026 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @class FieldRequiredMark
 * @ingroup plugins_generic_toggleRequiredMetadata
 *
 * @brief Marks the field that follows it as required, which some fields cannot
 *  be told to do themselves: they render their own label and ignore the
 *  configured one.
 */

namespace APP\plugins\generic\toggleRequiredMetadata\classes;

use PKP\components\forms\Field;

class FieldRequiredMark extends Field
{
    /** @copydoc Field::$component */
    public $component = 'trm-required-mark';

    /** @copydoc Field::$isInert */
    public bool $isInert = true;

    /** Names the marked field in the CSS class, so the mark can be styled and found per field. */
    public string $markedField = '';

    /** Announced to screen readers, so the requirement is not only an asterisk. */
    public string $requiredLabel = '';

    /**
     * @copydoc Field::getConfig()
     */
    public function getConfig()
    {
        $config = parent::getConfig();

        $config['markedField'] = $this->markedField;
        $config['requiredLabel'] = $this->requiredLabel;

        return $config;
    }
}
