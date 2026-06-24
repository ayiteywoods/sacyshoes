<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoices
    ) {}

    public function show(Order $order): View
    {
        $this->invoices->authorizeView($order);

        return $this->invoices->renderShow($order);
    }

    public function pdf(Order $order): Response
    {
        $this->invoices->authorizeView($order);

        return $this->invoices->downloadPdf($order);
    }
}
