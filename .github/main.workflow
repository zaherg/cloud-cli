workflow "CI" {
  on = "push"
  resolves = [
    "Run tests (PHP 7.1)",
    "Run tests (PHP 7.2)",
    "Run tests (PHP 7.3)",
  ]
}

action "Install dependencies" {
  uses = "pxgamer/composer-action@master"
  args = "install --prefer-dist --no-interaction --prefer-stable --no-suggest"
}

action "Run tests (PHP 7.1)" {
  uses = "franzliedke/gh-action-php@master"
  needs = ["Install dependencies"]
  runs = "php7.1 vendor/bin/phpunit --testdox"
}

action "Run tests (PHP 7.2)" {
  uses = "franzliedke/gh-action-php@master"
  needs = ["Install dependencies"]
  runs = "php7.2 vendor/bin/phpunit --testdox"
}

action "Run tests (PHP 7.3)" {
  uses = "franzliedke/gh-action-php@master"
  needs = ["Install dependencies"]
  runs = "php7.3 vendor/bin/phpunit --testdox"
}
