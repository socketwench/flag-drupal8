Flag for Drupal 8 [![Build Status](https://travis-ci.org/socketwench/flag-drupal8.svg?branch=master)](https://travis-ci.org/socketwench/flag-drupal8)
=================

Contents:
 * Introduction
 * History and Maintainers
 * Installation
 * Configuration
 * Support

Introduction
------------

The Flag module allows you to define a boolean toggle field and attach it to a
node, comment, user, or any entity type. You may define as many of these 'flags'
as your site requires. By default, flags are per-user. This means any user with
the proper permission may chose to flag the entity.

One example use of Flag is to create a "read later" flag for content. Each user
may choose to mark the content as "read later". The site builder can create a
view that displays all the content the user has chosen to read later.

Other flag examples include:
 * Favorite
 * Mark as spam
 * Friend (for users)

You may want to visit the handbook of this module, at:

  http://drupal.org/handbook/modules/flag

History and Maintainers
-----------------------

This module was formerly known as Views Bookmark, which was originally was
written by Earl Miles. Later versions of Flag were written by Nathan Haug and
Mooffie. Flag 8.x was written by socketwench.

Current Flag Maintainers:
 * Joachim
 * Shabana Blackborder
 * socketwench

Installation
------------

Flag 8.x is installed like any other Drupal 8 module and requires brief
configuration prior to use.

1. Download the module to your DRUPAL_ROOT/modules directory, or where ever you
install contrib modules on your site.
2. Go to Admin > Extend and enable the module.

Configuration
-------------

Configuration of Flag module involves creating one or more flags.

1. Go to Admin > Structure > Flags, and click "Add New Flag".
2. Select the target entity type, and click "Continue".
3. Enter the flag link text, link type, and any other options.
4. Click "Save Flag".
5. Under Admin > People, configure the permissions for each Flag.

Once you are finished creating flags, you may choose to use Views to leverage your new flags.

Support
-------

If you experience a problem with flag or have a problem, file a request or
issue on the flag queue at http://drupal.org/project/issues/flag.

DO NOT POST IN THE FORUMS.

Posting in the issue queues is a direct line of communication with the module
authors.

No guarantee is provided with this software, no matter how critical your
information, module authors are not responsible for damage caused by this
software or obligated in any way to correct problems you may experience.

Licensed under the GPL 2.0.
http://www.gnu.org/licenses/gpl-2.0.txt
