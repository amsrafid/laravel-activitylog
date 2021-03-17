<?php

namespace Amsrafid\ActivityLog\Traits;

use Illuminate\Support\Str;
use Amsrafid\ActivityLog\ActivityLog;

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
        $this->property = [
            'old' => $this->attributes
        ];

        $delete = parent::delete();

        $this->validateLog();

        return $delete;
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
        $instance->primary_id = $this->getKeyForSaveQuery();
        $instance->create();
    }
    
    /**
     * Create log instance with 
     */
    private function getLogInstance()
    {
        $globalScopes = [
            'log_name',
            'description',
            'property'
        ];

        $model = get_class($this);

        $log = new ActivityLog($model, $this->mode);

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

        $this->property = [
            'new' => $this->attributes
        ];

        return 'insert';
    }
}
