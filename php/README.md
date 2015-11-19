Installation
============
* Download and install composer `https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx`
* run `composer install` and configure API access parameters
* [optional] you can install survos api command in system by running:
```
ln -s  "`pwd`/survos" /usr/local/bin/survos
```

Running command
===========

* to get list of commands run `survos`
* to get help on specific command run `survos help import:example`
* to run a command `survos import:example filename`

List of available commands
==========================
* import:projects - imports projects from CSV file: `survos import:project data/new_projects.csv --server-code="local"`
* import:members
* import:example


Adding new command
==================
* create new class in Command folder according to ExampleCommand.php example
* add new class to `survos` file
