<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Liens externes
    |--------------------------------------------------------------------------
    |
    | Le même codebase tourne hébergé ou self-hosté : ces liens sont pilotés
    | par variables d'environnement pour pouvoir être adaptés sans toucher
    | au code.
    |
    */

    'coffee_url' => env('LEMUR_COFFEE_URL', 'https://buymeacoffee.com/tfa.the.forge.agency'),

    'github_url' => env('LEMUR_GITHUB_URL', 'https://github.com/The-Forge-Agency/LeMur'),

];
