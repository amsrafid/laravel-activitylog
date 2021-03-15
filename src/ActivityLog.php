<?php

namespace Amsrafid\ActivityLog;

use Illuminate\Support\Facades\Facade;
use Amsrafid\ActivityLog\ActivityLogException;
use Amsrafid\ActivityLog\Models\ActivityLog as ModelsActivityLog;

class ActivityLog extends Facade
{
    /**
     * Log name
     * 
     * @var string
     */
    protected $log_name = 'default';

    /**
     * Log mode for insert/update/delete
     * 
     * @var string
     */
    protected $mode = null;

    /**
     * Description
     * 
     * @var string
     */
    protected $description = 'Activity log';

    /**
     * Model name
     * 
     * @var null
     */
    protected $model = null;

    /**
     * Log mode
     * 
     * @var array
     */
    protected $loggingModes = [
        'insert',
        'update',
        'delete'
    ];

    /**
     * Property
     * 
     * @var array
     */
    protected $property = [];

    /**
     * Configuration information
     * 
     * @var array
     */
    protected $config = null;
    
    /**
     * ActivityLog class constructor
     * 
     * @param \Illuminate\Database\Eloquent\Model|string    $modelName
     * @param string                                        $mode
     * @return void
     */
    public function __construct($modelName, $mode = '')
    {
        $this->config = config('activitylog');

        if (is_string($modelName)) {
            $this->model = $modelName;
        } else if ($modelName instanceof \Illuminate\Database\Eloquent\Model) {
            $this->model = get_class($modelName);
        } else {
            throw new ActivityLogException("Model must be a string or an instance of \Illuminate\Database\Eloquent\Model in type.");
        }

        if (! empty($mode)) {
            $this->mode = $mode;
        }
    }

    /**
     * Create an activity log
     * 
     * @return boolean
     */
    public function create()
    {
        if (is_null($this->mode)) {
            throw new ActivityLogException("Logging mode must not be empty.");
        }

        if (! $this->config['allow_null_properties'] && ! $this->propertyValidate()) {
            return true;
        }
        
        $log = new ModelsActivityLog();
        $log->log_name = $this->log_name;
        $log->mode = $this->mode;
        $log->description = $this->description;
        $log->model = $this->model;
        $log->primary_id = $this->primary_id;
        
        if (isset(auth()->user()->id)) {
            $log->user_id = auth()->user()->id;
        }

        $log->properties = json_encode($this->property);

        if (! $log->save()) {
            return false;
        }

        return true;
    }

    /**
     * Property change listener
     * 
     * @return boolean
     */
    public function propertyValidate()
    {
        if (array_filter($this->property)) {
            return true;
        }

        return false;
    }
    
    /**
     * Assign Log description
     * 
     * @param array $description
     * @return \Amsrafid\ActivityLog\ActivityLog
     */
    public function description($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Assign Log name
     * 
     * @param array $name
     * @return \Amsrafid\ActivityLog\ActivityLog
     */
    public function logName($name)
    {
        $this->log_name = $name;

        return $this;
    }

    /**
     * Assign log mode
     * 
     * @param string $name
     * @return \Amsrafid\ActivityLog\ActivityLog
     */
    public function mode($name)
    {
        $name = strtolower($name);
        
        if (! in_array($name, $this->loggingModes)) {
            throw new ActivityLogException("Logging mode must be " . implode("/ ", $this->loggingModes));
        }

        $this->mode = $name;

        return $this;
    }

    /**
     * Assign model name
     * 
     * @param string $modelName
     * @return \Amsrafid\ActivityLog\ActivityLog
     */
    public function model($modelName)
    {
        $this->model = $modelName;

        return $this;
    }

    /**
     * Extends log property
     * 
     * @param array $extendedProperty
     * @return \Amsrafid\ActivityLog\ActivityLog
     */
    public function property($extendedProperty)
    {
        $this->property = array_merge($this->property, $extendedProperty);

        return $this;
    }
}