<?php

namespace Amsrafid\ActivityLog;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Amsrafid\ActivityLog\Traits\Barrier;
use Amsrafid\ActivityLog\Models\ActivityLog;
use Amsrafid\ActivityLog\ActivityLogException;
use Amsrafid\ActivityLog\Traits\PropertyHandler;

class Logging
{
    use PropertyHandler, Barrier;

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
    protected $description = 'Activity log description.';

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
        'delete',
        'forceDelete'
    ];

    /**
     * Property
     * 
     * @var array
     */
    protected $property = [];

    /**
     * Primary key of table records to be logged
     * 
     * @var int|null
     */
    protected $primary_id = null;

    /**
     * Configuration information
     * 
     * @var array
     */
    protected $config = null;

    /**
     * Ignore log fields
     * listed filed change will not to be logged
     * 
     * @var array
     */
    protected $ignore_fields = [
        'created_at',
        'updated_at'
    ];

    /**
     * Model Instance
     * 
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    protected $modelInstance = null;

    /**
     * ActivityLog class constructor
     * 
     * @param \Illuminate\Database\Eloquent\Model|string    $model      Model name or instance
     * @param string                                        $mode       DEFAULT insert
     * @return void
     */
    public function __construct($model, $mode = 'insert')
    {
        $this->config = config('activitylog');
        $this->mode = $mode;

        if (isset($this->config['default_log_name'])) {
            $this->log_name = $this->config['default_log_name'];
        }

        if (isset($this->config['default_description'])) {
            $this->description = $this->config['default_description'];
        }

        if (! \in_array($mode, $this->loggingModes)) {
            throw new ActivityLogException("Logging mode must be " . implode("/", $this->loggingModes) . ".");
        }

        if (\is_string($model)) {
            $this->model = $model;

            return;
        }
        
        if ($model instanceof Model) {
            $this->modelInstance = $model;

            $this->formatProperty($model);

            $this->model = get_class($model);

            $this->primary_id = $model->getKey();

            return;
        }
        
        throw new ActivityLogException("Model must be a string or an instance of \Illuminate\Database\Eloquent\Model in type.");
    }

    /**
     * Create an activity log
     * 
     * @return boolean
     */
    public function create()
    {
        $query = null;

        if (is_null($this->mode)) {
            throw new ActivityLogException("Logging mode must not be empty.");
        }

        if (! empty($this->ignore_fields)) {
            $this->trimProperty($this->property);
        }

        if (! $this->config['allow_null_properties'] && ! $this->propertyValidate()) {
            return true;
        }

        try {
            DB::beginTransaction();
            
            if ($this->modelInstance instanceof Model) {
                $query = $this->dispatchQuery();
            }
            
            if (! self::isPaused()) {
                $this->dispatchLog();
            }

            DB::commit();
            
            return $query;
        } catch (\Throwable $th) {
            DB::rollBack();

            throw new ActivityLogException($th->getMessage());
        }
    }

    /**
     * Dispatch activity log
     * 
     * @return bool
     */
    private function dispatchLog()
    {
        $log = new ActivityLog();
        $log->log_name = $this->log_name;
        $log->mode = $this->mode;
        $log->description = $this->description;
        $log->model = $this->model;
        $log->primary_id = $this->primary_id;
        
        if (auth()->check()) {
            $log->user_id = auth()->user()->id;
        }

        $log->properties = json_encode($this->property);
        
        return $log->save();
    }
     
    /**
     * Dispatch query when Model instance has been found
     * 
     * @return bool|null
     */
    private function dispatchQuery()
    {
        if (! ($this->modelInstance instanceof Model)) {
            return null;
        }

        if (\in_array($this->mode, ['delete', 'forceDelete'])) {
            return $this->modelInstance->{$this->mode}();
        }
        
        $save = $this->modelInstance->save();
        $this->primary_id = $this->modelInstance->getKey();

        return $save;
    }

    /**
     * Format property with respect to mode
     * 
     * @param   \Illuminate\Database\Eloquent\Model     $model
     * @return array
     */
    public function formatProperty($model)
    {
        if (\in_array($this->mode, ['delete', 'forceDelete'])) {
            return $this->property = [
                'old' => $model->getRawOriginal()
            ];
        }

        if ($this->mode == 'insert') {
            return $this->property = [
                'new' => $model->getAttributes()
            ];
        }

        return $this->setProperty($model->getAttributes(), $model->getRawOriginal());
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
     * @return \Amsrafid\ActivityLog\Logging
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
     * @return \Amsrafid\ActivityLog\Logging
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
     * @return \Amsrafid\ActivityLog\Logging
     */
    public function mode($name)
    {
        $name = strtolower($name);
        
        if (! in_array($name, $this->loggingModes)) {
            throw new ActivityLogException("Logging mode must be " . implode("/", $this->loggingModes) . ".");
        }

        $this->mode = $name;

        return $this;
    }

    /**
     * Assign model name
     * 
     * @param string $modelName
     * @return \Amsrafid\ActivityLog\Logging
     */
    public function model($modelName)
    {
        $this->model = $modelName;

        return $this;
    }

    /**
     * Add ignore fields
     * 
     * @param mixed $fields
     * @return \Amsrafid\ActivityLog\Logging
     */
    public function ignoreFields(...$fields)
    {
        array_walk_recursive($fields, function($field) {
            $this->ignore_fields [] = $field;
        });
        
        return $this;
    }

    /**
     * Trim keys of data not to be logged
     * 
     * @param array &$set   Data set in which key has to be considered
     * @return void
     */
    public function trimProperty(array &$set)
    {
        array_walk($set, function(&$value, $key) use(&$set) {
            if (is_array($value)) {
                return $this->trimProperty($value);
            }
            
            if (in_array($key, $this->ignore_fields)) {
                unset($set[$key]);
            }
        });
    }

    /**
     * Assign primary id
     * 
     * @param string $id
     * @return \Amsrafid\ActivityLog\Logging
     */
    public function primaryId($id)
    {
        $this->primary_id = $id;

        return $this;
    }

    /**
     * Extends log property
     * 
     * @param array $extendedProperty
     * @return \Amsrafid\ActivityLog\Logging
     */
    public function property($extendedProperty)
    {
        $this->property = array_merge($this->property, $extendedProperty);

        return $this;
    }

    /**
     * Start Logging
     * 
     * @return boolean
     */
    public function start()
    {
        return $this->create();
    }
}