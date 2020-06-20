<?php

namespace Thinktomorrow\DynamicAttributes\Tests;

use Thinktomorrow\DynamicAttributes\DynamicDocument;

class DynamicDocumentTest extends TestCase
{
   /** @test */
   public function it_can_get_a_value()
   {
       $document = new DynamicDocument(['foo' => 'bar']);

       $this->assertEquals('bar', $document->get('foo'));
   }

    /** @test */
    public function it_can_get_all_the_values()
    {
        $document = new DynamicDocument(['foo' => 'bar', 'rab' => 'dab']);

        $this->assertEquals(['foo' => 'bar', 'rab' => 'dab'], $document->all());
    }

    /** @test */
    public function it_can_get_all_the_values_as_json()
    {
        $document = new DynamicDocument(['foo' => 'bar', 'rab' => 'dab']);

        $this->assertEquals(json_encode(['foo' => 'bar', 'rab' => 'dab']), $document->toJson());
    }

    /** @test */
    public function it_can_merge_values()
    {
        $document = new DynamicDocument(['foo' => 'bar', 'rab' => 'dab']);
        $merged = $document->merge(['zab' => 'snap', 'rab' => 'crap']);

        $this->assertEquals([
            'foo' => 'bar',
            'zab' => 'snap',
            'rab' => 'crap',
        ],$merged->all());

        // Merging is an immutable action, i'll prove it
        $this->assertEquals(['foo' => 'bar', 'rab' => 'dab'],$document->all());
    }

    /** @test */
    public function it_can_get_a_nested_value_using_dot_notation()
    {
        $document = new DynamicDocument(['foo' => ['bar' => 'rab']]);

        $this->assertEquals('rab', $document->get('foo.bar'));
    }

    /** @test */
    public function it_gets_the_default_if_value_is_not_found()
    {
        $document = new DynamicDocument();

        $this->assertNull($document->get('xxx'));
    }

    /** @test */
    public function a_default_can_be_passed_as_argument_on_runtime()
    {
        $document = new DynamicDocument();

        $this->assertEquals('fallback value', $document->get('xxx', 'fallback value'));
    }

    /** @test */
    public function it_can_set_a_value()
    {
        $document = new DynamicDocument();
        $document->set('foo', 'bar');

        $this->assertEquals('bar', $document->get('foo'));
    }

    /** @test */
    public function it_can_pass__nonassociated_array_as_value_source()
    {
        $document = new DynamicDocument(['bar']);

        $this->assertEquals('bar', $document->get(0));
    }

    /** @test */
    public function it_can_set_a_nested_value()
    {
        $document = new DynamicDocument();
        $document->set('foo.bar', 'zab');

        $this->assertEquals('zab', $document->get('foo.bar'));
    }

    /** @test */
    public function setting_a_value_will_overwrite_any_existing_value()
    {
        $document = new DynamicDocument(['foo' => 'bar']);
        $document->set('foo', 'rab');

        $this->assertEquals('rab', $document->get('foo'));
    }

    /** @test */
    public function types_of_the_value_are_preserved()
    {
        $document = new DynamicDocument(['foo' => ['bar','baz'], 'one' => 1, 'two' => null, 'three' => $doc = new DynamicDocument()]);

        $this->assertSame(['bar','baz'], $document->get('foo'));
        $this->assertSame(1, $document->get('one'));
        $this->assertSame(null, $document->get('two'));
        $this->assertSame($doc, $document->get('three'));
    }

    /** @test */
    public function it_can_check_existence_of_value()
    {
        $document = new DynamicDocument(['foo' => ['bar', 'baz' => ['rab']], 'one' => 1, 'two' => null, 'three' => $doc = new DynamicDocument()]);

        $this->assertTrue($document->has('foo'));
        $this->assertTrue($document->has('foo.baz'));
        $this->assertFalse($document->has('foo.xxx'));

        $this->assertTrue($document->has('one'));
        $this->assertTrue($document->has('two'));
        $this->assertTrue($document->has('three'));
        $this->assertFalse($document->has('xxx'));
    }
}
