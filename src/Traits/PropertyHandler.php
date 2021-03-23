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
     * @param array|null $new    New Value of save query
     * @param array|null $old    Old Values of data
     * @return void
     */
    public function setProperty($new = null, $old = null)
    {
        $oldValue = [];
        $newValue = array_diff_assoc($new, $old);

        array_map(function($key) use(&$oldValue, $old) {
            $oldValue[$key] = $old[$key];
        }, array_keys($newValue));

        if (! is_null($new)) {
            $this->property['new'] = $newValue;
        }

        if (! is_null($old)) {
            $this->property['old'] = $oldValue;
        }

        return $this->property;
    }
}