import '../support/commands';

describe('Toogle Required Metadata - Plugin enabling and setup', function() {
	it('Enables plugin', function() {
		cy.loginAs('dbarnes');
		cy.goToPluginsGrid();

		cy.get('input[id^=select-cell-togglerequiredmetadataplugin]').check();
		cy.get('input[id^=select-cell-togglerequiredmetadataplugin]').should('be.checked');
		cy.wait(2000); // Let the enabling request finish before navigating away

		cy.goToPluginSettings();
		// ORCID is not enabled in the test data, so the option is offered as
		// disabled and the saved choice is left untouched
		cy.get('#requireOrcid').should('be.disabled');
		cy.get('#requireAffiliation').uncheck();
		cy.get('#requireBiography').uncheck();

		cy.get('#toggleRequiredMetadataSettingsForm .submitFormButton').click();
		cy.wait(2000); // Let the settings request finish before navigating away
	});
});