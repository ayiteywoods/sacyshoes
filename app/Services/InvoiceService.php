<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Support\GuestOrderAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class InvoiceService
{
    public function authorizeView(Order $order): void
    {
        if (request()->routeIs('admin.*')) {
            return;
        }

        GuestOrderAccess::assertCanAccess($order);

        abort_unless(
            $order->payment_status === PaymentStatus::Paid,
            403,
            'Your invoice will be available after payment is completed.'
        );
    }

    /**
     * @return array{currency: string, logoSrc: ?string}
     */
    public function viewData(Order $order): array
    {
        $order->loadMissing('items');

        return [
            'currency' => (string) config('shop.currency_symbol', '₵'),
            'logoSrc' => $this->logoDataUri(),
        ];
    }

    public function renderShow(Order $order): View
    {
        return view('invoices.show', array_merge(
            ['order' => $order],
            $this->viewData($order),
        ));
    }

    public function downloadPdf(Order $order): Response
    {
        $filename = $this->pdfFilename($order);

        return $this->makePdf($order)
            ->download($filename);
    }

    public function pdfFilename(Order $order): string
    {
        return 'invoice-'.$order->order_number.'.pdf';
    }

    public function pdfBinary(Order $order): string
    {
        return $this->makePdf($order)->output();
    }

    /**
     * @param  Collection<int, Order>  $orders
     */
    public function downloadBulk(Collection $orders): Response
    {
        if ($orders->count() === 1) {
            return $this->downloadPdf($orders->first());
        }

        $zip = new \ZipArchive;
        $path = tempnam(sys_get_temp_dir(), 'invoices-');

        if ($path === false || $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Unable to prepare invoice export.');
        }

        foreach ($orders as $order) {
            $zip->addFromString($this->pdfFilename($order), $this->pdfBinary($order));
        }

        $zip->close();

        $filename = 'invoices-'.now()->format('Y-m-d-His').'.zip';

        return response()->download($path, $filename)->deleteFileAfterSend();
    }

    /**
     * @param  Collection<int, Order>  $orders
     */
    public function renderBulkPrint(Collection $orders, int $skipped = 0): View
    {
        $invoices = $orders->map(function (Order $order) {
            $order->loadMissing('items');

            return array_merge(
                ['order' => $order],
                $this->viewData($order),
            );
        });

        return view('invoices.bulk-print', [
            'invoices' => $invoices,
            'skipped' => $skipped,
        ]);
    }

    protected function makePdf(Order $order): \Barryvdh\DomPDF\PDF
    {
        $order->loadMissing('items');
        $data = array_merge(['order' => $order], $this->viewData($order));

        return Pdf::loadView('invoices.pdf', $data)
            ->setPaper('a4', 'portrait');
    }

    public function logoDataUri(): ?string
    {
        $candidates = [
            public_path('images/brand/logo1.webp'),
            public_path('images/brand/logo1.png'),
            public_path('images/brand/logo1.jpg'),
        ];

        foreach ($candidates as $path) {
            if (! is_file($path)) {
                continue;
            }

            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            $mime = match ($extension) {
                'webp' => 'image/webp',
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                default => null,
            };

            if ($mime === null) {
                continue;
            }

            return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
        }

        return null;
    }
}
