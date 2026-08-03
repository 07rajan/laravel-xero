<?php

namespace Xerointegration\LaravelXero\Services;

use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Models\Accounting\Contact;
use XeroAPI\XeroPHP\Models\Accounting\Invoice;
use XeroAPI\XeroPHP\Models\Accounting\Invoices;
use XeroAPI\XeroPHP\Models\Accounting\LineItem;

class XeroInvoiceService
{
    public function createInvoice(array $data)
    {
        return app(XeroApiService::class)->execute(AccountingApi::class,
            function ($api, $tenantId) use ($data) {
                $invoice = $this->prepareInvoice($data);
                $invoices = new Invoices();
                $invoices->setInvoices([$invoice]);
                return $api->createInvoices($tenantId,$invoices);
            }
        );
    }

    public function updateInvoice($invoiceId,array $data) {
        return app(XeroApiService::class)->execute(AccountingApi::class,
            function ($api,$tenantId) use ($invoiceId,$data) 
            {
                $invoice = $this->prepareInvoice($data);
                $invoice->setInvoiceId($invoiceId);
                $invoices = new Invoices();
                $invoices->setInvoices([$invoice]);
                return $api->updateOrCreateInvoices($tenantId,$invoices);
            }
        );
    }

    public function getInvoice($invoiceId)
    {
        return app(XeroApiService::class)->execute(AccountingApi::class,
            function ($api,$tenantId) use ($invoiceId)
            {
                $response = $api->getInvoice($tenantId,$invoiceId);
                return $response->getInvoices()[0]?? null;
            }
        );
    }

    public function getInvoices($where = null)
    {
        return app(XeroApiService::class)->execute(AccountingApi::class,
            function ($api,$tenantId) use ($where)
            {
                return $api->getInvoices($tenantId,null,$where);
            }
        );
    }

    public function approveInvoice($invoiceId)
    {
        return $this->updateInvoice($invoiceId,['status' => 'AUTHORISED']);
    }

    public function voidInvoice($invoiceId)
    {
        return $this->updateInvoice($invoiceId,['status' => 'VOIDED']);
    }

    protected function prepareInvoice(array $data)
    {
        $invoice = new Invoice();
        foreach ($data as $key => $value)
        {
            if ($key === 'Contact') {
                $invoice->setContact($this->prepareContact($value));
                continue;
            }
            if ($key === 'lineItems')
            {
                $invoice->setLineItems($this->prepareLineItems($value));
                continue;
            }
            $method = 'set'.ucfirst($key);
            if (method_exists($invoice,$method))
            {
                $invoice->$method($value);
            }
        }
        return $invoice;
    }

    protected function prepareContact(array $data)
    {
        $contact = new Contact();
        foreach ($data as $key => $value)
        {
            $method = 'set'.ucfirst($key);
            if (method_exists($contact,$method))
            {
                $contact->$method($value);
            }
        }
        return $contact;
    }

    protected function prepareLineItems(array $items)
    {
        $lineItems = [];
        foreach ($items as $itemData)
        {
            $lineItem = new LineItem();
            foreach ($itemData as $key => $value)
            {
                $method = 'set'.ucfirst($key);
                if (method_exists($lineItem,$method))
                {
                    $lineItem->$method($value);
                }
            }
            $lineItems[] = $lineItem;
        }
        return $lineItems;
    }
}