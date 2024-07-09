<?php

return [
    'contacts' => [
        'exact_matched' =>[
            'error' => "No se ha podido crear un contacto. Los campos solicitados del contacto coinciden exactamente con un contacto.",
        ],
        'create' => [
            'success' => "Contacto creado con éxito.",
            'error' => "La creación del contacto ha fallado.",
        ],
        'update' => [
            'success' => "SEsta información relacionada con :name contacto, Contacto actualizado con éxito.",
            'error' => "No se ha podido actualizar el contacto.",
        ]
    ],
];
