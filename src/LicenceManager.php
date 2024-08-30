<?php

namespace Getkey\licencephp;

use Exception;

class LicenceManager
{
    protected $endpoint;

    public function __construct($endpoint)
    {
        $this->endpoint = rtrim($endpoint, '/');
    }

    public function getLicense($scriptName)
    {
        $url = $this->endpoint . '/' . urlencode($scriptName);
        $response = @file_get_contents($url);

        if ($response === false) {
            $httpResponseCode = $this->getHttpResponseCode($http_response_header);

            if ($httpResponseCode === 403) {
                throw new Exception("The license is already in use by a different IP address. Please generate a new one.");
            } elseif ($httpResponseCode === 404) {
                throw new Exception("License not found. Please check the script name.");
            } else {
                $error = error_get_last();
                throw new Exception("Failed to fetch license. Error: " . $error['message']);
            }
        }

        $data = json_decode($response, true);

        if (isset($data['message'])) {
            throw new Exception("Error: " . $data['message']);
        }

        return $data['license'];
    }

    public function saveLicenseToPem($scriptName, $filePath)
    {
        try {
            // Ensure directory exists
            $directory = dirname($filePath);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0777, true)) {
                    throw new Exception("Failed to create directory: " . $directory);
                }
            }

            // Fetch the license
            $license = $this->getLicense($scriptName);
            if (empty($license)) {
                throw new Exception("License is empty or not retrieved properly.");
            }

            // Prepare PEM content
            $pemContent = "-----BEGIN LICENSE-----\n" . $license . "\n-----END LICENSE-----";

            // Write to the .pem file
            $result = file_put_contents($filePath, $pemContent);
            if ($result === false) {
                throw new Exception("Failed to write PEM file: " . $filePath);
            }

        } catch (Exception $e) {
            throw new Exception("Error saving license to PEM file: " . $e->getMessage());
        }
    }

    public function verifyPemContent($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception("The file does not exist.");
        }

        $content = file_get_contents($filePath);
        if (strpos($content, "-----BEGIN LICENSE-----") === false || strpos($content, "-----END LICENSE-----") === false) {
            throw new Exception("Invalid PEM file content.");
        }

        return true;
    }

    private function getHttpResponseCode($http_response_header)
    {
        if (is_array($http_response_header)) {
            $parts = explode(' ', $http_response_header[0]);
            if (count($parts) > 1) { // e.g., "HTTP/1.1 200 OK"
                return intval($parts[1]); // return the code as an integer
            }
        }
        return 0; // Unknown response code
    }

    // Method to combine all steps
    public function manageLicense($scriptName, $pemFilePath)
    {
        $this->saveLicenseToPem($scriptName, $pemFilePath);
        $this->verifyPemContent($pemFilePath);
    }
}
?>