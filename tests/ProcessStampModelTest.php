<?php

namespace OrisIntel\ProcessStamps\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use OrisIntel\ProcessStamps\ProcessStamp;

class ProcessStampModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function proccess_hash_can_be_generated()
    {
        $hash = 'cd371296c96ff34b7eb993229d0bede9da2702d6';
        $process = [
            'type' => 'artisan',
            'name' => 'migrate --help',
        ];

        $this->assertEquals($hash, ProcessStamp::makeProcessHash($process));
    }

    /** @test */
    public function process_id_entry_can_be_saved()
    {
        $process = [
            'type' => 'artisan',
            'name' => 'db:seed',
        ];

        $model = ProcessStamp::firstOrCreateByProcess($process);

        $this->assertInstanceOf(ProcessStamp::class, $model);
        $this->assertEquals('artisan', $model->type);
        $this->assertEquals('db:seed', $model->name);
    }

    /** @test */
    public function existing_process_id_entry_can_be_retreived()
    {
        $process = [
            'type' => 'artisan',
            'name' => 'db:seed',
        ];

        // Insert the first one
        $first = ProcessStamp::firstOrCreateByProcess($process);

        // Hopefully retrieve it on the second
        $second = ProcessStamp::firstOrCreateByProcess($process);

        // Assert they are actually the same individual row
        $this->assertEquals($first->getKey(), $second->getKey());
    }
}
