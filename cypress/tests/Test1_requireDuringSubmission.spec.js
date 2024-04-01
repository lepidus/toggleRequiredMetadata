import '../support/commands.js';

function step1(submissionData) {
    cy.get('select[id="locale"]').select('en_US');
    cy.get('select[id="sectionId"]').select(submissionData.section);
    cy.get('input[id^="checklist-"]').click({ multiple: true });
    cy.get('input[id=privacyConsent]').click();
    cy.get('#submitStep1Form button.submitFormButton').click();
}

function step2() {
    cy.get('#submitStep2Form button.submitFormButton').click();
}

function step3(submissionData) {
    cy.get('input[name^="title"]').first().type(submissionData.title, { delay: 0 });
    cy.get('label').contains('Title').click();
    cy.get('textarea[id^="abstract-"').then((node) => {
        cy.setTinyMceContent(node.attr("id"), submissionData.abstract);
    });
    cy.get('.section > label:visible').first().click();
    cy.get('ul[id^="en_US-keywords-"]').then(node => {
        for(let keyword of submissionData.keywords) {
            node.tagit('createTag', keyword);
        }
    });
}

function checkFieldsAreNotRequired() {
    cy.get('input[id^=orcid-]').should('not.have.attr', 'required');
    cy.get('input[id^=affiliation-]').should('not.have.attr', 'required');
    cy.get('textarea[id^=biography-]').should('not.have.attr', 'required');
}

function addContributorWithoutRequirements(contributorData) {
    cy.contains('a', 'Add Contributor').click();
    checkFieldsAreNotRequired();

    cy.get('input[id^=givenName-en_US]').type(contributorData.given, {delay: 0});
    cy.get('input[id^=familyName-en_US]').type(contributorData.family, {delay: 0});
    cy.get('input[name="email"]').type(contributorData.email, {delay: 0});
    cy.get('select[name="country"]').select(contributorData.country);
    cy.get('input[name="userGroupId"][value="14"]').click();

    cy.get('#editAuthor .submitFormButton').click();
    cy.waitJQuery();
}

function deleteContributor(fullName) {
    cy.contains('span', fullName).parent().parent().within(() => {
        cy.get('.show_extras').click();
    });
    cy.get('.pkp_linkaction_deleteAuthor:visible').click();
    cy.get('button.pkpModalConfirmButton').click();
    cy.wait(1000);
}

function fillContributorRequiredFields(contributorData) {
    let fullName = contributorData.given + ' ' + contributorData.family;
    cy.contains('span', fullName).parent().parent().within(() => {
        cy.get('.show_extras').click();
    });
    cy.get('.pkp_linkaction_editAuthor:visible').click();
    cy.get('input[name="orcid"]').type(contributorData.orcid, {delay: 0});
    cy.get('input[id^=affiliation-en_US]').type(contributorData.affiliation, {delay: 0});
    cy.get('input[id^=affiliation-fr_CA]').type(contributorData.affiliation, {delay: 0});
    cy.get('textarea[id^="biography-"').then((node) => {
        cy.setTinyMceContent(node.attr("id"), contributorData.biography);
    });
    cy.contains("Contributor's role").click();

    cy.get('#editAuthor .submitFormButton').click();
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
            ]
		}
    });
    
    it('Author creates new submission without requirements', function () {
        cy.login('cmontgomerie', null, 'publicknowledge');
        cy.get('div#myQueue a:contains("New Submission")').click();

        step1(submissionData);
        step2();
        step3(submissionData);
        addContributorWithoutRequirements(submissionData.contributors[0]);

        cy.get('#submitStep3Form button.submitFormButton').click();
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
        cy.contains('a', '3. Enter Metadata').click();
        cy.wait(1000);

        deleteContributor('Craig Montgomerie');
        cy.get('#submitStep3Form button.submitFormButton').click();
        cy.contains('The ORCID field is required for all contributors');
        cy.contains('The affiliation field is required for all contributors');
        cy.contains('The biography statement field is required for all contributors');

        fillContributorRequiredFields(submissionData.contributors[0]);
        cy.get('#submitStep3Form button.submitFormButton').click();
        cy.wait(1000);

        cy.contains('Your submission has been uploaded and is ready to be sent.');
        cy.contains('button', 'Finish Submission').click();
        cy.wait(1000);
		cy.get('button.pkpModalConfirmButton').click();

		cy.waitJQuery();
		cy.get('h2:contains("Submission complete")');
    });
});
