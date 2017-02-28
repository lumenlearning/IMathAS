# Purpose

MyOpenMath, Wamap, and Lumen OHM all share the same server. So the `config.php` for this server works different than your average IMathAS server.

# Details

Either a `config/local.php` should exist are the `CONFIG_ENV` should be set to 'production' or 'staging' 

The domain you are accessing from determines which config file you'll get loaded. 

Right now the configs are the same for staging/production, but you should use the `CONFIG_ENV` to change some values if needed.

