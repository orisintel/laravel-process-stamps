<?php

namespace OrisIntel\ProcessStamps\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use OrisIntel\ProcessStamps\ProcessStamp;
use OrisIntel\ProcessStamps\Tests\Fakes\Post;

class ParentTest extends TestCase
{
    use DatabaseTransactions;

    protected function createPost() : Post
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
    public function artisan_simple()
    {
        $process = ProcessStamp::getProcessName('artisan', 'test:sync');

        $this->assertEquals('artisan test:sync', $process['name']);
    }

    /** @test */
    public function artisan_with_flags()
    {
        $process = ProcessStamp::getProcessName('artisan', 'test:sync --help');

        $this->assertEquals('artisan test:sync --help', $process['name']);
        $this->assertEquals('artisan test:sync', $process['parent_name']);
    }

    /** @test */
    public function artisan_with_options()
    {
        $process = ProcessStamp::getProcessName('artisan', 'test:sync --url_id=12345 --limit=4');

        $this->assertEquals('artisan test:sync --url_id=12345 --limit=4', $process['name']);
        $this->assertEquals('artisan test:sync', $process['parent_name']);
    }
}
