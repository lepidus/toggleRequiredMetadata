<?php

import('plugins.generic.toggleRequiredMetadata.ToggleRequiredMetadataPlugin');
import('classes.article.Author');
import('classes.publication.Publication');
import('classes.submission.Submission');
import('classes.core.Request');
import('lib.pkp.classes.form.Form');

use PHPUnit\Framework\TestCase;

class ToggleRequiredMetadataPluginTest extends TestCase
{
    private function createPluginWithSetting($value)
    {
        $plugin = $this->getMockBuilder(ToggleRequiredMetadataPlugin::class)
            ->onlyMethods(['getSetting', 'updateSetting'])
            ->getMock();
        $plugin->method('getSetting')->willReturn($value);

        return $plugin;
    }

    public function testDoesNotRequireTheMetadataUntilTheJournalChoosesIt(): void
    {
        $plugin = $this->createPluginWithSetting(null);

        $this->assertFalse($plugin->shouldRequireField('requireAffiliation', 1));
    }

    public function testDoesNotRequireTheMetadataOnceTheRequirementWasTurnedOff(): void
    {
        $plugin = $this->createPluginWithSetting('');

        $this->assertFalse($plugin->shouldRequireField('requireAffiliation', 1));
    }

    public function testRequiresTheMetadataWhileTheRequirementIsTurnedOn(): void
    {
        $plugin = $this->createPluginWithSetting('on');

        $this->assertTrue($plugin->shouldRequireField('requireAffiliation', 1));
    }

    /**
     * Reading a requirement used to write the default back. On this line
     * register() asks for one on every page of the site, so that was a write
     * on every page too.
     */
    public function testDoesNotWriteTheDefaultWhileReadingIt(): void
    {
        $plugin = $this->createPluginWithSetting(null);
        $plugin->expects($this->never())->method('updateSetting');

        $plugin->shouldRequireField('requireAffiliation', 1);
    }

    public function testLooksARequirementUpOnlyOncePerRequest(): void
    {
        $plugin = $this->getMockBuilder(ToggleRequiredMetadataPlugin::class)
            ->onlyMethods(['getSetting'])
            ->getMock();
        $plugin->expects($this->once())->method('getSetting')->willReturn('on');

        $plugin->shouldRequireField('requireAffiliation', 1);
        $plugin->shouldRequireField('requireAffiliation', 1);
    }

    public function testAnswersNothingIsRequiredWithoutAContext(): void
    {
        $plugin = $this->createPluginWithSetting('on');

        $this->assertFalse($plugin->shouldRequireField('requireAffiliation', null));
    }

    private function createRequest(?string $verb)
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getContext', 'getUserVar'])
            ->getMock();
        $request->method('getContext')->willReturn(null);
        $request->method('getUserVar')->willReturnMap([['verb', $verb]]);

