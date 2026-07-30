<?php

namespace APP\plugins\generic\toggleRequiredMetadata\classes;

use APP\author\Author;

class MetadataChecker
{
    private function checkRequiredMetadata(array $authors, string $metadata): bool
    {
        foreach ($authors as $author) {
            if (!$this->checkHasMetadata($author, $metadata)) {
                return false;
            };
        }

        return true;
    }

    private function checkHasMetadata(Author $author, string $metadata): bool
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

    public function checkOrcidsOrAuthorizationRequested(array $authors): bool
    {
        foreach ($authors as $author) {
            if (!$this->hasStartedOrcidAuthorization($author)) {
                return false;
            }
        }

        return true;
    }

    private function hasStartedOrcidAuthorization(Author $author): bool
    {
        return $this->checkHasMetadata($author, 'orcidEmailToken')
            || $this->hasValidOrcidAccessToken($author);
    }

    private function hasValidOrcidAccessToken(Author $author): bool
    {
        if (!$author->getData('orcidAccessToken')) {
            return false;
        }

        $expirationDate = $author->getData('orcidAccessExpiresOn');
        if (empty($expirationDate)) {
            return true;
        }

        return strtotime($expirationDate) > time();
    }

    public function getAuthorsWithoutOrcidAuthorization(array $authors): array
    {
        $authorsWithoutAuthorization = [];

        foreach ($authors as $author) {
            if (!$this->hasValidOrcidAccessToken($author)) {
                $authorsWithoutAuthorization[] = $author;
            }
        }

        return $authorsWithoutAuthorization;
    }
}
