<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'actor_role';
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    protected $hidden = ['creator', 'created_at', 'updated_at', 'updater'];
    protected $guarded = ['code', 'creator', 'created_at', 'updated_at', 'updater'];
    public $incrementing = false;
}
