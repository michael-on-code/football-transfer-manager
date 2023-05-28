Internet Projects Ltd Test Task
========================

Football Transfer Manager Project Task.

Demo
------------

Access [this link][1] to try a demo.

Credentials : 
  * Username : michaeloncode
  * Password : administrator

Requirements
------------

  * PHP 8.0 or higher;
  * MySQL or MariaDB. MariaDB was used during the development. Do not forget to change the DATABASE_URL in the .env accordingly
  * and the [usual Symfony application requirements][2].

Installation
------------

```bash
$ git clone https://github.com/michael-on-code/internet-projects-test.git
```

Install Composer dependencies:

```bash
$ composer install
```

Create Database:

```bash
$ php bin/console doctrine:database:create
```

Execute Migration i.e creating needed tables, relations and constraints:

```bash
$ symfony console doctrine:migrations:migrate -n
```

Start the app:

```bash
$ symfony serve 
```


Usage
-----

Use the following credentials to log into the platform 

Username : administrator

Password : administrator

Once In, Log in into Settings Page, Upload the site's logo and you are good to go.

Enjoy !


[1]: https://bit.ly/michaeloncode-transfer-manager

[2]: https://symfony.com/doc/current/reference/requirements.html
