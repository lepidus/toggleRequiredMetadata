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
			$output = $this->setRequiredOnInputFields($output, $matches);
			$output = $this->addRequiredFieldSpanToLabel($output);
			$templateMgr->unregisterFilter('output', array($this, 'authorAffiliationFormFilter'));
        }
        return $output;
    }

	private function setRequiredOnInputFields($output, $inputFieldMatches) {
		$timesEditedOutput = 0;
		foreach($inputFieldMatches[0] as $match) {
			$matchedText = $match[0];
			$posMatch = $match[1];
			
			$requiredParam = " required=\"true\" ";
			$inputTagStart = "<input " ;
			$editionsOffset = $timesEditedOutput*strlen($requiredParam);
			
			$output = substr_replace($output, $requiredParam, $posMatch + strlen($inputTagStart) + $editionsOffset, 0);
			$timesEditedOutput++;
		}

		return $output;
	}

	private function addRequiredFieldSpanToLabel($output) {
		preg_match('/<label *class="sub_label" *for="affiliation[^>]+>/', $output, $matches, PREG_OFFSET_CAPTURE);
		$posStartContentLabel = $matches[0][1] + strlen($matches[0][0]);

		preg_match('/<\/label>/', $output, $matches, PREG_OFFSET_CAPTURE, $posStartContentLabel);
		$posEndContentLabel = $matches[0][1];

		$requiredFieldSpan = "<span class=\"req\">*</span> ";
		return substr_replace($output, $requiredFieldSpan, $posEndContentLabel, 0);
	}

}
