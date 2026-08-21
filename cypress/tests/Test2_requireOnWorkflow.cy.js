import '../support/commands';

describe('Toggle Required Metadata - Requirement during workflow', function () {
    let contributorName = 'Benedict Cumberbatch';

    it('Contributor form keeps the metadata required in the workflow', function () {
        cy.findSubmissionAsEditor('dbarnes', null, 'Cumberbatch');

        cy.openWorkflowMenu('Contributors');
        cy.get('.listPanel__item:contains("' + contributorName + '")').find('button').contains('Edit').click();

        cy.get('label[for="contributor-biography-control-en"] .pkpFormFieldLabel__required').should('exist');
        cy.get('.trmRequiredMark--affiliations').should('exist');
    });
});
