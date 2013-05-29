bootsy
======

### General

This is chiefly a learning project for Symfony 2, my Hello World application, if you will. The goal is to create a basic
webapp with generic functionality that is reusable for most actual applications. That is, if successful, this will be code
I can continue to use.

Specs are [here](https://github.com/hfietz/bootsy/wiki/Specs)

### Status (as of 2013-05-29)
The current code is very much WIP and even features that are complete might still be a little rough in many places.

Currently functional URLs:

* /admin/db/status
* /admin/db/versions
* /admin/errors

Current todos:
* create a main layout and a route to /
* secure admin pages and establish a user / rights management

### Installation
* git clone
* composer install
* copy app/config/parameters.yml.dist to app/config/parameters.yml (no need to adjust anything, that can be done via the browser)
* set up a -Postgres- database and user account
* point a webserver to the web/ directory
* make sure webserver can write into app/cache
* visit <location of your install>/admin/db/status in your browser
* Enter db user, password, etc and click the button to store that data in parameters.yml
* visit <location of install>/admin/db/versions
* You should see two scripts with a status of "new", click the button to run them, the status should change to "current"

### Notes

This project is being constructed from the ground up rather than starting with a ready-made Symfony distribution, in order
to get a precise understanding for the dependency management with Composer and the non-core bundles used in the Standard
Edition.

So far, it is using:

* Symfony 2.2.1
* Doctrine 2.2 (only the DBAL is used so far)

It will probably also use:

* Swift Mailer
* Friends Of Symfony User Bundle

I am still very hesitant to use the Doctrine ORM, not because there's anything wrong with it, rather because I tend to
not get a lot out of ORMs in general. This is mostly due to a habit of creating a rather rich model layer that often
turns out to map badly to a normalized database.

So, for the moment, we have an ObjectMapper interface that is used by the database service for loading and hydrating
objects, and there are two methods, insertOrSelect and selectOrInsert, which try to get the primary key of an existing
record and will insert it from the data they are given, if it's not there. The end result of both methods is the same,
but they represent two common strategies to implement what is known to MySQL users as "INSERT IGNORE", and, depending on
your use case, either one may be more suited.

It should be clear from the above that object-to-database mapping is still incomplete at this time.
