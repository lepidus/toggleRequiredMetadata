Cypress.Commands.add('loginAs', (username) => {
	// A session surviving the previous test turns the login below into a no-op
	cy.logout();
	cy.login(username, null, 'publicknowledge');
	// Let the login land: navigating while it is in flight cancels it, and the session never exists
	cy.location('pathname').should('include', '/submissions');
});

Cypress.Commands.add('goToPluginsGrid', () => {
	// A fresh load, so that no half-reloaded grid from a previous action is left around
	cy.visit('index.php/publicknowledge/management/settings/website');
	cy.get('#plugins-button').should('be.visible').click();
	cy.wait(2000); // The grid reloads its rows, detaching whatever was clicked too early
});

Cypress.Commands.add('goToPluginSettings', () => {
	const pluginRowId = 'component-grid-settings-plugins-settingsplugingrid-category-generic-row-togglerequiredmetadataplugin';
	cy.goToPluginsGrid();

	cy.get('#' + pluginRowId + ' > .first_column > .show_extras').click();
	cy.get('a[id^='+ pluginRowId + '-settings-button]').click();
	cy.wait(2000); // Avoid occasional failure due to form init taking time
});

Cypress.Commands.add('findSubmission', function(tab, title) {
	cy.get('#' + tab + '-button').click();
    cy.get('.listPanel__itemSubtitle:visible:contains("' + title + '")').first()
        .parent().parent().within(() => {
            cy.get('.pkpButton:contains("View")').click();
        });
});