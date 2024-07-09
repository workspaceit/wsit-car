<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */
    'blocked'    => "Your account has been banned.",
    'unverified' => 'Your email address is not yet verified! Please check your inbox/spam for the verification email.',

    'failed' => 'These credentials do not match our records.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    "copy_url_text" => "If you’re having trouble clicking the \":actionText\" button, copy and paste the URL below\n into your web browser: [:actionURL](:actionURL)",
    'reset_password' => [
        "reset_password" => "Reset Password",
        "reset_password_notification" => "Reset Password Notification",
        "reset_password_link_validity" => "This password reset link will expire in :count minutes.",
        "reset_password_no_action_required_notification" => "If you did not request a password reset, no further action is required.",
        "reset_password_eamil_received_notification" => "You are receiving this email because we received a password reset request for your account.",
    ],
    'passwords_confirm' => [
        "confirm" => "Confirm",
        "confirm_password" => "Confirm Password",
        "confirm_password_slogan" => "Please confirm you password before continuing.",
    ]
];
