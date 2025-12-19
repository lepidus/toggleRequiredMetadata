<?php

class MetadataChecker
{
    private function checkRequiredMetadata(array $authors, string $metadata): bool
    {
        foreach ($authors as $author) {
            if (!$this->checkMetadata($author, $metadata)) {
                return false;
            }
        }

        return true;
    }

    public function checkMetadata(Author $author, string $metadata): bool
    {
        if (!$author->getData($metadata)) {
            return false;
        } elseif (is_array($author->getData($metadata))) {
            $entryFilled = false;
            foreach ($author->getData($metadata) as $entry) {
                if ($entry) {
                    $entryFilled = true;
                    break;
                }
            }
            if (!$entryFilled) {
                return false;
            }
        }

        return true;
    }

    public function checkOrcids(array $authors): bool
    {
        return $this->checkRequiredMetadata($authors, 'orcid');
    }

    public function checkAffiliations(array $authors): bool
    {
        return $this->checkRequiredMetadata($authors, 'affiliation');
    }

    public function checkBiographies(array $authors): bool
    {
        return $this->checkRequiredMetadata($authors, 'biography');
    }

    public function checkRequestOrcidAuthorization(array $authors): bool
    {
        foreach ($authors as $author) {
            $hasOrcidEmailToken = $this->checkMetadata($author, 'orcidEmailToken');
            $hasOrcidAccessToken = $this->checkMetadata($author, 'orcidAccessToken');

            if (!$hasOrcidEmailToken && !$hasOrcidAccessToken) {
                return false;
            }
        }

        return true;
    }
}
