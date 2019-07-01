<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // Status Constants
    const UNASSIGNED_STATUS = 'UNASSIGNED';
    const ASSIGNED_STATUS = 'TAKEN';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
       'created_at','updated_at','start_latitude','start_longtitude','end_latitude','end_longtitude'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'status','distance','start_latitude','start_longtitude','end_latitude','end_longtitude'
    ];

}