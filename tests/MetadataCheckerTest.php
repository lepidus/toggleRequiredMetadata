<?php

import('plugins.generic.toggleRequiredMetadata.classes.MetadataChecker');
import('classes.article.Author');
import('lib.pkp.classes.user.User');

use PHPUnit\Framework\TestCase;

class MetadataCheckerTest extends TestCase
{
    private $checker;
    private $authors;
    private $orcids = [
        'https://orcid.org/0000-0002-1825-0097',
        'https://orcid.org/0000-0002-1825-1997',
        'https://orcid.org/0000-0002-1825-2049'
    ];
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

        for ($i = 0; $i < count($this->orcids) ; $i++) {
            $author = new Author();
            $author->setData('orcid', $this->orcids[$i]);
            $author->setData('affiliation', ['en_US' => $this->affiliations[$i]]);
            $author->setData('biography', ['en_US' => $this->biographies[$i]]);

            $authors[] = $author;
        }

        return $authors;
    }

    private function requestAuthorizationForEveryAuthor(): void
    {
        foreach ($this->authors as $author) {
            $author->setData('orcidEmailToken', 'a1b2c3d4e5f6');
        }
    }

    private function completeAuthorizationForEveryAuthor(): void
    {
        foreach ($this->authors as $author) {
            $author->setData('orcidAccessToken', 'f6e5d4c3b2a1');
            $author->setData('orcidAccessExpiresOn', $this->dateIn('+20 years'));
        }
    }

    private function createUserWithOrcid(string $orcid, bool $authenticated): User
    {
        $user = new User();
        $user->setOrcid($orcid);

        if ($authenticated) {
            $user->setData('orcidAccessToken', 'f6e5d4c3b2a1');
        }

        return $user;
    }

    private function dateIn(string $interval): string
    {
        return date('Y-m-d H:i:s', strtotime($interval));
    }

    public function testChecksForOrcid(): void
    {
        $this->assertTrue($this->checker->checkOrcids($this->authors));

        $this->authors[0]->unsetData('orcid');
        $this->assertFalse($this->checker->checkOrcids($this->authors));
    }

    public function testChecksForAffiliation(): void
    {
        $this->assertTrue($this->checker->checkAffiliations($this->authors));

        $this->authors[1]->unsetData('affiliation');
        $this->assertFalse($this->checker->checkAffiliations($this->authors));

        $this->authors[1]->setData('affiliation', ['en_US' => 'UFAM', 'pt_BR' => '']);
        $this->assertTrue($this->checker->checkAffiliations($this->authors));

        $this->authors[1]->setData('affiliation', ['en_US' => '', 'pt_BR' => '']);
        $this->assertFalse($this->checker->checkAffiliations($this->authors));
    }

    public function testChecksForBiography(): void
    {
        $this->assertTrue($this->checker->checkBiographies($this->authors));

        $this->authors[2]->unsetData('biography');
        $this->assertFalse($this->checker->checkBiographies($this->authors));
    }

    public function testRejectsContributorsThatOnlyHaveUnauthenticatedOrcids(): void
    {
        $this->assertFalse($this->checker->checkAnyAuthenticatedOrcid($this->authors));
    }

    public function testAcceptsASingleAuthenticatedContributor(): void
    {
        $this->authors[1]->setData('orcidAccessToken', 'f6e5d4c3b2a1');

        $this->assertTrue($this->checker->checkAnyAuthenticatedOrcid($this->authors));
    }

    public function testRejectsAnAuthenticationWhoseAccessTokenHasExpired(): void
    {
        $this->completeAuthorizationForEveryAuthor();
        foreach ($this->authors as $author) {
            $author->setData('orcidAccessExpiresOn', $this->dateIn('-1 day'));
        }

        $this->assertFalse($this->checker->checkAnyAuthenticatedOrcid($this->authors));
    }

    public function testRejectsAnAuthenticationWhoseAccessTokenCameWithoutAnOrcid(): void
    {
        $this->completeAuthorizationForEveryAuthor();
        foreach ($this->authors as $author) {
            $author->unsetData('orcid');
        }

        $this->assertFalse($this->checker->checkAnyAuthenticatedOrcid($this->authors));
    }

    public function testRejectsContributorThatOnlyHasAnUnauthenticatedOrcid(): void
    {
        $this->assertNotEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testAcceptsContributorsWhoseAuthorizationWasRequested(): void
    {
        $this->requestAuthorizationForEveryAuthor();

        $this->assertEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testRejectsContributorWhoseAuthorizationWasNotRequested(): void
    {
        $this->requestAuthorizationForEveryAuthor();
        $this->authors[1]->unsetData('orcidEmailToken');

        $this->assertNotEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testAcceptsContributorsWhoseAuthorizationWasCompleted(): void
    {
        $this->completeAuthorizationForEveryAuthor();

        $this->assertEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testAcceptsContributorWhoseAccessTokenHasNoExpirationDate(): void
    {
        $this->completeAuthorizationForEveryAuthor();
        $this->authors[1]->unsetData('orcidAccessExpiresOn');

        $this->assertEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testRejectsContributorWhoseAccessTokenHasExpired(): void
    {
        $this->completeAuthorizationForEveryAuthor();
        $this->authors[1]->setData('orcidAccessExpiresOn', $this->dateIn('-1 day'));

        $this->assertNotEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testRejectsContributorWhoseAccessTokenCameWithoutAnOrcid(): void
    {
        $this->completeAuthorizationForEveryAuthor();
        $this->authors[0]->unsetData('orcid');

        $this->assertNotEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testAcceptsSubmittingAuthorAuthenticatedInTheirUserProfile(): void
    {
        $this->requestAuthorizationForEveryAuthor();
        $this->authors[0]->unsetData('orcidEmailToken');
        $submittingUser = $this->createUserWithOrcid($this->orcids[0], true);

        $this->assertEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors, $submittingUser));
    }

    public function testRejectsSubmittingAuthorWhoseUserProfileOrcidIsNotAuthenticated(): void
    {
        $this->requestAuthorizationForEveryAuthor();
        $this->authors[0]->unsetData('orcidEmailToken');
        $submittingUser = $this->createUserWithOrcid($this->orcids[0], false);

        $this->assertNotEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors, $submittingUser));
    }

    public function testRejectsContributorWhoseOrcidDiffersFromTheAuthenticatedUser(): void
    {
        $this->requestAuthorizationForEveryAuthor();
        $this->authors[0]->unsetData('orcidEmailToken');
        $submittingUser = $this->createUserWithOrcid($this->orcids[1], true);

        $this->assertNotEmpty($this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors, $submittingUser));
    }

    public function testListsContributorsWhoseAuthorizationWasNotRequested(): void
    {
        $this->requestAuthorizationForEveryAuthor();
        $this->authors[1]->unsetData('orcidEmailToken');

        $this->assertEquals([$this->authors[1]], $this->checker->getAuthorsWithoutRequestedOrcidAuthorization($this->authors));
    }

    public function testListsContributorsWithoutCompletedOrcidAuthorization(): void
    {
        $this->authors[1]->setData('orcidAccessToken', 'f6e5d4c3b2a1');

        $authorsWithoutAuthorization = $this->checker->getAuthorsWithoutAuthenticatedOrcid($this->authors);

        $this->assertEquals([$this->authors[0], $this->authors[2]], $authorsWithoutAuthorization);
    }

    public function testListsContributorWhoseAccessTokenHasExpired(): void
    {
        $this->completeAuthorizationForEveryAuthor();
        $this->authors[2]->setData('orcidAccessExpiresOn', $this->dateIn('-1 day'));

        $authorsWithoutAuthorization = $this->checker->getAuthorsWithoutAuthenticatedOrcid($this->authors);

        $this->assertEquals([$this->authors[2]], $authorsWithoutAuthorization);
    }

    public function testListsContributorWhoseAccessTokenCameWithoutAnOrcid(): void
    {
        $this->completeAuthorizationForEveryAuthor();
        $this->authors[0]->unsetData('orcid');

        $authorsWithoutAuthorization = $this->checker->getAuthorsWithoutAuthenticatedOrcid($this->authors);

        $this->assertEquals([$this->authors[0]], $authorsWithoutAuthorization);
    }

    public function testListsNoContributorWhenEveryOrcidAuthorizationWasCompleted(): void
    {
        foreach ($this->authors as $author) {
            $author->setData('orcidAccessToken', 'f6e5d4c3b2a1');
        }

        $this->assertEquals([], $this->checker->getAuthorsWithoutAuthenticatedOrcid($this->authors));
    }
}
