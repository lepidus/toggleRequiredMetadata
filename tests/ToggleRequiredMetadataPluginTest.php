<?php

namespace APP\plugins\generic\toggleRequiredMetadata\tests;

use Illuminate\Support\Collection;
use PKP\tests\PKPTestCase;
use PKP\components\forms\publication\ContributorForm;
use PKP\components\forms\FormComponent;
use APP\author\Author;
use APP\publication\Publication;
use APP\submission\Submission;
use PKP\components\forms\FieldText;
use APP\journal\Journal;
use APP\core\Request;
use PKP\plugins\Hook;
use APP\plugins\generic\toggleRequiredMetadata\ToggleRequiredMetadataPlugin;

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

    private function createPluginWithSetting(mixed $value): ToggleRequiredMetadataPlugin
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
     * Reading a requirement used to write the default back, on every page that
     * asked for it, which is a write on a path that only reads. On this line it
     * also runs from the plugin's own register(), so on every page at all.
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

    private function createAuthor(string $givenName): Author
    {
        $author = new Author();
        $author->setGivenName($givenName, 'en');

        return $author;
    }

    public function testAnswersTheSettingsRequestWithAnErrorOutsideAContext(): void
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getContext', 'getUserVar'])
            ->getMock();
        $request->method('getContext')->willReturn(null);
        $request->method('getUserVar')->willReturnMap([['verb', 'settings']]);

        $response = (new ToggleRequiredMetadataPlugin())->manage([], $request);

        $this->assertFalse($response->getStatus());
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
            ->onlyMethods(['shouldRequireField', 'isOrcidProfilePluginEnabled'])
            ->getMock();
        $plugin->method('shouldRequireField')
            ->willReturnCallback(function (string $setting, ?int $contextId = null) use (&$contextIds) {
                $contextIds[] = $contextId;

                return false;
            });
        $plugin->method('isOrcidProfilePluginEnabled')->willReturn(true);

        $this->validateSubmissionOf($plugin, [$this->createAuthor('Ana')]);

        $this->assertSame([1, 1, 1], $contextIds);
    }

    /**
     * The review step hides the list these errors are rendered in when there is
     * no contributor, so reporting a requirement would disable the submission
     * with nothing on screen to explain what for. The application has its own
     * warning for an empty contributor list.
     */
    public function testReportsNothingWhenThereAreNoContributorsAtAll(): void
    {
        $plugin = $this->getMockBuilder(ToggleRequiredMetadataPlugin::class)
            ->onlyMethods(['shouldRequireField', 'isOrcidProfilePluginEnabled'])
            ->getMock();
        $plugin->method('shouldRequireField')->willReturn(true);
        $plugin->method('isOrcidProfilePluginEnabled')->willReturn(true);

        $errors = $this->validateSubmissionOf($plugin, []);

        $this->assertSame([], $errors);
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
