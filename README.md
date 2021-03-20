## Laravel Activity Log

Simple but very powerful activity log package for laravel framework.

## Get Started

Activity log has to be started after migrating log table. To make migration, hit the command bellow to the terminal,

~~~php
php artisan migrate
~~~

## Basic Use

Activity Log is very easy to use. A trait named **_ActivityLogHandler_** is to be added to any model to activate logging. On the other hand log can be enabled manually by initiating **_Logging_** class. Auto logging system can be added as like bellow,

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
        // if any logging mode is to be ignored in (insert, update, delete, forceDelete)
    ];
}
~~~

Manual logging system is added bellow,

~~~php
$myModel = MyModel::find(1);
$myModel->data = 'value';
$log = new Logging($myModel, 'update'); // model instance, mode -> [insert, update, delete, forceDelete]
$log->start();
~~~
OR
~~~php
// Insertion operation
$myModel = new MyModel;
$myModel->data = 'value';
$myModel->save();

// Create new Activity Log
$log = new Logging(MyModel::class, 'insert');   // model name, mode -> [insert, update, delete, forceDelete]
$log->property([
    'new' => $myModel->toArray()
]);
$log->logName('Save my model');
$log->description('My model log has been created manually.');
$log->primaryId($myModel->id);
$log->start();
~~~

_Note: Clear configuration cache to active configuration file. Otherwise, log may not be created._

## Barrier

Logging can be paused at any time and to be proceed by using `paused` and `proceed` static method respectively.

~~~php
$myModel = new MyModel;
$myModel->data = 'value 1'; // Log created
$myModel->save();

Logging::paused();  // Logging become paused

$myModel->data = 'value 2'; // Log not created
$myModel->save();

Logging::proceed(); // Logging proceed again

$myModel->data = 'value 3'; // Log created
$myModel->save();
~~~

_Note: Log barrier can be checked by `isPaused` static method._

## Log cleaning up

Log can be deleted when a lot of activity has been recorded. To solve this problem, custom artisan command `clear:log` can help. It can operate when run the command bellow to the command window,

~~~php
php artisan clear:log
~~~
OR
~~~php
php artisan clear:log --day=7
~~~
OR
~~~php
php artisan clear:log --date=2021-03-19
~~~

Here, option `--date` denotes the date before log will be deleted and `--day` to the number of day(s) before log will be cleared. Option _**day**_ will not be applicable when _**date**_ is given.

To operate cleaning automatically, a schedule can be created to console Kernel like bellow,

~~~php
// ~/app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
   $schedule->command('clear:log')->daily();
}
~~~

*Note: Data will be deleted the day(s) before the value of the key `clean_log_before_days` given into config file when `--date` or `--day` option is not given.*

## Authors

_Initial development_ - **_A. M. Sadman Rafid_**

## Security Vulnerabilities

If you discover a security vulnerability within Laravel Activity Log, please send an e-mail to _A. M. Sadman Rafid_ via [amsrafid@gmail.com](mailto:amsrafid@gmail.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel Activity Log is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
