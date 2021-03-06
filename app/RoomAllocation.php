<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoomAllocation extends Model
{
    protected $fillable = ['description','tenant_id', 'room_id', 'user_id','is_active'];

    protected $guard = ['_token'];

    protected $table = "room_allocation";

    public function tenant()
    {
        return $this->belongsTo('App\Tenant');
    }

    public function room()
    {
        return $this->belongsTo('App\Room');
    }
}
