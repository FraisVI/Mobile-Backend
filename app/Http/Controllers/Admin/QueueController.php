<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class QueueController extends AdminController
{
    public function failedJobs()
    {
        $title = 'Упавшие задачи (Failed Jobs)';
        
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->paginate(50);
        
        return view('admin.queue.failed-jobs', compact('title', 'failedJobs'));
    }
    
    public function retryJob($id)
    {
        try {
            Artisan::call('queue:retry', ['id' => [$id]]);
            return redirect()->route('queue.failed-jobs')->with('success-message', 'Задача поставлена в очередь на повтор.');
        } catch (\Throwable $e) {
            return redirect()->route('queue.failed-jobs')->with('error-message', 'Ошибка: ' . $e->getMessage());
        }
    }
    
    public function retryAll()
    {
        try {
            Artisan::call('queue:retry', ['id' => ['all']]);
            return redirect()->route('queue.failed-jobs')->with('success-message', 'Все упавшие задачи поставлены в очередь на повтор.');
        } catch (\Throwable $e) {
            return redirect()->route('queue.failed-jobs')->with('error-message', 'Ошибка: ' . $e->getMessage());
        }
    }
    
    public function forgetJob($id)
    {
        try {
            Artisan::call('queue:forget', ['id' => $id]);
            return redirect()->route('queue.failed-jobs')->with('success-message', 'Задача удалена из списка упавших.');
        } catch (\Throwable $e) {
            return redirect()->route('queue.failed-jobs')->with('error-message', 'Ошибка: ' . $e->getMessage());
        }
    }
    
    public function flushAll()
    {
        try {
            Artisan::call('queue:flush');
            return redirect()->route('queue.failed-jobs')->with('success-message', 'Все упавшие задачи удалены.');
        } catch (\Throwable $e) {
            return redirect()->route('queue.failed-jobs')->with('error-message', 'Ошибка: ' . $e->getMessage());
        }
    }
}
