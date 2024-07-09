<?php

return [
    'contacts' => [
        'exact_matched' => [
            'error' => "Échec de la création d'un contact. Les champs demandés par le contact correspondent exactement à ceux d'un contact.",
        ],
        'create'        => [
            'success' => "Contact créé avec succès.",
            'error'   => "La mise à jour des contacts a échoué !",
        ],
        'update'        => [
            'success' => "Cette information est liée à :name contact, Contact mis à jour avec succès.",
            'error'   => "Échec de la mise à jour du contact !",
        ]
    ],
    'alerts' => [
        'dealer_not_assigned'   => 'Le concessionnaire ne vous est pas attribué.',
    ],
    'success' => [
        'updated' => ':attribute est mis à jour !',
    ],
];
