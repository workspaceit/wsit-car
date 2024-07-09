<?php

return [
    "form" => [
        'email'            => "Courriel",
        'username'         => "Nom d'utilisateur",
        'password'         => "Mot de passe",
        'dealers'          => "Concessionnaires",
        'type'             => "Type",
        'status'           => "Statut",
        'active'           => "Actif",
        'pending'          => "En attente",
        'approve'          => "Approuver",
        'inactive'         => "Inactif",
        'control'          => "Contrôle",
        'local'            => "Local",
        'wholesale'        => "Vente en gros",
        'exporter'         => "Exportateur",
        'level'            => "Niveau",
        'save'             => "Sauvez",
        'active_at'        => "Actif à",
        'last_activity_at' => "Dernière activité",
        'language'         => "Langue par défaut",
        'email_notify'     => "Notification des tâches",
        'or'               => "or",
        'sign_in'          => "S'inscrire",
        'sign_up'          => "S'inscrire",
        "sign_in_using_google" => "Continuer avec Google",
        "sign_in_using_facebook" => "Continuer avec Facebook",
        "sign_in_as_an_individual" => "S'inscrire en tant qu'individu",
        "go_back_to_login" => "Retourner à la connexion",
        'validations'      => [
            'email' => [
                'unique' => "Le compte est déjà enregistré, <a class='text-primary' href = " . route('password.request') .">récupérer le mot de passe.</a>"
            ]
        ]
    ]
];
