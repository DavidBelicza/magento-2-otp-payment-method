# OTP Bank Payment Method Module to Magento 2

<p align="center">
<img src="http://youama.hu/frontend/image/product-otp-2.jpg" />
</p>

[![Latest Stable Version](https://poser.pugx.org/youama/module-otp-2/v/stable.svg)](https://packagist.org/packages/youama/module-otp-2)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

>This source code is a Magento module what provides a Payment itengration between Magento 2 ecommerce platform and OTP Bank Direct. The module creates a new Payment method "OTP" and allow to pay orders through OTP Bank. The source code is **under The MIT licence from 2018.02.18**.

You can disable/enable the module and you can customize it by Magento admin interface. The module supports multiple languages, currencies and stores.

This module and the OTP online payment service are not same. You have to contact to OTP, make a commercial licence and then you can use OTP payment in your shop. You can use only the common OTP demo if you don't have contract with the bank.

## System requirements

* Working Magento Community Edition.
* At least Magento version 2.0.
* Working Cron.
* OTP licence (only for production version).

## Install

Using Magento in DEV mode without cache is definitely recommended when you want to install module.

* Get the source, for example you can use Composer: ```composer require youama/module-otp-2```
* Setup: ```php bin/magento setup:upgrade```

## Payment system process
1. The customer places an order on the Checkout of the webshop.
2. The module computes the order and sends it to the OTP payment system.
3. The module redirects the customer to the OTP payment user interface.
4. The customer pays by a credit card and the OTP makes the payment transaction immediately.
5. If the customer didn't close the browser then the Bank redirects the user back to the Magento webshop and the bank sends a message to the webshop about the status of payment.
6. The module sends a message to the customer about status of the payment.
7. If the customer closed the browser, the bank can't redirects the customer back to the shop and it can't notifies the customer. In this case, the scheduled process of the module synchronizes the payment statuses of the unfinished orders from the bank and it sends notifies about status of the payment.

## Settings

**Setup page**

You can find the configuration place on System -> Configuration -> Sales -> Payment methods -> Youama OTP.

**Enabled**

You can disable or enable the whole payment method in website scope.

**Private key**

You can add your own private key file in website scope - given by OTP Bank.

**POS ID**

This is the ID of the shop in website scope - given by OTP Bank.

**Currency**

Hungarian Forint, USD or Euro. Currency code, defined by OTP bank. This is the order currency. (Website scope.)

**Language**

The language of OTP payment user interface. (Website scope.)

**Shop Comment**

A short title or description on payment user interface of OTP. (Store view.)

**Title**

Title of OTP payment in Checkout of Magento. (Store view.)

**Success url**

When customer payment was successful, he arrives to this page. (Store view.)

**Fail url**

When customer payment was failed, he arrives to this page. It should be a CMS page. (Store view.)

**Cancel order**

When customer's order has failed payment, the module changes the status or the state of the order. It can cancels order by automatically or only notifies the user. (Website scope.)

**Paid message**

Customer gets notify by automatically when his order has been paid status. This notify contains a short HTML message. (Store view.)

**Unpaid message**

Customer gets notify by automatically when his order have been unpaid status. This notify contains a short HTML message. (Store view.)

## OTP Payment UI

<p align="center">
<img src="http://youama.hu/media/otp5.jpg" />
</p>

<p align="center">
<img src="http://youama.hu/media/otp1.jpg" />
</p>

## Testing

### Test Cron

Run Cron Job manually from Magento CLI API to sync all not finished payment and change their status and their order/payment/transaction state.

* ```php bin/magento cron:run --group="youama_otp"```

### Test Card Data

* **Card number:** *4908 3660 9990 0425*
* **Expires:** *2014.10*
* **Cvc2 code:** *823*

## Author

This module created by David Belicza and published as a YOUAMA Software in 2014 and open sourced in 2018.

## Licence

THE MIT LICENCE

Copyright 2018 David Belicza

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

## History

* 2018 Open sourced under MIT licence.
* 2016 release a new major version to Magento 2.
* 2015 publish on Magento Connect under unique licence.
* 2014 beta to Magento 1.

<p align="center">
<img src="http://youama.hu/frontend/image/logo_black.png" />
</p>
