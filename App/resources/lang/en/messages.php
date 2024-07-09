<?php

return [
    'contacts' => [
        'exact_matched' => [
            'error' => "Failed to create a contact. Contact requested fields exactly match with a contact.",
        ],
        'create'        => [
            'error'   => 'Contact creation failed!',
            'success' => 'Contact created successfully.',
        ],
        'update'        => [
            'error'   => 'Failed to update the contact!',
            'success' => 'This info related to :name contact, Contact updated successfully.',
        ]
    ],
    'alerts' => [
        'dealer_not_assigned'   => 'The dealer is not assigned to you.',
    ],
    'success' => [
        'updated' => ':attribute is updated!',
    ],
];
