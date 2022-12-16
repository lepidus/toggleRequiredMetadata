const serverName = Cypress.env('ServerName');

function loginAdminUser() {
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
}

function goToPluginSettings() {
	loginAdminUser();
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
}

function goToContributorsForm() {
	loginAdminUser();
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
}

describe('Orcid fields when this field is required or not required', function() {
	it('Start plugin without orcid field required', function() {
		goToPluginSettings();
		cy.get('#requireOrcid').uncheck();
		cy.get('#requireAffiliation').check();
		cy.get('#requireBiography').check();

		cy.get('#requireOrcid').should('not.be.checked');
		cy.get('#requireAffiliation').should('be.checked');
		cy.get('#requireBiography').should('be.checked');

		cy.get(
			'form[id="toggleRequiredMetadataSettingsForm"] button[name="submitFormButton"]'
		).click();
	});

	it('Edit list of contributes when orcid field is not required', function() {
		goToContributorsForm();
		cy.get('input[id^=orcid-]').should('not.have.attr', 'required');
	});

	it('Start plugin with orcid field required', function() {
		goToPluginSettings();
		cy.get('#requireOrcid').check();
		cy.get('#requireAffiliation').uncheck();
		cy.get('#requireBiography').uncheck();

		cy.get('#requireOrcid').should('be.checked');
		cy.get('#requireAffiliation').should('not.be.checked');
		cy.get('#requireBiography').should('not.be.checked');

		cy.get(
			'form[id="toggleRequiredMetadataSettingsForm"] button[name="submitFormButton"]'
		).click();
	});
	it('Edit list of contributes when orcid field is required', function() {
		goToContributorsForm();
		cy.get('input[id^=orcid-]').should('have.attr', 'required');
	});
});

describe('Affiliation fields when this field is required or not required', function() {
	it('Start plugin without affiliation field required', function() {
		goToPluginSettings();
		cy.get('#requireOrcid').check();
		cy.get('#requireAffiliation').uncheck();
		cy.get('#requireBiography').check();

		cy.get('#requireOrcid').should('be.checked');
		cy.get('#requireAffiliation').should('not.be.checked');
		cy.get('#requireBiography').should('be.checked');

		cy.get(
			'form[id="toggleRequiredMetadataSettingsForm"] button[name="submitFormButton"]'
		).click();
	});

	it('Edit list of contributes when affiliation field is not required', function() {
		goToContributorsForm();
		cy.get('input[id^=affiliation-]').should('not.have.attr', 'required');
	});

	it('Start plugin with affiliation field required', function() {
		goToPluginSettings();
		cy.get('#requireOrcid').uncheck();
		cy.get('#requireAffiliation').check();
		cy.get('#requireBiography').uncheck();

		cy.get('#requireOrcid').should('not.be.checked');
		cy.get('#requireAffiliation').should('be.checked');
		cy.get('#requireBiography').should('not.be.checked');

		cy.get(
			'form[id="toggleRequiredMetadataSettingsForm"] button[name="submitFormButton"]'
		).click();
	});
	it('Edit list of contributes when affiliation field is required', function() {
		goToContributorsForm();
		cy.get('input[id^=affiliation-]').should('have.attr', 'required');
	});
});

describe('Biography fields when this field is required or not required', function() {
	it('Start plugin without biography field required', function() {
		goToPluginSettings();
		cy.get('#requireOrcid').check();
		cy.get('#requireAffiliation').check();
		cy.get('#requireBiography').uncheck();

		cy.get('#requireOrcid').should('be.checked');
		cy.get('#requireAffiliation').should('be.checked');
		cy.get('#requireBiography').should('not.be.checked');

		cy.get(
			'form[id="toggleRequiredMetadataSettingsForm"] button[name="submitFormButton"]'
		).click();
	});

	it('Edit list of contributes when biography field is not required', function() {
		goToContributorsForm();
		cy.get('textarea[id^=biography-]').should('not.have.attr', 'required');
	});

	it('Start plugin with biography field required', function() {
		goToPluginSettings();
		cy.get('#requireOrcid').uncheck();
		cy.get('#requireAffiliation').uncheck();
		cy.get('#requireBiography').check();

		cy.get('#requireOrcid').should('not.be.checked');
		cy.get('#requireAffiliation').should('not.be.checked');
		cy.get('#requireBiography').should('be.checked');

		cy.get(
			'form[id="toggleRequiredMetadataSettingsForm"] button[name="submitFormButton"]'
		).click();
	});
	it('Edit list of contributes when biography field is required', function() {
		goToContributorsForm();
		cy.get('textarea[id^=biography-]').should('have.attr', 'required');
	});
});
