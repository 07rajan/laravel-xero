<?php
namespace Xerointegration\LaravelXero\Traits;
use Xerointegration\LaravelXero\Services\XeroContactService;
use Xerointegration\LaravelXero\Services\XeroItemService;
use Xerointegration\LaravelXero\Services\XeroInvoiceService;

use Carbon\Carbon;

trait XeroTrait
{
    protected static function createOrUpdateContact($data, $xeroId = null)
    {   
        $xeroContactService = app(XeroContactService::class);
        $createData = [
            "Name" => $data['name'],
            "FirstName" => isset($data['first_name']) ? $data['first_name'] : '',
            "LastName"=> isset($data['last_name']) ? $data['last_name'] : '',
            "Website" => isset($data['website']) ? $data['website'] : '',
            "IsCustomer" => true,
            "EmailAddress" => isset($data['email_address']) ? $data['email_address'] : ''
        ];
        if(isset($data['phone']))
        {
            $data['Phones'] = [["PhoneType" => "MOBILE", "PhoneNumber" => $data['phone']]];
        }
        if(count($data['contact_persons']) > 0)
        {
            $contactPersonArr = [];
            foreach($data['contact_persons'] as $contactPerson)
            {
                $contactPersonArr[] = [
                    "FirstName" => isset($contactPerson['first_name']) ? $contactPerson['first_name'] : '',
                    "LastName" => isset($contactPerson['last_name']) ? $contactPerson['last_name'] : '',
                    "EmailAddress" => isset($contactPerson['email']) ? $contactPerson['email'] : ''
                ];
            }
            $createData['ContactPersons'] = $contactPersonArr;
        }

        $addressData = [];

        foreach($data['address'] as $address)
        {
            $addressData[] = [
                "AddressType"=> $address['type'],
                "AddressLine1"=> isset($address['address_1']) ? $address['address_1'] : '',
                "AddressLine2"=> isset($address['address_2']) ? $address['address_2'] : '',
                "City"=> isset($address['city']) ? $address['city'] : '',
                "Region"=> isset($address['state']) ? $address['state'] : '',
                "PostalCode"=> isset($address['postal_code']) ? $address['postal_code'] : '',
                "Country"=> isset($address['country']) ? $address['country'] : '',
            ];
        }

        $createData['Addresses'] = $addressData;
        if(!empty($xeroId))
        {
            return $xeroContactService->updateCustomer($xeroId, $createData);
        }
        else
        {
            return $xeroContactService->createCustomer($createData);
        }
    }

    protected static function createOrUpdateItem($data, $xeroId = null)
    {   
        $xeroItemService = app(XeroItemService::class);
        $createData = [
            "code" => $data['code'],
            "name" => isset($data['name']) ? $data['name'] : '',
            "description"=> isset($data['description']) ? $data['description'] : '',
            "purchaseDescription" => isset($data['purchaseDescription']) ? $data['purchaseDescription'] : '',
            "IsTrackedAsInventory" => isset($data['isTrackedAsInventory']) ? $data['isTrackedAsInventory'] : false,
            "quantityOnHand" => isset($data['quantityOnHand']) ? $data['quantityOnHand'] : '',
            "purchaseDetails" => [
                "unitPrice" => isset($data['purchasePrice']) ? $data['purchasePrice'] : 0,
                "taxType" => isset($data['taxType']) ? $data['taxType'] : 'OUTPUT',
            ]
        ];
        if(isset($data['isSold']))
        {
            $createData['isSold'] = $data['isSold'];
        }
        if(isset($data['isPurchased']))
        {
            $createData['isPurchased'] = $data['isPurchased'];
        }
        if(isset($data['isTrackedAsInventory']) && $data['isTrackedAsInventory'])
        {
            $createData['InventoryAssetAccountCode'] = "630";
            $createData['purchaseDetails']['COGSAccountCode'] = "310";
        }
        if(isset($data['salePrice']))
        {
            $createData['salesDetails'] = [
                "unitPrice" => isset($data['salePrice']) ? $data['salePrice'] : '',
                // "accountCode" => isset($data['accountCode']) ? $data['accountCode'] : '200',
                "taxType" => isset($data['taxType']) ? $data['taxType'] : 'OUTPUT',
            ];
             if(!$createData['IsTrackedAsInventory'])
            {
                $createData['salesDetails']['accountCode'] = '200';
            }
        }
        /* if(isset($data['purchasePrice']))
        {
            $createData['purchaseDetails'] = [
                "unitPrice" => isset($data['purchasePrice']) ? $data['purchasePrice'] : '',
                // "accountCode" => isset($data['accountCode']) ? $data['accountCode'] : '300',
                "taxType" => isset($data['taxType']) ? $data['taxType'] : 'INPUT',
            ];
        } */

        // echo '<pre>'; print_r(json_encode($createData)); die;

        if(!empty($xeroId))
        {
            return $xeroItemService->updateItem($xeroId, $createData);
        }
        else
        {
            return $xeroItemService->createItem($createData);
        }
    }

