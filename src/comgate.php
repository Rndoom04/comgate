<?php
    /*
     * Comgate payment gateway https://www.comgate.cz/
     * version: 1.0
     * release date: 6.11.2022
     * Author: Kollert Slavomír
     */

    namespace Rndoom04\comgate;

    use GuzzleHttp\Client;
    use GuzzleHttp\RequestOptions;

    class comgate {
        /** Properties **/
        // Test mode - default false
        private $test_mode = false;

        // Errors
        private $errors = [];

        // BaseURI
        private $baseURI = "https://payments.comgate.cz";

        // Endpoints
        private $endpoints = [
            "create_payment" => "/v1.0/create", // For create a payment
            "get_payment_status" => "/v1.0/status", // For getting data about payment
            "payment_methods" => "/v1.0/methods", // Get allowed payment methods
            "refund" => "/v1.0/refund", // Refund payment
            "storno" => "/v1.0/cance" // Cancel/storno payment
        ];

        // Merchant data
        private $merchant;
        private $secret;
        
        
        
        /** Methods **/
        public function __construct() {}
        


        // Set merchant
        public function setMerchant(string $merchant, string $secret) {
            if (!empty($merchant) && !empty($secret)) {
                $this->merchant = $merchant;
                $this->secret = $secret;

                // Everything is OK
                return true;
            }

            // Something went wrong
            return false;
        }
        
        // Is merchant ok?
        public function checkMerchant() {
            return (!empty($this->merchant) && !empty($this->secret))?true:false;
        }
        


        // Get payment information
        public function getPaymentInfo(string $transID) {
            if ($this->checkMerchant()) {
                // Prepare data
                $data = [
                    "merchant" => $this->merchant,
                    "secret" => $this->secret,
                    "transId" => $transID
                ];

                // Prepare Guzzle
                $client = new \GuzzleHttp\Client([
                    'base_uri' => $this->baseURI,
                    'http_errors' => false,
                ]);

                // Send request for get payment status
                try {
                    $response = $client->request('POST', $this->endpoints['get_payment_status'], [
                        'form_params' => $data
                    ]);
                    $body = (string)$response->getBody();
                    return $this->parseData($body);
                } catch (Exception $e) {
                    $this->addError("Exception: ".$e);
                    return false;
                }
            } else {
                $this->addError("Merchant data is not available. First of all use setMerchant() method.");
            }
        }

        // Get payment allowed methods
        public function getPaymentMethods() {
            if ($this->checkMerchant()) {
                // Prepare data
                $data = [
                    "merchant" => $this->merchant,
                    "secret" => $this->secret,
                ];

                // Prepare Guzzle
                $client = new \GuzzleHttp\Client([
                    'base_uri' => $this->baseURI,
                    'http_errors' => false,
                ]);

                // Send request for get payment status
                try {
                    $response = $client->request('POST', $this->endpoints['payment_methods'], [
                        'form_params' => $data
                    ]);
                    
                    $body = (string)$response->getBody(); // Returns XML

                    // Convert xml to array through json
                    $xml = simplexml_load_string($body, "SimpleXMLElement", LIBXML_NOCDATA);
                    $arr = json_decode(json_encode($xml), true);
                    if (isset($arr['method'])) {
                        return $arr['method'];
                    }

                    return null;
                } catch (Exception $e) {
                    $this->addError("Exception: ".$e);
                    return false;
                }
            } else {
                $this->addError("Merchant data is not available. First of all use setMerchant() method.");
            }
        }

        // Storno payment
        public function storno(string $transID) {
            if ($this->checkMerchant()) {
                // Prepare data
                $data = [
                    "merchant" => $this->merchant,
                    "secret" => $this->secret,
                    "transId" => $transID
                ];

                // Prepare Guzzle
                $client = new \GuzzleHttp\Client([
                    'base_uri' => $this->baseURI,
                    'http_errors' => false,
                ]);

                // Send request for get payment status
                try {
                    $response = $client->request('POST', $this->endpoints['storno'], [
                        'form_params' => $data
                    ]);
                    $body = (string)$response->getBody();
                    
                    return $this->parseData($body);
                } catch (Exception $e) {
                    $this->addError("Exception: ".$e);
                    return false;
                }
            } else {
                $this->addError("Merchant data is not available. First of all use setMerchant() method.");
            }
        }

        // Refund payment
        public function refundPayment(string $transID, int $amount, $curr, $refId, $test) {
            if ($this->checkMerchant()) {
                // Prepare data
                $data = [
                    "merchant" => $this->merchant,
                    "secret" => $this->secret,
                    "transId" => $transID,
                    "amount" => $amount,
                    "curr" => $curr,
                    "refId" => $refId,
                    "test" => $test
                ];

                // Prepare Guzzle
                $client = new \GuzzleHttp\Client([
                    'base_uri' => $this->baseURI,
                    'http_errors' => false,
                ]);

                // Send request for get payment status
                try {
                    $response = $client->request('POST', $this->endpoints['refund'], [
                        'form_params' => $data
                    ]);
                    $body = (string)$response->getBody();
                    
                    return $this->parseData($body);
                } catch (Exception $e) {
                    $this->addError("Exception: ".$e);
                    return false;
                }
            } else {
                $this->addError("Merchant data is not available. First of all use setMerchant() method.");
            }
        }

        // Create payment  - get gateway url
        public function createPayment($price, $curr, $label, $refId, $method, $customer, $test, $prepareOnly) {
            if ($this->checkMerchant()) {
                // Prepare data
                $data = [
                    "merchant" => $this->merchant,
                    "secret" => $this->secret,
                    "price" => $price,
                    "curr" => $curr,
                    "label" => $label,
                    "refId" => $refId,
                    "method" => $method,
                    "email" => $customer['email'],
                    "phone" => $customer['email'],
                    "test" => $test,
                    "prepareOnly" => 'true'
                ];

                // Prepare Guzzle
                $client = new \GuzzleHttp\Client([
                    'base_uri' => $this->baseURI,
                    'http_errors' => false
                ]);

                // Send request for get payment status
                try {
                    $headers = [
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ];
                    $options = [
                        'form_params' => [
                            'merchant' => $this->merchant,
                            'secret' => $this->secret,
                            'price' => $price,
                            'curr' => $curr,
                            'label' => $label,
                            'refId' => $refId,
                            'method' => $method,
                            'test' => $test,
                            'prepareOnly' => $prepareOnly?'true':'false'
                        ]
                    ];
                    $request = new \GuzzleHttp\Psr7\Request('POST', $this->endpoints['create_payment'], $headers);
                    $res = $client->sendAsync($request, $options)->wait();
                    
                    return $this->parseData($res->getBody()->getContents());
                } catch (Exception $e) {
                    $this->addError("Request for create payment ends with errors. Probably bad data format.");
                    return false;
                }
            } else {
                $this->addError("Merchant data is not available. First of all use setMerchant() method.");
            }
        }
        


        // Add error
        private function addError(string $error) {
            $this->errors[] = $error;
        }
        // Is any error logged?
        public function hasError() {
            return empty($this->errors)?false:true;
        }
        // Get all errors
        public function getErrors() {
            return $this->errors;
        }
        


        // Enable/disable test mode
        public function setTestMode(bool $mode) {
            $this->test_mode = $mode;
        }
        // Get test mode
        public function getTestMode() {
            return $this->test_mode; // returns bool
        }
        


        // Parse data from response
        private function parseData(string $data) {
            $result = null;
            parse_str($data, $result);

            return $result;
        }
    }
?>