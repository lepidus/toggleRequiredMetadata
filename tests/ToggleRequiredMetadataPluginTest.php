<?php

namespace APP\plugins\generic\toggleRequiredMetadata\tests;

use Illuminate\Support\Collection;
use PKP\tests\PKPTestCase;
use APP\author\Author;
use APP\publication\Publication;
use APP\submission\Submission;
use PKP\components\forms\FieldOrcid;
use PKP\components\forms\publication\ContributorForm;
use APP\core\Application;
use APP\core\Request;
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

    private function createPluginRequiringOnlyOrcid(): ToggleRequiredMetadataPlugin
    {
        $plugin = $this->getMockBuilder(ToggleRequiredMetadataPlugin::class)
            ->onlyMethods(['shouldRequireField', 'isOrcidEnabled'])
            ->getMock();
        $plugin->method('shouldRequireField')
            ->willReturnCallback(fn (string $setting) => $setting === 'requireOrcid');
        $plugin->method('isOrcidEnabled')->willReturn(true);

        return $plugin;
    }

    private function createAuthor(string $fullName, bool $verified = false, bool $requested = false): Author
    {
        $author = new Author();
        $author->setGivenName($fullName, 'en');
        $author->setData('orcidVerificationRequested', $requested);

        if ($verified) {
            $author->setData('orcid', 'https://orcid.org/0000-0002-1825-0097');
            $author->setOrcidVerified(true);
        }

        return $author;
    }

    private function validateSubmissionOf(ToggleRequiredMetadataPlugin $plugin, array $authors): array
    {
        $publication = new Publication();
        $publication->setId(1);
        $publication->setData('authors', new Collection($authors));

        $submission = new Submission();
        $submission->setData('currentPublicationId', 1);
        $submission->setData('publications', [$publication]);

        $errors = [];
        $plugin->validateSubmissionFields('Submission::validateSubmit', [&$errors, $submission, $this->createContext()]);

        return $errors;
    }

    /**
     * The requirements are about the submission being validated, so they are
     * answered for the context the hook hands over and not for the one the
     * request happens to carry.
     */
    public function testChecksTheRequirementsAgainstTheContextBeingValidated(): void
    {
        $contextIds = [];
        $plugin = $this->getMockBuilder(ToggleRequiredMetadataPlugin::class)
            ->onlyMethods(['shouldRequireField', 'isOrcidEnabled'])
            ->getMock();
        $plugin->method('shouldRequireField')
            ->willReturnCallback(function (string $setting, ?int $contextId = null) use (&$contextIds) {
                $contextIds[] = $contextId;

                return false;
            });
        $plugin->method('isOrcidEnabled')->willReturn(true);

        $this->validateSubmissionOf($plugin, [$this->createAuthor('Ana')]);

        $this->assertSame([1, 1, 1], $contextIds);
    }

    public function testReportsTheOrcidRequirementUnderItsOwnKey(): void
    {
        $errors = $this->validateSubmissionOf($this->createPluginRequiringOnlyOrcid(), [
            $this->createAuthor('Ana'),
        ]);

        $this->assertArrayHasKey(ToggleRequiredMetadataPlugin::ORCID_REQUIREMENT, $errors);
        $this->assertArrayNotHasKey('contributors', $errors);
    }

    public function testDescribesWhereEachContributorStandsOnOrcid(): void
    {
        $errors = $this->validateSubmissionOf($this->createPluginRequiringOnlyOrcid(), [
            $this->createAuthor('Ana', verified: true),
            $this->createAuthor('Bruno', requested: true),
            $this->createAuthor('Carla'),
        ]);

        $statuses = array_column($errors[ToggleRequiredMetadataPlugin::ORCID_REQUIREMENT]['contributors'], 'status');

        $this->assertSame(['verified', 'awaitingVerification', 'notRequested'], $statuses);
    }

    /**
     * One verified iD is enough, so the requirement is met even while the
     * others are only waiting for their own verification.
     */
    public function testReportsNothingOnceOneContributorVerifiedAndTheRestWereAsked(): void
    {
        $errors = $this->validateSubmissionOf($this->createPluginRequiringOnlyOrcid(), [
            $this->createAuthor('Ana', verified: true),
            $this->createAuthor('Bruno', requested: true),
        ]);

        $this->assertSame([], $errors);
    }

    /**
     * The review step hides the list the block is rendered in when there is no
     * contributor, so reporting the requirement would disable the submission
     * with nothing on screen to explain what for. The application has its own
     * warning for an empty contributor list.
     */
    public function testReportsNothingWhenThereAreNoContributorsAtAll(): void
    {
        $errors = $this->validateSubmissionOf($this->createPluginRequiringOnlyOrcid(), []);

        $this->assertSame([], $errors);
    }

    public function testAnswersTheSettingsRequestWithAnErrorOutsideAContext(): void
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getContext', 'getUserVar'])
            ->getMock();
        $request->method('getContext')->willReturn(null);
        $request->method('getUserVar')->willReturnMap([['verb', 'settings']]);

        $response = $this->createPluginRequiring(true)->manage([], $request);

        $this->assertFalse($response->getStatus());
    }

    public function testReportsTheRequirementWhileNobodyVerified(): void
    {
        $errors = $this->validateSubmissionOf($this->createPluginRequiringOnlyOrcid(), [
            $this->createAuthor('Ana', requested: true),
            $this->createAuthor('Bruno', requested: true),
        ]);

        $this->assertArrayHasKey(ToggleRequiredMetadataPlugin::ORCID_REQUIREMENT, $errors);
    }

    private function createPluginWithSetting(bool $exists, mixed $value = null): ToggleRequiredMetadataPlugin
    {
        $plugin = $this->getMockBuilder(ToggleRequiredMetadataPlugin::class)
            ->onlyMethods(['settingExists', 'getSetting', 'updateSetting'])
            ->getMock();
        $plugin->method('settingExists')->willReturn($exists);
        $plugin->method('getSetting')->willReturn($value);

        return $plugin;
    }

    public function testRequiresTheMetadataUntilTheJournalChoosesOtherwise(): void
    {
        $plugin = $this->createPluginWithSetting(false);

        $this->assertTrue($plugin->shouldRequireField('requireAffiliation', 1));
    }

    public function testDoesNotRequireTheMetadataOnceTheRequirementWasTurnedOff(): void
    {
        $plugin = $this->createPluginWithSetting(true, null);

        $this->assertFalse($plugin->shouldRequireField('requireAffiliation', 1));
    }

    public function testRequiresTheMetadataWhileTheRequirementIsTurnedOn(): void
    {
        $plugin = $this->createPluginWithSetting(true, 'on');

        $this->assertTrue($plugin->shouldRequireField('requireAffiliation', 1));
    }

    /**
     * Reading a requirement used to write the default back, on every page that
     * asked for it, which is a write on a path that only reads.
     */
    public function testDoesNotWriteTheDefaultWhileReadingIt(): void
    {
        $plugin = $this->createPluginWithSetting(false);
        $plugin->expects($this->never())->method('updateSetting');

        $plugin->shouldRequireField('requireAffiliation', 1);
    }

    public function testLooksARequirementUpOnlyOncePerRequest(): void
    {
        $plugin = $this->getMockBuilder(ToggleRequiredMetadataPlugin::class)
            ->onlyMethods(['settingExists', 'getSetting'])
            ->getMock();
        $plugin->expects($this->once())->method('settingExists')->willReturn(true);
        $plugin->method('getSetting')->willReturn('on');

        $plugin->shouldRequireField('requireAffiliation', 1);
        $plugin->shouldRequireField('requireAffiliation', 1);
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
