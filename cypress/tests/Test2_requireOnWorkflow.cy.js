import '../support/commands';

describe('Toggle Required Metadata - Requirement during workflow', function () {
    let submissionTitle = 'The Imitation Game';

    it('Author can not finish submission without filling required fields', function () {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.findSubmission('active', submissionTitle);

        cy.get('#publication-button').click();
        cy.get('#contributors-button').click();

        cy.get('.listPanel__itemTitle:visible:contains("Benedict Cumberbatch")')
            .parent().parent().within(() => {
                cy.contains('button', 'Edit').click();
            });
        
            cy.get('input[name="orcid"]').should('have.attr', 'required');
            cy.get('input[name="affiliation-en"]').should('have.attr', 'required');
            cy.get('label[for="contributor-biography-control-en"] .pkpFormFieldLabel__required');
    });
});