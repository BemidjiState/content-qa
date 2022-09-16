This class was made to validate and also modify content entered by a user (e.g. WordPress pages). The data is passed to the class which creates an object containing errors, and error messages.

Content QA runs **completely independent of WordPress.** While developing, **only use native PHP functions**.

Checks are done by modules (classes that extend the BSU_Base_Module class) that do very specific checks. The data can be passed to the class without configuration which will make *all* modules run. There are various configs that will allow you to only run specific modules.
