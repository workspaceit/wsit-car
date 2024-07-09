<?php

return [
    "form" => [
        'email'            => 'Email',
        'username'         => "Username",
        'password'         => "Password",
        'dealers'          => "Dealers",
        'type'             => 'Type',
        'status'           => "Status",
        'active'           => "Active",
        'pending'          => "Pending",
        'approve'          => "Approve",
        'inactive'         => "Inactive",
        'control'          => 'Control',
        'local'            => 'Local',
        'wholesale'        => 'Wholesale',
        'exporter'         => 'Exporter',
        'level'            => 'Level',
        'save'             => "Save",
        'active_at'        => "Active At",
        'last_activity_at' => "Last Activity",
        'language'         => "Default Language",
        'email_notify'     => "Task Notification",
        'or'               => "or",
        'sign_in'          => "Sign In",
        'sign_up'          => "Sign Up",
        "sign_in_using_facebook" => "Continue with Facebook",
        "sign_in_using_google" => "Continue with Google",
        "sign_in_as_an_individual" => "Sign up as an individual",
        "go_back_to_login" => "Go Back to Login",
        'validations'      => [
            'email' => [
                'unique' => "Account is already registered, <a class='text-primary' href = " . route('password.request') .">recover password.</a>"
            ]
        ]
    ]
];
