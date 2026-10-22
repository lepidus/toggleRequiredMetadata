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

    public function getAuthorsWithVerifiedOrcid(array $authors): array
    {
        return array_values(array_filter($authors, function (Author $author) {
            return $this->hasVerifiedOrcid($author);
        }));
    }

    public function hasVerifiedOrcid(Author $author): bool
    {
        return !empty($author->getData('orcid')) && $author->hasVerifiedOrcid();
    }

    public function getOrcidStatus(Author $author): string
    {
        if ($this->hasVerifiedOrcid($author)) {
            return 'verified';
        }

        if (!$author->getData('orcidVerificationRequested')) {
            return empty($author->getData('orcid')) ? 'notRequested' : 'unverifiedOrcid';
        }

        if (empty($author->getData('orcidEmailToken')) && !empty($author->getData('orcidAccessDenied'))) {
            return 'declined';
        }

        return 'awaitingVerification';
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
            || $this->hasVerifiedOrcid($author);
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
