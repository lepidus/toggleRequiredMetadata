import '../support/commands';

describe('Toggle Required Metadata - Requirement during workflow', function () {
    let submissionTitle = 'The Imitation Game';

    it('Author can not finish submission without filling required fields', function () {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.findSubmission('active', submissionTitle);

        cy.get('#publication-button').click();
        cy.get('#contributors-button').click();

        cy.contains('span', 'Benedict Cumberbatch').parent().parent().within(() => {
            cy.get('.show_extras').click();
        });
        cy.get('.pkp_linkaction_editAuthor:visible').click();
        
        cy.get('input[id^=orcid-]').should('have.attr', 'required');
        cy.get('input[id^=affiliation-]').should('have.attr', 'required');
        cy.get('textarea[id^=biography-]').should('have.attr', 'required');
    });
});