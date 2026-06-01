## insert database schema generation logic here

create a new user instance to trigger the schema generation
```php
$user = new User(
    "test@example.com",
    "John",
    "Doe",
    "password123",
    "profile.jpg"
);
```

create new user instance to trigger the record insertion
```php
$user.save();
```

update a record
```php

User::updateRecord([
    "email" => "test@example.com"
], [
    "firstName" => "Jane"
]);
```

delete a record
```php

User::deleteRecord([
    "email" => "test@example.com"
]);
```

select all records
```php
User::selectAll();
```

select with where condition
```php
User::where([
    "email" => "test@example.com"
]);
```