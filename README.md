# Saga PHP

Transactions for distributed system.

Read it if you are not familiar with Saga pattern [https://microservices.io/patterns/data/saga.html].

This library has no dependencies on any external libs and works on PHP 5.5+.

## Install

```bash
composer require slava-basko/saga-php
```

## Usage
Create a class that implements `Basko\Saga\StageInterface` and add it to the pipeline. Simple as that.
For example:
```php
class OrderFlight implements StageInterface
{
    /**
     * @param Booking $payload
     * @return Booking
     */
    public function execute($payload)
    {
        // Logic related to flight order.
        // Call external API and then $payload->setFlightId($flightId);
        return $payload;
    }

    /**
     * @param Booking $payload
     * @return Booking
     */
    public function rollback($payload)
    {
        // Logic related to flight cancellation
        // Call external API with $payload->getFlightId(); and then $payload->resetFlightId();
        return $payload;
    }
}
```
The pipeline:
```php
try {
    $pipe = new Pipeline();
    $pipe->addStage(new OrderFlight());
    $pipe->addStage(new OrderCar());
    $pipe->addStage(new OrderHotel());

    $pipe->execute($booking);
} catch (\Exception $e) {
    // Log
}
```
All stages will be executed sequentially: `OrderFlight::execute()`, `OrderCar::execute()`, `OrderHotel::execute()`.

Rollback also called sequentially but in reverse order: `OrderHotel::rollback()`, `OrderCar::rollback()`, `OrderFlight::rollback()`.