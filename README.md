The Content QA class was created for validating and modifying content entered by a user (e.g. WordPress pages). When errors are returned, with the intent to provide an explenation of what is wrong and how to fix it. In cases of copliancy issuse, a link is provided to documentation (e.g. WCAG).

This class runs **completely independent of WordPress.** While developing, **only use native PHP functions**.

Content QA is modular so you can configure it to use specific checks (e.g. heading structure) or certain types of checks (e.g. only validate, no modifying). If Content QA is initialized without passing any configuration args then every modual that is detected will run.

# How to use it
Add this git repo to you project's `package.json` for NPM or add it as a Git submodule. Below is an example of how you can add it as a dependencie to your `package.json` file for NPM.

    "dependencies": {
        "content-qa": "git+https://[GIT_USERNAME]:[GIT_ACCESS_TOKEN]@bitbucket.bemidjistate.edu/scm/inc/content-qa.git"
    }

Then be sure the `content-qa` directory is moved to a location within your project where it can be included or required with PHP, like in the example below.

```php
require_once 'DIR/IN/YOUR/PROJECT/content-qa/class-bsu-content-qa.php';
$content_qa = new BSU_Content_QA( $html_to_check, $args ); // $args is optional
```

## Configuration
When you create a new Content QA object, you can choose to pass it configuration args. If no args are passed it will run every module with default settings. Modules are autoloaded from `modules/modify/*` and `modules/validate/*`. Below are all of the args you can pass. None are required.

```php
$args = array(
    'headings_start'   => 2, // (int|string) to indicate level that headings should start at.
    'headings_end'     => 6, // (int|string) to indicate level that headings should end at.
    'par_limit'        => 3, // (int|string) the max number of paragraphs allowed.
    'word_limit'       => 300, // (int|string) the max number of words allowed.
    'modules' => array( // Specific modules to be run.
        'modify'   => array( // The type of moduleModules that modify content.
            'Module_Class_Name', // (string) The class name of modules to be used.
        'validate' => array(
            'Module_Class_Name', // (string) The class name of modules to be used.
        ),
    ),
    'modules_disabled' => array( // Modules to NOT run.
        'BSU_Links_Status',
    ),
);
```

If you wanted to run every module except one, you could use the `modules_disabled` arg but not pass the `modules` arg at all. The example below would do just that. Since no other args were passed all other checks will run their default settings.

```php
$args = array(
    'modules_disabled' => array( // Modules to NOT run.
        'BSU_Links_Status', // The name of the class.
    ),
);
```

# Development, Creating Modules
While this is separate from WordPress, we still use the same linting rules as we do when developing for WordPress. This way coding style stays consistent.

Each validation or modification (a module) is done by a single file. Each module extends the `BSU_Base_Module` class to do a very specific type of check. This way we can choose which types of checks to do on an as needed basis.

## Types of checks
There are two types of modules: **modify** and **validate**. All of the modify modules are run before the validate modules. This way any modifications are done before we check for errors.

## Naming conventions
Each module file should be the exact name of the class it contains, followed by the PHP file extension (e.g. Some_Class_Name.php). This allows the module to be discovered by Content QA.

An autoloader is used to load modules that are discovered in the `modify` and `validate` directories. This way to add or remove a module, all we need to do is create a new file for it.

Modules run in the order they are sorted in directory structure. For that reason, the name of **modify** modules is preceeded with a number (e.g. 00-Class_Name.php). This way you can controll the order in which modifications happen.
