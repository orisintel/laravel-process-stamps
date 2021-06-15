<?php

namespace AlwaysOpen\ProcessStamps\Tests\Fakes;

use Illuminate\Database\Eloquent\Model;
use AlwaysOpen\ProcessStamps\ProcessStampable;

class Post extends Model
{
    use ProcessStampable;

    public $table = 'posts';
    public $guarded = [];
}
