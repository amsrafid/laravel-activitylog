<?php

namespace Amsrafid\ActivityLog\Traits;

/**
 * Activity log property Handler
 */
trait PropertyHandler
{
    /**
     * Set property value for update
     * 
     * @return void
     */
    public function setProperty($attributes, $original)
    {
        $oldValue = [];
        $newValue = array_diff_assoc($attributes, $original);

        array_map(function($key) use(&$oldValue, $original) {
            $oldValue[$key] = $original[$key];
        }, array_keys($newValue));


        $this->property = [
            'old' => $oldValue,
            'new' => $newValue
        ];
    }
}