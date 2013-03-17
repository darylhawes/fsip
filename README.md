# FSIP

Free Stock Image Project

Drag-and-drop uploading, automatic metadata retrieval, and deceptively simple theming are just the beginning. Based on Alkaline.

Alkaline was developed by [Budin Ltd.](http://www.budinltd.com/) as a commercial PHP content management system from February 2011 to August 2012. It has since been discontinued, and is now available as open source software under the MIT license. A license is no longer required to use the application. You can learn more about Alkaline at [alkalineapp.com](http://www.alkalineapp.com/).

## Help Files

### Site Admin and Using FSIP
- [FAQ](docs/faq.md.html)
- [Simple Customization](docs/simple-customization.md.html)
- [Installation and Updates](docs/installation-and-updates.md.html)
- [Managing Images](docs/managing-images.md.html)
- [Troubleshooting](docs/troubleshooting.md.html)
- [Uploading Images](docs/uploading-images.md.html)
- [Using Features (Rights, Sets, Tags, Pages, Comments)](docs/using-features.md.html)
- [Backup](docs/backup.md.html)
- [Versions, Revering and Recovering](docs/versions-reverting-recovering.md.html)


### Extending
- [Extensions](docs/extensions.md.html)
- [Themes](docs/themes.md.html)

### About / More info
- [Metadata](docs/metadata.md.html)
- [How to: Enable URL Rewriting](docs/howto-enable-url-rewriting.md.html)
- [How to: Inscrease Reach](docs/howto-increase-reach.md.html)
- [How to: Increase Upload Size Limit](docs/howto-increase-upload-size-limit.md.html)
- [How to: Pagination](docs/howto-pagination.md.html)
- [How to: Reset Admin Password](docs/howto-reset-admin-password.md.html)
- [How to: Sell Photos](docs/howto-sell-photos.md.html)
- [How to: Setup Sphinx Search](docs/howto-setup-sphinx-search.md.html)


## Requirements

- PHP 5.2+ with modules: GD, JSON, PDO (with appropriate database driver), SimpleXML
- A MySQL 5.x or PostgreSQL 8.x database, or SQLite 3.x support

Unsure if your site is compatible? Use the [compatibility suite](/cs.php/) to check your configuration automatically. The site admin installing FSIP should also have rudimentary knowledge of HTML and previous experience transferring files via FTP. Also, additional RAM may need to be allocated to PHP in order for FSIP to process very large images; your Web hosting provider should be able to do this for you if you're unable to do so on your own.


## Installation

1. Download FSIP <a href="https://github.com/darylhawes/fsip">available for free on GitHub</a>.

2. Unpack the archive (usually by double-clicking on it).
3. Use an FTP application to move the contents of the folder `fsip/` from your computer to your Web site.
	- Set the permissions on the folders: `cache/`, `data/db/`, `data/images/`, and `shoebox/` to `0777` (read, write, and execute), also set the same permissions to the file `config.json`
	- Delete the `update/` folder
	- *Recommended:* Remove the included themes and extesions by deleting the folders within `extensions/` and `themes/` (except `themes/p1/`)
4. Once you're done uploading the files visit the `install/` directory of your Web site where you installed Alkaline to complete your installation.

## Support

No support is offered for this open source product. "Swim at your own risk."

## Contributing

FSIP is undergoing development by Daryl Hawes and you are welcome to contribute bug reports, patches and comments via [git.com](https://github.com/darylhawes/fsip)

The original "Alkaline" project  is no longer undergoing active development. 

### Credits

##### Design elements

- "Moi" by [IconDock](http://icondock.com/), all rights reserved
- "Pictos" by [Drew Wilson](http://www.drewwilson.com/), all rights reserved
- "Rocky" by [IconDock](http://icondock.com/), all rights reserved
- "Dark Metal Grid" by [Orman Clark](http://www.ormanclark.com/), public domain

##### Open source components

Modifications published by Budin Ltd. to these components, where allowed by their licenses, are copyrighted.

- [ExplorerCanvas](http://code.google.com/p/explorercanvas/), Apache 2 license
- [Flot](http://code.google.com/p/flot/), MIT license
- [Formalize](http://formalize.me/), MIT/GPL license
- [GeoNames](http://www.geonames.org/), CC-Attribution license
- [jQuery](http://jquery.com/), MIT/GPL2 license
- [jQuery AjaxQ](https://code.google.com/p/jquery-ajaxq/), MIT license
- [jQuery Caret Range](http://plugins.jquery.com/project/caret-range), MIT/GPL license
- [jQuery HTML5 Upload](http://code.google.com/p/jquery-html5-upload/), MIT license
- [jQuery JSON](http://code.google.com/p/jquery-json/), MIT license
- [jQuery UI](http://jqueryui.com/), MIT/GPL2 license
- [PEAR](http://pear.php.net/), PHP license
- [PEAR Cache_Lite](http://pear.php.net/package/Cache_Lite), LGPL license
- [PEAR Text_Diff](http://pear.php.net/package/Text_Diff), LGPL license
- [PHPThumb](http://phpthumb.gxdlabs.com/), MIT license
- [TipTip](http://code.drewwilson.com/entry/tiptip-jquery-plugin), MIT/GPL license


## License

The MIT License (MIT)  
Copyright (c) 2010-2012 Budin Ltd.
 
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 
The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.