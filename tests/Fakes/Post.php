<?php

namespace OrisIntel\ProcessStamps\Tests\Fakes;

use Illuminate\Database\Eloquent\Model;
use OrisIntel\ProcessStamps\ProcessStampable;

class Post extends Model
{
    use ProcessStampable;

    public $table = 'posts';
    public $guarded = [];
}
