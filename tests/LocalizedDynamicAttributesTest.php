<?php

namespace Thinktomorrow\DynamicAttributes\Tests;

use Thinktomorrow\DynamicAttributes\Tests\Stubs\FallbackLocaleModelStub;
use Thinktomorrow\DynamicAttributes\Tests\Stubs\ModelStub;

class LocalizedDynamicAttributesTest extends TestCase
{
    public function test_it_can_get_a_localized_dynamic_attribute()
    {
        $model = new ModelStub(['values' => [
            'title' => [
                'nl' => 'localized title nl',
                'en' => 'localized title en',
            ],
        ]]);

        app()->setLocale('nl');
        $this->assertEquals('localized title nl', $model->title);

        app()->setLocale('en');
        $this->assertEquals('localized title en', $model->title);
    }

    public function test_it_can_get_a_localized_value()
    {
        $model = new ModelStub(['values' => [
            'title' => [
                'nl' => 'localized title nl',
                'en' => 'localized title en',
            ],
        ]]);

        $this->assertEquals('localized title en', $model->dynamic('title', 'en'));
        $this->assertEquals('localized title nl', $model->dynamic('title', 'nl'));
    }

    public function test_it_can_null_when_localized_value_is_not_present()
    {
        $model = new ModelStub(['values' => [
            'title' => [
                'nl' => 'localized title nl',
                'en' => null,
            ],
        ]]);

        $this->assertNull($model->localizedDynamic('title', 'en'));
    }

    public function test_it_can_get_localized_dynamic_values_with_fallback_logic()
    {
        $model = new ModelStub(['values' => [
            'title' => [
                'nl' => 'localized title nl',
                'en' => null,
            ],
        ]]);

        $model->setDynamicFallbackLocales(['en' => 'nl']);

        $this->assertEquals('localized title nl', $model->localizedDynamic('title', 'en'));
    }

    public function test_it_can_set_custom_empty_check_for_fallback_logic()
    {

        $model = new ModelStub(['values' => [
            'title' => [
                'nl' => 'localized title nl',
                'en' => 'foobar',
            ],
        ]]);

        $model->setCustomValueEmpty(function ($value) {
            return $value === 'foobar';
        });

        $model->setDynamicFallbackLocales(['en' => 'nl']);

        $this->assertEquals('localized title nl', $model->localizedDynamic('title', 'en'));
    }

    public function test_it_can_get_a_localized_dynamic_attribute_with_dot_syntax()
    {
        $model = new ModelStub([
            'title' => [
            'nl' => 'localized title nl',
            'en' => 'localized title en',
        ], ]);

        $this->assertEquals('localized title en', $model->dynamic('title.en'));
        $this->assertEquals('localized title nl', $model->dynamic('title.nl'));
    }

    public function test_it_does_not_provide_a_fallback_localized_value()
    {
        $model = new ModelStub(['values' => [
            'title' => ['nl' => 'localized title nl'],
        ]]);

        app()->setLocale('en');
        $this->assertNull($model->title);
    }

    public function test_it_can_retrieve_a_fallback_default_when_localized_value_is_not_found()
    {
        $model = new ModelStub(['values' => [
            'title' => ['nl' => 'localized title nl'],
        ]]);

        $this->assertEquals('title default', $model->dynamic('title', 'en', 'title default'));
    }

    public function test_it_can_provide_a_fallback_localized_value()
    {
        $model = new FallbackLocaleModelStub(['values' => [
            'title' => ['nl' => 'localized title nl'],
        ]]);

        app()->setLocale('en');
        $this->assertEquals('localized title nl', $model->title);
    }

    public function test_it_can_provide_a_fallback_localized_value_when_value_is_null()
    {
        $model = new FallbackLocaleModelStub(['values' => [
            'title' => ['nl' => 'localized title nl', 'en' => null],
        ]]);

        app()->setLocale('en');
        $this->assertEquals('localized title nl', $model->title);
    }

    public function test_it_gets_own_localized_value_when_value_is_empty_string()
    {
        $model = new FallbackLocaleModelStub(['values' => [
            'title' => ['nl' => 'localized title nl', 'en' => ''],
        ]]);

        app()->setLocale('en');
        $this->assertEquals('', $model->title);
    }

    public function test_it_can_set_active_dynamic_locale()
    {
        $model = new FallbackLocaleModelStub(['values' => [
            'title' => ['nl' => 'localized title nl', 'en' => null, 'fr' => 'localized title fr'],
        ]]);

        $model->setDynamicFallbackLocales([
            'nl' => 'fr',
            'en' => 'fr',
            'de' => 'nl',
        ]);

        $model->setActiveDynamicLocale('en');
        $this->assertEquals('localized title fr', $model->title);

        $model->setActiveDynamicLocale('de');
        $this->assertEquals('localized title nl', $model->title);

        $model->setActiveDynamicLocale('nl');
        $this->assertEquals('localized title nl', $model->title);
    }

