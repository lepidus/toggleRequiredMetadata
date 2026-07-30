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

    public function checkOrcidsOrAuthorizationRequested(array $authors, $submittingUser = null): bool
    {
        foreach ($authors as $author) {
            if (!$this->hasStartedOrcidAuthorization($author, $submittingUser)) {
                return false;
            }
        }

        return true;
    }

    private function hasStartedOrcidAuthorization(Author $author, $submittingUser): bool
    {
        return $this->checkMetadata($author, 'orcidEmailToken')
            || $this->hasAuthenticatedOrcid($author)
            || $this->matchesAuthenticatedUser($author, $submittingUser);
    }

    private function matchesAuthenticatedUser(Author $author, $submittingUser): bool
    {
        return !is_null($submittingUser)
            && $this->hasAuthenticatedOrcid($submittingUser)
            && $author->getData('orcid') === $submittingUser->getOrcid();
    }

    public function hasAuthenticatedOrcid($userOrAuthor): bool
    {
        if (!$userOrAuthor->getData('orcid') || !$userOrAuthor->getData('orcidAccessToken')) {
            return false;
        }

        $expirationDate = $userOrAuthor->getData('orcidAccessExpiresOn');

        return empty($expirationDate) || strtotime($expirationDate) > time();
    }

    public function checkOrcidAuthorization(array $authors): bool
    {
        return empty($this->getAuthorsWithoutOrcidAuthorization($authors));
    }

    public function getAuthorsWithoutOrcidAuthorization(array $authors): array
    {
        $authorsWithoutAuthorization = [];

        foreach ($authors as $author) {
            if (!$this->hasAuthenticatedOrcid($author)) {
                $authorsWithoutAuthorization[] = $author;
            }
        }

        return $authorsWithoutAuthorization;
    }
}
