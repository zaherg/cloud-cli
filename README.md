# Cloud CLI Application for Cloudflare®

```
  _____ _                 _    _____ _      _____ 
 / ____| |               | |  / ____| |    |_   _|
| |    | | ___  _   _  __| | | |    | |      | |  
| |    | |/ _ \| | | |/ _` | | |    | |      | |  
| |____| | (_) | |_| | (_| | | |____| |____ _| |_ 
 \_____|_|\___/ \__,_|\__,_|  \_____|______|_____|
```


- Built on top of the [Laravel](https://laravel.com) components.
- Built using [laravel-zero.com](https://laravel-zero.com) components.
- Built using [Cloudflare® SDK](https://github.com/cloudflare/cloudflare-php) (v4 API Binding for PHP 7).

------

## Documentation

```
  v1.0.1-alpha1

  USAGE: cloud <command> [options] [arguments]

  init             Create the default config environment variables
  self-update      Updates cloudflare phar file to the latest version

  dns:add          Create a new DNS record for a zone
  dns:delete       Delete DNS Record
  dns:list-records List, search, sort, and filter a zones' DNS records
  dns:update       Update DNS Record

  user:details     Get current user details
  user:email       Get the current user email
  user:id          Get the current user id
  user:update      Edit part of your user details

  zone:list        List, search, sort, and filter your zones
  zone:purge-all   Remove ALL files from Cloudflare's cache, for every Website
```

Please check the [docs](./docs) directory for more details about each command.

## Usage

I know I didn't write much, but as always you can list all available commands using the following command:

```bash
$ cloud list
```

### Initiate the config values

Before doing anything , you need to retrieve your CloudFlare API key. Log into the CloudFlare console and navigate 
to My Settings. Scroll down until you find the API Key item and press the button labeled View API Key. 
After your API key displays, record it for use later.

To setup your credentials you should run the following command:

```bash
$ cloud init
``` 

And you will be asked to enter your email and API Key

```
Create the default config environment variables
===============================================

 What is your CloudFlare email?:
 > youremail@provider.com

 What is your CloudFlare API KEY:
 > yourAPIKey

```

If everything went as planned you will get a feedback about the process:

```
Create the default config environment variables: ✔
```

Your config information will be saved at `<your_home_directory>/.cloudflare/.env` file.

If you did anything wrong, you can run the command again to recreate the file


## TODO:

- [ ] Write more documentation.
- [ ] Write tests.

## Progress 

__PS__ : I may not be able to cover all the functionality, especially if they are not available for the free plan.

#### Finished

- [x] User Administration (partial)

#### Work in progress

- [ ] [DNS Records](https://www.cloudflare.com/dns/)
- [ ] Zones

#### Not started yet

- [ ] [Cloudflare® IPs](https://www.cloudflare.com/ips/)
- [ ] [Page Rules](https://support.cloudflare.com/hc/en-us/articles/200168306-Is-there-a-tutorial-for-Page-Rules-)
- [ ] [Web Application Firewall (WAF)](https://www.cloudflare.com/waf/)
- [ ] Virtual DNS Management
- [ ] Custom hostnames
- [ ] Manage TLS settings
- [ ] Zone Lockdown and User-Agent Block rules
- [ ] Organization Administration
- [ ] [Railgun](https://www.cloudflare.com/railgun/) administration
- [ ] [Keyless SSL](https://blog.cloudflare.com/keyless-ssl-the-nitty-gritty-technical-details/)
- [ ] [Origin CA](https://blog.cloudflare.com/universal-ssl-encryption-all-the-way-to-the-origin-for-free/)

 
## License

CLI Application for Cloudflare® is an open-source software licensed under the [MIT license](LICENSE.md).
