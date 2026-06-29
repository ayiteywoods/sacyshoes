<?php

namespace App\Console\Commands;

use App\Models\CartItem;
use App\Services\StockReservationService;
use Illuminate\Console\Command;

class ReleaseExpiredCartReservations extends Command
{
    protected $signature = 'cart:release-expired';

    protected $description = 'Clear legacy cart reservation timestamps and stock hold counters';

    public function handle(StockReservationService $stock): int
    {
        $clearedItems = CartItem::query()
            ->whereNotNull('reserved_until')
            ->update([
                'reserved_until' => null,
            ]);

        $clearedStock = $stock->clearLegacyReservations();

        $this->info("Cleared {$clearedItems} cart reservation timestamp(s) and reset {$clearedStock} legacy stock hold(s).");

        return self::SUCCESS;
    }
}
