<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Unit;

use KraenzleRitter\ResourcesComponents\Helpers\LabelHelper;
use KraenzleRitter\ResourcesComponents\Tests\TestCase;

class LabelHelperTest extends TestCase
{
    /** @test */
    public function it_returns_string_label_as_is()
    {
        $label = 'Simple String Label';
        $result = LabelHelper::getLocalizedLabel($label);

        $this->assertEquals('Simple String Label', $result);
    }

    /** @test */
    public function it_returns_localized_label_for_current_locale()
    {
        $label = [
            'en' => 'English Label',
            'de' => 'Deutsche Bezeichnung',
            'fr' => 'Étiquette française',
        ];

        app()->setLocale('de');
        $result = LabelHelper::getLocalizedLabel($label);

        $this->assertEquals('Deutsche Bezeichnung', $result);
    }

    /** @test */
    public function it_falls_back_to_english_when_locale_not_found()
    {
        $label = [
            'en' => 'English Label',
            'de' => 'Deutsche Bezeichnung',
        ];

        app()->setLocale('fr'); // Not available in label array
        $result = LabelHelper::getLocalizedLabel($label);

        $this->assertEquals('English Label', $result);
    }

    /** @test */
    public function it_returns_first_available_label_when_no_english_fallback()
    {
        $label = [
            'de' => 'Deutsche Bezeichnung',
            'fr' => 'Étiquette française',
        ];

        app()->setLocale('it'); // Not available, and no English fallback
        $result = LabelHelper::getLocalizedLabel($label);

        $this->assertEquals('Deutsche Bezeichnung', $result);
    }

    /** @test */
    public function it_returns_empty_string_for_invalid_input()
    {
        $result = LabelHelper::getLocalizedLabel(null);
        $this->assertEquals('', $result);

        $result = LabelHelper::getLocalizedLabel([]);
        $this->assertEquals('', $result);
    }

    /** @test */
    public function it_gets_provider_label_from_config()
    {
        // Mock the config
        config([
            'resources-components.providers.test-provider' => [
                'label' => [
                    'en' => 'Test Provider',
                    'de' => 'Test-Anbieter',
                ]
            ]
        ]);

        app()->setLocale('de');
        $result = LabelHelper::getProviderLabel('test-provider');

        $this->assertEquals('Test-Anbieter', $result);
    }

    /** @test */
    public function it_falls_back_to_provider_key_when_no_label_configured()
    {
        config(['resources-components.providers.unknown-provider' => []]);

        $result = LabelHelper::getProviderLabel('unknown-provider');

        $this->assertEquals('unknown-provider', $result);
    }
}
