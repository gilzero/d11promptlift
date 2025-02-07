# Autoservices and autowiring

Brings features from symfony 3.3 and above into the Drupal
service container.

## Features

* Register services without a MODULENAME.services.yml just the class.
* [Autowire](https://symfony.com/doc/current/service_container/autowiring.html) those services.
* Register aliases for many Drupal services to ease autowiring.

## Usage

This module provides new ways for other modules to register services so
it does nothing by itself and it should be installed as a dependency.

Once enabled any class placed in `modulename/src/Autoservice/` will be
registered into the container with autowiring enabled. This means that
if the constructor has all the classes that it needs correctly type-hinted
with the corresponding interfaces the service will be available
with its full class name as the identifier.

Since the autowiring needs to know what service corresponds with an
interface and Drupal doesn't provide that mapping this module also
adds many aliases so the autowiring knows better what service to inject.
It won't work if you interface doesn't follow the pattern of being in
the same namespace as the service and the same name with the `Interface`
suffix.

Other services that can be registered:

* `modulename/src/AutoEventListener/`: The event_listener tag is automatically added. Autowired.
* `modulename/src/AutoPluginManager/`: Inherits from the `default_plugin_manager`. Not autowired.

## Contributions

The project is open to improvements about how to bring more
symfony container features into Drupal but also feel free to open any discussion about
how to promote and stabilize it further so more modules
can rely on it.

Patches on drupal.org are accepted but merge requests on
[gitlab](https://gitlab.com/upstreamable/drupal-autoservices) are preferred.

## Real time communication

Join the [#autoservices](https://drupalchat.me/channel/autoservices)
channel on [drupalchat.me](https://drupalchat.me).

## Notes

This project started as a way to ease development of
[Libraries provider](https://gitlab.com/upstreamable/drupal-libraries-provider)
so at the moment it works for the services defined there (no services.yml file!)

If you want to contribute to bring this features into core
[there is a meta issue.](https://www.drupal.org/project/drupal/issues/3021900)

The inspiration about how to achieve the auto-registrations came from
[this talk](https://www.youtube.com/watch?v=q06b2uVM8MQ) (mute the left channel of your audio).

## Similar modules

[Extended container](https://www.drupal.org/project/extended_container): it needs
patches to Drupal and that makes it more difficult to install and maintain.
