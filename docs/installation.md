
# ChangeLanguage v3

1. [Installation](installation.md)
2. [Linking pages](pages.md)
3. [Frontend module](frontend-module.md)
4. [Insert tags](inserttags.md)
5. [Tips & FAQ](tips-faq.md)


## Installation

Minimum requirements:

 - Contao 3.5 or Contao 4.1
 - Haste 4.13
 - MultiColumnWizard 3.3


### Install using Composer (recommended)

[Composer][1] is our recommend way to install Contao modules.
The Contao plugin will take care of copying the files to the correct place.

    $ composer.phar require terminal42/contao-changelanguage ^3.0


### Install from Extension Repository (in Contao 3.5)

ChangeLanguage v3 can also be installed from the old Contao Extension Repository.
Follow the Contao manual on [how to install extensions][2].


### Manual installation

Download [`terminal42/contao-changelanguage`][3] and copy the folder to your Contao
installation in `system/modules/changelanguage`. You must also download and
install [`codefog/contao-haste`][4] and [`menatwork/contao-multicolumnwizard`][5].



[1]: https://getcomposer.org
[2]: https://docs.contao.org/books/manual/3.5/en/05-system-administration/extensions.html
[3]: https://github.com/terminal42/contao-changelanguage/archive/master.zip
[4]: https://github.com/codefog/contao-haste/archive/master.zip
[5]: https://github.com/menatwork/MultiColumnWizard/archive/master.zip
