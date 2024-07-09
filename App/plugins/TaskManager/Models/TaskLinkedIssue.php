<?php

namespace Plugins\TaskManager\Models;

use Illuminate\Database\Eloquent\Model;

class TaskLinkedIssue extends Model
{
    protected $guarded = ["id", "created_at", "updated_at"];

    protected $appends = ["link"];

    public function task()
    {
        return $this->belongsTo(Tasks::class, "task_id", "id");
    }

    public function getLinkAttribute()
    {
        if($this->type === 'deal'){
            return route('leads.show', ["id" => $this->doc_id]);
        }

        if($this->type === 'contact'){
            return route('contacts.edit', ["id" => $this->doc_id]);
        }

        if($this->type === 'vehicle'){
            return route('cars.show', ["dealerId" => $this->dealer_id, "carId" => $this->doc_id]);
        }

        if($this->type === 'product'){
            return route('inventory.others.details', ["product" => $this->doc_id]);
        }

        return null;
    }
}