        return $request;
    }

    public function testAnswersTheSettingsRequestWithAnErrorOutsideAContext(): void
    {
        $response = (new ToggleRequiredMetadataPlugin())->manage([], $this->createRequest('settings'));

        $this->assertFalse($response->getStatus());
    }

    public function testHandsAnyOtherRequestToTheApplication(): void
    {
        $this->expectExceptionMessage('Unhandled management action!');

        (new ToggleRequiredMetadataPlugin())->manage([], $this->createRequest('unknown'));
    }

    private function createAuthor(string $givenName, bool $authenticated = false, bool $requested = false): Author
    {
        $author = new Author();
        $author->setGivenName($givenName, 'en_US');

        if ($authenticated) {
            $author->setData('orcid', 'https://orcid.org/0000-0002-1825-0097');
            $author->setData('orcidAccessToken', 'f6e5d4c3b2a1');
        }

        if ($requested) {
            $author->setData('orcidEmailToken', 'a1b2c3d4e5f6');
        }

        return $author;
    }

    private function createSubmissionWithAuthors(array $authors, int $status = STATUS_QUEUED): Submission
    {
        $publication = new Publication();
        $publication->setId(1);
        $publication->setData('authors', $authors);

        $submission = new Submission();
        $submission->setData('status', $status);
        $submission->setData('currentPublicationId', 1);
        $submission->setData('publications', [$publication]);

        return $submission;
    }

    private function namesIn(array $authors): array
    {
        return array_map(function (Author $author) {
            return $author->getGivenName('en_US');
        }, $authors);
    }

    public function testWarnsAboutTheContributorsNobodyAskedForTheOrcidAuthorization(): void
    {
        $warning = (new ToggleRequiredMetadataPlugin())->describeOrcidWarning($this->createSubmissionWithAuthors([
            $this->createAuthor('Ana', false, true),
            $this->createAuthor('Bruno'),
        ]));

        $this->assertSame(['Bruno'], $this->namesIn($warning['notRequested']));
        $this->assertSame(['Ana'], $this->namesIn($warning['awaitingAuthorization']));
    }

    public function testLeavesOutOfTheWarningAStateNobodyIsIn(): void
    {
        $warning = (new ToggleRequiredMetadataPlugin())->describeOrcidWarning($this->createSubmissionWithAuthors([
            $this->createAuthor('Ana', false, true),
        ]));

        $this->assertSame(['awaitingAuthorization'], array_keys($warning));
    }

    public function testDoesNotWarnOnceTheRequirementIsMet(): void
    {
        $warning = (new ToggleRequiredMetadataPlugin())->describeOrcidWarning($this->createSubmissionWithAuthors([
            $this->createAuthor('Ana', true),
            $this->createAuthor('Bruno', false, true),
        ]));

        $this->assertSame([], $warning);
    }

    public function testDoesNotWarnOnAPublishedSubmission(): void
    {
        $warning = (new ToggleRequiredMetadataPlugin())->describeOrcidWarning(
            $this->createSubmissionWithAuthors([$this->createAuthor('Ana')], STATUS_PUBLISHED)
        );

        $this->assertSame([], $warning);
    }

    public function testDoesNotWarnOnADeclinedSubmission(): void
    {
        $warning = (new ToggleRequiredMetadataPlugin())->describeOrcidWarning(
            $this->createSubmissionWithAuthors([$this->createAuthor('Ana')], STATUS_DECLINED)
        );

        $this->assertSame([], $warning);
    }

    public function testDoesNotWarnOnAScheduledSubmission(): void
    {
        $warning = (new ToggleRequiredMetadataPlugin())->describeOrcidWarning(
            $this->createSubmissionWithAuthors([$this->createAuthor('Ana')], STATUS_SCHEDULED)
        );

        $this->assertSame([], $warning);
    }

    public function testDoesNotWarnWithoutContributors(): void
    {
        $warning = (new ToggleRequiredMetadataPlugin())->describeOrcidWarning($this->createSubmissionWithAuthors([]));

        $this->assertSame([], $warning);
    }

    private function createPluginRequiringOnlyAffiliation(bool $contributorsSlotIsFree)
    {
        $plugin = $this->getMockBuilder(ToggleRequiredMetadataPlugin::class)
            ->onlyMethods(['shouldRequireField', 'isOrcidProfilePluginEnabled', 'isContributorsSlotFree', 'renderMissingMetadata'])
            ->getMock();
        $plugin->method('shouldRequireField')->willReturnCallback(function ($setting) {
            return $setting === 'requireAffiliation';
        });
        $plugin->method('isOrcidProfilePluginEnabled')->willReturn(false);
        $plugin->method('isContributorsSlotFree')->willReturn($contributorsSlotIsFree);
        $plugin->method('renderMissingMetadata')->willReturnCallback(function (array $missingMetadata) {
            return implode('|', $missingMetadata);
        });

        return $plugin;
    }

    private function validateStep3With($plugin)
    {
        $form = new class () extends Form {
            public $submission;
        };
        $form->submission = $this->createSubmissionWithAuthors([$this->createAuthor('Ana')]);

        $plugin->addValidationToStep3('submissionsubmitstep3form::validate', [&$form]);

        return $form;
    }

    public function testListsTheMissingMetadataBesideTheContributors(): void
    {
        $form = $this->validateStep3With($this->createPluginRequiringOnlyAffiliation(true));

        $this->assertStringContainsString(
            __('plugins.generic.toggleRequiredMetadata.stepValidation.error.affiliation'),
            $form->getData('additionalContributorsFields')
        );
    }

    public function testListsTheMissingMetadataAfterTheMetadataWhereTheSlotIsTaken(): void
    {
        $plugin = $this->createPluginRequiringOnlyAffiliation(false);
        $form = $this->validateStep3With($plugin);

        $output = '';
        $plugin->listMissingMetadataAfterMetadataFields(
            'Templates::Submission::SubmissionMetadataForm::AdditionalMetadata',
            [[], null, &$output]
        );

        $this->assertNull($form->getData('additionalContributorsFields'));
        $this->assertStringContainsString(
            __('plugins.generic.toggleRequiredMetadata.stepValidation.error.affiliation'),
            $output
        );
    }

    public function testStillBlocksTheStepWhereverTheListGoes(): void
    {
        $form = $this->validateStep3With($this->createPluginRequiringOnlyAffiliation(false));

        $this->assertFalse($form->isValid());
    }
}
