# Purpose

MyOpenMath, Wamap, and Lumen OHM all share the same server. So the `config.php` for this server works different than your average IMathAS server.

# Details

Either a `config/local.php` should exist are the `CONFIG_ENV` should be set to 'production' or 'staging' 

The domain you are accessing from determines which config file you'll get loaded. 

Right now the configs are the same for staging/production, but you should use the `CONFIG_ENV` to change some values if needed.

# Local Vue server

When running the local Vue server, you will need to modify your
`/config/local.php` file.

1. Uncomment the following lines.
1. Set `$CFG['assess2-use-vue-dev']` to `true`.
1. Set `$CFG['assess2-use-vue-dev-address']` if using something like ngrok.

```
$CFG['assess2-use-vue-dev'] = false;
$CFG['assess2-use-vue-dev-address'] = 'http://localhost:8080'; // no trailing slash
```

## Using ngrok or similar tool

If you will be using something like ngrok to forward localhost to another
machine, set `$CFG['assess2-use-vue-dev-address']` to the address provided
by ngrok for the Vue server.
