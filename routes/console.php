<?php

use App\Console\Commands\RetryMediaThumbnails;
use App\Models\JobOpening;
use App\Services\ActivityLogger;
use App\Services\PublicApiCache;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (): void {
    $closedAny = false;
    JobOpening::query()->where('status', 'open')->whereDate('closing_date', '<', today())->orderBy('id')->chunkById(100, function ($jobs) use (&$closedAny): void {
        foreach ($jobs as $job) {
            $closed = DB::transaction(function () use ($job): bool {
                $current = JobOpening::query()->whereKey($job->id)->lockForUpdate()->first();
                if (! $current || $current->status !== 'open' || ! $current->closing_date || $current->closing_date->isToday() || $current->closing_date->isFuture()) {
                    return false;
                }
                $current->update(['status' => 'closed']);
                app(ActivityLogger::class)->log(null, 'update', JobOpening::class, $current->id, ['status' => 'open'], ['status' => 'closed']);

                return true;
            });
            $closedAny = $closedAny || $closed;
        }
    });

    if ($closedAny) {
        app(PublicApiCache::class)->invalidate();
    }
})->name('close-expired-job-openings')->daily()->withoutOverlapping();

Schedule::command(RetryMediaThumbnails::class)->hourly()->withoutOverlapping();
