<?php

import('plugins.generic.toggleRequiredMetadata.ToggleRequiredMetadataPlugin');
import('classes.core.Request');

use PHPUnit\Framework\TestCase;

class ToggleRequiredMetadataPluginTest extends TestCase
{
    private function createPluginWithSetting(bool $exists, $value = null)
    {
        $plugin = $this->getMockBuilder(ToggleRequiredMetadataPlugin::class)
            ->onlyMethods(['settingExists', 'getSetting', 'updateSetting'])
            ->getMock();
        $plugin->method('settingExists')->willReturn($exists);
        $plugin->method('getSetting')->willReturn($value);

        return $plugin;
    }

    public function testRequiresTheMetadataUntilTheJournalChoosesOtherwise(): void
    {
        $plugin = $this->createPluginWithSetting(false);

        $this->assertTrue($plugin->shouldRequireField('requireAffiliation', 1));
    }

    public function testDoesNotRequireTheMetadataOnceTheRequirementWasTurnedOff(): void
    {
        $plugin = $this->createPluginWithSetting(true, null);

        $this->assertFalse($plugin->shouldRequireField('requireAffiliation', 1));
    }

    public function testRequiresTheMetadataWhileTheRequirementIsTurnedOn(): void
    {
        $plugin = $this->createPluginWithSetting(true, 'on');

        $this->assertTrue($plugin->shouldRequireField('requireAffiliation', 1));
    }

    /**
     * Reading a requirement used to write the default back. On this line
     * register() asks for one on every page of the site, so that was a write
     * on every page too.
     */
    public function testDoesNotWriteTheDefaultWhileReadingIt(): void
    {
        $plugin = $this->createPluginWithSetting(false);
        $plugin->expects($this->never())->method('updateSetting');

        $plugin->shouldRequireField('requireAffiliation', 1);
    }

    public function testLooksARequirementUpOnlyOncePerRequest(): void
    {
        $plugin = $this->getMockBuilder(ToggleRequiredMetadataPlugin::class)
            ->onlyMethods(['settingExists', 'getSetting'])
            ->getMock();
        $plugin->expects($this->once())->method('settingExists')->willReturn(true);
        $plugin->method('getSetting')->willReturn('on');

        $plugin->shouldRequireField('requireAffiliation', 1);
        $plugin->shouldRequireField('requireAffiliation', 1);
    }

    public function testAnswersNothingIsRequiredWithoutAContext(): void
    {
        $plugin = $this->createPluginWithSetting(false);

        $this->assertFalse($plugin->shouldRequireField('requireAffiliation', null));
    }

    private function createRequest(?string $verb)
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getContext', 'getUserVar'])
            ->getMock();
        $request->method('getContext')->willReturn(null);
        $request->method('getUserVar')->willReturnMap([['verb', $verb]]);

        return $request;
    }

    public function testAnswersTheSettingsRequestWithAnErrorOutsideAContext(): void
    {
        $response = (new ToggleRequiredMetadataPlugin())->manage([], $this->createRequest('settings'));

        $this->assertFalse($response->getStatus());
    }

    public function testHandsAnyOtherRequestToTheApplication(): void
    {
        $this->expectExceptionMessage('Unhandled management action!');

        (new ToggleRequiredMetadataPlugin())->manage([], $this->createRequest('unknown'));
    }
}
