# ChangeLanguage v3

1. [Installation](01-installation.md)
2. [Basic configuration](02-basics.md)
3. [Advanced configuration](03-advanced.md)
4. [Backend tools](04-backend.md)
5. [Frontend module](05-frontend-module.md)
6. [Insert tags](06-inserttags.md)
7. [**Developers**](07-developers.md)
8. [Tips & FAQ](08-tips-faq.md)


## Supporting ChangeLanguage in custom modules

If your Contao module has a list and reader page, it probably works
very similar to news, calendars and faqs. The reader page will have an
alias or an ID in the URL to find the current record.

If your content is also available in multiple languages, you can implement
the `changelanguageNavigation` hook to tell the module about the correct URL.


### Example code

#### Registering the hook

Add your listener class and method to the `config/config.php` in your
extension folder (Contao 3) or bundle's `Resources/contao` folder (Contao 4).

```php
$GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = [
    'Vendor\Extension\EventListener\ChangelanguageNavigationListener',
    'onChangelanguageNavigation'
];
```

The hook will be called multiple times on each page, once for each
language of your *ChangeLanguage* navigation!


#### Rewriting an URL parameter

```php
// Vendor\Extension\EventListener\ChangelanguageNavigationListener.php
public function onChangelanguageNavigation(
    \Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent $event
) {
    // The target root page for current event
    $targetRoot = $event->getNavigationItem()->getRootPage();
    $language   = $targetRoot->rootLanguage; // The target language

    // Find your current and new alias from the current URL
    $newAlias = '…';

    // Pass the new alias to ChangeLanguage
    $event->getUrlParameterBag()->setUrlAttribute('items', $newAlias);
}
```

The `items` key is whatever you use in your module as the URL key.
If your module supports `auto_item`, make sure to **not** set an URL
key `auto_item`, but simply the regular name. Your `auto_item` key should
be registered in `$GLOBALS['TL_AUTO_ITEM]` and will be automatically
detected by *ChangeLanguage*.


#### Adjusting the navigation item

If for any reason you need to change how the navigation items is
rendered, use the navigation item of the event to do that.

```php
// Vendor\Extension\EventListener\ChangelanguageNavigationListener.php
public function onChangelanguageNavigation(
    \Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent $event
) {
    $navigationItem = $event->getNavigationItem();

    // Detect item for the current page, e.g. to do nothing
    if ($navigationItem->isCurrentPage()) {
        return;
    }

    // Override label of navigation item
    $navigationItem->setLabel('custom label');

    // Make the link open in a new window
    $navigationItem->setNewWindow(true);
}
```


#### Removing a navigation item

If for any reason you need to manually remove a language from
*ChangeLanguage* based on a custom condition, you can also do that with
the `changelanguageNavigation` event.

```php
// Vendor\Extension\EventListener\ChangelanguageNavigationListener.php
public function onChangelanguageNavigation(
    \Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent $event
) {
    // The target root page for current event
    $targetRoot = $event->getNavigationItem()->getRootPage();

    // Do something to figure out if the link should be hidden
    $shouldBeHidden = true;

    if ($shouldBeHidden) {
        $event->skipInNavigation();
    }
}
```

Be aware that calling this method will stop the event loop, no further
hook (for the same even / root page) will be called.


#### Finding a page in another language
If you are looking for a page in another language you can use the pageFinder to do so.

```php
$pageFinder = new \Terminal42\ChangeLanguage\PageFinder();
$pageFinder->findAssociatedForLanguage(\PageModel::findByPk(4), 'en');
```

## Upgrading from ChangeLanguage v2

Be aware that *ChangeLanguage* and its hooks have been completely rewritten
in version 3. The `translateUrlParameters` from version 2 will no longer
work. It is, however, recommended to keep it to support both versions.

Simply register two callbacks for both hook names, and handle each
separately. An example implementation can be found in [Isotope eCommerce 2.4][1].



[1]: https://github.com/isotope/core/blob/4983245961ad6cacebe272bc9995bfb5e5c43d10/system/modules/isotope/library/Isotope/EventListener/ChangeLanguageListener.php
