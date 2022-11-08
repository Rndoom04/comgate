# Rndoom04\Comgate

Comgate is payment gateway from the Czech republic. https://www.comgate.cz/

## Installation

Install Rndoom04\comgate with composer

```bash
  composer require Rndoom04\comgate
```
## Usage/Examples

First - init the library.
```php
use Rndoom04\comgate\comgate;

$comgate = new comgate();
```

Set the merchant and secret
```php
$comgate->setMerchant('mergantID', '*************');
```

Create payment
```php
// Prepare data
$paymentData = (object)[
  "price" => 10000, // 100.00 CZK should be 10000 (×100)
  "curr" => "CZK", // Currency
  "label" => "Product name", // Short desc. name
  "refId" => "123456789", // Variable symbol
  "method" => "ALL", // Method for payment
  "test" => true, // false
  "prepareOnly" => false // !important
];

// Create payment, obtain transId and redirect URL
$pay = $comgate->createPayment($data->price, $data->curr, $data->label, $data->refId, $data->method, $data->test, $data->prepareOnly);

// Process
if ($pay['code'] == 0) {
  // OK, save $pay['transId'] for futher use
  // var_dump($pay);
  // Redirect
  Header("Location: ".$pay['redirect']);
  die();
} else {
  // Something went wrong
  // ... do some magic
}
```

Get payment info
```php
// Get payment info
$info = $comgate->getPaymentInfo("some-payment-id");
var_dump($info);
```