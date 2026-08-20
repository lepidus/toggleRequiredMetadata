<?php

namespace APP\plugins\generic\toggleRequiredMetadata\tests;

use PKP\tests\PKPTestCase;
use PKP\components\forms\publication\ContributorForm;
use APP\core\Application;
use PKP\context\Context;
use APP\plugins\generic\toggleRequiredMetadata\ToggleRequiredMetadataPlugin;

class ToggleRequiredMetadataPluginTest extends PKPTestCase
{
    private ContributorForm $contributorForm;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockRequest();
        $this->contributorForm = new ContributorForm('url', [], null, $this->createContext());
    }

    private function createContext(): Context
    {
        $context = Application::getContextDAO()->newDataObject();
        $context->setId(1);

        return $context;
    }

    private function createPluginRequiring(bool $required): ToggleRequiredMetadataPlugin
    {
        $plugin = $this->getMockBuilder(ToggleRequiredMetadataPlugin::class)
            ->onlyMethods(['shouldRequireField'])
            ->getMock();
        $plugin->method('shouldRequireField')->willReturn($required);

        return $plugin;
    }

    public function testMakesBiographyRequired(): void
    {
        $plugin = $this->createPluginRequiring(true);

        $plugin->editAuthorFormDataFields('Form::config::before', $this->contributorForm);

        $this->assertTrue($this->contributorForm->getField('biography')->isRequired);
    }

    public function testMarksTheAffiliationsFieldRightBeforeIt(): void
    {
        $plugin = $this->createPluginRequiring(true);

        $plugin->editAuthorFormDataFields('Form::config::before', $this->contributorForm);

        $fieldNames = array_map(fn ($field) => $field->name, $this->contributorForm->fields);
        $noticePosition = array_search('trmAffiliationsRequired', $fieldNames);
        $affiliationsPosition = array_search('affiliations', $fieldNames);

        $this->assertNotFalse($noticePosition);
        $this->assertSame($affiliationsPosition - 1, $noticePosition);
    }

    /**
     * The field is left alone on purpose: adding an affiliation emits no change
     * event, so an error raised for it is never cleared and the contributor form
     * can no longer be saved.
     */
    public function testLeavesTheAffiliationsFieldSaveable(): void
    {
        $plugin = $this->createPluginRequiring(true);

        $plugin->editAuthorFormDataFields('Form::config::before', $this->contributorForm);

        $this->assertFalse($this->contributorForm->getField('affiliations')->isRequired);
    }

    public function testLeavesFieldsAloneWhenNothingIsRequired(): void
    {
        $plugin = $this->createPluginRequiring(false);

        $plugin->editAuthorFormDataFields('Form::config::before', $this->contributorForm);

        $this->assertFalse($this->contributorForm->getField('biography')->isRequired);
        $this->assertNull($this->contributorForm->getField('trmAffiliationsRequired'));
    }
}
