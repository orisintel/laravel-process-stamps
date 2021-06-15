<?php

namespace AlwaysOpen\ProcessStamps\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use AlwaysOpen\ProcessStamps\ProcessStamp;

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

        $stamp = ProcessStamp::firstOrCreateByProcess($process);

        $this->assertInstanceOf(ProcessStamp::class, $stamp);
        $this->assertEquals('artisan', $stamp->type);
        $this->assertEquals('db:seed', $stamp->name);
        $this->assertNotNull($stamp->hash);
        $this->assertNull($stamp->parent);
        $this->assertTrue($stamp->children->isEmpty());
    }

    /** @test */
    public function existing_process_id_entry_can_be_retrieved()
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

    /** @test */
    public function different_hash_by_type()
    {
        $types = [
            'artisan',
            'url',
            'file',
            'cmd',
        ];

        $process_hashes = [];

        foreach ($types as $type) {
            $input_array = [
                'type' => $type,
                'name' => 'delete.php',
            ];
            $process = ProcessStamp::firstOrCreateByProcess($input_array);
            $this->assertFalse(in_array($process->hash, $process_hashes));
            $process_hashes[] = $process->hash;
        }
    }
}
