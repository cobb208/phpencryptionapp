# File Encryption with PHP
___

*Author: cobb208*

This simple applet allows someone to upload a file it will be encrypted and can be downloaded 
by someone who knows the receiver email, passcode, and passphrase.

[Read the 1st tutorial here.](https://goodeveningtech.com/2022/03/lock-down-encryption-with-php/)
<br>
[Read the 2nd tutorial here.](https://goodeveningtech.com/2022/03/better-encryption-with-php/)
---
## License
> [GNU Public License](https://www.gnu.org/licenses/gpl-3.0.en.html)
---

## Installation and Use

### Requirements:

- Apache, Nginx, XAMPP instance 
- PHP 8.X installation
- MySQL or MariaDB

Once downloaded move project to the Main Document Root set in your server's config file.

Check folder permissions that the instance of your server has full control over the "uploads" folder. 

> Web server usually runs under www-data:www-data or httpd:httpd

Ensure all services are running prior to going to the site. 

- Apache2, Httpd, or Nginx
- MySQL or MariaDB 

---

## To do List
- [ ] Create Cron task to remove files after X date
- [ ] Create functions for input fields for similar look
- [x] Switch to Classes & Namespaces
- [ ] Create URL manager instead of global variable class for URLs
- [ ] Validation on Decryption.php POST

---

## Index.php

The landing page, general information, no real purpose to application.

---

## Encryption.php

Handles the GET and POST request(s). Once the form is submitted it will provide the user the passcode and passphrase to pass on.

## Decryption.php

Handles the GET and POST request(s). Once form is submitted if passphrase, passcode, and email match it will decode file and send it to user.

- [ ] Add error messages to invalid input.
- [ ] Use JavaScript to handle form submission to ensure other submissions do not happen.