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
    public function setProperty(array $new = [], array $old = [])
    {
        $oldValue = [];
        $newValue = empty($new) && ! empty($old)
                    ? $old
                    : array_diff_assoc($new, $old);
        
        if (! empty($old)) {
            array_map(function($key) use(&$oldValue, $old) {
                $oldValue[$key] = $old[$key] ?? null;
            }, array_keys($newValue));
        }

        if (! empty($new)) {
            $this->property['new'] = $newValue;
        }

        if (! empty($old)) {
            $this->property['old'] = $oldValue;
        }

        return $this->property;
    }
}
