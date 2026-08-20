<?php

/**
 * @file plugins/generic/toggleRequiredMetadata/classes/FieldAffiliationsRequired.php
 *
 * Copyright (c) 2022 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @class FieldAffiliationsRequired
 * @ingroup plugins_generic_toggleRequiredMetadata
 *
 * @brief Marks the affiliations field as required, which the field itself
 *  cannot be told to do: it renders its own label and ignores the configured
 *  one.
 */

namespace APP\plugins\generic\toggleRequiredMetadata\classes;

use PKP\components\forms\Field;

class FieldAffiliationsRequired extends Field
{
    /** @copydoc Field::$component */
    public $component = 'trm-affiliations-required';

    /** @copydoc Field::$isInert */
    public bool $isInert = true;

    /** Announced to screen readers, so the requirement is not only an asterisk. */
    public string $requiredLabel = '';

    /**
     * @copydoc Field::getConfig()
     */
    public function getConfig()
    {
        $config = parent::getConfig();

        $config['requiredLabel'] = $this->requiredLabel;

        return $config;
    }
}
