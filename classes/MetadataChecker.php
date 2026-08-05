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

    public function checkAnyAuthenticatedOrcid(array $authors): bool
    {
        foreach ($authors as $author) {
            if ($this->hasAuthenticatedOrcid($author)) {
                return true;
            }
        }

        return false;
    }

    public function getAuthorsWithoutAuthenticatedOrcid(array $authors): array
    {
        return $this->getAuthorsNotMeeting($authors, function (Author $author) {
            return $this->hasAuthenticatedOrcid($author);
        });
    }

    public function getAuthorsWithoutRequestedOrcidAuthorization(array $authors): array
    {
        return $this->getAuthorsNotMeeting($authors, function (Author $author) {
            return $this->hasStartedOrcidAuthorization($author);
        });
    }

    private function getAuthorsNotMeeting(array $authors, callable $requirement): array
    {
        return array_values(array_filter($authors, function (Author $author) use ($requirement) {
            return !$requirement($author);
        }));
    }

    private function hasStartedOrcidAuthorization(Author $author): bool
    {
        return $this->checkHasMetadata($author, 'orcidEmailToken')
            || $this->hasAuthenticatedOrcid($author);
    }

    private function hasAuthenticatedOrcid(Author $author): bool
    {
        if (!$author->getData('orcid') || !$author->getData('orcidAccessToken')) {
            return false;
        }

        $expirationDate = $author->getData('orcidAccessExpiresOn');

        return empty($expirationDate) || strtotime($expirationDate) > time();
    }
}
