# How to Contribute

The contribution guideline is derived from the SlimPHP contribution guideline
 
## Pull Requests

1. Fork the Moon repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch to the **master** branch

It is very important to separate new features or improvements into separate feature branches and to send a
pull request for each branch.

This allows me to review and pull in new features or improvements individually.

## Style Guide

All pull requests must adhere to the code style specified in the `.php_cs.dist` file, you can easily run `composer fix` to do it automatically.
Moon uses [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) to fix the code style.

## Unit Testing

All pull requests must be accompanied by passing unit tests and complete code coverage.
Moon uses [PHPUnit](https://github.com/sebastianbergmann/phpunit) to test the code.
