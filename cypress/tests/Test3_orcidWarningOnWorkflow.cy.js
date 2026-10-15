import '../support/commands';

const notRequestedWarning = 'The ORCID authorization was not requested yet from';
const awaitingAuthorizationWarning = 'Waiting for the ORCID authorization of';

describe('Toggle Required Metadata - ORCID warning on the workflow', function () {
    it('Enables the ORCID Profile plugin', function () {
        cy.loginAs('dbarnes');
        cy.goToPluginsGrid();

        cy.get('input[id^=select-cell-orcidprofileplugin]').check();
        cy.get('input[id^=select-cell-orcidprofileplugin]').should('be.checked');
        cy.wait(2000);
    });

    it('Requires the ORCID iD in the plugin settings', function () {
        cy.loginAs('dbarnes');
        cy.goToPluginSettings();

        cy.get('#requireOrcid').check();
        cy.get('#toggleRequiredMetadataSettingsForm .submitFormButton').click();
        cy.wait(2000);
    });

    it('Names the contributor nobody asked for the authorization', function () {
        cy.loginAs('dbarnes');
        cy.findSubmission('active', 'The Imitation Game');

        cy.contains(notRequestedWarning).should('be.visible');
        cy.contains(notRequestedWarning).should('contain', 'Benedict Cumberbatch');
        cy.contains(awaitingAuthorizationWarning).should('not.exist');
    });
});
