<?php
namespace Xerointegration\LaravelXero\Traits;
use Xerointegration\LaravelXero\Services\XeroContactService;
use Xerointegration\LaravelXero\Services\XeroItemService;

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
            "isSold" => isset($data['isSold']) ? $data['isSold'] : '',
            "isPurchased" => isset($data['isPurchased']) ? $data['isPurchased'] : '',
            "isTrackedAsInventory" => isset($data['isTrackedAsInventory']) ? $data['isTrackedAsInventory'] : '',
            "quantityOnHand" => isset($data['quantityOnHand']) ? $data['quantityOnHand'] : '',
        ];
        if(isset($data['salePrice']))
        {
            $createData['salesDetails'] = [
                "unitPrice" => isset($data['salePrice']) ? $data['salePrice'] : '',
                "accountCode" => isset($data['accountCode']) ? $data['accountCode'] : '200',
                "taxType" => isset($data['taxType']) ? $data['taxType'] : 'OUTPUT',
            ];
        }
        if(isset($data['purchasePrice']))
        {
            $createData['purchaseDetails'] = [
                "unitPrice" => isset($data['purchasePrice']) ? $data['purchasePrice'] : '',
                "accountCode" => isset($data['accountCode']) ? $data['accountCode'] : '300',
                "taxType" => isset($data['taxType']) ? $data['taxType'] : 'INPUT',
            ];
        }

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
        $xeroItemService = app(XeroItemService::class);
        $createData = [
            "code" => $data['code'],
            "name" => isset($data['name']) ? $data['name'] : '',
            "description"=> isset($data['description']) ? $data['description'] : '',
            "purchaseDescription" => isset($data['purchaseDescription']) ? $data['purchaseDescription'] : '',
            "isSold" => isset($data['isSold']) ? $data['isSold'] : '',
            "isPurchased" => isset($data['isPurchased']) ? $data['isPurchased'] : '',
            "isTrackedAsInventory" => isset($data['isTrackedAsInventory']) ? $data['isTrackedAsInventory'] : '',
            "quantityOnHand" => isset($data['quantityOnHand']) ? $data['quantityOnHand'] : '',
        ];
        if(isset($data['salePrice']))
        {
            $createData['salesDetails'] = [
                "unitPrice" => isset($data['salePrice']) ? $data['salePrice'] : '',
                "accountCode" => isset($data['accountCode']) ? $data['accountCode'] : '200',
                "taxType" => isset($data['taxType']) ? $data['taxType'] : 'OUTPUT',
            ];
        }
        if(isset($data['purchasePrice']))
        {
            $createData['purchaseDetails'] = [
                "unitPrice" => isset($data['purchasePrice']) ? $data['purchasePrice'] : '',
                "accountCode" => isset($data['accountCode']) ? $data['accountCode'] : '300',
                "taxType" => isset($data['taxType']) ? $data['taxType'] : 'INPUT',
            ];
        }

        if(!empty($xeroId))
        {
            return $xeroItemService->updateItem($xeroId, $createData);
        }
        else
        {
            return $xeroItemService->createItem($createData);
        }
    }
}