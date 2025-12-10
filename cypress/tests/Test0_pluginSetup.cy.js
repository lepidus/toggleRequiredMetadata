import '../support/commands';

describe('Toogle Required Metadata - Plugin enabling and setup', function() {
	it('Enables plugin', function() {
		cy.login('dbarnes', null, 'publicknowledge');
		cy.contains('a', 'Website').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();

		cy.get('input[id^=select-cell-togglerequiredmetadataplugin]').check();
		cy.get('input[id^=select-cell-togglerequiredmetadataplugin]').should('be.checked');

		cy.goToPluginSettings();
		cy.get('#requireOrcid').uncheck();
		cy.get('#requireAffiliation').uncheck();
		cy.get('#requireBiography').uncheck();

		cy.get('#toggleRequiredMetadataSettingsForm .submitFormButton').click();
	});

	it('Disable ORCID profile plugin', function () {
		cy.login('dbarnes', null, 'publicknowledge');
		cy.contains('a', 'Website').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();

		cy.get('input[id^=select-cell-orcidprofileplugin]').uncheck();
		cy.get('.pkp_modal_confirmation button.pkpModalConfirmButton').click();
		cy.get('input[id^=select-cell-orcidprofileplugin]').should('not.be.checked');
    });
});