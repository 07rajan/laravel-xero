<?php

namespace Xerointegration\LaravelXero\Services;

use Xerointegration\LaravelXero\Models\XeroWebhookEvent;
use DB;
use Carbon\Carbon;
use App\Http\Controllers\BaseController;

class XeroWebhookProcessor
{
    public function process(XeroWebhookEvent $event)
    {
        $data = null;
        switch ($event->resource_type) {

            case 'INVOICE':
                $data = $this->processInvoice($event);
                break;

            case 'CONTACT':
                $this->processContact($event);
                break;

            case 'CREDIT_NOTE':
                $this->processCreditNote($event);
                break;

            default:
                throw new \Exception('Unsupported webhook type.');
        }
        return $data;
    }

    protected function processInvoice(XeroWebhookEvent $event)
    {
        $invoice = app(XeroInvoiceService::class)->getInvoice($event->resource_id);
        if(strtolower($invoice['type']) == 'accpay' && (strtolower($invoice['status']) == 'paid' || strtolower($invoice['status']) == 'authorised'))
        {
            $lineitems = $invoice['line_items'];
            $createArr = [];
            foreach($lineitems as $lineitem)
            {
                if(isset($lineitem['item']['item_id']))
                {
                    $productDetails = DB::table('products')->where('xeroId', $lineitem['item']['item_id'])->first();
                    if($productDetails)
                    {
                        $qty = isset($lineitem['quantity']) ? $lineitem['quantity'] : 1;
                        $createArr[] = [
                            'type'            => 'inbound',
                            'date'            => isset($invoice['date']) ? Carbon::createFromTimestamp($invoice['date'])->format('Y-m-d') : null,
                            'related_barcode' => isset($productDetails->barcode) ? $productDetails->barcode : null,
                            'related_id'      => $productDetails->id,
                            'related_type'    => 'product',
                            'purchase_price'  => isset($lineitem['unit_amount']) ? $lineitem['unit_amount'] : 0,
                            'product'         => $productDetails->id,
                            'line_item_id'    => null,
                            'quantity'        => $qty,
                            'remarks'         => isset($lineitem['description']) ? $lineitem['description'] : null,
                            'added_by'        => 0,
                            'added_at'        => now(),
                        ];
                        DB::table('products')->where('id', $productDetails->id)->increment('initial_stock', $qty);
                    }
                }
            }
            if(count($createArr) > 0)
            {
                DB::table('non_serialized_items')->insert($createArr);
            }
        }
        else
        {
            $invoiceDetails = DB::table('invoices')->where('xeroId', $event->resource_id)->first();
            if($invoiceDetails)
            {
                DB::table('payments')->where('invoice', $invoiceDetails->id)->delete();
                $payments = $invoice['payments'];
                $paymentArr = [];
                $paidAmount = 0;
                foreach($payments as $payment)
                {
                    $barcode = $this->generatePaymentBarcode();
                    $amount = isset($payment['amount']) ? (float)$payment['amount'] : 0.00;
                    $paymentArr[] = [
                        'barcode' => $barcode,
                        'reference' => isset($payment['reference']) ? $payment['reference'] : null,
                        'invoice' => $invoiceDetails->id,
                        'customer' => $invoiceDetails->customer,
                        'contact_person' => $invoiceDetails->property_contact_person,
                        'property' => $invoiceDetails->property,
                        'amount' => $amount,
                        'payment_date' => isset($payment['date']) ? Carbon::createFromTimestamp($payment['date'])->format('Y-m-d') : null,
                        'admin_note' => isset($payment['details']) ? $payment['details'] : null,
                        'payment_mode' => isset($payment['payment_mode']) ? (int)$payment['payment_mode'] : 0,
                        'status' => 1,
                        'added_at' => now(),
                        'added_by' => 1,
                        'xeroId' => isset($payment['payment_id']) ? (int)$payment['payment_id'] : 0,
                        'xeroSync' => true,
                    ];
                    $paidAmount = $paidAmount+$amount;
                }
                $status = 1;
                $updateInvoice = [
                    'status' => 1,
                    'paid_amount' => $paidAmount
                ];
                if(count($paymentArr) > 0)
                {
                    $updateInvoice['status'] = strtolower($invoice['status']) == 'authorised' ? 2 : 3;
                    DB::table('payments')->insert($paymentArr);
                }
                else if(strtolower($invoice['status']) == 'authorised')
                {
                    $updateInvoice['status'] = 4;
                }
                DB::table('invoices')->where('id', $invoiceDetails->id)->update($updateInvoice);
            }
        }
        return $invoice;
    }

    protected function generatePaymentBarcode()
    {
        $settingArr = DB::table('app_settings')->where('type', 10)->get()->pluck('value', 'field');
        $status = true;

        $prefix = isset($settingArr['prefix_string']) ? (string)$settingArr['prefix_string'] : null;

        $number = isset($settingArr['next_number']) ? (int)$settingArr['next_number'] : null;
        $perfixLen = strlen($prefix);
        $padLen = isset($settingArr['number_length']) ? (int)$settingArr['number_length'] : null;

        if ($padLen > $perfixLen) {
            $padLen = $padLen - $perfixLen;
        } else {
            $prefix = substr($prefix, 0, ($padLen - 2));
            $perfixLen = strlen($prefix);
            if ($padLen > $perfixLen) {
                $padLen = $padLen - $perfixLen;
            }
        }
        $barcode = '';
        while ($status === true) {
            $barcode = (string)$prefix . str_pad($number, $padLen, '0', STR_PAD_LEFT);

            $dataArr = DB::table('payments')->where('barcode', $barcode)->exists();

            if (!$dataArr) {
                $status = false;
                DB::table('app_settings')->where('type', 10)
                    ->where('field', 'next_number')
                    ->update(['value' => $number + 1]);
            } else {
                ++$number;
            }
        }
        return $barcode;
    }

     protected function processContact(XeroWebhookEvent $event): void
    {
        $contact = app(XeroContactService::class)
            ->getById($event->resource_id);

        app(XeroContactSyncService::class)
            ->sync($contact);
    }

    protected function processCreditNote(XeroWebhookEvent $event): void
    {
        $creditNote = app(XeroCreditNoteService::class)
            ->getById($event->resource_id);

        app(XeroCreditNoteSyncService::class)
            ->sync($creditNote);
    }
}