    protected static function createOrUpdateInvoice($data, $xeroId = null)
    {   
        $xeroInvoiceService = app(XeroInvoiceService::class);
        
        $createData = [
            "Type" => isset($data['Type']) ? $data['Type'] : 'ACCREC',
            "DueDate" => isset($data['DueDate']) ? $data['DueDate'] : '',
            "Date" => isset($data['Date']) ? $data['Date'] : '',
            "InvoiceNumber" => isset($data['InvoiceNumber']) ? $data['InvoiceNumber'] : '',
            "Reference" => isset($data['Reference']) ? $data['Reference'] : '',
            "SubTotal" => isset($data['SubTotal']) ? $data['SubTotal'] : '',                 
            "TotalTax" => isset($data['TotalTax']) ? $data['TotalTax'] : '',
            "Total" => isset($data['Total']) ? $data['Total'] : '',
            "CurrencyCode" => isset($data['CurrencyCode']) ? $data['CurrencyCode'] : 'SGD',
            "Contact" => ["ContactID" => isset($data['ContactID']) ? $data['ContactID'] : '', "ContactName" => isset($data['ContactName']) ? $data['ContactName'] : ''],
        ];

        switch($data['Status'])
        {
            case 1:
                $createData['Status'] = 'DRAFT';
                break;
            case 4:
                $createData['Status'] = 'AUTHORISED';
                break;
            case 5:
                $createData['Status'] = $this->oldStatus==1?'DELETED':'VOIDED';
                break;
            case 6:
                $createData['Status'] = $this->oldStatus==1?'DELETED':'VOIDED';
                break;
            default:
                $createData['Status'] = 'AUTHORISED';
                break;
        }

        $LineItems = [];
        foreach($data['LineItems'] as $line_items)
        {
            $unitAmount = isset($line_items['UnitAmount']) ? $line_items['UnitAmount'] : 0;
            $qty = isset($line_items['Quantity']) ? $line_items['Quantity'] : 1;
            $LineItemData = [
                "Description"=> isset($line_items['Description']) ? self::formatDescription($line_items['Description']) : "Description",
                "UnitAmount"=> $unitAmount,
                "LineAmount"=> isset($line_items['LineAmount']) ? $line_items['LineAmount'] : 0,
                "Quantity"=> $qty,
                "AccountCode" => isset($line_items['AccountCode']) ? $line_items['AccountCode'] : "200",
                "DiscountAmount" => isset($line_items['DiscountAmount']) ? $line_items['DiscountAmount'] : 0,
                "TaxType" => isset($data['xeroTaxType']) ? $data['xeroTaxType'] : "OUTPUT"
            ];
            if(isset($line_items['ItemCode']))
            {
                $LineItemData['ItemCode'] = $line_items['ItemCode'];
            }

            $LineItems[] = $LineItemData;
        }

        $createData['LineItems'] = $LineItems;

        if(!empty($xeroId))
        {
            return $xeroInvoiceService->updateInvoice($xeroId, $createData);
        }
        else
        {
            return $xeroInvoiceService->createInvoice($createData);
        }
    }

    protected static function formatDescription($description)
    {   
        $description = preg_replace('/<\/(p|div|h[1-6]|blockquote)>/i', "\n\n", $description);
        $count = 1;
        $description = preg_replace_callback('/<li>(.*?)<\/li>/is', function ($matches) use (&$count) {
            return $count++ . '. ' . strip_tags($matches[1]) . "\n";
        }, $description);
        $description = str_replace(['<br>', '<br/>', '<br />'], "\n", $description);
        $text = strip_tags($description);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim($text);
    }
}