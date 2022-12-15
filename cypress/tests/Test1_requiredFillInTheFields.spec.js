const serverName = Cypress.env('ServerName');

var submissionId = null;

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

describe('Create submission with all fields required', function() {
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

	it('Edit list of contributes when orcid, affiliation and biography required', function() {
		loginAdminUser();
		cy.get('.app__nav a')
			.contains('Submissions')
			.click();
		cy.get(':nth-child(1) > :nth-child(1) > .app__navItem').click();
		cy.get('#myQueue-button').click();
		cy.get(
			'.submissionsListPanel > .listPanel > .listPanel__header > .pkpHeader > .pkpHeader__actions > a.pkpButton'
		)
			.contains('New Submission')
			.click();

		cy.get(
			'#pkp_submissionChecklist > .checkbox_and_radiobutton > :nth-child(1) > label > input'
		).check();
		cy.get(
			'#pkp_submissionChecklist > .checkbox_and_radiobutton > :nth-child(2) > label > input'
		).check();
		cy.get(
			'#pkp_submissionChecklist > .checkbox_and_radiobutton > :nth-child(3) > label > input'
		).check();
		cy.get(
			'#pkp_submissionChecklist > .checkbox_and_radiobutton > :nth-child(4) > label > input'
		).check();
		cy.get('.checkbox_and_radiobutton > :nth-child(5) > label > input').check();
		cy.get('#privacyConsent').check();

		cy.get('.submitFormButton')
			.contains('Save and continue')
			.click();

		cy.wait(2000);
		cy.get('.form_buttons > .submitFormButton')
			.contains('Save and continue')
			.click();

		cy.get('.show_extras').click();
		cy.get('.pkp_linkaction_icon_edit')
			.contains('Edit')
			.click();

		cy.get(
			'#userGroupId > .checkbox_and_radiobutton > li > label > #userGroup14'
		).check();

		cy.get('#editAuthor > .form_buttons > .submitFormButton')
			.contains('Save')
			.click();
	});
});

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
});
