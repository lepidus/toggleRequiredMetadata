import '../support/commands';

describe('Toogle Required Metadata - Plugin enabling and setup', function() {
	it('Enables plugin', function() {
		cy.loginAs('dbarnes');
		cy.goToPluginsGrid();

		cy.get('input[id^=select-cell-togglerequiredmetadataplugin]').check();
		cy.get('input[id^=select-cell-togglerequiredmetadataplugin]').should('be.checked');
		cy.wait(2000); // Let the enabling request finish before navigating away

		cy.goToPluginSettings();
		cy.get('#requireOrcid').uncheck();
		cy.get('#requireAffiliation').uncheck();
		cy.get('#requireBiography').uncheck();

		cy.get('#toggleRequiredMetadataSettingsForm .submitFormButton').click();
		cy.wait(2000); // Let the settings request finish before navigating away
	});
});