import '../support/commands.js';

function beginSubmission(submissionData) {
    cy.get('input[name="locale"][value="en"]').click();
    cy.get('input[name="sectionId"][value="1"]').click();
    cy.setTinyMceContent('startSubmission-title-control', submissionData.title);

    cy.get('input[name="submissionRequirements"]').check();
    cy.get('input[name="privacyConsent"]').check();
    cy.contains('button', 'Begin Submission').click();
}

function detailsStep(submissionData) {
    cy.setTinyMceContent('titleAbstract-abstract-control-en', submissionData.abstract);
    cy.get('.submissionWizard__footer button').contains('Continue').click();
}

function filesStep(submissionData) {
    cy.uploadSubmissionFiles(submissionData.files);
    cy.get('.submissionWizard__footer button').contains('Continue').click();
}

function waitForContributorForm(modalTitle) {
    // The modal mounts its fields asynchronously, and typing into the earlier
    // markup is dropped when it re-renders
    cy.get('div[role=dialog]:contains("' + modalTitle + '") input[name="givenName-en"]').should('be.visible');
    cy.wait(2000); // Avoid occasional failure due to form init taking time
}

function checkRequiredMarks(required) {
    const assertion = required ? 'exist' : 'not.exist';
    cy.get('label[for="contributor-biography-control-en"] .pkpFormFieldLabel__required').should(assertion);
    // The affiliations field renders its own label, so the plugin marks it from
    // an adjacent element instead of through the form config
    cy.get('.trmAffiliationsRequired').should(assertion);
}

function addContributorWithoutRequirements(contributorData) {
    cy.contains('button', 'Add Contributor').click();
    waitForContributorForm('Add Contributor');
    checkRequiredMarks(false);

    cy.get('input[name="givenName-en"]').type(contributorData.given, {delay: 0});
    cy.get('input[name="familyName-en"]').type(contributorData.family, {delay: 0});
    cy.get('input[name="email"]').type(contributorData.email, {delay: 0});
    cy.get('select[name="country"]').select(contributorData.country);

    cy.get('div[role=dialog]:contains("Add Contributor")').find('button').contains('Save').click();
    cy.waitJQuery();
}

function fillContributorRequiredFields(contributorData) {
    let fullName = contributorData.given + ' ' + contributorData.family;
    cy.get('.listPanel__item:contains("' + fullName + '")').find('button').contains('Edit').click();
    waitForContributorForm('Edit');
    checkRequiredMarks(true);

    cy.setTinyMceContent('contributor-biography-control-en', contributorData.biography);
    addAffiliation(contributorData.affiliation);

    cy.get('div[role=dialog]:contains("Edit")').find('button').contains('Save').click();
    cy.waitJQuery();
}

function addAffiliation(affiliation) {
    // The field searches the ROR registry as you type; the test only needs the
    // manually typed name, so the registry is kept out of the way
    cy.intercept('GET', 'https://api.ror.org/**', {statusCode: 200, body: {items: []}});

    cy.get('.pkpFormField--affiliations input.pkpAutosuggest__input').type(affiliation, {delay: 0});
    cy.get('.autosuggest__results-item').contains(affiliation).click();
    cy.get('.pkpFormField--affiliations button').contains('Add').click();
    cy.get('.pkpFormField--affiliations').contains(affiliation);
}

describe('Toggle Required Metadata - Requirement during submission', function () {
    let submissionData;

    before(function() {
        submissionData = {
            title: 'The Imitation Game',
            abstract: 'Mathematicians during the second world war build a machine to decipher puzzles',
            section: 'Articles',
            contributors: [
                {
                    'given': 'Benedict',
                    'family': 'Cumberbatch',
                    'email': 'benedict.cumberbatch@hollywood.com.uk',
                    'affiliation': 'London Academy of Music and Dramatic Art',
                    'biography': 'I made some films, pal',
                    'country': 'GB'
                }
            ],
            files: [
                {
                    'file': 'dummy.pdf',
                    'fileName': 'dummy.pdf',
                    'mimeType': 'application/pdf',
                    'genre': 'Article Text'
                }
            ]
        }
    });

    it('Author creates new submission without requirements', function () {
        cy.loginAs('cmontgomerie');
        cy.contains('Start A New Submission').click();

        beginSubmission(submissionData);
        detailsStep(submissionData);
        filesStep(submissionData);

        cy.get('.listPanel:contains("Contributors")').find('button').contains('Delete').click();
        cy.get('div[role=dialog]').find('button').contains('Delete Contributor').click();
        cy.waitJQuery();
        addContributorWithoutRequirements(submissionData.contributors[0]);
        cy.get('.submissionWizard__footer button').contains('Continue').click();
        cy.get('.submissionWizard__footer button').contains('Continue').click();
        cy.wait(1000);

        cy.contains('button', 'Submit').should('not.be.disabled');
    });
    it('Sets all metadata as required', function () {
        cy.loginAs('dbarnes');
        cy.goToPluginSettings();

        cy.get('#requireOrcid').should('be.disabled');
        cy.get('#requireAffiliation').check();
        cy.get('#requireBiography').check();

        cy.get('#toggleRequiredMetadataSettingsForm .submitFormButton').click();
    });
    it('Author can not finish submission without filling required fields', function () {
        cy.loginAs('cmontgomerie');
        cy.visit('index.php/publicknowledge/dashboard/mySubmissions');
        cy.get('nav').contains('Incomplete submissions').click();
        cy.openSubmission(submissionData.contributors[0].family);

        cy.get('.submissionWizard__footer button').contains('Continue').click();
        cy.get('.submissionWizard__footer button').contains('Continue').click();
        cy.get('.submissionWizard__footer button').contains('Continue').click();
        cy.get('.submissionWizard__footer button').contains('Continue').click();
        cy.wait(1000);

        cy.contains('The affiliation field is required for all contributors');
        cy.contains('The biography statement field is required for all contributors');

        cy.get('.submissionWizard__reviewPanel:contains("Contributors")').find('button').contains('Edit').click();
        fillContributorRequiredFields(submissionData.contributors[0]);
        cy.get('.submissionWizard__footer button').contains('Continue').click();
        cy.get('.submissionWizard__footer button').contains('Continue').click();
        cy.wait(1000);

        cy.contains('button', 'Submit').should('not.be.disabled');
        cy.contains('button', 'Submit').click();
        cy.get('div[role=dialog]:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
});
