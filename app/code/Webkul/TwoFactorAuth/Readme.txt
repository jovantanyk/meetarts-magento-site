#Installation

Magento2 TwoFactorAuth module installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and create Webkul(vendor) and TwoFactorAuth(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Webkul/TwoFactorAuth/ folder.

Download Twilio Sdk
-----------------------------------
composer require twilio/sdk:^7.9

Download library Twilio SendGrid 
-----------------------------------
composer require sendgrid/sendgrid

Download library For TOTP/Authentication
-----------------------------------
composer require pragmarx/google2fa
composer require pragmarx/google2fa-qrcode

Run Following Command via terminal
-----------------------------------
php bin/magento setup:upgrade

php bin/magento setup:di:compile

php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

now module is properly installed

#User Guide

For Magento2 TwoFactorAuth module's working process follow user guide - https://webkul.com/blog/magento2-two-factor-authentication/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/
