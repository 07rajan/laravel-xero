<?php

namespace Xerointegration\LaravelXero\Services;

use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Models\Accounting\Item;
use XeroAPI\XeroPHP\Models\Accounting\Items;

class XeroItemService
{
    public function createItem(array $data)
    {
         return app(XeroApiService::class)->execute(AccountingApi::class,
            function ($api, $tenantId) use ($data)
            {
                $item = $this->prepareItem($data);
                $items = new Items();
                $items->setItems([$item]);
                return $api->createItems($tenantId,$items);
            }
        );
    }

    public function updateItem($itemId, array $data)
    {

        return app(XeroApiService::class)->execute(AccountingApi::class,
            function ($api, $tenantId) use ($itemId,$data)
            {
                $response = $api->getItem($tenantId,$itemId);
                $existingItem = $response->getItems()[0];
                foreach ($data as $key => $value) {
                    $method = 'set'.ucfirst($key);
                    if (method_exists($existingItem,$method)) 
                    {
                        $existingItem->$method($value);
                    }
                }
                $items = new Items();
                $items->setItems([$existingItem]);
                return $api->updateItem($tenantId,$itemId,$items);
            }
        );
    }

    public function getQuantityOnHand($itemId)
    {
        return app(XeroApiService::class)->execute(AccountingApi::class,
            function ($api,$tenantId) use ($itemId)
            {
                $response = $api->getItem($tenantId,$itemId);
                return $response->getItems()[0]->getQuantityOnHand();
            }
        );
    }

    public function adjustStock($itemId,$newQuantity,$costPrice = 0,$reference = null)
    {
        $currentQty =$this->getQuantityOnHand($itemId);
        $difference =$newQuantity - $currentQty;
        if ($difference == 0) {
            return ['status' => 'NO_CHANGE'];
        }
        if ($difference > 0) {
            return $this->increaseStock($itemId,$difference,$costPrice,$reference);
        }
        return $this->decreaseStock($itemId,abs($difference),$costPrice,$reference);
    }

    protected function prepareItem(array $data)
    {
        $item = new Item();
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($item, $method)) {
                $item->$method($value);
            }
        }
        return $item;
    }
}