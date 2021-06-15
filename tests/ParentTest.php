<?php

namespace AlwaysOpen\ProcessStamps\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use AlwaysOpen\ProcessStamps\ProcessStamp;
use AlwaysOpen\ProcessStamps\Tests\Fakes\Post;

class ParentTest extends TestCase
{
    use DatabaseTransactions;

    protected function createPost(): Post
    {
        return Post::create(['name' => 'Test 123']);
    }

    /** @test */
    public function url_simple()
    {
        $process = ProcessStamp::getProcessName('url', '/test/hello');

        $this->assertEquals('/test/hello', $process['name']);
        $this->assertEquals('/test', $process['parent_name']);
    }

    /** @test */
    public function url_simple_with_extension()
    {
        $process = ProcessStamp::getProcessName('url', '/test/hello.php');

        $this->assertEquals('/test/hello.php', $process['name']);
        $this->assertEquals('/test', $process['parent_name']);
    }

    /** @test */
    public function url_simple_with_query_string()
    {
        $process = ProcessStamp::getProcessName('url', '/test/hello?test=1234&another=true');

        $this->assertEquals('/test/hello?test=1234&another=true', $process['name']);
        $this->assertEquals('/test/hello', $process['parent_name']);
    }

    /** @test */
    public function saved_model_includes_parent()
    {
        $this->assertCount(0, ProcessStamp::all());

        // Set the url so process stamps can detect it
        $_SERVER['REQUEST_URI'] = '/test/hello?test=1234&another=true';
        $model = $this->createPost();

        $this->assertEquals('/test/hello?test=1234&another=true', $model->processCreated->name);
        $this->assertEquals('/test/hello', $model->processCreated->parent->name);
        $this->assertEquals(1, $model->processCreated->parent->children()->count());
        $this->assertEquals('/test', $model->processCreated->parent->parent->name);
        $this->assertCount(3, ProcessStamp::all());
    }

    /** @test */
    public function saved_model_with_resolve_recursive_disabled_does_not_generate_parent()
    {
        config()->set('process-stamps.resolve_recursive', false);

        $this->assertCount(0, ProcessStamp::all());

        // Set the url so process stamps can detect it
        $_SERVER['REQUEST_URI'] = '/test/hello?test=1234&another=true';
        $model = $this->createPost();

        $this->assertEquals('/test/hello?test=1234&another=true', $model->processCreated->name);
        $this->assertNull($model->processCreated->parent);
        $this->assertCount(1, ProcessStamp::all());
    }

    /** @test */
    public function artisan_simple()
    {
        $process = ProcessStamp::getProcessName('artisan', 'test:sync');

        $this->assertEquals('test:sync', $process['name']);
    }

    /** @test */
    public function artisan_with_flags()
    {
        $process = ProcessStamp::getProcessName('artisan', 'test:sync --help');

        $this->assertEquals('test:sync --help', $process['name']);
        $this->assertEquals('test:sync', $process['parent_name']);
    }

    /** @test */
    public function artisan_with_options()
    {
        $process = ProcessStamp::getProcessName('artisan', 'test:sync --url_id=12345 --limit=4');

        $this->assertEquals('test:sync --url_id=12345 --limit=4', $process['name']);
        $this->assertEquals('test:sync', $process['parent_name']);
    }

    /** @test */
    public function make_sure_artisan_does_not_duplicate()
    {
        $process = ProcessStamp::getProcessName('artisan', 'violation:audit --no-rollbar');

        $this->assertEquals('violation:audit --no-rollbar', $process['name']);
        $this->assertEquals('violation:audit', $process['parent_name']);
    }
}
