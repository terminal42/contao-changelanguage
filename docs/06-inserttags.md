# ChangeLanguage v3

1. [Installation](01-installation.md)
2. [Basic configuration](02-basics.md)
3. [Advanced configuration](03-advanced.md)
4. [Backend tools](04-backend.md)
5. [Frontend module](05-frontend-module.md)
6. [**Insert tags**](06-inserttags.md)
7. [Developers](07-developers.md)
8. [Tips & FAQ](08-tips-faq.md)


## Insert tags

Several insert tags are provided to ease handling of multilingual websites
with *ChangeLanguage*. Basically, [the Contao core link insert tags][1] are
supported with a `changelanguage_` prefix and language suffix.

The page reference can either be the database ID or the page alias.

### Examples
{% raw %}
1. <code>{{changelanguage_link::15::fr}}</code>

    Generates a link to the french version of page ID 15.

2. <code>{{changelanguage_link_open::15::de}}</code>

    Generates the opening anchor for linking to the German version of page ID 15.

3. <code>{{changelanguage_link_url::15::en}}</code>

    Returns the URL to the English version of page ID 15.

4. <code>{{changelanguage_link_title::15::it}}</code>

    Returns the title of the Italian version for page ID 15.

5. <code>{{changelanguage_link_name::15::es}}</code>

    Returns the name of the Spanish version of page ID 15.
{% endraw %}

[1]: https://docs.contao.org/books/manual/3.5/en/04-managing-content/insert-tags.html#link-elements
