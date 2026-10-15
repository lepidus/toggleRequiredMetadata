import '../support/commands';

const submissionData = {
    title: 'Computing Machinery and Intelligence',
    abstract: 'A proposal to replace the question "can machines think" with an imitation game',
    files: [
        {
            'file': 'dummy.pdf',
            'fileName': 'dummy.pdf',
            'mimeType': 'application/pdf',
            'genre': 'Article Text'
        }
    ],
    contributors: [
        {
            'given': 'Alan',
            'family': 'Turing',
            'email': 'alan.turing@bletchley.org.uk',
            'country': 'GB',
            'requestOrcidVerification': false
        },
        {
            'given': 'Joan',
            'family': 'Clarke',
            'email': 'joan.clarke@bletchley.org.uk',
            'country': 'GB',
            'requestOrcidVerification': true
        }
    ]
};

function waitForContributorForm(modalTitle) {
    cy.get('div[role=dialog]:contains("' + modalTitle + '") input[name="givenName-en"]').should('be.visible');
    cy.wait(2000); // Avoid occasional failure due to form init taking time
}

function addContributor(contributor) {
    cy.contains('button', 'Add Contributor').click();
    waitForContributorForm('Add Contributor');

    cy.get('input[name="givenName-en"]').type(contributor.given, {delay: 0});
    cy.get('input[name="familyName-en"]').type(contributor.family, {delay: 0});
    cy.get('input[name="email"]').type(contributor.email, {delay: 0});
    cy.get('select[name="country"]').select(contributor.country);

    // The plugin marks the ORCID field, which renders its own label and so
    // cannot be told to carry the required mark through the form config
    cy.get('.trmRequiredMark--orcid').should('exist');

    if (contributor.requestOrcidVerification) {
        cy.get('div[role=dialog]:contains("Add Contributor")').find('button').contains('Request verification').click();
        cy.get('div[role=dialog]:contains("Request ORCID verification")').find('button').contains('Yes').click();
        // The e-mail of a contributor who does not exist yet is only sent once
        // the form is saved, so the button is what confirms the request landed
        cy.contains('button', 'ORCID Verification has been requested!').should('exist');
    }

    cy.get('div[role=dialog]:contains("Add Contributor")').find('button').contains('Save').click();
    cy.waitJQuery();
}

const notRequestedWarning = 'The ORCID iD verification was not requested yet from';
const awaitingVerificationWarning = 'Waiting for the ORCID iD verification of';

