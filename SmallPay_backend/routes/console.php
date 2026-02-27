<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('smallpay:backfill-product-created-by {--user_id=} {--dry-run}', function () {
    $userId = $this->option('user_id');
    $dryRun = (bool) $this->option('dry-run');

    if ($userId) {
        $targetUser = User::query()->find($userId);
        if (! $targetUser) {
            $this->error('User not found: ' . $userId);
            return 1;
        }
        if (!in_array($targetUser->role, ['admin', 'super_admin'], true)) {
            $this->error('Target user must be admin or super_admin. Current role: ' . ($targetUser->role ?? 'null'));
            return 1;
        }
    } else {
        $targetUser = User::query()->where('role', 'super_admin')->orderBy('id')->first();
        if (! $targetUser) {
            $this->error('No super_admin found. Please pass --user_id=<admin_or_super_admin_id>.');
            return 1;
        }
    }

    $count = Product::query()->whereNull('created_by')->count();
    $this->info('Products with NULL created_by: ' . $count);
    $this->info('Target user: #' . $targetUser->id . ' (' . $targetUser->name . ') role=' . $targetUser->role);

    if ($count === 0) {
        $this->info('Nothing to backfill.');
        return 0;
    }

    if ($dryRun) {
        $this->info('Dry run enabled. No changes were made.');
        return 0;
    }

    DB::transaction(function () use ($targetUser, &$updated) {
        $updated = Product::query()
            ->whereNull('created_by')
            ->update(['created_by' => $targetUser->id]);
    });

    $this->info('Updated rows: ' . ($updated ?? 0));
    return 0;
})->purpose('Backfill products.created_by for existing products');
