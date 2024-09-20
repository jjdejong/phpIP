<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Actor extends Model
{
    protected $table = 'actor';

    protected $hidden = ['login', 'last_login', 'password', 'remember_token', 'creator', 'created_at', 'updated_at', 'updater'];

    protected $guarded = ['id', 'password', 'created_at', 'updated_at'];

    // use \Venturecraft\Revisionable\RevisionableTrait;
    // protected $revisionEnabled = true;
    // protected $revisionCreationsEnabled = true;
    // protected $revisionCleanup = true; //Remove old revisions (works only when used with $historyLimit)
    // protected $historyLimit = 500; //Maintain a maximum of 500 changes at any point of time, while cleaning up old revisions.

    public function company()
    {
        return $this->belongsTo(\App\Models\Actor::class, 'company_id');
    }

    public function parent()
    {
        return $this->belongsTo(\App\Models\Actor::class, 'parent_id');
    }

    public function site()
    {
        return $this->belongsTo(\App\Models\Actor::class, 'site_id');
    }

    public function matters()
    {
        return $this->belongsToMany(\App\Models\Matter::class, 'matter_actor_lnk');
    }

    public function droleInfo()
    {
        return $this->belongsTo(\App\Models\Role::class, 'default_role');
    }

    public function countryInfo()
    {
        return $this->belongsTo(\App\Models\Country::class, 'country');
    }

    public function country_mailingInfo()
    {
        return $this->belongsTo(\App\Models\Country::class, 'country_mailing');
    }

    public function country_billingInfo()
    {
        return $this->belongsTo(\App\Models\Country::class, 'country_billing');
    }

    public function nationalityInfo()
    {
        return $this->belongsTo(\App\Models\Country::class, 'nationality');
    }

    public function getTableComments($table_name = null)
    {
        if (! isset($table_name)) {
            return false;
        }

        $comments = [];
        foreach (Schema::getColumns($table_name) as $column) {
            $col_name = $column['name'];
            $comments[$col_name] = $column['comment'];
        }

        return $comments;
    }
}
