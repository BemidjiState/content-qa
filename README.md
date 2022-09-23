The Content QA class was created for validating and modifying content entered by a user (e.g. WordPress pages). This class runs **completely independent of WordPress.** While developing, **only use native PHP functions**.

Content QA is modular so you You can configure it to use specific checks (e.g. heading structure) or certain types of checks (e.g. only validate, without modifying). If you run it without passing any configuration args then every modual that is detected will run.

# How to use it
Within your project, include this as a package with NPM or as a submodule with Git. Below is an example of how you can add it as a dependencie to your `package.json` file for NPM.

    "dependencies": {
        "content-qa": "git+https://[GIT_USERNAME]:[GIT_ACCESS_TOKEN]@bitbucket.bemidjistate.edu/scm/inc/content-qa.git"
    }

## Configuration
When you create a new Content QA object, you can choose to pass it configuration args. If no args are passed it will run every module with default settings.

# Development, Creating Modules
While this is separate from WordPress, we still use the same linting rules as we do when developing for WordPress.

Each validation or modification is done by a single file (classes that extend the `BSU_Base_Module` class) that do very specific checks. The data can be passed to the class without configuration which will make *all* modules run. There are various configs that will allow you to only run specific modules.
