import '../support/commands';

const notRequestedWarning = 'The ORCID authorization was not requested yet from';
const awaitingAuthorizationWarning = 'Waiting for the ORCID authorization of';

describe('Toggle Required Metadata - ORCID warning on the workflow', function () {
    it('Enables the ORCID Profile plugin', function () {
        const pluginRowId = 'component-grid-settings-plugins-settingsplugingrid-category-generic-row-orcidprofileplugin';
        cy.loginAs('dbarnes');
        cy.goToPluginsGrid();

        cy.get('#' + pluginRowId + ' > .first_column > .show_extras').click();
        cy.get('a[id^=' + pluginRowId + '-settings-button]').click();
        cy.get('select#orcidProfileAPIPath').should('be.visible').select('Public Sandbox');
        cy.get('input[id^=orcidClientId]').type('APP-CYPRESSTEST00000', {delay: 0});
        cy.get('input[id^=orcidClientSecret]').type('abcdef01-2345-6789-abcd-ef0123456789', {delay: 0});
        cy.get('#orcidProfileSettingsForm .submitFormButton').click();
        cy.wait(2000);

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

    it('Leaves a published article without the warning', function () {
        cy.loginAs('dbarnes');
        cy.findSubmission('archive', 'Antimicrobial, heavy metal resistance');

        cy.get('#publication-button').should('be.visible');
        cy.contains(notRequestedWarning).should('not.exist');
        cy.contains(awaitingAuthorizationWarning).should('not.exist');
    });
});
