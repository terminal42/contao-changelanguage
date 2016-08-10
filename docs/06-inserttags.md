# ChangeLanguage v3

1. [Installation](01-installation.md)
2. [Basic configuration](02-basics.md)
3. [Advanced configuration](03-advanced.md)
4. [Backend tools](04-backend.md)
5. [Frontend module](05-frontend-module.md)
6. [**Insert tags**](06-inserttags.md)
7. [Developers](07-developers.md)
8. [Tips & FAQ](08-tips-faq.md)


## Insert Tags

Serveral insert tags are provided to ease handling of multilingual websites
with ChangeLanguage. Basically, [the Contao core link insert tags][1] are
supported with a `changelanguage_` prefix and language suffix.

The page reference can either be the database ID or the page alias.

### Examples

1. `{{changelanguage_link::15::fr}}`

    Generates a link to the french version of page ID 15.

2. `{{changelanguage_link_open::15::de}}`

    Generates the opening anchor for linking to the german version of page ID 15.

3. `{{changelanguage_link_url::15::en}}`

    Returns the URL to the english version of page ID 15.

4. `{{changelanguage_link_title::15::it}}`

    Returns the title of the italian version for page ID 15.

5. `{{changelanguage_link_name::15::es}}`

    Returns the name of the spanish version of page ID 15.


[1]: https://docs.contao.org/books/manual/3.5/en/04-managing-content/insert-tags.html#link-elements
