<?php

return [
    'inbox'         => 'Inbox',
    'title'         => 'Offers',
    'accepted'      => 'Accepted',
    'replied'       => 'Replied',
    'refused'       => 'Refused',
    'message'       => 'Message',
    'read_more'     => 'Read more',
    'read_less'     => 'Read less',
    'table'         => [
        'offer_title'    => 'Offer',
        'amount'         => 'Amount',
        'offer_by'       => 'Offer By',
        'offer_to'       => 'Offer To',
        'offer_date'     => 'Date',
        'vehicle'        => 'Vehicle',
        'vin'            => 'Vin',
        'control'        => 'Control',
        'status'         => 'Status',
        'sender'         => 'Sender',
        'dealer'         => 'Seller',
        'offer'          => ':user :amount$ offer sent regarding to vehicle :vehicle.',
        'offer_accepted' => ':amount$ offer regarding to vehicle :vehicle accepted by :user.',
        'offer_refused'  => ':amount$ offer regarding to vehicle :vehicle refused by :user.',
        'offer_alert'    => ':amount$ offer regarding to vehicle :vehicle',
    ],
    'buttons'       => [
        'show'          => 'Show',
        'send'          => 'Send',
        'message'       => 'Message',
        'accept_offer'  => 'Accept Offer',
        'refuse_offer'  => 'Refuse Offer',
        'send_offer'    => 'Send Offer',
        'pending_offer' => 'Pending',
    ],
    'messages'      => [
        'you'         => 'You',
        'title'       => 'Messages',
        'placeholder' => 'Enter your message...',
        'create'      => [
            'error'   => 'Failed to send offer.',
            'success' => 'Successfully offer send to seller.',
            'phantom' => 'Failed to send new offer. Your previously send offer is in under review. Please wait for performing action by seller regarding to your previous sending offer.',
        ],
        'inbox'       => [
            'create' => [
                'error'   => 'Failed to send message.',
                'success' => 'Successfully offer message sent.',
            ]
        ],
        'accept'      => [
            'success' => 'Successfully offer mark as accepted.',
            'notify'  => "Are you sure you want to accept"
        ],
        'refuse'      => [
            'success' => 'Successfully offer mark as refused.',
            'notify'  => "Are you sure you want to refuse"
        ],
        'destroy'     => [
            'success' => 'Successfully delete the offer.',
            'notify'  => "Are you sure you want to delete"
        ]
    ],
    'notifications' => [
        'title'                       => "Notifications",
        'notify_title'                => "Title",
        'notified_at'                 => "Notified At",
        'not_found'                   => 'All caught up! No new notifications.',
        'offer_sent'                  => ':user :amount$ offer sent you regarding to vehicle :vehicle.',
        'offer_accepted'              => ':amount$ offer accepted regarding to vehicle :vehicle.',
        'offer_refused'               => ':amount$ offer refused regarding to vehicle :vehicle.',
        'offer_message'               => ':user, message you regarding to offer :amount$ on :vehicle.',
        'messages'                    => [
            'notify_title' => ':user, message you regarding to offer :amount on :vehicle.'
        ],
        'sse_all_notification_button' => "See All Notifications"
    ]
];
