# Cloudflare CLI Application

```
  _____ _                 _  __ _                   _____ _      _____ 
 / ____| |               | |/ _| |                 / ____| |    |_   _|
| |    | | ___  _   _  __| | |_| | __ _ _ __ ___  | |    | |      | |  
| |    | |/ _ \| | | |/ _` |  _| |/ _` | '__/ _ \ | |    | |      | |  
| |____| | (_) | |_| | (_| | | | | (_| | | |  __/ | |____| |____ _| |_ 
 \_____|_|\___/ \__,_|\__,_|_| |_|\__,_|_|  \___|  \_____|______|_____|
```


- Built on top of the [Laravel](https://laravel.com) components.
- Built using [laravel-zero.com](https://laravel-zero.com) components.
- Built using [Cloudflare SDK](https://github.com/cloudflare/cloudflare-php) (v4 API Binding for PHP 7).

------

## Documentation

```                                                                    
  v1.0.1-alpha

  USAGE: cloudflare <command> [options] [arguments]

  init             Create the default config environment variables.
  self-update      Updates cloudflare to the latest version

  dns:add          Create a new DNS record for a zone.
  dns:list-records List, search, sort, and filter a zones' DNS records.

  user:details     Get current user details.
  user:email       Get the current user email.
  user:id          Get the current user id.
  user:update      Edit part of your user details

  zone:list        List, search, sort, and filter your zones
  zone:purge-all   Remove ALL files from Cloudflare's cache, for every Website.
```

## License

Cloudflare CLI Application is an open-source software licensed under the [MIT license](LICENSE.md).
