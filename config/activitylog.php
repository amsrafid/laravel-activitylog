<?php

return [
    
    /*
    | -----------------------------
    | Control creating activity log
    | When
    | Property gains data or not
    | ------------------------------------
    */

    'allow_null_properties' => false,

    /*
    | -------------------------------------------------------------------------------
    | Clean activity log record
    | before the given date bellow
    | This will only be functional when 'php artisan clean:log' command will be added
    | to system kurnel as schedule
    | Minimum day value is 1
    | ----------------------------------------------------------------------------------------------
    */

    'clean_log_before_days' => 30   // Days
];
