<?php

namespace App\Events;

use App\Models\ChangeLog;
use App\Models\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ItemEventObserver
{

    /**
     * Savig event handler callback, when fired, it sets the modified attributes
     * @param Model $model
     * @return boolean True
     */
    public function saving(Model $model)
    {
        $model->setModifiedAttributes();

        return true;
    }

    /**
     * Saved event handler callback, when fired, create a history log
     * @param Model $model
     * @return void
     */
    public function saved(Model $model)
    {
        $this->performAction($model, 'changed');
        $model->clearModifiedAttributes();
    }

    /**
     * Deleted event handler callback, when fired, create a history log
     * @param Model $model
     * @return void
     */
    public function deleted(Model $model)
    {
        $model->setCustomAttributes($model->getOriginal());
        $this->performAction($model, 'deleted');
        $model->clearModifiedAttributes();
    }

    /**
     * ForceDeleted event handler callback, when fired, create a history log
     * @param Model $model
     * @return void
     */
    public function forceDeleted(Model $model)
    {
        $model->setCustomAttributes($model->getOriginal());
        $this->performAction($model, 'permanently deleted');
        $model->clearModifiedAttributes();
    }

    /**
     * Perform the action
     * @param Model $model
     * @param type $event
     * @return HistoryLog|null Returns null when nothing changes
     */
    public function performAction(Model $model, $event)
    {
        $changedValues = $model->getModifiedAttributes();
        Log::info("User Change log : " . json_encode($changedValues));

        if (count($changedValues)) {
            $prevLogs = ChangeLog::where('model_id', $model->id)
            ->where('model_type', get_class($model))
            ->first()->data ?? [];

            foreach($changedValues as $key => $log){
                $prevLogs[$key] = $log;
            }

            $history = ChangeLog::updateOrCreate([
                'model_id' => $model->id,
                'model_type' => get_class($model)
            ],[
                'identity_number' => $model->sku,
                'title'           => $model->title,
                'dealer_id'       => $model->dealer_id,
                'data'            => json_encode($prevLogs),
                'category'        => $model->category->name ?? null,
                'sub_category'    => $model->sub_category->name ?? null,
                'user_id'         => auth()->check() ? auth()->user()->id: null,
                'username'        => auth()->check() ? auth()->user()->username: null,
                'status_id'       => Status::firstOrCreate(['name' => $event])->id ?? null,
            ]);

            return $history->writeAuthChangeLogs($changedValues);
        }

        return null;
    }
}
