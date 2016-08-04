
# ChangeLanguage v3

1. [Installation](installation.md)
2. [Linking pages](pages.md)
3. [Frontend module](frontend-module.md)
4. [Insert tags](inserttags.md)
5. [Tips & FAQ](tips-faq.md)



## Tips & Tricks

<dl>

<dt>Do not use flags for the language selection</dt>
<dd>Using flag icons for languages is a very bad idea. Flags represent
    <u>countries</u> and not <u>languages</u>. Not every language belongs to
    only one country! Would you take the American flag, the British flag or
    the Australian flag for english? What flags do you use for <i>Chinese
    Simplified</i> and <i>Chinese Traditional</i> (two different forms of writing
    for the same chinese language)? Don't do it…</dd>

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


## Frequently Asked Questions

<dl>

<dt>Where can I get support for ChangeLanguage</dt>
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
