<?php

namespace AlwaysOpen\ProcessStamps;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait ProcessStampable
{
    /**
     * Boots trait and adds proper hooks into the model.
     */
    public static function bootProcessStampable(): void
    {
        static::creating(function ($model) {
            $model->{static::getProcessCreatedColumnName()} = ProcessStamp::getCurrentProcessId();
            $model->{static::getProcessUpdatedColumnName()} = ProcessStamp::getCurrentProcessId();
        });

        static::updating(function ($model) {
            $model->{static::getProcessUpdatedColumnName()} = ProcessStamp::getCurrentProcessId();
        });
    }

    /**
     * @return string
     */
    protected static function getProcessCreatedColumnName(): string
    {
        return config('process-stamps.columns.created');
    }

    /**
     * @return string
     */
    protected static function getProcessUpdatedColumnName(): string
    {
        return config('process-stamps.columns.updated');
    }

    /**
     * @return BelongsTo|null
     */
    public function processCreated(): ?BelongsTo
    {
        return $this->belongsTo(ProcessStamp::class, static::getProcessCreatedColumnName());
    }

    /**
     * @return BelongsTo|null
     */
    public function processUpdated(): ?BelongsTo
    {
        return $this->belongsTo(ProcessStamp::class, static::getProcessUpdatedColumnName());
    }
}
