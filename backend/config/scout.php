<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    */
    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    */
    'prefix' => env('SCOUT_PREFIX', 'medicon_'),

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queue' => env('SCOUT_QUEUE', false),

    /*
    |--------------------------------------------------------------------------
    | Meilisearch Configuration
    |--------------------------------------------------------------------------
    */
    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://meilisearch:7700'),
        'key' => env('MEILISEARCH_KEY', 'medicon_meili_secret'),
        'index-settings' => [
            \App\Models\Doctor::class => [
                'filterableAttributes' => ['specialty', 'consultation_fee_cents', 'is_active'],
                'sortableAttributes' => ['consultation_fee_cents', 'rating', 'years_of_experience'],
                'searchableAttributes' => ['name', 'specialty', 'bio', 'license_number'],
            ],
            \App\Models\MedicalRecord::class => [
                'filterableAttributes' => ['patient_id', 'doctor_id'],
                'searchableAttributes' => ['diagnosis', 'clinical_notes', 'treatment_plan', 'icd_10_codes'],
            ],
            \App\Models\Prescription::class => [
                'filterableAttributes' => ['patient_id', 'doctor_id', 'is_dispensed'],
                'searchableAttributes' => ['medications', 'notes'],
            ],
        ],
    ],
];
