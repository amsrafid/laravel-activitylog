<?php

return [
    
    /*
    | -----------------------------
    | Control creating activity log
    | When property gains data or not
    | Log will not be created,
    | when value is false and property doesn't contain changes
    | ----------------------------------------------------------------
    */

    'allow_null_properties' => false,

    /*
    | -------------------------------------------------------------------------------
    | Clean activity log record
    | before the given date bellow
    | This will only be functional when 'php artisan clean:log' command will be added
    | to system kernel as schedule
    | Minimum day value is 1
    | ----------------------------------------------------------------------------------------------
    */

    'clean_log_before_days' => 30,   // Days

    /*
    | -------------------------
    | Contains Default log name
    | --------------------------------
    */
    'default_log_name' => 'default',

    /*
    | -------------------------
    | Contains Default log description
    | --------------------------------------
    */
    'default_description' => 'Activity log description.',

];
