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
		cy.get('#requireOrcid').should('be.disabled').and('not.be.checked');
		cy.get('#requireAffiliation').should('not.be.checked');
		cy.get('#requireBiography').should('not.be.checked');
	});
});