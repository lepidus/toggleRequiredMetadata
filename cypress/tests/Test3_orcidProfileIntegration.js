import '../support/commands';

describe('Toggle Required Metadata - Integration with ORCID Profile', function () {
    it('Enables plugin', function() {
		cy.login('dbarnes', null, 'publicknowledge');
		cy.contains('a', 'Website').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();

		cy.get('input[id^=select-cell-togglerequiredmetadataplugin]').check();
		cy.get('input[id^=select-cell-togglerequiredmetadataplugin]').should('be.checked');

		cy.goToPluginSettings();
		cy.get('#requireOrcid').check();

		cy.get('#toggleRequiredMetadataSettingsForm .submitFormButton').click();
	});

    it('Enable and configure ORCID profile plugin', function () {
		cy.login('dbarnes', null, 'publicknowledge');
		cy.contains('a', 'Website').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();

		cy.get('input[id^=select-cell-orcidprofileplugin]').check();
		cy.get('input[id^=select-cell-orcidprofileplugin]').should('be.checked');
        
        cy.get('#component-grid-settings-plugins-settingsplugingrid-category-generic-row-orcidprofileplugin > .first_column > .show_extras').click();
        cy.get('a[id^=component-grid-settings-plugins-settingsplugingrid-category-generic-row-orcidprofileplugin-settings-button]').click();

        cy.get('#orcidProfileAPIPath').select('https://pub.sandbox.orcid.org/')
        cy.get('input[id^=orcidClientId]').type(Cypress.env('orcidClientId'), {delay: 0});
        cy.get('input[id^=orcidClientSecret]').type(Cypress.env('orcidClientSecret'), {delay: 0});

        cy.get('#orcidProfileSettingsForm .submitFormButton').click();
    });

    it('Submitting author need to fill ORCID and co-authors must authorize ORCID', function () {
        cy.login('cmontgomerie', null, 'publicknowledge');
        cy.get('div#myQueue a:contains("New Submission")').click();
        cy.get('input[name="locale"][value="en"]').click();
        cy.get('input[name="sectionId"][value="1"]').click();
        cy.setTinyMceContent('startSubmission-title-control', 'The Quantum Paradox');
        
        cy.get('input[name="submissionRequirements"]').check();
        cy.get('input[name="privacyConsent"]').check();
        cy.contains('button', 'Begin Submission').click();
        cy.setTinyMceContent('titleAbstract-abstract-control-en', 'Researchers attempt to unify quantum mechanics with general relativity through a controversial new theory.');
        cy.contains('button', 'Continue').click();
        cy.uploadSubmissionFiles([
            {
                'file': 'dummy.pdf',
                'fileName': 'dummy.pdf',
                'mimeType': 'application/pdf',
                'genre': 'Article Text'
            }
        ]);
        cy.contains('button', 'Continue').click();

        cy.get('.listPanel__itemTitle:visible:contains("Craig Montgomerie")')
            .parent().parent().within(() => {
                cy.contains('button', 'Edit').click();
            });
        cy.get('.modal__panel:contains("Edit")').find('button').contains('Save').click();
        cy.contains('The submitting author must fill in the ORCID field.');

        cy.contains('button', 'Override').click();
        cy.get('input[name="orcid"]').type('https://orcid.org/0000-0002-1825-0097', {delay: 0});
        cy.get('.modal__panel:contains("Edit")').find('button').contains('Save').click();
        cy.waitJQuery();

        cy.contains('button', 'Add Contributor').click();

        cy.get('input[name="givenName-en"]').type('Alice', {delay: 0});
        cy.get('input[name="familyName-en"]').type('Johnson', {delay: 0});
        cy.get('input[name="email"]').type('alice.johnson@mitexample.edu', {delay: 0});
        cy.get('select[name="country"]').select('US');
        cy.get('input[name="userGroupId"][value="14"]').click();

        cy.get('.modal__panel:contains("Add Contributor")').find('button').contains('Save').click();
        cy.contains('Co-authors must authorize the use of their ORCID.');

        cy.get('input[name="requestOrcidAuthorization"]').check();
		cy.get('input[name="requestOrcidAuthorization"]').should('be.checked');
        cy.get('.modal__panel:contains("Add Contributor")').find('button').contains('Save').click();
        cy.waitJQuery();
    });

    it('Author can not finish submission without filling ORCID and send authorization email for co-authors', function () {
        cy.login('dbarnes', null, 'publicknowledge');
		cy.contains('a', 'Website').click();
		cy.waitJQuery();
		cy.get('#plugins-button').click();
		cy.goToPluginSettings();
		cy.get('#requireOrcid').uncheck();
		cy.get('#toggleRequiredMetadataSettingsForm .submitFormButton').click();
        cy.logout();

        cy.login('cmontgomerie', null, 'publicknowledge');
        cy.get('div#myQueue a:contains("New Submission")').click();
        cy.get('input[name="locale"][value="en"]').click();
        cy.get('input[name="sectionId"][value="1"]').click();
        cy.setTinyMceContent('startSubmission-title-control', 'Ocean Currents and Climate Change');
        cy.get('input[name="submissionRequirements"]').check();
        cy.get('input[name="privacyConsent"]').check();
        cy.contains('button', 'Begin Submission').click();
        cy.setTinyMceContent('titleAbstract-abstract-control-en', 'A study of how shifting ocean patterns influence global temperatures and biodiversity.');
        cy.contains('button', 'Continue').click();
        cy.uploadSubmissionFiles([
            {
                'file': 'dummy.pdf',
                'fileName': 'dummy.pdf',
                'mimeType': 'application/pdf',
                'genre': 'Article Text'
            }
        ]);
        cy.contains('button', 'Continue').click();

        cy.contains('button', 'Add Contributor').click();
        cy.get('input[name="givenName-en"]').type('Nora', {delay: 0});
        cy.get('input[name="familyName-en"]').type('Tanaka', {delay: 0});
        cy.get('input[name="email"]').type('nora.tanaka@marinescience.jp', {delay: 0});
        cy.get('select[name="country"]').select('JP');
        cy.get('input[name="userGroupId"][value="14"]').click();
        cy.get('.modal__panel:contains("Add Contributor")').find('button').contains('Save').click();
        cy.waitJQuery();
        cy.logout();

        cy.login('dbarnes', null, 'publicknowledge');
		cy.contains('a', 'Website').click();
		cy.waitJQuery();
		cy.get('#plugins-button').click();
		cy.goToPluginSettings();
		cy.get('#requireOrcid').check();
		cy.get('#toggleRequiredMetadataSettingsForm .submitFormButton').click();
        cy.logout();

        cy.login('cmontgomerie', null, 'publicknowledge');
        cy.findSubmission('myQueue', 'Ocean Currents and Climate Change');
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.wait(1000);
        cy.contains('The ORCID field is required for the submitting author');
        cy.contains('It is mandatory to send the ORCID authorization email to co-authors.');

        cy.get('.pkpSteps__step button:contains("Contributors")').click();
        cy.get('.listPanel__itemTitle:visible:contains("Craig Montgomerie")')
            .parent().parent().within(() => {
                cy.contains('button', 'Edit').click();
            });
        cy.contains('button', 'Override').click();
        cy.get('input[name="orcid"]').type('https://orcid.org/0000-0002-1825-0097', {delay: 0});
        cy.get('.modal__panel:contains("Edit")').find('button').contains('Save').click();
        cy.waitJQuery();

        cy.get('.pkpSteps__step button:contains("Contributors")').click();
        cy.get('.listPanel__itemTitle:visible:contains("Nora Tanaka")')
            .parent().parent().within(() => {
                cy.contains('button', 'Edit').click();
            });
        cy.get('input[name="requestOrcidAuthorization"]').check();
		cy.get('input[name="requestOrcidAuthorization"]').should('be.checked');
        cy.get('.modal__panel:contains("Edit")').find('button').contains('Save').click();
        cy.waitJQuery();

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

    it('Show pending ORCID notification on the workflow page', function () {
        cy.login('cmontgomerie', null, 'publicknowledge');
        cy.findSubmission('myQueue', 'Ocean Currents and Climate Change');
        cy.contains('ORCID pending, co-authors need to validate it for the article to proceed through the editorial workflow.').should('be.visible');
        cy.logout();

        cy.login('dbarnes', null, 'publicknowledge');
        cy.findSubmission('active', 'Ocean Currents and Climate Change');
        cy.contains('There are ORCIDs that have not been validated by the authors.').should('be.visible');
    });
});
