# Membership for laravel

This is a website membership management package for Laravel that you can use in your projects.

In this package, you can entrust the members or users of any model or table you have to it and don't worry about anything anymore, this package helps to make user memberships simple and you can entrust any membership to it without worry.

## Install via composer

Run the following command to pull in the latest version:

```bash
composer require jobmetric/laravel-membership
```

## Documentation

To use the services of this package, please follow the instructions below.

In this package, we have two trait classes that must be connected to both sides of the user and member models.

User models can include `user` and `admin` models or anything else that we want to include in the member model.

The member model is a model that wants any user to be a member, such as `post`, `product`, `order`, and anything else.

For example, you need to connect two trait classes to both user and order models.

### User model

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use JobMetric\Membership\CanMember;

class User extends Authenticatable
{
    use CanMember;
}
```

### Order model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use JobMetric\Membership\Contracts\MemberContract;
use JobMetric\Membership\HasMember;

class Order extends Model implements MemberContract
{
    use HasMember;
    
    /**
     * allow the member collection.
     *
     * @return array
     */
    public function allowMemberCollection(): array
    {
        return [
            'owner' => 'single',
            'members' => 'multiple'
        ];
    }
}
```

The allowMemberCollection method must be in the order class, and we define the collections we want to have in the order class.

> The `owner` collection is a `single` collection that has only one member, and the `members` collection is a `multiple` collection that can have multiple members.
> 
> The array keys are designed according to the needs of your model fields, and you can use any word, but the value in front of each key must be selected between `single` and `multiple`.
> 
> `single`: means that only one member can be in the collection.
> 
> `multiple`: means that multiple members can be in the collection.

Now we have connected our two traits to the mentioned models, and now we can use their functions to register and get user membership information in the order model.

## `HasMember` trait methods

### `members()`

This method returns the members of the order model.

```php
$order = Order::find(1);
$members = $order->members();
```

### `storeMember($person, $collection, $expired_at = null)`

This method stores a member in the order model.

> `$person`: The user model that you want to store in the order model.
> 
> `$collection`: The collection that you want to store the user in it.
> 
> `$expired_at`: The expiration date of the membership. If you do not specify this parameter, the membership will not expire.

```php
$order = Order::find(1);
$user = User::find(1);
$order->storeMember($user, 'members');
```

### `forgetMember($person, $collection)`

This method removes a member from the order model.

> `$person`: The user model that you want to remove from the order model.
> 
> `$collection`: The collection that you want to remove the user from it.

```php
$order = Order::find(1);
$user = User::find(1);
$order->forgetMember($user, 'members');
```

### `hasMember($person, $collection)`

This method checks if a user is a member of the order model.

> `$person`: The user model that you want to check if it is a member of the order model.
> 
> `$collection`: The collection that you want to check if the user is a member of it.

```php
$order = Order::find(1);
$user = User::find(1);
$order->hasMember($user, 'members');
```

### `renewMember($person, $collection, $expired_at = null)`

This method renews the membership of a user in the order model.

> `$person`: The user model that you want to renew the membership in the order model.
> 
> `$collection`: The collection that you want to renew the membership in it.
> 
> `$expired_at`: The expiration date of the membership. If you do not specify this parameter, the membership will not expire.

```php
$order = Order::find(1);
$user = User::find(1);
$order->renewMember($user, 'members');
```

### `updateExpiredAtMember($person, $collection, $expired_at = null)`

This method updates the expiration date of the membership of a user in the order model.

> `$person`: The user model that you want to update the expiration date of the membership in the order model.
> 
> `$collection`: The collection that you want to update the expiration date of the membership in it.
> 
> `$expired_at`: The expiration date of the membership. If you do not specify this parameter, the membership will not expire.

```php
$order = Order::find(1);
$user = User::find(1);
$order->updateExpiredAtMember($user, 'members');
```

### `getPerson($collection = null, $is_expired = false)`

This method returns the user who is a member of the order model.

> `$collection`: The collection that you want to get the user from it. If you do not specify this parameter, the method will return the owner of the order model.
> 
> `$is_expired`: If you want to get the expired membership, you can set this parameter to `true`.

```php
$order = Order::find(1);
$order->getPerson('members');
```

## `CanMember` trait methods

### `persons()`

This method returns the orders that the user is a member of.

```php
$user = User::find(1);
$orders = $user->persons();
```

### `storePerson($memberable, $collection, $expired_at = null)`

This method stores a user in the order model.

> `$memberable`: The member model that you want to store the user in it.
> 
> `$collection`: The collection that you want to store the user in it.
> 
> `$expired_at`: The expiration date of the membership. If you do not specify this parameter, the membership will not expire.

```php
$user = User::find(1);
$order = Order::find(1);
$user->storePerson($order, 'members');
```

### `forgetPerson($memberable, $collection)`

This method removes a user from the order model.

> `$memberable`: The member model that you want to remove the user from it.
> 
> `$collection`: The collection that you want to remove the user from it.

