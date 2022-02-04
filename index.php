<?php
/**
 * @defgroup plugins_generic_mandatoryAffiliation
 */
/**
 * @file plugins/generic/mandatoryAffiliation/index.php
 *
 * Copyright (c) 2022 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @ingroup plugins_generic_mandatoryAffiliation
 * @brief Wrapper for the Mandatory Affiliation plugin.
 *
 */
require_once('MandatoryAffiliationPlugin.inc.php');
return new MandatoryAffiliationPlugin();