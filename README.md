# REST APIs for Order Management System

## About 

- [Docker](https://www.docker.com/) as the container service to isolate the environment.
- [NGINX](https://www.nginx.com/) as a Web Server layer
- [PHP](https://php.net/) to develop backend support.
- [Laravel](https://laravel.com) as the server framework layer
- [MySQL](https://mysql.com/) as the database layer
- [SWAGGER](https://swagger.io/) for API documentation


## How to Install & Run

1.  Clone the repo
2.  Set Google Distance API key for GOOGLE_MAP_KEY variable in .env (environment file) located in ./backend directory
3.  Run `./start.sh` to build docker containers, executing migration and PHPunit test cases

## API Documentation using Swagger

1. Swagger API docs can be accessed at URL http://localhost:8080/docs
2. Swagger Json can be assessed at backend/public/swagger/swagger.json

## Code coverage report

1. Code coverage report can be accessed at URL http://localhost:8080/codecoverage/index.html
2. Xdebug has been used for code analysis


## Manually Migrating tables

1. To run migrations manually use this command `docker exec app php artisan migrate`

## Manually Starting the docker and test Cases

1. You can run `docker-compose up` from terminal
2. Server can be accessed at `http://localhost:8080`
3. Run manual testcase suite:
    - Unit Tests: `docker exec app php ./vendor/bin/phpunit /var/www/html/tests/Unit`
    - Integration Tests: `docker exec app php ./vendor/bin/phpunit /var/www/html/tests/Feature`  

## API Reference Documentation

- `localhost:8080/orders` :

    POST Method - to create new order with origin and distination coordinates
    1. Header :
        - POST /orders HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json
        - Accept: application/json

    2. Post-Data :
    ```
         {
            "origin" :["21.344353", "74.102493"],
            "destination" :["21.535517", "73.391029"]
         }
    ```

    3. Responses :
    ```
            - Response
            {
              "id": 103,
              "distance": 3495825,
              "status": "UNASSIGNED"
            }
    ```

        Code                    Description
        - 200                   Successful
        - 400                   Api request denied or not responding
        - 422                   Invalid Request


- `localhost:8080/orders/:id` :

    PATCH method to update status of an order
    1. Header :
        - PATCH /orders/54 HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json
        - Accept: application/json

    2. Post-Data :
    ```
         {
            "status" : "TAKEN"
         }
    ```

    3. Responses :
    ```
            - Response
            {
              "status": "SUCCESS"
            }
    ```

        Code                    Description
        - 200                   successful operation
        - 422                   Invalid Request Parameter
        - 409                   Order already taken
        - 417                   Invalid Order Id


- `localhost:8080/orders?page=:page&limit=:limit` :

    GET Method - to fetch orders with page number and limit
    1. Header :
        - GET /orders?page=2&limit=5 HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json

    2. Responses :

    ```
            - Response
            [
              {
                "id": 6,
                "distance": 474895,
                "status": "TAKEN"
              },
              {
                "id": 7,
                "distance": 474895,
                "status": "UNASSIGNED"
              },
              {
                "id": 8,
                "distance": 340224,
                "status": "UNASSIGNED"
              },
              {
                "id": 9,
                "distance": 334514,
                "status": "UNASSIGNED"
              },
              {
                "id": 10,
                "distance": 4395824,
                "status": "UNASSIGNED"
              }
            ]
    ```

        Code                    Description
        - 200                   successful operation
        - 422                   Invalid Request Parameter
        - 500                   Internal Server Error


## App Structure

**.env**

- config contains project configuration like app configs, Google API Key, db connection

**./app**

- contains server configuration file, services, repositories, controllers and models
- migration files are written under database folder in migrations directory
    - To run migrations use `docker exec app php artisan migrate` command
- `OrderController` contains the api's methods :
    1. localhost:8080/orders - POST method to create new order with origin and destination params
    2. localhost:8080/orders - PATCH method to update status for taken, also handled race condition, only 1 order can be TAKEN at one point
    3. localhost:8080/orders?page=1&limit=4 - GET url to fetch orders with page and limit

**./tests**
- this folder contains Integraton and UnitTest cases, written under /tests/Feature and /tests/Unit respectively
    - Unit Tests: `docker exec app php ./vendor/bin/phpunit /var/www/html/tests/Unit`
    - Integration Tests: `docker exec app php ./vendor/bin/phpunit /var/www/html/tests/Feature`  
