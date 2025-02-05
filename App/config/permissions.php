<?php

return [
    'dashboard'            => ['index', 'report'],
    'vehicles'             => ['listing', 'store', 'modify', 'view', 'download', 'destroy'],
    'products'             => ['listing', 'store', 'modify', 'view', 'download', 'destroy'],
    'locations'            => ['listing', 'view'],
    'dealers'              => ['listing', 'store', 'modify', 'fetch', 'flash', 'truncate', 'destroy'],
    'histories'            => ['listing', 'destroy', 'view'],
    'makes'                => ['listing', 'store', 'modify', 'destroy'],
    'models'               => ['listing', 'store', 'modify', 'destroy'],
    'categories'           => ['listing', 'store', 'modify', 'destroy'],
    'users'                => ['listing', 'store', 'modify', 'destroy'],
    'members'              => ['listing', 'store', 'modify', 'destroy'],
    'tasks'                => ['listing', 'store', 'modify', 'destroy'],
    'provinces'            => ['listing', 'store', 'modify', 'destroy'],
    'settings'             => ['modify', 'view'],
    'watermarks'           => ['listing', 'store', 'modify', 'apply', 'destroy'],
    'expenses'             => ['listing', 'store', 'modify', 'destroy'],
    'documents'            => ['listing', 'store', 'modify'],
    'leads'                => ['listing', 'store', 'modify', 'destroy'],
    'contacts'             => ['listing', 'store', 'modify', 'destroy'],
    'reports'              => ['listing', 'modify', 'destroy'],
    'errors'               => ['listing', 'destroy'],
    'logs'                 => ['listing', 'view', 'destroy'],
    'accounting'           => ['listing', 'store', 'modify', 'destroy'],
    'payment_histories'    => ['listing', 'store', 'modify', 'destroy'],
    'expense_histories'    => ['listing', 'store', 'modify', 'destroy'],
    'payments'             => ['listing', 'store', 'modify', 'destroy'],
    'supports'             => ['listing', 'store', 'modify', 'destroy'],
    'offers'               => ['listing', 'store', 'modify', 'destroy'],
    'messages'             => ['listing', 'store', 'modify', 'destroy'],
    'invitations'          => ['listing', 'store', 'modify', 'destroy'],
    'notifications'        => ['listing', 'store', 'modify', 'destroy'],
    'transporter_requests' => ['listing', 'store', 'modify', 'destroy'],
];
