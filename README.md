bootsy
======

This is chiefly a learning project for Symfony 2, my Hello World application, if you will. The goal is to create a basic
webapp with generic functionality that is reusable for most actual applications. That is, if successful, this will be code
I can continue to use.

Specs are [here](https://github.com/hfietz/bootsy/wiki/Specs)

The project will be constructed from the ground up rather than starting with a ready-made Symfony distribution, in order
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
turns out to map badly to a normalized database. I usually favor factory methods on the domain models that take primitive
data objects gathered directly from the db result set by a very simple controller whose responsibility is mostly in
piecing together the right SQL statements. The two things that make me consider using an ORM are object identity and
merging of user input and existing records. On the other hand, those two tasks are also where I have had the most trouble
with ORMs.

Current todos:
* create a main layout and a route to /
