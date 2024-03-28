Cypress.Commands.add('goToPluginSettings', () => {
	const pluginRowId = 'component-grid-settings-plugins-settingsplugingrid-category-generic-row-togglerequiredmetadataplugin';
	cy.contains('a', 'Website').click();

	cy.waitJQuery();
	cy.get('#plugins-button').click();

	cy.get('#' + pluginRowId + ' > .first_column > .show_extras').click();
	cy.get('a[id^='+ pluginRowId + '-settings-button]').click();
});

Cypress.Commands.add('findSubmission', function(tab, title) {
	cy.get('#' + tab + '-button').click();
    cy.get('.listPanel__itemSubtitle:visible:contains("' + title + '")').first()
        .parent().parent().within(() => {
            cy.get('.pkpButton:contains("View")').click();
        });
});