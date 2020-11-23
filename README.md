# tests-DI-112020

## Setup

### Backend and frontend
Install composer dependencies `composer install`

Install frontend dependencies `npm install`

### Database

Create database `php bin/console doctrine:database:create`

Create test database `php bin/console --env=test doctrine:database:create`

Apply the migrations `php bin/console doctrine:migrations:migrate`

Apply the migrations for test database `php bin/console --env=test doctrine:migrations:migrate`

## Results

### Backend

#### Command
New command `ugo:orders:import`.

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