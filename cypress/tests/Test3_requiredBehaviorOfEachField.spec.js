import '../support/commands';

describe('Orcid fields when this field is required or not required', function() {
  it('Start plugin without orcid field required', function() {
    cy.goToPluginSettings();
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
    cy.goToContributorsForm();
    cy.get('input[id^=orcid-]').should('not.have.attr', 'required');
  });

  it('Start plugin with orcid field required', function() {
    cy.goToPluginSettings();
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
    cy.goToContributorsForm();
    cy.get('input[id^=orcid-]').should('have.attr', 'required');
  });
});

describe('Affiliation fields when this field is required or not required', function() {
  it('Start plugin without affiliation field required', function() {
    cy.goToPluginSettings();
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
    cy.goToContributorsForm();
    cy.get('input[id^=affiliation-]').should('not.have.attr', 'required');
  });

  it('Start plugin with affiliation field required', function() {
    cy.goToPluginSettings();
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
    cy.goToContributorsForm();
    cy.get('input[id^=affiliation-]').should('have.attr', 'required');
  });
});

describe('Biography fields when this field is required or not required', function() {
  it('Start plugin without biography field required', function() {
    cy.goToPluginSettings();
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
    cy.goToContributorsForm();
    cy.get('textarea[id^=biography-]').should('not.have.attr', 'required');
  });

  it('Start plugin with biography field required', function() {
    cy.goToPluginSettings();
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
    cy.goToContributorsForm();
    cy.get('textarea[id^=biography-]').should('have.attr', 'required');
  });
});