```php
$user = User::find(1);
$order = Order::find(1);
$user->forgetPerson($order, 'members');
```

### `hasPerson($memberable, $collection)`

This method checks if a user is a member of the order model.

> `$memberable`: The member model that you want to check if the user is a member of it.
> 
> `$collection`: The collection that you want to check if the user is a member of it.

```php
$user = User::find(1);
$order = Order::find(1);
$user->hasPerson($order, 'members');
```

### `renewPerson($memberable, $collection, $expired_at = null)`

This method renews the membership of a user in the order model.

> `$memberable`: The member model that you want to renew the membership in it.
> 
> `$collection`: The collection that you want to renew the membership in it.
> 
> `$expired_at`: The expiration date of the membership. If you do not specify this parameter, the membership will not expire.

```php
$user = User::find(1);
$order = Order::find(1);
$user->renewPerson($order, 'members');
```

### `updateExpiredAtPerson($memberable, $collection, $expired_at = null)`

This method updates the expiration date of the membership of a user in the order model.

> `$memberable`: The member model that you want to update the expiration date of the membership in it.
> 
> `$collection`: The collection that you want to update the expiration date of the membership in it.
> 
> `$expired_at`: The expiration date of the membership. If you do not specify this parameter, the membership will not expire.

```php
$user = User::find(1);
$order = Order::find(1);
$user->updateExpiredAtPerson($order, 'members');
```

### `getMember($memberable = null, $collection = null, $is_expired = false)`

This method returns the user who is a member of the order model.

> `$memberable`: The member model that you want to get the user from it. If you do not specify this parameter, the method will return the owner of the user model.
> 
> `$collection`: The collection that you want to get the user from it. If you do not specify this parameter, the method will return the owner of the user model.
> 
> `$is_expired`: If you want to get the expired membership, you can set this parameter to `true`.

```php
$user = User::find(1);
$user->getMember('members');
```

## Add personable attribute in Resource

In the member resource, there is a field called `personable` that can display your model, but it must be set as follows.

First, you create a listener for the model you want to display in the member resource.

```php
php artisan make:listener AddUserResourceToPersonableResourceListener
```

Then, you add the following code to the listener.

```php
use JobMetric\Membership\Events\PersonableResourceEvent;

class AddUserResourceToPersonableResourceListener
{
    public function handle(PersonableResourceEvent $event)
    {
        $personable = $event->personable;

        if (personable instanceof \App\Models\User) {
            $event->resource = new \App\Http\Resources\UserResource($personable);
        }
    }
}
```

Finally, you add the listener to the `EventServiceProvider` class.

```php
protected $listen = [
    \JobMetric\Membership\Events\PersonableResourceEvent::class => [
        \App\Listeners\AddUserResourceToPersonableResourceListener::class,
    ],
];
```

The work is done, now when the `MemberResource` is called and if the `UserResource` should be returned, the details of that resource will be displayed in the `personable` attribute.

## Add memberable attribute in Resource

In the member resource, there is a field called `memberable` that can display your model, but it must be set as follows.

First, you create a listener for the model you want to display in the member resource.

```php
php artisan make:listener AddOrderResourceToMemberableResourceListener
```

Then, you add the following code to the listener.

```php
use JobMetric\Membership\Events\MemberableResourceEvent;

class AddOrderResourceToMemberableResourceListener
{
    public function handle(MemberableResourceEvent $event)
    {
        $memberable = $event->memberable;

        if ($memberable instanceof \App\Models\User) {
            $event->resource = new \App\Http\Resources\OrderResource($memberable);
        }
    }
}
```

Finally, you add the listener to the `EventServiceProvider` class.

```php
protected $listen = [
    \JobMetric\Membership\Events\MemberableResourceEvent::class => [
        \App\Listeners\AddOrderResourceToMemberableResourceListener::class,
    ],
];
```

The work is done, now when the `MemberResource` is called and if the `UserResource` should be returned, the details of that resource will be displayed in the `memberable` attribute.

## Events

This package contains several events for which you can write a listener as follows

| Event                            | Description                                                              |
|----------------------------------|--------------------------------------------------------------------------|
| `MemberableResourceEvent`        | This event is called after getting the memberable resource.              |
| `PersonableResourceEvent`        | This event is called after getting the personable resource.              |
| `MembershipStoredEvent`          | This event is called after storing a membership.                         |
| `MembershipForgetEvent`          | This event is called after forgetting a membership.                      |
| `MembershipRenewEvent`           | This event is called after renewing a membership.                        |
| `MembershipUpdateExpiredAtEvent` | This event is called after updating the expiration date of a membership. |
| `MembershipRemoveExpiredEvent`   | This event is called after removing the expired memberships.             |

## Contributing

Thank you for considering contributing to the Laravel Membership! The contribution guide can be found in the [CONTRIBUTING.md](https://github.com/jobmetric/laravel-membership/blob/master/CONTRIBUTING.md).

## License

The MIT License (MIT). Please see [License File](https://github.com/jobmetric/laravel-membership/blob/master/LICENCE.md) for more information.
