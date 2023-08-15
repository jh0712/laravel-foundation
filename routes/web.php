<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Jobs\KiteJobs;
use App\Jobs\BatchJobs;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
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

require __DIR__.'/auth.php';

Route::get('jobs/{jobs}',function($jobs){
   $user = User::find(1);
   for($i = 0; $i<$jobs;$i++){
       KiteJobs::dispatch($user);
   }
   return 'Jobs processing';
});
Route::get('batchjobs',function(){
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
    ->dispatch();;

    return 'Batch: '. $batch->id . 'is processing';
});
