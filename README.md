Stop russian war. **🇺🇦 Free Ukraine!**

# sentry

This is a fork. DSN JS not yet implemented.

![Screenshot](images/sentry.png?raw=true)

To install, copy and paste content of src directory into `dolibarr/htdocs/custom/sentry/`.\
To upgrade, remove content of the previous directory and restart installation.\
For configuration, go to: `Home / Configuration / Modules` and search `syslog`.

Notes:
- errors when profiling with Blackfire are not sent to Sentry
- undefined errors of Dolibarr core are not sent to Sentry
- errors are sent to Sentry after `fastcgi_finish_request` in the `__destruct()`

---

- Current version: 2.2.0 (10/10/2023)
- Compatibility: Dolibarr 5+ (18 included), PHP 7.2 / 7.3 / 7.4 / 8.0 / 8.1 / 8.2 / 8.3
- License: GNU GPL 3+

If you like, take some of your time to improve some translations, go to https://bit.ly/2HyCCEc.