describe('Toggle Required Metadata - ORCID requirement on the review step', function () {
    it('Enables ORCID in the journal', function () {
        cy.loginAs('dbarnes');
        cy.visit('index.php/publicknowledge/management/settings/access');

        cy.get('#orcidSettings-button').click();
        cy.get('#orcidSettings').contains('.pkpFormField--options', 'Enable ORCID functionality')
            .find('input[type="checkbox"]').check();

        // The credentials are never exercised: the plugin only reads whether
        // ORCID is enabled, and no ORCID API call is made during the test
        cy.get('#orcidSettings input[name="orcidClientId"]').type('APP-CYPRESSTEST0000', {delay: 0});
        cy.get('#orcidSettings input[name="orcidClientSecret"]').type('cypress-test-secret', {delay: 0});

        cy.intercept('POST', '**/api/v1/contexts/*').as('saveOrcidSettings');
        cy.get('#orcidSettings').find('button').contains('Save').click();
        cy.wait('@saveOrcidSettings').its('response.statusCode').should('eq', 200);
    });

    it('Requires the ORCID iD in the plugin settings', function () {
        cy.loginAs('dbarnes');
        cy.goToPluginSettings();

        // Only the ORCID requirement is left on, so that nothing else can block
        // the contributor form or add errors of its own to the review step
        cy.get('#requireOrcid').should('not.be.disabled').check();
        cy.get('#requireAffiliation').uncheck();
        cy.get('#requireBiography').uncheck();
        cy.get('#toggleRequiredMetadataSettingsForm .submitFormButton').click();
        cy.wait(2000); // Let the settings request finish before navigating away
    });

    it('Shows one block naming where each contributor stands', function () {
        cy.loginAs('cmontgomerie');
        cy.contains('Start A New Submission').click();

        cy.get('input[name="locale"][value="en"]').click();
        cy.get('input[name="sectionId"][value="1"]').click();
        cy.setTinyMceContent('startSubmission-title-control', submissionData.title);
        cy.get('input[name="submissionRequirements"]').check();
        cy.get('input[name="privacyConsent"]').check();
        cy.contains('button', 'Begin Submission').click();

        cy.setTinyMceContent('titleAbstract-abstract-control-en', submissionData.abstract);
        cy.get('.submissionWizard__footer button').contains('Continue').click();

        cy.uploadSubmissionFiles(submissionData.files);
        cy.get('.submissionWizard__footer button').contains('Continue').click();

        cy.get('.listPanel:contains("Contributors")').find('button').contains('Delete').click();
        cy.get('div[role=dialog]').find('button').contains('Delete Contributor').click();
        cy.waitJQuery();
        submissionData.contributors.forEach(addContributor);

        cy.get('.submissionWizard__footer button').contains('Continue').click();
        cy.get('.submissionWizard__footer button').contains('Continue').click();
        cy.wait(2000); // Let the validation of the review step finish

        // One block for the whole requirement, in place of the two separate
        // warnings that read as unrelated problems
        cy.get('.trmOrcidRequirement').should('have.length', 1);
        cy.get('.trmOrcidRequirement').within(() => {
            cy.get('.trmOrcidRequirement__contributor--notRequested')
                .should('contain', 'Alan Turing');
            cy.get('.trmOrcidRequirement__contributor--awaitingVerification')
                .should('contain', 'Joan Clarke');
        });

        // The hook renders the block at the end of the list, so only the CSS
        // puts it where the warnings of the wizard itself are: check where it
        // landed, not where it was written
        cy.get('.trmOrcidRequirement').then(($requirement) => {
            const requirementTop = $requirement[0].getBoundingClientRect().top;

            cy.get('.submissionWizard__reviewPanel__list__name').first().then(($firstName) => {
                expect(requirementTop).to.be.lessThan($firstName[0].getBoundingClientRect().top);
            });
        });

        cy.contains('button', 'Submit').should('be.disabled');
    });

    /**
     * An editor works through every stage of every submission, so the warning is
     * kept to the tab it is about instead of following them the whole way.
     */
    it('Shows the editor warning only in the contributors tab', function () {
        cy.findSubmissionAsEditor('dbarnes', null, 'Cumberbatch');

        cy.get('[data-cy="active-modal"]').should('be.visible');
        cy.contains(notRequestedWarning).should('not.exist');

        cy.openWorkflowMenu('Contributors');
        cy.contains(notRequestedWarning).should('be.visible');
        cy.contains(notRequestedWarning).should('contain', 'Benedict Cumberbatch');
        cy.contains(awaitingVerificationWarning).should('not.exist');

        // Leaving the tab hides it again, which is what tells the absence above
        // apart from the workflow simply not having finished rendering
        cy.openWorkflowMenu('Title & Abstract');
        cy.contains(notRequestedWarning).should('not.exist');
    });

    it('Tells a requested verification apart from one never asked for', function () {
        cy.findSubmissionAsEditor('dbarnes', null, 'Cumberbatch');
        cy.openWorkflowMenu('Contributors');
        cy.contains(notRequestedWarning).should('be.visible');

        cy.get('.listPanel__item:contains("Benedict Cumberbatch")').find('button').contains('Edit').click();
        waitForContributorForm('Edit');
        cy.get('div[role=dialog]:contains("Edit")').find('button').contains('Request verification').click();
        cy.get('div[role=dialog]:contains("Request ORCID verification")').find('button').contains('Yes').click();
        cy.contains('button', 'ORCID Verification has been requested!').should('exist');
        cy.get('div[role=dialog]:contains("Edit")').find('button').contains('Save').click();
        cy.waitJQuery();

        cy.contains(awaitingVerificationWarning).should('be.visible');
        cy.contains(awaitingVerificationWarning).should('contain', 'Benedict Cumberbatch');
        cy.contains(notRequestedWarning).should('not.exist');
    });
});
