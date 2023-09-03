<?php

use App\Events\SomeEvent;
use App\Http\Controllers\ProfileController;
use App\Jobs\BatchJobs;
use App\Jobs\KiteJobs;
use App\Mail\SendEmail;
use App\Models\Post;
use App\Models\User;
use App\Notifications\InvoicePaid;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use PHPUnit\Event\Code\Throwable;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::get('jobs/{jobs}', function ($jobs) {
    $user = User::find(1);
    for ($i = 0; $i < $jobs;$i++) {
        KiteJobs::dispatch($user);
    }
    return 'Jobs processing';
});
Route::get('batchjobs', function () {
    $batch = Bus::batch([
        new BatchJobs(User::find(1)),
        new BatchJobs(User::find(2)),
        new BatchJobs(User::find(1)),
        new BatchJobs(User::find(2)),
    ])->then(function (Batch $batch) {
        // All jobs completed successfully...
        Log::info('All jobs completed successfully...');
    })->catch(function (Batch $batch, Throwable $e) {
        // First batch job failure detected...
    })->finally(function (Batch $batch) {
        // The batch has finished executing...
        Log::info('The batch has finished executing...');
    })->name('My batch of jobs')
    ->dispatch();

    return 'Batch: ' . $batch->id . 'is processing';
});

Route::get('cache', function () {
    if (Cache::get('user')) {
        return Cache::get('user');
    }
    Cache::put('user', User::find(1), 8);
    return 'User cached for 8 seconds';
});

Route::get('dump', function () {
    $user1 = User::find(1);
    $user2 = User::find(2);
    dump($user1);
    dump($user2);
    return 'Dump complete';
});

Route::get('events', function () {
    event(new SomeEvent(User::find(1)));
    return 'Event fired.';
});

Route::get('exceptions', function () {
    throw new Exception('this is error message');
});
Route::get('exceptions2', function () {
    // this one can not caught by telescope
    try {
        throw new Exception('this is error message 2');
    } catch (Exception $exception) {
        Log::info($exception->getMessage());
    }
    return 'error pass';
});

Route::get('post_first_gate/{post}/edit', function (Post $post, Request $request) {
    return 'you are editing post first gate';
})->middleware('can:first-gate-update-post,post');

Route::get('post/{post}/edit', function (Post $post, Request $request) {
    return 'you are editing post second gate';
})->middleware('can:view,post');

Route::get('logs', function () {
    Log::emergency('Emergency');
    Log::alert('Alert');
    Log::critical('Critical');
    Log::error('Error');
    Log::warning('Warning');
    Log::notice('Notice');
    Log::info('info');
    Log::debug('debug');
    return 'stuff was logged';
});

Route::get('sendEmail', function () {
    $data['email'] = 'test@gmail.com';
    $data['template'] = 'email.test_email';
    $data['subject'] = 'this is subject';
    $data['conetent'] = 'this is content data';
    Mail::to($data['email'])->send(new SendEmail($data));
    return 'mail sent';
});

Route::get('notification', function () {
    $user = User::find(1);
    $user->notify(new InvoicePaid());
    return 'notification sent';
});

Route::get('setRedis', function () {
    Redis::set('kitetest_key', 'kitetest_value');
    $value = Redis::get('kitetest_key');
    return 'redis is set.';
});
