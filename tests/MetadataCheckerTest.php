<?php

use APP\plugins\generic\toggleRequiredMetadata\classes\MetadataChecker;
use APP\author\Author;
use PKP\affiliation\Affiliation;
use PHPUnit\Framework\TestCase;

class MetadataCheckerTest extends TestCase
{
    private $checker;
    private $authors;
    private $affiliations = ['Harvard', 'UERJ', 'Cambridge'];
    private $biographies = ['I did some things', 'I did more', 'I did nothing'];

    public function setUp(): void
    {
        parent::setUp();
        $this->checker = new MetadataChecker();
        $this->authors = $this->createTestAuthors();
    }

    private function createTestAuthors()
    {
        $authors = [];

        for ($i = 0; $i < count($this->affiliations); $i++) {
            $author = new Author();
            $author->setAffiliations([$this->createAffiliation(['en' => $this->affiliations[$i]])]);
            $author->setData('biography', ['en' => $this->biographies[$i]]);

            $authors[] = $author;
        }

        return $authors;
    }

    private function createAffiliation(?array $name, ?string $ror = null): Affiliation
    {
        $affiliation = new Affiliation();
        $affiliation->setName($name);
        $affiliation->setRor($ror);

        return $affiliation;
    }

    private function requestVerificationForEveryAuthor(): void
    {
        foreach ($this->authors as $author) {
            $author->setData('orcidVerificationRequested', true);
        }
    }

    private function verifyOrcidForEveryAuthor(): void
    {
        foreach ($this->authors as $author) {
            $author->setData('orcid', 'https://orcid.org/0000-0002-1825-0097');
            $author->setOrcidVerified(true);
        }
    }

    public function testChecksForAffiliation(): void
    {
        $this->assertTrue($this->checker->checkAffiliations($this->authors));

        $this->authors[1]->setAffiliations([]);
        $this->assertFalse($this->checker->checkAffiliations($this->authors));

        $this->authors[1]->setAffiliations([$this->createAffiliation(['en' => 'UFAM', 'pt_BR' => ''])]);
        $this->assertTrue($this->checker->checkAffiliations($this->authors));

        $this->authors[1]->setAffiliations([$this->createAffiliation(['en' => '', 'pt_BR' => ''])]);
        $this->assertFalse($this->checker->checkAffiliations($this->authors));
    }

    public function testAcceptsAffiliationIdentifiedOnlyByItsRor(): void
    {
        $this->authors[2]->setAffiliations([$this->createAffiliation([], 'https://ror.org/013meh722')]);

        $this->assertTrue($this->checker->checkAffiliations($this->authors));
    }

    public function testChecksForBiography(): void
    {
        $this->assertTrue($this->checker->checkBiographies($this->authors));

        $this->authors[2]->unsetData('biography');
        $this->assertFalse($this->checker->checkBiographies($this->authors));
    }

    public function testFindsNoVerifiedContributorWhenNobodyVerified(): void
    {
        $this->assertEmpty($this->checker->getAuthorsWithVerifiedOrcid($this->authors));
    }

    public function testFindsTheContributorsThatVerified(): void
    {
        $this->authors[1]->setData('orcid', 'https://orcid.org/0000-0002-1825-0097');
        $this->authors[1]->setOrcidVerified(true);

        $this->assertSame([$this->authors[1]], $this->checker->getAuthorsWithVerifiedOrcid($this->authors));
    }

    public function testDoesNotCountAVerificationWithoutAnOrcidStored(): void
    {
        $this->authors[1]->setOrcidVerified(true);

        $this->assertEmpty($this->checker->getAuthorsWithVerifiedOrcid($this->authors));
        $this->assertContains($this->authors[1], $this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testDoesNotCountAContributorWhoseVerificationWasOnlyRequested(): void
    {
        $this->requestVerificationForEveryAuthor();

        $this->assertEmpty($this->checker->getAuthorsWithVerifiedOrcid($this->authors));
    }

    public function testRejectsContributorWithoutAnyOrcidStep(): void
    {
        $this->assertNotEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testAcceptsContributorsWhoseVerificationWasRequested(): void
    {
        $this->requestVerificationForEveryAuthor();

        $this->assertEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testRejectsContributorWhoseVerificationWasNotRequested(): void
    {
        $this->requestVerificationForEveryAuthor();
        $this->authors[1]->unsetData('orcidVerificationRequested');

        $this->assertNotEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testAcceptsContributorsWhoseOrcidIsAlreadyVerified(): void
    {
        $this->verifyOrcidForEveryAuthor();

        $this->assertEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testRejectsSubmittingAuthorLikeEveryOtherContributor(): void
    {
        $this->requestVerificationForEveryAuthor();
        $this->authors[0]->unsetData('orcidVerificationRequested');

        $this->assertNotEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testListsContributorsWhoseVerificationWasNotRequested(): void
    {
        $this->requestVerificationForEveryAuthor();
        $this->authors[1]->unsetData('orcidVerificationRequested');

        $this->assertEquals([$this->authors[1]], $this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testTellsAContributorNobodyAskedForTheVerification(): void
    {
        $this->assertSame('notRequested', $this->checker->getOrcidStatus($this->authors[0]));
    }

    public function testTellsAnUnverifiedOrcidStoredWithoutARequest(): void
    {
        $this->authors[0]->setData('orcid', 'https://orcid.org/0000-0002-1825-0097');

        $this->assertSame('unverifiedOrcid', $this->checker->getOrcidStatus($this->authors[0]));
    }

    public function testTellsAVerificationWaitingOnItsLink(): void
    {
        $this->authors[0]->setData('orcidVerificationRequested', true);
        $this->authors[0]->setData('orcidEmailToken', 'a1b2c3d4e5f6');

        $this->assertSame('awaitingVerification', $this->checker->getOrcidStatus($this->authors[0]));
    }

    public function testTellsAVerificationTheContributorDeclined(): void
    {
        $this->authors[0]->setData('orcidVerificationRequested', true);
        $this->authors[0]->setData('orcidAccessDenied', '2026-10-16 10:00:00');

        $this->assertSame('declined', $this->checker->getOrcidStatus($this->authors[0]));
    }

    public function testTellsARequestWhoseEmailIsStillQueuedAsWaiting(): void
    {
        $this->authors[0]->setData('orcidVerificationRequested', true);

        $this->assertSame('awaitingVerification', $this->checker->getOrcidStatus($this->authors[0]));
    }

    public function testTellsARequestSentAgainAfterADeclineAsWaiting(): void
    {
        $this->authors[0]->setData('orcidVerificationRequested', true);
        $this->authors[0]->setData('orcidAccessDenied', '2026-10-16 10:00:00');
        $this->authors[0]->setData('orcidEmailToken', 'a1b2c3d4e5f6');

        $this->assertSame('awaitingVerification', $this->checker->getOrcidStatus($this->authors[0]));
    }

    public function testTellsAVerifiedContributor(): void
    {
        $this->verifyOrcidForEveryAuthor();

        $this->assertSame('verified', $this->checker->getOrcidStatus($this->authors[0]));
    }

    public function testCountsAVerificationWithoutAPendingLinkAsRequested(): void
    {
        $this->requestVerificationForEveryAuthor();

        $this->assertEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }
}
