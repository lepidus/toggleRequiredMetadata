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
    cy.contains('button', 'Continue').click();
}

function filesStep(submissionData) {
    cy.uploadSubmissionFiles(submissionData.files);
    cy.contains('button', 'Continue').click();
}

function checkFieldsAreNotRequired() {
    cy.get('input[name="orcid"]').should('not.have.attr', 'required');
    cy.get('input[name="affiliation-en"]').should('not.have.attr', 'required');
    cy.get('label[for="contributor-biography-control-en"] .pkpFormFieldLabel__required').should('not.exist');
}

function addContributorWithoutRequirements(contributorData) {
    cy.contains('button', 'Add Contributor').click();
    checkFieldsAreNotRequired();

    cy.get('input[name="givenName-en"]').type(contributorData.given, {delay: 0});
    cy.get('input[name="familyName-en"]').type(contributorData.family, {delay: 0});
    cy.get('input[name="email"]').type(contributorData.email, {delay: 0});
    cy.get('select[name="country"]').select(contributorData.country);
    cy.get('input[name="userGroupId"][value="14"]').click();

    cy.get('.modal__panel:contains("Add Contributor")').find('button').contains('Save').click();
    cy.waitJQuery();
}

function fillContributorRequiredFields(contributorData) {
    let fullName = contributorData.given + ' ' + contributorData.family;
    cy.get('.listPanel__itemTitle:visible:contains("' + fullName + '")')
        .parent().parent().within(() => {
            cy.contains('button', 'Edit').click();
        });
    cy.get('input[name="orcid"]').type(contributorData.orcid, {delay: 0});
    cy.get('input[name="affiliation-en"]').type(contributorData.affiliation, {delay: 0});
    cy.setTinyMceContent('contributor-biography-control-en', contributorData.biography);

    cy.get('.modal__panel:contains("Edit")').find('button').contains('Save').click();
    cy.waitJQuery();
}

describe('Toggle Required Metadata - Requirement during submission', function () {
    let submissionData;
    
    before(function() {
        submissionData = {
            title: 'The Imitation Game',
			abstract: 'Mathematicians during the second world war build a machine to decipher puzzles',
            section: 'Articles',
			keywords: [
                'plugin', 'testing',
            ],
            contributors: [
                {
                    'given': 'Benedict',
                    'family': 'Cumberbatch',
                    'email': 'benedict.cumberbatch@hollywood.com.uk',
                    'affiliation': 'London Academy of Music and Dramatic Art',
                    'biography': 'I made some films, pal',
                    'orcid': 'https://orcid.org/0000-0002-1825-0097',
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
        cy.login('cmontgomerie', null, 'publicknowledge');
        cy.get('div#myQueue a:contains("New Submission")').click();

        beginSubmission(submissionData);
        detailsStep(submissionData);
        filesStep(submissionData);

        cy.get('.contributorsListPanel button:contains("Delete")').click();
        cy.contains('button', 'Delete Contributor').click();
        cy.waitJQuery();
        addContributorWithoutRequirements(submissionData.contributors[0]);
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.wait(1000);

        cy.contains('button', 'Submit').should('not.be.disabled');
    });
    it('Sets all metadata as required', function () {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.goToPluginSettings();
        
        cy.get('#requireOrcid').check();
		cy.get('#requireAffiliation').check();
		cy.get('#requireBiography').check();

		cy.get('#toggleRequiredMetadataSettingsForm .submitFormButton').click();
    });
    it('Author can not finish submission without filling required fields', function () {
        cy.login('cmontgomerie', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.wait(1000);

        cy.contains('The ORCID field is required for all contributors');
        cy.contains('The affiliation field is required for all contributors');
        cy.contains('The biography statement field is required for all contributors');

        cy.get('.pkpSteps__step button:contains("Contributors")').click();
        fillContributorRequiredFields(submissionData.contributors[0]);
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.wait(1000);

        cy.contains('button', 'Submit').should('not.be.disabled');
        cy.contains('button', 'Submit').click();
        cy.get('.modal__panel:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
});
