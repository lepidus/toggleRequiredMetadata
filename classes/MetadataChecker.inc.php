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
            || $this->hasValidOrcidAccessToken($author)
            || $this->matchesAuthenticatedUser($author, $submittingUser);
    }

    private function matchesAuthenticatedUser(Author $author, $submittingUser): bool
    {
        if (is_null($submittingUser) || !$this->hasValidOrcidAccessToken($submittingUser)) {
            return false;
        }

        $authorOrcid = $author->getData('orcid');

        return !empty($authorOrcid) && $authorOrcid === $submittingUser->getOrcid();
    }

    public function hasValidOrcidAccessToken($userOrAuthor): bool
    {
        if (!$userOrAuthor->getData('orcidAccessToken')) {
            return false;
        }

        $expirationDate = $userOrAuthor->getData('orcidAccessExpiresOn');
        if (empty($expirationDate)) {
            return true;
        }

        return strtotime($expirationDate) > time();
    }

    public function checkOrcidAuthorization(array $authors): bool
    {
        return empty($this->getAuthorsWithoutOrcidAuthorization($authors));
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
