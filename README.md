# PhpCalendarTool
A Standalone PHP program that can read, update and create events in Google Calendar

To install, please run:
```
git clone https://github.com/alexandre67fr/PhpCalendarTool
```

Compiled file will be located here: `PhpCalendarTool/build/calendar_tool.php`.

Then, include the file to your project:

```
<?php
include 'PhpCalendarTool/build/calendar_tool.php';
```

# Compilcation, Tests
First, install dependencies (PHPUnit and ApiGen) using composer:

```
composer install
```

Then, compile the file and run tests:

```
composer compile
```

*Warning*: PHPUnit tests delete all events in the first found calendar. Please use only test accounts for PHPUnit.

# Documentation
Available in `doc` folder, or online: https://rawgit.com/alexandre67fr/PhpCalendarTool/master/docs/index.html
