<?php

namespace App\Console\Commands;

use App\Support\OrderNumberGenerator;
use Illuminate\Console\Command;

class RenumberOrders extends Command
{
    protected $signature = 'orders:renumber';

    protected $description = 'Renumber all orders starting from the configured order number (default 1000)';

    public function handle(): int
    {
        $count = OrderNumberGenerator::renumberAll();

        $this->info("Renumbered {$count} order(s).");

        return self::SUCCESS;
    }
}
