<?php

namespace Thinktomorrow\DynamicAttributes\Tests;

use Thinktomorrow\DynamicAttributes\Tests\Stubs\FullDynamicModelStub;
use Thinktomorrow\DynamicAttributes\Tests\Stubs\ModelStub;

class HasDynamicAttributesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        ModelStub::migrateUp();
    }

    /** @test */
    public function it_can_get_a_dynamic_attribute()
    {
        $model = new ModelStub(['values' => ['title' => 'title value']]);

        $this->assertEquals('title value', $model->title);
    }

    /** @test */
    public function the_dynamic_document_is_kept_next_to_the_original_attributes()
    {
        $model = new ModelStub(['values' => ['title' => 'title value']]);

        $this->assertIsString($model->values);
        $this->assertEquals(json_encode(['title' => 'title value']), $model->values);
    }

    /** @test */
    public function a_model_attribute_is_forced_as_dynamic_when_its_set_as_dynamic_attribute()
    {
        $model = new ModelStub(['title' => 'model title']);

        $this->assertEquals('model title', $model->title);
        $this->assertEquals('model title', $model->dynamic('title'));
    }

    /** @test */
    public function a_model_can_set_option_to_allow_all_properties_to_be_dynamic()
    {
        $model = new FullDynamicModelStub(['content' => 'model content', 'title' => 'model title']);

        $this->assertEquals('model content', $model->content);
        $this->assertEquals('model content', $model->dynamic('content'));

        // own attribute remains untouched
        $this->assertEquals('model title', $model->title);
        $this->assertNull($model->dynamic('title'));
    }

    /** @test */
    public function it_can_set_a_dynamic_attribute()
    {
        $model = new ModelStub(['values' => ['title' => 'title value']]);
        $model->title = 'new title value';

        $this->assertEquals('new title value', $model->dynamic('title'));
        $this->assertEquals('new title value', $model->title);
    }

    /** @test */
    public function it_can_set_a_new_dynamic_attribute()
    {
        $model = new ModelStub(['values' => []]);
        $model->title = 'title value';

        $this->assertEquals('title value', $model->dynamic('title'));
        $this->assertEquals('title value', $model->title);
    }

    /** @test */
    public function it_can_save_a_dynamic_attribute()
    {
        $model = new ModelStub(['values' => []]);
        $model->title = 'title value';
        $model->save();

        $model = ModelStub::first();

        $this->assertEquals('title value', $model->dynamic('title'));
        $this->assertEquals('title value', $model->title);
    }
}
