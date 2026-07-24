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

    public function testAcceptsContributorWithOrcidOrRequestedAuthorization(): void
    {
        // Every contributor has an ORCID iD (client scenario)
        $this->assertTrue($this->checker->checkOrcidOrAuthorizationRequested($this->authors));

        // A contributor without an ORCID iD but with a requested authorization is accepted
        $this->authors[0]->unsetData('orcid');
        $this->assertFalse($this->checker->checkOrcidOrAuthorizationRequested($this->authors));

        $this->authors[0]->setData('orcidEmailToken', 'email-token');
        $this->assertTrue($this->checker->checkOrcidOrAuthorizationRequested($this->authors));

        // An already authenticated contributor is accepted too
        $this->authors[0]->unsetData('orcidEmailToken');
        $this->authors[0]->setData('orcidAccessToken', 'access-token');
        $this->assertTrue($this->checker->checkOrcidOrAuthorizationRequested($this->authors));

        // A contributor with neither ORCID iD nor requested authorization blocks
        $this->authors[0]->unsetData('orcidAccessToken');
        $this->assertFalse($this->checker->checkOrcidOrAuthorizationRequested($this->authors));
    }

    public function testOnlyShowsPendingOrcidWarningWhenRequirementAndIntegrationAreEnabled(): void
    {
        $this->assertFalse($this->checker->shouldShowOrcidWarning($this->authors, false, true));
        $this->assertFalse($this->checker->shouldShowOrcidWarning($this->authors, true, false));
        $this->assertTrue($this->checker->shouldShowOrcidWarning($this->authors, true, true));

        foreach ($this->authors as $author) {
            $author->setData('orcidAccessToken', 'access-token');
        }

        $this->assertFalse($this->checker->shouldShowOrcidWarning($this->authors, true, true));
    }
}
