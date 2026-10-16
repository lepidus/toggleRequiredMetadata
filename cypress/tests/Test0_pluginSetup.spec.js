import '../support/commands';

describe('Toogle Required Metadata - Plugin enabling and setup', function() {
	it('Enables plugin', function() {
		cy.loginAs('dbarnes');
		cy.goToPluginsGrid();

		cy.get('input[id^=select-cell-togglerequiredmetadataplugin]').check();
		cy.get('input[id^=select-cell-togglerequiredmetadataplugin]').should('be.checked');
		cy.wait(2000); // Let the enabling request finish before navigating away

		cy.goToPluginSettings();
		cy.get('#requireOrcid').should('not.be.checked');
		cy.get('#requireAffiliation').should('not.be.checked');
		cy.get('#requireBiography').should('not.be.checked');
	});
});
