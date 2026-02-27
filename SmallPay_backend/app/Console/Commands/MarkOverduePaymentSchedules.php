<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PaymentFlowService;

class MarkOverduePaymentSchedules extends Command
{
    protected $signature = 'payment:mark-overdue';

    protected $description = 'Marquer les échéances de paiement en retard';

    public function handle()
    {
        $this->info('Marquage des échéances en retard...');

        $service = new PaymentFlowService();
        $count = $service->markOverdueSchedules();

        $this->info("✓ {$count} échéances marquées comme en retard");

        return Command::SUCCESS;
    }
}
