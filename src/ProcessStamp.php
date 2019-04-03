<?php

namespace OrisIntel\ProcessStamps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessStamp extends Model
{
    public $fillable = ['hash', 'name', 'type'];

    /**
     * Override the primary key name to use the config.
     *
     * @return string
     */
    public function getKeyName() : string
    {
        return config('process-stamps.columns.primary_key');
    }

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
     * @param array       $process
     * @param null|string $hash
     *
     * @return ProcessStamp
     */
    public static function firstOrCreateByProcess(array $process, ?string $hash = null) : self
    {
        if (empty($process['type'])) {
            $process['type'] = 'other';
        }

        if (! $hash) {
            $hash = static::makeProcessHash($process);
        }

        return static::firstOrCreate(['hash' => $hash], $process);
    }

    /**
     * @param array $process
     *
     * @return string
     */
    public static function makeProcessHash(array $process) : string
    {
        return sha1($process['type'].'-'.$process['name']);
    }

    /**
     * Get the parent of the command, if there is one.
     *
     * @return BelongsTo|null
     */
    public function parent() : ?BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
