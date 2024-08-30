# GetKey PHP API Client Library

Support: Framework Laravel, Ci

## Introduction

The **GetKey PHP API Client Library** allows the integration of various data validation features into your applications. For more information, please visit [getkey.my.id](https://getkey.my.id).

## Requirements

To use the GetKey API client, you need the following:

- A GetKey account
- An Application project created with a generated API key

## Installation

To begin using the GetKey PHP API Client library, follow these installation steps:

### With Composer

1. Install the library using Composer:

    ```bash
    composer require getkey/licencephp
    ```

2. Include the Composer autoloader in your PHP script:

    ```php
    require_once 'vendor/autoload.php';
    ```

### Without Composer

1. Download the library and add it to your project.

2. Include the file `vendor/autoload.php` in your PHP script:

    ```php
    include_once "getkey-licence-api-client/vendor/autoload.php";
    ```
If you experience errors, then try adding this

    require_once __DIR__ . '../vendor/getkey/licencephp/src/LicenceManager.php';
    

## Getting Started

To start validating and using the API, create a Traits system in your framework. You need to create a licence first on the [getkey.my.id](https://getkey.my.id) site, then you can use the licence name to call the licence API.

### Full Example of License Validation

Create a trait to manage and validate the licence:

```php
trait ManagesLicense
{
    protected $licenceManager;
    protected $pemFilePath;

    // Automatically initialize when the trait is used
    public function __construct()
    {
        $this->initializeLicenseManager();
    }

    // Initialize the LicenseManager instance
    protected function initializeLicenseManager($endpoint = null)
    {
        $endpoint = $endpoint ?: config('services.licence.endpoint');
        $this->licenceManager = new LicenseManager($endpoint);
    }

    // Set the PEM file path based on the script name
    protected function setPemFilePath($scriptName)
    {
        $this->pemFilePath = storage_path('licences/' . $scriptName . '.pem');
    }

    // Get the PEM file path
    protected function getPemFilePath()
    {
        return $this->pemFilePath;
    }

    // Combined method to manage and validate the licence
    public function manageAndValidateLicense($scriptName)
    {
        try {
            if (!$this->licenceManager) {
                $this->initializeLicenseManager();
            }

            // Set the PEM file path using the script name
            $this->setPemFilePath($scriptName);
            $pemFilePath = $this->getPemFilePath();

            // Manage the licence (fetch, save, and validate)
            $this->licenceManager->manageLicense($scriptName, $pemFilePath);

            // Validate the PEM file content
            return $this->licenceManager->verifyPemContent($pemFilePath);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
 ```
## Setting Services
Add this setting to the service configuration, for example in config/services.php for Laravel:

```php
'licence' => [
    'endpoint' => env('LICENCE_ENDPOINT', 'https://getkey.my.id/api/v1/getLicence'),
],
 ```
Alternatively, you can set it in your .env file:

- LICENCE_ENDPOINT=https://getkey.my.id/api/v1/getLicence


## Example of Validation in Controller
```php
use App\Traits\ManagesLicense;

class YourController extends Controller
{
    use ManagesLicense;

    public function yourMethod()
    {
        $scriptName = "Licence1";

        // Manage and validate the licence
        $result = $this->manageAndValidateLicense($scriptName);

        if ($result === true) {
            // Logic for successful validation
            return view('welcome');
        } else {
            // Logic for failed validation
            return response()->json(['error' => $result], 500);
        }
    }
}
 ```

## Response Class
The Response class is returned with every request, providing methods to handle responses as detailed in the library documentation.

## Testing

getkey is still in beta stage, and you can try it right now. report any bugs in the class licence or api. please note:

for api endpoints it has not been validated with token, only validated by licencename, but with hashing security... then the licence is used by one user, it cannot be used by two users in one project.