    public function test_it_sets_by_default_active_dynamic_locale_by_app_locale()
    {
        $model = new FallbackLocaleModelStub(['values' => [
            'title' => ['nl' => 'localized title nl', 'en' => null, 'fr' => 'localized title fr'],
        ]]);

        $model->setDynamicFallbackLocales([
            'nl' => 'fr',
            'en' => 'fr',
            'de' => 'nl',
        ]);

        // en uses fr as fallback
        app()->setLocale('en');
        $this->assertEquals('localized title fr', $model->title);

        // de uses nl as fallback
        app()->setLocale('de');
        $this->assertEquals('localized title nl', $model->title);

        // nl uses fr as fallback but has value
        app()->setLocale('nl');
        $this->assertEquals('localized title nl', $model->title);
    }

    public function test_when_locale_is_not_provided_as_dynamic_locale_it_will_return_null()
    {
        $model = new ModelStub(['values' => [
            'title' => ['nl' => 'localized title nl'],
        ]]);

        app()->setLocale('dk');
        $this->assertNull($model->title);
    }

    public function test_it_can_save_a_localized_dynamic_attribute()
    {
        ModelStub::migrateUp();

        $model = new ModelStub(['values' => []]);
        $model->setDynamic('title', 'title value nl', 'nl');
        $model->setDynamic('title', 'title value en', 'en');
        $model->save();

        $model = ModelStub::first();

        app()->setLocale('nl');
        $this->assertEquals('title value nl', $model->dynamic('title', 'nl'));
        $this->assertEquals('title value en', $model->dynamic('title', 'en'));
        $this->assertEquals('title value nl', $model->title); // app locale is nl
    }

    public function test_it_can_store_localized_values()
    {
        ModelStub::migrateUp();

        $model = new ModelStub();
        $model->setDynamic('title.nl', 'title value nl');
        $model->setDynamic('title.en', 'title value en');
        $model->save();

        $model->refresh();

        $this->assertEquals('title value nl', $model->dynamic('title.nl'));
        $this->assertEquals('title value en', $model->dynamic('title.en'));
    }

    public function test_it_can_set_localized_values_on_create()
    {
        ModelStub::migrateUp();

        $model = ModelStub::create(['title.nl' => 'title value nl', 'title.en' => 'title value en']);

        $this->assertEquals('title value nl', $model->dynamic('title.nl'));
        $this->assertEquals('title value en', $model->dynamic('title.en'));
    }

    public function test_it_ignores_invalid_keys_on_mass_assignment()
    {
        ModelStub::migrateUp();

        $model = ModelStub::create(['content.xx' => 'fake content']);
        $this->assertNull($model->getAttribute('content.xx'));
    }

    public function test_it_can_check_if_locale_key_is_dynamic()
    {
        $model = new ModelStub(['content' => 'model content', 'title.nl' => 'title value nl', 'title.en' => 'title value en']);

        $this->assertTrue($model->isDynamic('title'));
        $this->assertFalse($model->isDynamic('title.nl'));
        $this->assertFalse($model->isDynamic('content'));
    }

    public function test_it_can_return_array_if_keys_of_the_dynamic_value_are_not_localized()
    {
        $model = new ModelStub(['values' => [
            'title' => [
                'nl' => 'localized title nl',
                'en' => 'localized title en',
            ],
            'customs' => [
                'first custom',
                'second custom',
            ],
        ]]);

        $this->assertEquals([
            'first custom',
            'second custom',
        ], $model->customs);

        $this->assertEquals([
            'first custom',
            'second custom',
        ], $model->dynamic('customs'));
    }

    public function test_it_can_remove_localized_values()
    {
        $model = new ModelStub();
        $model->setDynamic('title.nl', 'title value nl');
        $model->setDynamic('title.en', 'title value en');

        $model->removeDynamic('title.nl');

        $this->assertNull($model->dynamic('title.nl'));
        $this->assertEquals('title value en', $model->dynamic('title.en'));
    }

    public function test_it_falls_back_through_multiple_levels()
    {
        $model = new FallbackLocaleModelStub(['values' => [
            'title' => ['nl' => 'localized title nl', 'en' => null],
        ]]);

        $model->setDynamicFallbackLocales([
            'de' => 'en',
            'en' => 'nl',
            'nl' => null,
        ]);

        app()->setLocale('de');
        $this->assertEquals('localized title nl', $model->title);

        app()->setLocale('en');
        $this->assertEquals('localized title nl', $model->title);
    }
}
