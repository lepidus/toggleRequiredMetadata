import '../../../../../lib/pkp/cypress/support/commands';

const serverName = Cypress.env('ServerName');

Cypress.on('uncaught:exception', (err, runnable) => {
	// returning false here prevents Cypress from failing the test
	return false;
});

Cypress.Commands.add('loginAdminUser', () => {
	cy.visit(Cypress.env('baseUrl') + 'index.php/' + serverName + '/submissions');
	cy.get('input[id=username]').click();
	cy.get('input[id=username]').type(Cypress.env('OJSAdminUsername'), {
		delay: 0
	});
	cy.get('input[id=password]').click();
	cy.get('input[id=password]').type(Cypress.env('OJSAdminPassword'), {
		delay: 0
	});
	cy.get('button[class=submit]').click();
});

Cypress.Commands.add('startToggleRequiredMetadataPlugin', () => {
	cy.loginAdminUser();
	cy.get('.app__nav a')
		.contains('Website')
		.click();
	cy.get('button[id="plugins-button"]').click();
	cy.get(
		'#component-grid-settings-plugins-settingsplugingrid-category-generic-row-togglerequiredmetadataplugin'
	).then($pluginRow => {
		if (
			!$pluginRow.find(
				'input[id^="select-cell-togglerequiredmetadataplugin-enable"]:checked'
			).length
		) {
			cy.get(
				'input[id^="select-cell-togglerequiredmetadataplugin-enable"]'
			).check();
			cy.get('div').contains(
				'The plugin "Toggle required metadata" has been enabled.'
			);
		}
	});
	cy.logout();
});

Cypress.Commands.add('goToPluginSettings', () => {
	cy.loginAdminUser();
	cy.get('.app__nav a')
		.contains('Website')
		.click();
	cy.get('button[id="plugins-button"]').click();
	cy.get(
		'#component-grid-settings-plugins-settingsplugingrid-category-generic-row-togglerequiredmetadataplugin > .first_column > .show_extras'
	).click();
	cy.get(
		'tr[id="component-grid-settings-plugins-settingsplugingrid-category-generic-row-togglerequiredmetadataplugin-control-row"] > td > :nth-child(1)'
	).click();
	cy.wait(2000);
});

Cypress.Commands.add('goToContributorsForm', () => {
	cy.loginAdminUser();
	cy.get('.app__nav a')
		.contains('Submissions')
		.click();
	cy.get(':nth-child(1) > :nth-child(1) > .app__navItem').click();
	cy.get('#active-button').click();
	cy.get(
		'.listPanel__item:visible > .listPanel__item--submission > .listPanel__itemSummary > .listPanel__itemActions > a'
	)
		.first()
		.click();
	cy.get('#publication-button').click();
	cy.get('#contributors-button').click();
	cy.get(
		'#contributors-grid > .pkp_controllers_grid > .header > .actions > li:last-of-type > a'
	).click();
});
