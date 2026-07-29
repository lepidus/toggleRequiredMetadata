<?php

import('plugins.generic.toggleRequiredMetadata.classes.MetadataChecker');
import('classes.article.Author');

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

    public function testAcceptsContributorsThatAlreadyHaveOrcid(): void
    {
        $this->assertTrue($this->checker->checkOrcidsOrAuthorizationRequested($this->authors));
    }

    public function testRejectsContributorWithoutOrcidNorRequestedAuthorization(): void
    {
        $this->authors[1]->unsetData('orcid');

        $this->assertFalse($this->checker->checkOrcidsOrAuthorizationRequested($this->authors));
    }

    public function testAcceptsContributorWithoutOrcidWhenAuthorizationWasRequested(): void
    {
        $this->authors[1]->unsetData('orcid');
        $this->authors[1]->setData('orcidEmailToken', 'a1b2c3d4e5f6');

        $this->assertTrue($this->checker->checkOrcidsOrAuthorizationRequested($this->authors));
    }

    public function testAcceptsContributorWithoutOrcidWhenAuthorizationWasCompleted(): void
    {
        $this->authors[1]->unsetData('orcid');
        $this->authors[1]->setData('orcidAccessToken', 'f6e5d4c3b2a1');

        $this->assertTrue($this->checker->checkOrcidsOrAuthorizationRequested($this->authors));
    }

    public function testListsContributorsWithoutCompletedOrcidAuthorization(): void
    {
        $this->authors[1]->setData('orcidAccessToken', 'f6e5d4c3b2a1');

        $authorsWithoutAuthorization = $this->checker->getAuthorsWithoutOrcidAuthorization($this->authors);

        $this->assertEquals([$this->authors[0], $this->authors[2]], $authorsWithoutAuthorization);
    }

    public function testListsNoContributorWhenEveryOrcidAuthorizationWasCompleted(): void
    {
        foreach ($this->authors as $author) {
            $author->setData('orcidAccessToken', 'f6e5d4c3b2a1');
        }

        $this->assertEquals([], $this->checker->getAuthorsWithoutOrcidAuthorization($this->authors));
        $this->assertTrue($this->checker->checkOrcidAuthorization($this->authors));
    }
}
