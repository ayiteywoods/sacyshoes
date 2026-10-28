<?php

namespace App\Console\Commands;

use App\Services\StockReservationService;
use Illuminate\Console\Command;

class ReleaseExpiredStockHolds extends Command
{
    protected $signature = 'orders:release-expired-holds';

    protected $description = 'Return leftover stock from unpaid checkouts older than the hold window';

    public function handle(StockReservationService $stock): int
    {
        $released = $stock->releaseExpiredReservations();

        $this->info("Returned stock for {$released} unpaid order(s).");

        return self::SUCCESS;
    }
}
