<?php

use APP\plugins\generic\toggleRequiredMetadata\classes\MetadataChecker;
use APP\author\Author;
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
            $author->setData('affiliation', ['en' => $this->affiliations[$i]]);
            $author->setData('biography', ['en' => $this->biographies[$i]]);

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

        $this->authors[1]->setData('affiliation', ['en' => 'UFAM', 'pt_BR' => '']);
        $this->assertTrue($this->checker->checkAffiliations($this->authors));

        $this->authors[1]->setData('affiliation', ['en' => '', 'pt_BR' => '']);
        $this->assertFalse($this->checker->checkAffiliations($this->authors));
    }

    public function testChecksForBiography(): void
    {
        $this->assertTrue($this->checker->checkBiographies($this->authors));

        $this->authors[2]->unsetData('biography');
        $this->assertFalse($this->checker->checkBiographies($this->authors));
    }
}
