function loginAdminUser() {
    cy.get('input[id=username]').click();
    cy.get('input[id=username]').type(Cypress.env('OJSAdminUsername'), { delay: 0 });
    cy.get('input[id=password]').click();
    cy.get('input[id=password]').type(Cypress.env('OJSAdminPassword'), { delay: 0 });
    cy.get('button[class=submit]').click();
}

describe('Required Metadata Plugin - Affiliation and ORCID is Required', function() {
    it('Check if affiliation and orcid is required at edit author form', function() {
        
        cy.visit(Cypress.env('baseUrl') + 'index.php/anphlac/submissions');
        loginAdminUser();
        
        cy.get(':nth-child(1) > :nth-child(1) > .app__navItem').click();
        cy.get("#active-button").click();
        cy.get(".listPanel__item:visible > .listPanel__item--submission > .listPanel__itemSummary > .listPanel__itemActions > a").first().click();
        cy.wait(2000);
        cy.get("#publication-button").click();
        cy.get("#contributors-button").click();
        cy.get("#contributors-grid > .pkp_controllers_grid > .header > .actions > li:last-of-type > a").click();
        cy.get("input[id^=affiliation-]").should('have.attr', 'required');
        cy.get("input[id^=orcid-]").should('have.attr', 'required');
    });

});
