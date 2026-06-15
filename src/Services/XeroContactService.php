<?php

namespace Xerointegration\LaravelXero\Services;

use XeroAPI\XeroPHP\Api\AccountingApi;

use XeroAPI\XeroPHP\Models\Accounting\Contact;
use XeroAPI\XeroPHP\Models\Accounting\Contacts;

class XeroContactService
{
    public function createCustomer(
        $data
    ) {

        return app(
            XeroApiService::class
        )->execute(
            AccountingApi::class,
            function ($api, $tenantId) use (
                $data
            ) {
                $contact = $this->prepareContact($data);
                $contacts = new Contacts;
                $contacts->setContacts([
                    $contact
                ]);
                return $api->createContacts(
                    $tenantId,
                    $contacts
                );
            }
        );
    }

    public function updateCustomer(
        $contactId,
        array $data
    ) {

        return app(
            XeroApiService::class
        )->execute(
                AccountingApi::class,

                function ($api, $tenantId) use (
                    $contactId,
                    $data
                ) {
                    $response =
                        $api->getContact(
                            $tenantId,
                            $contactId
                        );

                    $existingContact =
                        $response
                            ->getContacts()[0];

                    foreach (
                        $data as $key => $value
                    ) {

                        $method = 'set'.ucfirst($key);
                        if (method_exists($existingContact,$method))
                        {
                            $existingContact->$method(
                                $value
                            );
                        }
                    }

                    $contacts = new Contacts;

                    $contacts->setContacts([
                        $existingContact
                    ]);

                    return $api->updateContact(

                        $tenantId,

                        $contactId,

                        $contacts
                    );
                }
            );
    }

    public function updateCustomerStatus($tenantId,$contactId, $status)
    {
        return $this->updateCustomer(
            $tenantId,
            $contactId,
            [
                'contactStatus' => $status
            ]
        );
    }

    protected function prepareContact(array $data)
    {
        $contact = new Contact;
        foreach ($data as $key => $value) {

            $method =
                'set'.ucfirst($key);

            if (
                method_exists(
                    $contact,
                    $method
                )
            ) {

                $contact->$method(
                    $value
                );
            }
        }
        return $contact;
    }
}