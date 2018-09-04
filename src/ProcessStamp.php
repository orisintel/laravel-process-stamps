<?php


namespace OrisIntel\ProcessStamps;

use Illuminate\Database\Eloquent\Model;

class ProcessStamp extends Model
{
    /**
     * Override the table name with the one in the config file.
     *
     * @return string
     */
    public function getTable() : string
    {
        return config('process-stamps.table');
    }

    /**
     * @param array $process
     * @param null|string $hash
     * @return ProcessStamp
     */
    public function firstOrCreateByProcess(array $process, ?string $hash) : self
    {
        if(! $hash) {
            $hash = static::makeProcessHash($process);
        }

        return static::firstOrCreate(['hash' => $hash], $process);
    }

    /**
     * @param array $process
     * @return string
     */
    public static function makeProcessHash(array $process) : string
    {
        return sha1($process['type'] . '-' . $process['name']);
    }
}
