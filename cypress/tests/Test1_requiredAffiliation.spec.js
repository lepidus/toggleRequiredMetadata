function loginAdminUser() {
    cy.get('input[id=username]').click();
    cy.get('input[id=username]').type(Cypress.env('OJSAdminUsername'), { delay: 0 });
    cy.get('input[id=password]').click();
    cy.get('input[id=password]').type(Cypress.env('OJSAdminPassword'), { delay: 0 });
    cy.get('button[class=submit]').click();
}

describe('Required Metadata Plugin - Make Affiliation and ORCID Required', function() {
    it('Check if affiliation and orcid is required at edit author form', function() {
        cy.visit(Cypress.env('baseUrl') + 'index.php/anphlac/submissions');
        loginAdminUser();
        
        cy.get('.app__nav a').contains('Website').click();
        cy.get('button[id="plugins-button"]').click();
        cy.get('#component-grid-settings-plugins-settingsplugingrid-category-generic-row-togglerequiredmetadataplugin > .first_column > .show_extras').click();
        cy.get('tr[id="component-grid-settings-plugins-settingsplugingrid-category-generic-row-togglerequiredmetadataplugin-control-row"] > td > :nth-child(1)').click();
        cy.wait(2000);
        cy.get('#requireOrcid').check();
        cy.get('#requireAffiliation').check();
        cy.get('#requireOrcid').should('be.checked');
        cy.get('#requireAffiliation').should('be.checked');
        cy.get('form[id="toggleRequiredMetadataSettingsForm"] button[name="submitFormButton"]').click();
        cy.get('.app__nav a').contains('Submissions').click();
        cy.get(':nth-child(1) > :nth-child(1) > .app__navItem').click();
        cy.get("#active-button").click();
        cy.get(".listPanel__item:visible > .listPanel__item--submission > .listPanel__itemSummary > .listPanel__itemActions > a").first().click();
        cy.get("#publication-button").click();
        cy.get("#contributors-button").click();
        cy.get("#contributors-grid > .pkp_controllers_grid > .header > .actions > li:last-of-type > a").click();
        cy.get("input[id^=affiliation-]").should('have.attr', 'required');
        cy.get("input[id^=orcid-]").should('have.attr', 'required');
    });

});