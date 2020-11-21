# tests-DI-112020

## Setup

### Backend and frontend
Install composer dependencies `composer install`

Install frontend dependencies `npm install`

### Database

Create database `php bin/console doctrine:database:create`

Apply the migrations `php bin/console doctrine:migrations:migrate`