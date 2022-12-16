const serverName = Cypress.env('ServerName');

const submissionData = {
	submitterRole: 'Journal manager',
	title: 'Traditions and Trends in the Study of the Commons',
	abstract:
		'The study of the commons has expe- rienced substantial growth and development over the past decades.1 Distinguished scholars in many disciplines had long studied how specific resources were managed or mismanaged at particular times and places (Coward 1980; De los Reyes 1980; MacKenzie 1979; Wittfogel 1957), but researchers who studied specific commons before the mid-1980s were, however, less likely than their contemporary colleagues to be well informed about the work of scholars in other disciplines, about other sec- tors in their own region of interest, or in other regions of the world.',
	keywords: [
		'Common pool resource',
		'common property',
		'intellectual developments'
	]
};

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

describe('Create submission without fields required', function() {
	it('Start plugin without fields required', function() {
		goToPluginSettings();
		cy.get('#requireOrcid').uncheck();
		cy.get('#requireAffiliation').uncheck();
		cy.get('#requireBiography').uncheck();

		cy.get('#requireOrcid').should('not.be.checked');
		cy.get('#requireAffiliation').should('not.be.checked');
		cy.get('#requireBiography').should('not.be.checked');

		cy.get(
			'form[id="toggleRequiredMetadataSettingsForm"] button[name="submitFormButton"]'
		).click();
	});

	it('Create submission when orcid, affiliation and biography not required', function() {
		loginAdminUser();
		cy.createSubmission(submissionData);
	});
});

describe('Edit list of contributes when all fields are required', function() {
	it('Start plugin with all fields required', function() {
		goToPluginSettings();
		cy.get('#requireOrcid').check();
		cy.get('#requireAffiliation').check();
		cy.get('#requireBiography').check();

		cy.get('#requireOrcid').should('be.checked');
		cy.get('#requireAffiliation').should('be.checked');
		cy.get('#requireBiography').should('be.checked');

		cy.get(
			'form[id="toggleRequiredMetadataSettingsForm"] button[name="submitFormButton"]'
		).click();
	});

	it('Edit list of contributes when orcid, affiliation and biography are required', function() {
		goToContributorsForm();
		cy.get('input[id^=affiliation-]').should('have.attr', 'required');
		cy.get('input[id^=orcid-]').should('have.attr', 'required');
		cy.get('textarea[id^=biography-]').should('have.attr', 'required');
		cy.get('.submitFormButton')
			.contains('Save')
			.click();
	});
});
