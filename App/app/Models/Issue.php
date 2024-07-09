<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{

    public function status()
    {
        return $this->belongsTo(Status::class,'status', 'id');
    }

    public function dealers()
    {
        return $this->belongsTo(Dealer::class,'dealer_id');
    }


    public function created_by()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function files()
    {
        return $this->hasMany(IssueFile::class);
    }

    public function photos()
    {
        return $this->hasMany(IssueFile::class);
    }
}
