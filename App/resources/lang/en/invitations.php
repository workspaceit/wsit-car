<?php

return [
    'inviter'       => "Inviter",
    'user'          => 'Username',
    'type'          => 'Type',
    'invited_at'    => 'Invited At',
    'accept'        => 'Accept',
    'reject'        => 'Reject',
    'menu'          => [
        'invitations' => 'Invitations'
    ],
    'breadcrumbs'   => [
        'invitations'        => 'Invitations',
        'add_new_invitation' => 'Add New Invitation'
    ],
    'form'          => [
        'dealer'      => 'Dealer',
        'buyer'       => 'Buyer',
        'exporter'    => 'Exporter',
        'wholesaler'  => 'Wholesaler',
        'transporter' => 'Transporter',
        'select_type' => 'Select Type',
        'search'      => 'Search...',
        'filter'      => 'Filter',
        'car_access'  => "Users",
        'sent_invitation' => "Send Invitation",
        'are_you_sure_you_want_to_accept_invitation' => "Are you sure you want to accept this invitation?",
        'are_you_sure_you_want_to_reject_invitation' => "Are you sure you want to reject this invitation?",
    ],
    'messages'      => [
        'note'   => 'Note',
        'phantom' => 'Selected invitation does not exists or is inaccessible!.',
        'create' => [
            'error'   => 'Failed to sent an invitation.',
            'success' => 'Successfully sent an invitation.',
            'invitation_accepted' => 'You already sent a relationship invitations to this user.',
        ],
        'status' => [
            'error'   => 'Failed to modify an invitation status.',
            'success_accepted' => 'Successfully update an invitation status to accepted.',
            'success_rejected' => 'Successfully update an invitation status to rejected.',
        ],
        'delete' => [
            'error'   => 'Failed to delete an invitation.',
            'success' => 'Successfully delete an invitation.'
        ]
    ],
    'notifications' => [
        'sent' => ":user, sent you invitations for relationship.",
        'accepted' => ":user, accept your relationship invitations.",
        'rejected' => ":user, reject your relationship invitations.",
    ]
];
