Change Language
==============


Allow the visitor to switch between different languages of a page.
----------------------------------------------------------

The idea is, that you have a multilingual site created with Contao. 

One of the website-roots has to be used as the fallback-language. Usually the fallback-language contains the majority of pages. 


How to
----------------------------------------------------------

In the first step, you create the site structure of you fallback-language. 

In the second step you define the root for a new language. 

By adding a regular page to this new language, you find a new select-box called “Fallback-Page”. This select-box shows all regular pages of the site structure of you fallback-language. Now you can select the page that corresponds to the one you just created. If there's no direct pendent, just leave the select-box at “No equal page”. The user will then be redirected to the start page.

The last step is to create a ChangeLanguage-Module and add it via the page layout or as content-element. 

By default the ChangeLanguage-Module will show you a list of clickable links for the available languages, by choosing the "nav_dropdown"-template in the module configuration you can change this to a dropdown-element (select-box).