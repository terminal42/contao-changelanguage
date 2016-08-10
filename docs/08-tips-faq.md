# ChangeLanguage v3

1. [Installation](01-installation.md)
2. [Basic configuration](02-basics.md)
3. [Advanced configuration](03-advanced.md)
4. [Backend tools](04-backend.md)
5. [Frontend module](05-frontend-module.md)
6. [Insert tags](06-inserttags.md)
7. [Developers](07-developers.md)
8. [**Tips & FAQ**](08-tips-faq.md)


## Tips & Tricks

<dl>

<dt>Do not use flags for the language selection</dt>
<dd>Using flag icons for languages is a very bad idea. Flags represent
    <u>countries</u> and not <u>languages</u>. Not every language belongs to
    only one country! Would you take the American flag, the British flag or
    the Australian flag for english? What flags do you use for <i>Chinese
    Simplified</i> and <i>Chinese Traditional</i> (two different forms of writing
    for the same chinese language)?<br>
    Read more at <a href="https://www.ethnologue.com/about/problem-language-identification">https://www.ethnologue.com/about/problem-language-identification</a></dd>

<dt>Use a select menu for language selection</dt>
<dd>Easy: go to your front end module settings and select <i>nav_dropdown</i> in
    the <i>Navigation template</i> option. <b>BAM!</b>, you're done :-)</dd>

<dt>Customizing alternate link tags</dt>
<dd>By default, <i>ChangeLanguage</i> will add <code>&lt;link rel="alternate"&gt;</code>
    markup to your page's head section. This will tell Google and other search
    engines which pages belong together for better search results.<br><br>
    If you want to customize the alternate tags, you can create a copy of the
    <code>block_alternate_links.html5</code> template and customize the output.</dd>

</dl>


## Common issues

<dl>

<dt>"You must not have more than one auto_item parameter" error message</dt>
<dd>This error is a misconfiguration based on all of the following conditions:
    <ol>
    <li>You do have <i>auto_item</i> functionality enabled in the system settings.</li>
    <li>Multiple modules (e.g. news and events) have the same <i>Redirect page</i> set.</li>
    <li>There are news and events with the same alias.</li>
    </ol>
    To fix the error message, you must correctly set up your page structure and modules.
</dd>

</dl>


## Frequently Asked Questions

<dl>

<dt>Where can I get support for ChangeLanguage?</dt>
<dd>Please use the official Contao community forums at
    https://community.contao.org for support on all our free extensions.</dd>

<dt>I have found a bug, where do I report it?</dt>
<dd>Please use the <a href="https://github.com/terminal42/contao-changelanguage/issues">GitHub issue tracker</a>
    to report bugs. Please make sure to first check if the problem has already been reported.</dd>

<dt>I need feature X, can you add that?</dt>
<dd>It depends. You can use <a href="https://github.com/terminal42/contao-changelanguage/issues">GitHub issues</a>
    to create feature requests. If your business depends on the feature, it's
    best to pay a developer to implement it for you and then give something back
    to the community.
    Make sure to read our <a href="https://www.terminal42.ch/en/open-source.html">Open Source manifest</a> on this topic.</dd>

<dt>How can I add localization for my native language?</dt>
<dd>Translations for ChangeLanguage are managed on <a href="https://www.transifex.com">Transifex</a>.
    To add your language, simply register yourself for the <a href="https://www.transifex.com/terminal42/contao-changelanguage/dashboard/">ChangeLanguage project</a>.
    and add the new language. New localizations will be published with each new release by default.</a></dd>

</dl>


## Known limitations

- The option in Contao 3.5 to disable the use of page aliases
  (`tl_settings.disableAlias`) is no longer supported.
