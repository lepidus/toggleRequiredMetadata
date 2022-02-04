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

class MandatoryAffiliationPlugin extends GenericPlugin {

	public function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path, $mainContextId);
		
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled($mainContextId)) {
			HookRegistry::register('authorform::display', array($this, 'editAuthorFormTemplate'));
		}
		return $success;
	}

	public function getDisplayName() {
		return __('plugins.generic.mandatoryAffiliation.displayName');
	}

	public function getDescription() {
		return __('plugins.generic.mandatoryAffiliation.description');
	}

	public function editAuthorFormTemplate($hookName, $params) {
        $request = PKPApplication::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
		$templateMgr->registerFilter("output", array($this, 'authorAffiliationFormFilter'));
		
		return false;
	}

	public function authorAffiliationFormFilter($output, $templateMgr) {
        if (preg_match_all('/<input[^>]+id="affiliation[^>]*>/', $output, $matches, PREG_OFFSET_CAPTURE)) {
			$timesEditedOutput = 0;
			foreach($matches[0] as $match) {
				$matchedText = $match[0];
				$posMatch = $match[1];
				
				$requiredParam = " required=\"true\" ";
				$inputTagStart = "<input " ;
				$additionalEditions = $timesEditedOutput*strlen($requiredParam);
				
				$output = substr_replace($output, $requiredParam, $posMatch + strlen($inputTagStart) + $additionalEditions, 0);
				$timesEditedOutput++;
			}

			error_log($output);
			$templateMgr->unregisterFilter('output', array($this, 'authorAffiliationFormFilter'));
        }
        return $output;
    }

}
