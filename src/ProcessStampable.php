<?php

namespace OrisIntel\ProcessStamps;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

trait ProcessStampable
{
    /**
     * Boots trait and adds proper hooks into the model.
     */
    public static function bootProcessStampable() : void
    {
        static::creating(function ($model) {
            $model->{static::getProcessCreatedColumnName()} = static::getProcessId();
            $model->{static::getProcessUpdatedColumnName()} = static::getProcessId();
        });

        static::updating(function ($model) {
            $model->{static::getProcessUpdatedColumnName()} = static::getProcessId();
        });
    }

    /**
     * @return string
     */
    protected static function getProcessCreatedColumnName() : string
    {
        return config('process-stamps.columns.created');
    }

    /**
     * @return string
     */
    protected static function getProcessUpdatedColumnName() : string
    {
        return config('process-stamps.columns.updated');
    }

    /**
     * @return BelongsTo|null
     */
    public function processCreated() : ?BelongsTo
    {
        return $this->belongsTo(ProcessStamp::class, static::getProcessCreatedColumnName());
    }

    /**
     * @return BelongsTo|null
     */
    public function processUpdated() : ?BelongsTo
    {
        return $this->belongsTo(ProcessStamp::class, static::getProcessUpdatedColumnName());
    }

    /**
     * Get a readable process name and type.
     *
     * @return array
     */
    public static function getProcessName() : array
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $type = 'url';
            $name = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['SCRIPT_NAME'])) {
            if ($_SERVER['SCRIPT_NAME'] === 'artisan') {
                $type = 'artisan';
                $name = 'artisan '.implode(' ', array_slice($_SERVER['argv'], 1));
            } else {
                $type = 'file';
                $name = $_SERVER['SCRIPT_NAME'];
            }
        } else {
            $type = 'cmd';
            $name = isset($_SERVER['argv'][0]) ? $_SERVER['argv'][0] : $GLOBALS['argv'][0];
        }

        return compact('type', 'name');
    }

    /**
     * @return int
     */
    public static function getProcessId() : int
    {
        $process = static::getProcessName();
        $hash = ProcessStamp::makeProcessHash($process);

        if (config('process-stamp.cache.enabled')) {
            return Cache::store(config('process-stamp.cache.store'))
                        ->forever($hash, function () use ($process, $hash) {
                            return ProcessStamp::firstOrCreateByProcess($process, $hash)->getKey();
                        });
        } else {
            return ProcessStamp::firstOrCreateByProcess($process, $hash)->getKey();
        }
    }
}
