<?php

namespace APP\plugins\generic\toggleRequiredMetadata\tests;

use PKP\tests\PKPTestCase;
use PKP\components\forms\publication\ContributorForm;
use PKP\components\forms\FormComponent;
use APP\submission\Submission;
use PKP\components\forms\FieldText;
use APP\journal\Journal;
use PKP\plugins\Hook;

class ToggleRequiredMetadataPluginTest extends PKPTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Hook::register('Form::config::before', array($this, 'editAuthorFormDataFields'));
    }

    public function editAuthorFormDataFields(string $hookName, FormComponent $form): void
    {
        if (!defined('FORM_CONTRIBUTOR') || $form->id !== FORM_CONTRIBUTOR) {
            return;
        }
        $form->removeField('biography');

        $form->addField(new FieldText('biography', [
            'label' => __('user.biography'),
            'isMultilingual' => true,
            'isRequired' => true,
        ]));
    }

    public function createSubmission()
    {
        $submission = new Submission();

        return $submission;
    }

    public function createContext()
    {
        $context = new Journal();
        $context->setId(1);

        return $context;
    }

    public function testBiographyRequiredField()
    {
        $form = new ContributorForm(
            'url',
            [],
            $this->createSubmission(),
            $this->createContext()
        );

        $formConfig = $form->getConfig();

        $biographyField = $form->getField('biography');

        $this->assertTrue($biographyField->isRequired);
    }
}
