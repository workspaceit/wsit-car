<?php

namespace App\Observers;

use App\Models\Invitation;
use App\Models\Notification;
use App\Models\Offer;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param \App\Observers\User $user
     * @return void
     */
    public function created(User $user)
    {
        //
    }

    /**
     * Handle the user "updated" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function updated(User $user)
    {
        $dealers = (array)$user->dealers ?? [];
        $invitations = $user->relational_users()->where('status', Invitation::STATUS_ACCEPTED)->get();
        foreach ($dealers as $dealer) {
            $invitations->each(function ($invitation) use ($dealer) {
                $invitation->relationship()->updateOrCreate([
                    'dealer_id' => $dealer,
                    'user_id' => $invitation->invite_to
                ]);
            });
        }
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function deleted(User $user)
    {

        $notifications = Notification::where(function ($query) use ($user){
            $query->where('created_by', $user->id)->orWhere('notified_to', $user->id);
        })->delete();

        $offers = Offer::where(function ($query) use ($user){
            $query->where('offered_by', $user->id)->orWhere('offered_to', $user->id);
        })->get()->each(function ($offer){
            $offer->messages()->delete();
            $offer->notifications()->delete();
            $offer->delete();
        });
    }

    /**
     * Handle the user "restored" event.
     *
     * @param \App\User $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param \App\User $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
