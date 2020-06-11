# Purpose

MyOpenMath, Wamap, and Lumen OHM all share the same server. So the `config.php` for this server works different than your average IMathAS server.

# Details

Either a `config/local.php` should exist are the `CONFIG_ENV` should be set to 'production' or 'staging' 

The domain you using to load OHM determines which config file you'll get loaded. 

Right now the configs are the same for staging/production, but you should use the `CONFIG_ENV` to change some values if needed.

# Local Vue server

When running the local Vue server, you will need to modify your
`/config/local.php` file.

1. Uncomment the two lines shown below.
1. Set `$CFG['assess2-use-vue-dev']` to `true`.

```
$CFG['assess2-use-vue-dev'] = false;
$CFG['assess2-use-vue-dev-address'] = 'http://localhost:8080'; // no trailing slash
```

# Using ngrok for OHM + Vue frontend

Follow these instructions to use `ngrok` to access OHM and its Vue frontend.

## 1. Setup ngrok config file

This is required to run multiple tunnels at the same time. (one for OHM, one
for Vue)

1. Place this into your `~/.ngrok2/ngrok.yml` file:
1. Start ngrok with `ngrok start --all`.

```
authtoken: (your_auth_token)
tunnels:
  ohm-backend:
    addr: 80
    proto: http
  ohm-frontend:
    addr: 8080
    proto: http
    host_header: rewrite
```

## 2. Edit OHM config file

1. Add or edit `$basesiteurl` in `/config/local.php` and set it to the
   address provided by ngrok for OHM. (ends in `:80`)
1. Set `$CFG['assess2-use-vue-dev-address']` to the address provided
   by ngrok for the Vue server. (ends in `:8080`)

## 3. Edit Vue config files

1. You will also need to create the file: `assess2/vue-src/.env.local` with
the following contents:

```
VUE_APP_IMASROOT=https://9e3f6be7.ngrok.io
```

Replace the above URL with the one provided by ngrok for OHM. (ends in `:80`,
but do not include the `:80` part)
