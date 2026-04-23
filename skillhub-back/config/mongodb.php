<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MongoDB (journaux d'activité SkillHub)
    |--------------------------------------------------------------------------
    |
    | Même usage que l'ancien skillhub/core/mongo.php : base dédiée aux logs,
    | collection activity_logs. Si MongoDB n'est pas disponible, les appels
    | de log sont ignorés sans faire échouer l'API.
    |
    */

    'uri' => env('MONGODB_URI', 'mongodb://127.0.0.1:27017'),

    'database' => env('MONGODB_DATABASE', 'skillhub'),

];
