# User Administration (partial)

Available commands are:

```bash
  user:details     Get current user details
  user:email       Get the current user email
  user:id          Get the current user id
  user:update      Edit part of your user details
``` 

## Get the details about your account

To retrieve the information about your account, you can run the command:

```
$ cloud user:details
```

This command needs no arguments nor options

```bash
Description:
  Get current user details

Usage:
  user:details
```


## Partially edit your user details

```bash
Description:
  Edit part of your user details

Usage:
  user:update [options]

Options:
      --first_name[=FIRST_NAME]  User's first name
      --last_name[=LAST_NAME]    User's last name
      --telephone[=TELEPHONE]    User's telephone number
      --country[=COUNTRY]        The country in which the user lives
      --zipcode[=ZIPCODE]        The zipcode or postal code where the user lives
```

Example:

```bash
cloud user:update --first_name=<first_name> --last_name=<last_name> --telephone=<telephone> --country=US --zipcode=<zipcode>
```
