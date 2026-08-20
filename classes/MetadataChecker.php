<?php

namespace APP\plugins\generic\toggleRequiredMetadata\classes;

use APP\author\Author;

class MetadataChecker
{
    public function checkAffiliations(array $authors): bool
    {
        foreach ($authors as $author) {
            if (!$this->hasAffiliation($author)) {
                return false;
            }
        }

        return true;
    }

    public function checkBiographies(array $authors): bool
    {
        foreach ($authors as $author) {
            if (!$this->hasAnyLocalizedValue($author->getData('biography'))) {
                return false;
            }
        }

        return true;
    }

    public function checkAnyAuthenticatedOrcid(array $authors): bool
    {
        foreach ($authors as $author) {
            if ($author->hasVerifiedOrcid()) {
                return true;
            }
        }

        return false;
    }

    public function getAuthorsWithoutRequestedOrcidAuthorization(array $authors): array
    {
        return array_values(array_filter($authors, function (Author $author) {
            return !$this->hasStartedOrcidAuthorization($author);
        }));
    }

    private function hasAffiliation(Author $author): bool
    {
        foreach ($author->getAffiliations() as $affiliation) {
            if ($affiliation->getRor() || $this->hasAnyLocalizedValue($affiliation->getName())) {
                return true;
            }
        }

        return false;
    }

    private function hasStartedOrcidAuthorization(Author $author): bool
    {
        return (bool) $author->getData('orcidVerificationRequested')
            || $author->hasVerifiedOrcid();
    }

    private function hasAnyLocalizedValue($value): bool
    {
        if (!is_array($value)) {
            return !empty($value);
        }

        foreach ($value as $localizedValue) {
            if (!empty($localizedValue)) {
                return true;
            }
        }

        return false;
    }
}
