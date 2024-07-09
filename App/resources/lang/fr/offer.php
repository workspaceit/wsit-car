<?php

return [
    'inbox'         => "Boîte de réception",
    'title'         => 'Offres',
    'accepted'      => 'Accepté',
    'replied'       => 'Réponse',
    'refused'       => 'Refusé',
    'message'       => 'Message',
    'read_more'     => "Lire la suite",
    'read_less'     => "Lire la moins",
    'table'         => [
        'offer_title'    => 'Offre',
        'amount'         => 'Montant',
        'offer_by'       => 'Offrir par',
        'offer_to'       => 'Offre à',
        'offer_date'     => "Date",
        'vehicle'        => 'Véhicule',
        'vin'            => 'Vin',
        'control'        => 'Contrôle',
        'status'         => 'Statut',
        'sender'         => "Expéditeur",
        'dealer'         => "Vendeur",
        'offer'          => ':user :amount$ offer sent regarding to vehicle :vehicle.',
        'offer_accepted' => ':amount$ offer regarding to vehicle :vehicle accepted by :user.',
        'offer_refused'  => ':amount$ offer regarding to vehicle :vehicle refused by :user.',
        'offer_alert'    => ":amount$ offre concernant le véhicule :vehicle",
    ],
    'buttons'       => [
        'show'         => 'Afficher',
        'send'         => 'Envoyer',
        'message'      => 'Message',
        'accept_offer' => "Accepter l'offre",
        'refuse_offer' => 'Offre de refus',
        'send_offer'   => "Envoyer l'offre",
        'pending_offer' => "En attente",
    ],
    'messages'      => [
        'you'         => 'Vous',
        'title'       => 'Messages',
        'placeholder' => 'Entrez votre message...',
        'create'      => [
            'error'   => "Échec de l'envoi de l'offre",
            'success' => "Offre réussie envoyée au vendeur.",
            'phantom' => "L'envoi de la nouvelle offre a échoué. Votre offre envoyée précédemment est en cours d'examen. Veuillez attendre l'exécution de l'action du vendeur concernant l'envoi de votre offre précédente.",
        ],
        'inbox'       => [
            'create' => [
                'error'   => "Échec de l'envoi du message.",
                'success' => "Message d'offre envoyé avec succès.",
            ]
        ],
        'accept'      => [
            'success' => "Réussir à proposer la marque comme acceptée.",
            'notify'  => "Vous êtes sûr de vouloir accepter"
        ],
        'refuse'      => [
            'success' => "Réussir à proposer de marquer comme refusé.",
            'notify'  => "Vous êtes sûr de vouloir refuser"
        ],
        'destroy'     => [
            'success' => "Supprimez l'offre avec succès.",
            'notify'  => "Vous êtes sûr de vouloir supprimer"
        ]
    ],
    'notifications' => [
        'title'                       => "Notifications",
        'not_found'                   => "Tout est en place ! Pas de nouvelles notifications.",
        'notify_title'                => "Titre",
        'notified_at'                 => "Notifié à",
        'offer_sent'                  => ':user :amount$ offre envoyée concernant le véhicule :vehicle.',
        'offer_accepted'              => ':amount$ offre acceptée concernant le véhicule :vehicle.',
        'offer_refused'               => ':amount$ offre refusée concernant le véhicule :vehicle.',
        'offer_message'               => ":user, message concernant l'offre :amount$ on :vehicle.",
        'messages'                    => [
            'notify_title' => ":user, message concernant l'offre :amount on :vehicle."
        ],
        'sse_all_notification_button' => "Voir toutes les notifications"
    ]
];
