# MyOwnFreeHost API Client

An API client to use the free hosting system from [MyOwnFreeHost](https://myownfreehost.net). Based off the API wrapper from InfinityFree

**IMPORTANT: THIS LIBRARY IS AIMED AT EXPERIENCED PHP DEVELOPERS. Experience with object-oriented PHP and Composer is required. If you can't use oo-PHP and Composer, don't bother with this library.**

## Installation

This package is best installed through Composer:

```bash
composer require Greenreader9/mofh-client
```

## Usage

Before you can get started, you need to get the API credentials from MyOwnFreeHost. Login to the [reseller panel](https://panel.myownfreehost.net), go to API -> Setup WHM API -> select the domain you want to configure. Copy the API Username and API password and set your own IP address as the Allowed IP Address (the IP address of your computer, server, or wherever you want to use this API client).

### Available Methods

See client file

### Example

The example below may not work due to modifications to the client. Please see the client file, or use the mofh-wrapper from the InfinityFreeHosting github profile

```php
use \Greenreader9\MofhClient\Client;

// Create a new API client with your API credentials.
$client = new Client("<MOFH API username>", "<MOFH API password>");

// Create a new hosting account.
$createResponse = $client->createAccount(
    'abcd1234', // A unique, 8 character identifier of the account. Primarily used as internal identifier.
    'password123', // A password to login to the control panel, FTP and databases.
    'user@example.com', // The email address of the user.
    'userdomain.example.com', // Initial domain of the account. Can be a subdomain or a custom domain.
    'my_plan', // The hosting plan name at MyOwnFreeHost.
);

// Check whether the request was successful.
if ($createResponse->isSuccessful()) {
    echo "Created account with username: ".$createResponse->getVpUsername();
} else {
   echo 'Failed to create account: ' . $createResponse->getMessage();
   die();
}
```

## License

Copyright 2023 Greenreader9/Hans Adema/InfinityFree

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
