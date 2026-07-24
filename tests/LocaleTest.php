<?php

use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    private const WORKFLOW_WARNING_KEY =
        'plugins.generic.toggleRequiredMetadata.notification.workflow.orcidWarning';
    private const AUTHOR_DASHBOARD_WARNING_KEY =
        'plugins.generic.toggleRequiredMetadata.notification.authorDashboard.orcidWarning';

    public function testOrcidWarningTranslationsBelongToPortugueseCatalog(): void
    {
        $portugueseCatalog = file_get_contents(__DIR__ . '/../locale/pt_BR/locale.po');
        $spanishCatalog = file_get_contents(__DIR__ . '/../locale/es_ES/locale.po');

        $this->assertSame(1, substr_count($portugueseCatalog, self::WORKFLOW_WARNING_KEY));
        $this->assertSame(1, substr_count($portugueseCatalog, self::AUTHOR_DASHBOARD_WARNING_KEY));
        $this->assertSame(1, substr_count($spanishCatalog, self::WORKFLOW_WARNING_KEY));
        $this->assertSame(1, substr_count($spanishCatalog, self::AUTHOR_DASHBOARD_WARNING_KEY));
    }
}
