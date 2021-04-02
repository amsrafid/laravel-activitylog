<?php

namespace Amsrafid\ActivityLog\Traits;

use Illuminate\Support\Str;
use Amsrafid\ActivityLog\Logging;

/**
 * Activity log operation Handler
 */
trait ActivityLogHandler
{
    use PropertyHandler;
    
    /**
     * Data modification bag
     * 
     * @var array
     */
    private $property = [];

    /**
     * Handle log mode
     * like, insert/update/delete
     * 
     * @var string
     */
    private $mode;

    /**
     * Ignore logging
     * like, insert/update/delete
     * 
     * @var array
     */
    private $ignore_logging = [];

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $this->mode = $this->getMode();

        $save = parent::save($options);
        
        $this->validateLog();

        return $save;
    }

    /**
     * Delete the model from the database.
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function delete()
    {
        $this->mode = 'delete';
        
        $this->performDelete();
    }

    /**
     * Force Delete the model from the database.
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function forceDelete()
    {
        $this->mode = 'forceDelete';
        
        $this->performDelete();
    }

    /**
     * Perform delete operation with logging
     * 
     * @return bool|null
     *
     * @throws \Exception
     */
    private function performDelete()
    {
        $this->property = $this->setProperty([], $this->attributes);
        
        $delete = parent::delete();

        $this->validateLog();

        return $delete;
    }

    /**
     * Validate when log is allowed to be created
     * 
     * @return void
     */
    private function validateLog()
    {
        if (isset($this->ignore_log)) {
            $this->ignore_logging = $this->ignore_log;
        }
        
        if (! in_array($this->mode, $this->ignore_logging)) {
            $this->saveLogging();
        }
    }

    /**
     * Create log when save method is called
     * 
     * @return void
     */
    private function saveLogging()
    {
        $instance = $this->getLogInstance();
        $instance->property($this->property);
        $instance->primaryId($this->getKeyForSaveQuery());
        $instance->create();
    }
    
    /**
     * Create log instance with 
     * 
     * @return \Amsrafid\ActivityLog\Logging
     */
    private function getLogInstance()
    {
        $globalScopes = [
            'log_name',
            'description',
            'property',
            'ignore_fields'
        ];

        $model = get_class($this);

        $log = new Logging($model, $this->mode);

        foreach($globalScopes as $scopes) {
            $method = Str::camel($scopes);

            if (! empty($this->{$scopes}) && method_exists($log, $method)) {
                $log->{$method}($this->{$scopes});
            }
        }

        return $log;
    }

    /**
     * Get mode as insert or update
     * 
     * return string
     */
    public function getMode()
    {
        if (! empty($this->original)) {
            $this->setProperty($this->attributes, $this->original);

            return 'update';
        }

        $this->property = $this->setProperty($this->attributes);

        return 'insert';
    }
}
