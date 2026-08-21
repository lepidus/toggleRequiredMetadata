<?php

namespace APP\plugins\generic\toggleRequiredMetadata\tests;

use PKP\tests\PKPTestCase;
use PKP\components\forms\FieldOrcid;
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

    private function createPluginRequiring(bool $required, bool $orcidEnabled = false): ToggleRequiredMetadataPlugin
    {
        $plugin = $this->getMockBuilder(ToggleRequiredMetadataPlugin::class)
            ->onlyMethods(['shouldRequireField', 'isOrcidEnabled'])
            ->getMock();
        $plugin->method('shouldRequireField')->willReturn($required);
        $plugin->method('isOrcidEnabled')->willReturn($orcidEnabled);

        return $plugin;
    }

    /**
     * ORCID is not enabled in the test request, so the contributor form leaves
     * the field out. The tests that need it add it where the form would.
     */
    private function addOrcidFieldToContributorForm(): void
    {
        $this->contributorForm->addField(
            new FieldOrcid('orcid', ['label' => 'ORCID iD']),
            [FIELD_POSITION_AFTER, 'url']
        );
    }

    private function positionOfField(string $fieldName): int|false
    {
        $fieldNames = array_map(fn ($field) => $field->name, $this->contributorForm->fields);

        return array_search($fieldName, $fieldNames);
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

        $markPosition = $this->positionOfField('trmRequiredAffiliations');

        $this->assertNotFalse($markPosition);
        $this->assertSame($this->positionOfField('affiliations') - 1, $markPosition);
    }

    public function testMarksTheOrcidFieldRightBeforeIt(): void
    {
        $this->addOrcidFieldToContributorForm();
        $plugin = $this->createPluginRequiring(true, true);

        $plugin->editAuthorFormDataFields('Form::config::before', $this->contributorForm);

        $markPosition = $this->positionOfField('trmRequiredOrcid');

        $this->assertNotFalse($markPosition);
        $this->assertSame($this->positionOfField('orcid') - 1, $markPosition);
    }

    /**
     * With ORCID off the requirement takes no effect, so the field must not be
     * marked even while the setting is kept on.
     */
    public function testLeavesTheOrcidFieldUnmarkedWhenOrcidIsNotEnabled(): void
    {
        $this->addOrcidFieldToContributorForm();
        $plugin = $this->createPluginRequiring(true, false);

        $plugin->editAuthorFormDataFields('Form::config::before', $this->contributorForm);

        $this->assertNull($this->contributorForm->getField('trmRequiredOrcid'));
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
        $this->assertNull($this->contributorForm->getField('trmRequiredAffiliations'));
    }
}
