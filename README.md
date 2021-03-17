## Laravel Activity Log

Simple but very powerful activity log package for laravel framework.

## Basic Use

Activity Log is very easy to use. A trait named **_ActivityLogHandler_** is to be added to any model to activate logging. On the other hand log can be enabled manually by initiating **_ActivityLog_** class. Auto logging system can be added as like bellow,

~~~php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Amsrafid\ActivityLog\Traits\ActivityLogHandler;

class MyModel extends Model
{
    use ActivityLogHandler;

    // Optionals
    protected $log_name = "Log name";

    protected $description = "Log description";

    protected $ignore_log = [
        // if any logging mode is to be ignored in (insert, update, delete)
    ];
}
~~~

Manual logging system is added bellow,

~~~php
// Insertion operation
$myModel = new MyModel;
$myModel->data = 'value';
$myModel->save();

// Create new Activity Log
$log = new ActivityLog(MyModel::class, 'insert');   // model name, mode -> [insert, update, delete]
$log->property([
    'new' => [
        'data' => 'value'
    ]
]);
$log->logName('Save my model');
$log->description('My model log has been created manually.');
$log->primary_id = $myModel->id;
$log->create();
~~~

_Note: Clear configuration cache to active configuration file. Otherwise, log may not be created._

## Authors

_Initial development_ - **_A. M. Sadman Rafid_**

## Security Vulnerabilities

If you discover a security vulnerability within Laravel Activity Log, please send an e-mail to _A. M. Sadman Rafid_ via [amsrafid@gmail.com](mailto:amsrafid@gmail.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel Activity Log is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
