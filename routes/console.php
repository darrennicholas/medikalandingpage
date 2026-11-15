<?php

use App\Models\JobOpening;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (): void {
    JobOpening::query()->where('status', 'open')->whereDate('closing_date', '<', today())->orderBy('id')->chunkById(100, function ($jobs): void {
        foreach ($jobs as $job) {
            DB::transaction(function () use ($job): void {
                $current = JobOpening::query()->whereKey($job->id)->lockForUpdate()->first();
                if (! $current || $current->status !== 'open' || ! $current->closing_date || $current->closing_date->isToday() || $current->closing_date->isFuture()) {
                    return;
                }
                $current->update(['status' => 'closed']);
                app(ActivityLogger::class)->log(null, 'update', JobOpening::class, $current->id, ['status' => 'open'], ['status' => 'closed']);
            });
        }
    });
})->daily();
