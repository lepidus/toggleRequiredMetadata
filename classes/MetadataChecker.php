<?php

namespace APP\plugins\generic\toggleRequiredMetadata\classes;

use APP\submission\Submission;
use APP\facades\Repo;
use APP\author\Author;
use APP\core\Application;
use PKP\log\event\PKPSubmissionEventLogEntry;

class MetadataChecker
{
    private function checkRequiredMetadata(array $authors, string $metadata): bool
    {
        foreach ($authors as $author) {
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

    public function checkContributorsOrcidAuthorization(Submission $submission, array $authors): bool
    {
        $submittingAuthor = $this->getSubmittingAuthor($submission, $authors);

        if ($submittingAuthor) {
            $authors = array_filter($authors, function ($author) use ($submittingAuthor) {
                return $author->getEmail() !== $submittingAuthor->getEmail();
            });
        }

        $hasOrcidEmailToken = $this->checkRequiredMetadata($authors, 'orcidEmailToken');
        $hasOrcid = $this->checkRequiredMetadata($authors, 'orcid');

        return $hasOrcidEmailToken && !$hasOrcid;
    }

    private function getSubmittingAuthor(Submission $submission, array $authors): ?Author
    {
        $user = $this->getSubmittingUser($submission);
        if (!$user) {
            return null;
        }

        foreach ($authors as $author) {
            if ($author->getEmail() === $user->getEmail()) {
                return $author;
            }
        }

        return null;
    }

    private function getSubmittingUser(Submission $submission)
    {
        if ($submission->getData('submissionProgress')) {
            return Application::get()->getRequest()->getUser();
        }

        $submissionSubmitEventLogEntry = Repo::eventLog()->getCollector()
            ->filterByAssoc(Application::ASSOC_TYPE_SUBMISSION, [$submission->getId()])
            ->getQueryBuilder()
            ->where('event_type', '=', PKPSubmissionEventLogEntry::SUBMISSION_LOG_SUBMISSION_SUBMIT)
            ->first();

        if (!$submissionSubmitEventLogEntry) {
            return null;
        }

        return Repo::user()->get($submissionSubmitEventLogEntry->user_id);
    }
}
