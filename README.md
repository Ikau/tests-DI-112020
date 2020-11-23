# tests-DI-112020

## Setup

### Backend and frontend
Install composer dependencies `composer install`

Install frontend dependencies `npm install`

### Database

A fil named `setup.sh` is present in the root folder. You can execute it after giving it privilege:

```
chmod +x setup.sh
./setup.sh
```

### PhpUnit
PhpUnit is in the composer dependencies but it looks like it does not install correctly...

```
# If PhpUnit was correctly installed, execute:
php bin/phpunit
```

```
# Otherwise you need to reinstall it:
composer remove symfony/phpunit-bridge
composer require --dev symfony/phpunit-bridge
php bin/phpunit
```

## Results

### Backend

#### Command
New command `ugo:orders:import`. <br/>
Usage: `ugo:orders:import <pathToCustomerCsv> <pathToOrdersCsv>`
* Paths can be relative or absolute (checked in this order)
* First line is skipped because assumed to be header lines
* For 'Customer', headers are: `id, title, lastname, firstname, postal_code, city, email`
* For 'Orders', headers are: `id, customer_id, product_id, quantity, price, currency, date`
* Delimiter is `;`

#### API endpoints
Two new API endpoints for `Customer` entity:
* `/api/customers`: list all existing customers in database
* `/api/customers/{customer_id}/orders`: list all orders for the related customers if it exists, `404` otherwise

### Frontend
Let's be honest: the frontend is a failure... 😭 <br/>
Here are the reason why I failed this part of the test:
* **Issue with integration**: it is the first time I set up a Symfony from scratch all by myself. <br/>
I was not able to correctly integrate TypeScript (thus I could not use it...) <br/>
Same problem for the frontend test framework

* **TypeScript** was not integrated so I had to rely on ES6 only, sorry!

* **React**: I did not understand how to correctly route my ReactJs components to my Symfony routing. 
I did try using 'ReactRouter' but the doc was not maintained so I give up after some hours of testing...