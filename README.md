bootsy
======

This is chiefly a learning project for Symfony 2, my Hello World application, if you will. The goal is to create a basic
webapp with generic functionality that is reusable for most actual applications. That is, if successful, this will be code
I can continue to use.

The project will be constructed from the ground up rather than starting with a ready-made Symfony distribution, in order
to get a precise understanding for the dependency management with Composer and the non-core bundles used in the Standard
Edition. To that end, it begins with a Composer project that installs the Symfony framework, but none of the standard 3rd
party bundles, such as Doctrine and SwiftMailer. Then a working skeleton with a kernel and a front controller is constructed,
to get the code called by the webserver. Then, bundles present in the Standard Edition and new, custom bundles are added
by and by, to eventually provide the following functionality:

* a database-backed user management with login, signup via email and password reset
* a typical HTML base layout that works both for mobile and desktop browsing and contains

    - one navigation bar

    - a "session info box", i. e. a place where the login status is displayed and links to log in / log out / account settings reside

    - a "CI box", where a logo, or a slogan, or a headline can be displayed

    - a main content area

* a set of CSS stylesheets, one each to
 ** reset all browser defaults
 ** position and size the above areas
 ** set colors, fonts, borders and backgrounds
* an error handling system that
 ** logs messages to the database, along with the relevant application context
 ** displays user-oriented error pages, which hide technical info in favour of a friendly message and helpful suggestions
 ** provides the user with an error identifier to pass on to technical personnel who can use it to retrieve the specific technical info
 ** provides a "wall of shame" for administrators to view the error log and technical info via the browser
 ** forwards the content of the "wall of shame" to the admin / developers by email (optionally: RSS)
 ** optionally provide a mechanism to forward errors to a bugtracker automatically, sample targets are JIRA and FogBugz

The assumption is that the above specs lead to a mix of new code and use of existing libraries which is best for getting
to know the whole ecosystem, while writing enough own code, but not too much. Also, those functionality should be widely
reusable in future projects.
