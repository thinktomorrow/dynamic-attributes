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

    public function test_it_can_get_a_dynamic_attribute()
    {
        $model = new ModelStub(['values' => ['title' => 'title value']]);

        $this->assertEquals('title value', $model->title);
    }

    public function test_it_can_get_a_dynamic_attribute_default()
    {
        $model = new ModelStub();

        $this->assertEquals('title default', $model->dynamic('title', null, 'title default'));
    }

    public function test_the_dynamic_document_is_kept_next_to_the_original_attributes()
    {
        $model = new ModelStub(['values' => ['title' => 'title value']]);

        $this->assertIsString($model->values);
        $this->assertEquals(json_encode(['title' => 'title value']), $model->values);
    }

    public function test_a_model_attribute_is_forced_as_dynamic_when_its_set_as_dynamic_attribute()
    {
        $model = new ModelStub(['title' => 'model title']);

        $this->assertEquals('model title', $model->title);
        $this->assertEquals('model title', $model->dynamic('title'));
    }

    public function test_it_can_set_a_dynamic_attribute()
    {
        $model = new ModelStub(['values' => ['title' => 'title value']]);
        $model->title = 'new title value';

        $this->assertEquals('new title value', $model->dynamic('title'));
        $this->assertEquals('new title value', $model->title);
    }

    public function test_it_can_set_a_new_dynamic_attribute()
    {
        $model = new ModelStub(['values' => []]);
        $model->title = 'title value';

        $this->assertEquals('title value', $model->dynamic('title'));
        $this->assertEquals('title value', $model->title);
    }

    public function test_it_can_remove_a_dynamic_attribute()
    {
        $model = new ModelStub(['values' => ['title' => 'title value']]);
        $model->title = 'new title value';

        $this->assertEquals('new title value', $model->title);

        $model->removeDynamic('title');
        $this->assertNull($model->title);
        $this->assertTrue(! array_key_exists('title', $model->rawDynamicValues()));
    }

    public function test_it_can_check_if_key_is_dynamic()
    {
        $model = new ModelStub(['content' => 'model content', 'title' => 'model title']);

        $this->assertTrue($model->isDynamic('title'));
        $this->assertFalse($model->isDynamic('content'));
    }

    public function test_it_can_get_the_raw_dynamic_array()
    {
        $model = new ModelStub(['values' => ['title' => ['nl' => 'title value nl', 'en' => 'title value en']]]);

        $this->assertEquals(['title' => ['nl' => 'title value nl', 'en' => 'title value en']], $model->rawDynamicValues());
    }

    public function test_it_can_save_a_dynamic_attribute()
    {
        $model = new ModelStub(['values' => []]);
        $model->title = 'title value';
        $model->content = 'content value';
        $model->save();

        $model = ModelStub::first();

        $this->assertEquals('title value', $model->dynamic('title'));
        $this->assertEquals('title value', $model->title);
        $this->assertEquals('content value', $model->content);
    }

    public function test_it_can_eloquent_create_a_model_with_dynamic_attributes()
    {
        $model = ModelStub::create(['title' => 'title value', 'content' => 'content value']);

        $this->assertEquals('title value', $model->dynamic('title'));
        $this->assertEquals('title value', $model->title);
        $this->assertEquals('content value', $model->content);
    }

    public function test_a_model_can_set_option_to_allow_all_properties_to_be_dynamic()
    {
        $model = new FullDynamicModelStub(['content' => 'model content', 'title' => 'model title']);

        $this->assertEquals('model content', $model->content);
        $this->assertEquals('model content', $model->dynamic('content'));

        // own attribute remains untouched
        $this->assertEquals('model title', $model->title);
        $this->assertNull($model->dynamic('title'));
    }

    public function test_a_model_with_dynamic_blacklist_can_set_dynamic_attributes_with_column_key()
    {
        $model = new FullDynamicModelStub(['values' => ['content' => 'model content'], 'title' => 'model title']);

        $this->assertEquals('model content', $model->content);
        $this->assertEquals('model content', $model->dynamic('content'));

        // own attribute remains untouched
        $this->assertEquals('model title', $model->title);
        $this->assertNull($model->dynamic('title'));
    }
}
