<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

/**
 * InvoiceController
 *
 * Generates a server-side PDF invoice using DomPDF (barryvdh/laravel-dompdf).
 * The customer cannot tamper with the output.
 * Route: GET /orders/{id}/invoice
 */
class InvoiceController extends Controller
{
    public function download(int $id): Response
    {
        $user  = auth('web')->user();
        $order = Order::with([
                         'user',
                         'vendor',
                         'items.product',
                         'coupon',
                         'statusHistory',
                     ])
                     ->forCustomer($user->id)
                     ->findOrFail($id);

        $pdf = Pdf::loadView('customer.invoice', compact('order'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled'      => false, // never fetch remote resources
                      'defaultFont'          => 'sans-serif',
                  ]);

        return $pdf->download("invoice-{$order->order_number}.pdf");
    }
}
