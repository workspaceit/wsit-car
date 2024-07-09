<?php

return [
    'inviter'       => "Inviter",
    'user'          => "Nom d'utilisateur",
    'type'          => 'Type',
    'invited_at'    => 'Invité à',
    'accept'        => 'Accepter',
    'reject'        => 'Rejeter',
    'menu'          => [
        'invitations' => 'Invitations'
    ],
    'breadcrumbs'   => [
        'invitations'        => 'Invitations',
        'add_new_invitation' => 'Ajouter une nouvelle invitation'
    ],
    'form'          => [
        'dealer'      => 'Concessionnaire',
        'buyer'       => 'Acheteur',
        'exporter'    => 'Exportateur',
        'wholesaler'  => 'Grossiste',
        'transporter' => 'Transporteur',
        'select_type' => 'type de sélection',
        'search'      => 'Recherche...',
        'filter'      => 'Filtre',
        'car_access'  => "Utilisateurs",
        'sent_invitation' => "Envoyer l'invitation",
        'are_you_sure_you_want_to_accept_invitation' => "Êtes-vous sûr de vouloir accepter cette invitation ?",
        'are_you_sure_you_want_to_reject_invitation' => "Êtes-vous sûr de vouloir rejeter cette invitation ?",
    ],
    'messages'      => [
        'note'   => 'Note',
        'phantom' => "L'invitation sélectionnée n'existe pas ou est inaccessible !",
        'create' => [
            'error'   => "L'envoi d'une invitation a échoué.",
            'success' => "L'invitation a été envoyée avec succès.",
            'invitation_accepted' => "Vous avez déjà envoyé une invitation de relation à cet utilisateur.",
        ],
        'status' => [
            'error'   => "Impossible de modifier le statut d'une invitation.",
            'success_accepted' => "Mise à jour réussie du statut d'une invitation à accepter.",
            'success_rejected' => "Mise à jour réussie d'un statut d'invitation à rejeter.",
        ],
        'delete' => [
            'error'   => "Échec de la suppression d'une invitation.",
            'success' => "Vous avez réussi à supprimer une invitation."
        ]
    ],
    'notifications' => [
        'sent' => ":user, vous a envoyé des invitations pour une relation.",
        'accepted' => ":user, accepter les invitations de votre relation.",
        'rejected' => ":user, rejeter vos invitations à la relation.",
    ]
];
