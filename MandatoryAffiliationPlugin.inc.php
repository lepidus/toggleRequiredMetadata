<?php
/**
 * @file plugins/generic/mandatoryAffiliation/MandatoryAffiliation.inc.php
 *
 * Copyright (c) 2022 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @class MandatoryAffiliation
 * @ingroup plugins_generic_authorVersion
 *
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class mandatoryAffiliation extends GenericPlugin {

	public function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path, $mainContextId);
		
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled($mainContextId)) {

		}
		return $success;
	}

	public function getDisplayName() {
		return __('plugins.generic.mandatoryAffiliation.displayName');
	}

	public function getDescription() {
		return __('plugins.generic.mandatoryAffiliation.description');
	}

}
