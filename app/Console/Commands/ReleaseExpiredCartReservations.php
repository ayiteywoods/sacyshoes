<?php

namespace App\Console\Commands;

use App\Models\CartItem;
use App\Services\StockReservationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReleaseExpiredCartReservations extends Command
{
    protected $signature = 'cart:release-expired';

    protected $description = 'Release stock for cart items that were not checked out in time';

    public function handle(StockReservationService $reservations): int
    {
        $expiredItems = CartItem::query()
            ->with('variant')
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<', now())
            ->get();

        if ($expiredItems->isEmpty()) {
            $this->info('No expired cart reservations.');

            return self::SUCCESS;
        }

        $released = 0;

        DB::transaction(function () use ($expiredItems, $reservations, &$released) {
            foreach ($expiredItems as $item) {
                if ($item->variant) {
                    $reservations->release($item->variant, $item->quantity);
                }

                $item->delete();
                $released++;
            }
        });

        $this->info("Released {$released} expired cart reservation(s).");

        return self::SUCCESS;
    }
}
