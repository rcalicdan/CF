# Aladyn API

This is the Aladyn API project built with Laravel. Follow the steps below to set up the project after cloning from GitHub.

## Prerequisites

Make sure you have the following installed on your machine:

-   PHP >= 8.2
-   Composer
-   PostgreSQL

## Setup Instructions

1. **Clone the repository:**

    ```sh
    git clone https://github.com/your-username/aladyn-api.git
    cd aladyn-backend
    ```

2. **Install dependencies:**

    ```sh
    composer install
    ```

3. **Copy the `.env` file:**

    ```sh
    cp .env.example .env
    ```

    or

    ```sh
    copy .env.example .env
    ```

4. **Generate the application key:**

    ```sh
    php artisan key:generate
    ```

5. **Configure the `.env` file:**

    - Update the database configuration to match your PostgreSQL setup:

        ```
        DB_CONNECTION=pgsql
        DB_HOST=127.0.0.1
        DB_PORT=5432
        DB_DATABASE=your_database_name
        DB_USERNAME=your_database_user
        DB_PASSWORD=your_database_password
        ```

    - Add or update the SMS API credentials:
        ```
        SMS_API_AUTH_TOKEN=your_sms_api_auth_token
        SMS_API_FROM_NUMBER=your_sender_number
        ```
    - You can use Test as value for the SMS_API_NUMBER if you use sms_api_linkmobility as your sms api provider for testing purposes.

6. **Run the database migrations:**

    ```sh
    php artisan migrate
    ```

7. **Seed the database:**

    ```sh
    php artisan db:seed
    ```

8. **Create a personal Access Token:**

    ```sh
    php artisan passport:client --personal
    ```

9. **Copy the `.env.testing` file for the testing environment:**

    ```sh
    cp .env.testing.example .env.testing
    ```

    or

    ```sh
    copy .env.testing.example .env.testing
    ```

10. **Generate the application key for the testing environment:**

    ```sh
    php artisan key:generate --env=testing
    ```

11. **Test the app:**

    ```sh
    php artisan test
    ```

12. **Start the queue worker:**

    To process queued jobs (e.g., sending SMS), run the following command:

    ```sh
    php artisan queue:work
    ```

13. **Serve the application:**

    ```sh
    php artisan serve
    ```

### SMS API Configuration

Ensure that the `test` parameter is set correctly based on the environment in the config/sms_api.php. It is automatically set to `0` for production or you can edit it manually in the config file:

```php
'smsapi_linkmobility' => [
    'method' => 'POST',
    'url' => 'https://api.smsapi.pl/sms.do',
    'params' => [
        'send_to_param_name' => 'to',
        'msg_param_name' => 'message',
        'others' => [
            'from' => env('SMS_API_FROM_NUMBER'),
            'format' => 'json',
            'encoding' => 'utf-8',
            'test' => env('APP_ENV') === 'production' ? '0' : '1', // Set to '0' for production
        ],
    ],
    'headers' => [
        'Authorization' => 'Bearer ' . env('SMS_API_AUTH_TOKEN'),
        'Content-Type' => 'application/json',
    ],
    'json' => true, // Ensure JSON format for request payload
    'add_code' => false, // Include country code in recipient number
],
```

Check for the logs in storage/logs/laravel.log to see if the SMS API is working correctly.

### Automatic Sms Notification

In the OrderService in app/ActionService/OrderService.php, the method createOrder has a variable for $testPhoneNumber.

```php
  public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $order = $this->createOrderRecord($data);
            $totalAmount = $this->processOrderServices($order, $data['services'], $data['price_list_id'], $order->is_complaint);
            $this->updateOrderTotalAmount($order, $totalAmount);

            $order->load('orderServices.service', 'client', 'driver', 'driver.user', 'priceList', 'orderCarpets');

            $message = 'Your order has been created. We will send later the schedule date for delivery';

            $testPhoneNumber = '48793676408'; // this just for testing purposes for sending test message for the number you provided, remove this after setting the app to production.
            $clientPhoneNumber = preg_replace('/[^\d]/', '', $order->client->phone_number); //use this in the job dispatch for sending real sms message to clients

            SendSmsJob::dispatch($clientPhoneNumber, $message)->afterCommit();

            return [
                'order' => $order,
                'summary' => $this->generateOrderSummary($order),
            ];
        });
    }
```

You can change the argument on $clientPhoneNumber for test number if you want to sent it to a test number. Same for QrValidationService in app/ActionService/QrValidationService.php.

```php
 public function validateAndUpdateStatus(string $qrCode, OrderCarpetStatus $status, string $message = ''): OrderCarpet
    {
        $orderCarpet = OrderCarpet::with('order.client')->where("qr_code", $qrCode)->firstOrFail();

        $orderCarpet->update([
            "status" => $status->value
        ]);

        $testPhoneNumber = '48793676408'; // this just for testing purposes for sending test message for the number you provided, remove this after setting the app to production.
        $clientPhoneNumber = preg_replace('/[^\d]/', '', $orderCarpet->order->client->phone_number); //use this in the job dispatch for sending real sms message to clients

        if (!empty($message)) {
            SendSmsJob::dispatch($clientPhoneNumber, $message);
        }

        return $orderCarpet;
    }
```
