<?php

namespace AlwaysOpen\ProcessStamps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class ProcessStamp extends Model
{
    public $fillable = [
        'hash',
        'name',
        'type',
        'parent_id',
    ];

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
     *
     * @throws ModelNotFoundException
     */
    public static function firstOrCreateByProcess(array $process, ?string $hash = null) : self
    {
        if (empty($process['type'])) {
            $process['type'] = 'other';
        }

        if (! $hash) {
            $hash = static::makeProcessHash($process);
        }

        $parent = null;

        if (config('process-stamps.resolve_recursive') && ! empty($process['parent_name'])) {
            $parent = static::firstOrCreateByProcess(static::getProcessName($process['type'], $process['parent_name']));
        }

        return retry(4, function() use ($hash, $process, $parent) {
            $stamp = static::firstWhere('hash', $hash);

            /*
             * If stamp does not exist in the database yet, go ahead and obtain a lock to create it.
             * This specifically doesn't lock as the first step to avoid all calls obtaining a lock from the cache if
             * the item already exists in the DB.
             */
            if (! $stamp) {
                Cache::lock('process-stamps-hash-create-' . $hash, 10)
                    ->get(function () use (&$stamp, $hash, $process, $parent) {
                        $stamp = static::firstOrCreate(['hash' => $hash], [
                            'name'      => trim($process['name']),
                            'type'      => $process['type'],
                            'parent_id' => optional($parent)->getKey(),
                        ]);
                    });
            }

            if (null === $stamp) {
                throw new ModelNotFoundException();
            }

            return $stamp;
        }, 25);
    }

    /**
     * @param array $process
     *
     * @return string
     */
    public static function makeProcessHash(array $process) : string
    {
        return sha1($process['type'] . '-' . trim($process['name']));
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

    /**
     * Get any child processes.
     *
     * @return HasMany|null
     */
    public function children() : ?HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return string
     */
    public static function getType() : string
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            return 'url';
        }

        if (app()->runningInConsole()) {
            return 'artisan';
        }

        if (isset($_SERVER['SCRIPT_NAME'])) {
            return 'file';
        }

        return 'cmd';
    }

    /**
     * Get a readable process name and type.
     *
     * @param string|null $type
     * @param string|null $raw_process
     *
     * @return array
     */
    public static function getProcessName(?string $type = null, ?string $raw_process = null) : array
    {
        $parent_name = null;
        $type = $type ?? static::getType();

        /*
         * KEEP THIS SHITTY. For now.
         *
         * This accesses $_SERVER directly as we can't count on the Laravel Request::server() object existing
         * for applications with legacy code outside of the Laravel request lifecycle.
         */
        switch ($type) {
            case 'url':
                $name = $raw_process ?? $_SERVER['REQUEST_URI'];
                $parent_name = static::getParentUrl($name);
                break;

            case 'artisan':
                $name = ($raw_process ?? implode(' ', array_slice($_SERVER['argv'], 1)));
                $parent_name = static::getParentArtisan($name);
                break;

            case 'file':
                $name = $raw_process ?? $_SERVER['SCRIPT_NAME'];
                break;

            case 'cmd':
                $name = $raw_process ?? $_SERVER['argv'][0] ?? $GLOBALS['argv'][0];
                break;
        }

        return compact('type', 'name', 'parent_name');
    }

    /**
     * @return int
     */
    public static function getCurrentProcessId() : int
    {
        $process = static::getProcessName();
        $hash = self::makeProcessHash($process);

        if (config('process-stamp.cache.enabled')) {
            return Cache::store(config('process-stamp.cache.store'))
                ->forever($hash, function () use ($process, $hash) {
                    return self::firstOrCreateByProcess($process, $hash)->getKey();
                });
        }

        return self::firstOrCreateByProcess($process, $hash)->getKey();
    }

    /**
     * @param string $url
     *
     * @return string|null
     */
    public static function getParentUrl(string $url) : ?string
    {
        if (strpos($stripped = trim($url, '/'), '/')) {
            $parts = preg_split("/(\/|\?)/", $stripped);

            if (! empty($parts) && count($parts) > 1) {
                array_pop($parts);

                return '/' . implode('/', $parts);
            }
        }

        return null;
    }

    public static function getParentArtisan(string $command) : ?string
    {
        $command = trim($command);

        if (strpos($command, ' --')) {
            return explode(' --', $command, 2)[0];
        }

        return null;
    }
}
