<?php

namespace Thinktomorrow\DynamicAttributes\Tests;

use Thinktomorrow\DynamicAttributes\Tests\Stubs\ModelStub;

class LocalizedDynamicAttributesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        ModelStub::migrateUp();
    }

    /** @test */
    public function it_can_get_a_localized_dynamic_attribute()
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

        $this->assertEquals('localized title en', $model->dynamic('title', 'en'));
        $this->assertEquals('localized title nl', $model->dynamic('title', 'nl'));
    }

    /** @test */
    public function it_does_not_provide_a_fallback_localized_value()
    {
        $model = new ModelStub(['values' => [
            'title' => ['nl' => 'localized title nl'],
        ]]);

        app()->setLocale('en');
        $this->assertNull($model->title);
    }

    /** @test */
    public function when_locale_is_not_provided_as_dynamic_locale_it_will_return_raw_result()
    {
        $model = new ModelStub(['values' => [
            'title' => ['nl' => 'localized title nl'],
        ]]);

        app()->setLocale('dk');
        $this->assertEquals(['nl' => 'localized title nl'], $model->title);
    }

    /** @test */
    public function it_can_save_a_localized_dynamic_attribute()
    {
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
}
