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
     * @param array $new    New Value of save query
     * @param array $old    Old Values of data
     * @return void
     */
    public function setProperty($new, $old)
    {
        $oldValue = [];
        $newValue = array_diff_assoc($new, $old);

        array_map(function($key) use(&$oldValue, $old) {
            $oldValue[$key] = $old[$key];
        }, array_keys($newValue));

        $this->property = [
            'old' => $oldValue,
            'new' => $newValue
        ];
    }
}