# FSIP

Free Stock Image Project

Drag-and-drop uploading, automatic metadata retrieval, and deceptively simple theming are just the beginning. Based on Alkaline.

Alkaline was developed by [Budin Ltd.](http://www.budinltd.com/) as a commercial PHP content management system from February 2011 to August 2012. It has since been discontinued, and is now available as open source software under the MIT license. A license is no longer required to use the application. You can learn more about Alkaline at [alkalineapp.com](http://www.alkalineapp.com/).

## Features & Documentation

See docs folder in your installation.

## Requirements

- PHP 5.2+ with modules: GD, JSON, PDO (with appropriate database driver), SimpleXML
- A MySQL 5.x or PostgreSQL 8.x database, or SQLite 3.x support

Unsure if your site is compatible? Use the [compatibility suite](/compatibility/) to check your configuration automatically. The site admin installing FSIP should also have rudimentary knowledge of HTML and previous experience transferring files via FTP. Also, additional RAM may need to be allocated to PHP in order for FSIP to process very large images; your Web hosting provider should be able to do this for you if you're unable to do so on your own.


## Installation

1. Download FSIP <a href="https://github.com/darylhawes/fsip">available for free on GitHub</a>.

2. Unpack the archive (usually by double-clicking on it).
3. Use an FTP application to move the contents of the folder `fsip/` from your computer to your Web site.
	- Set the permissions on the folders: `cache/`, `db/`, `images/`, and `shoebox/` to `0777` (read, write, and execute), also set the same permissions to the file `config.json`
	- Delete the `update/` folder
	- *Recommended:* Remove the included themes and extesions by deleting the folders within `extensions/` and `themes/` (except `themes/p1/`)
4. Once you're done uploading the files visit the `install/` directory of your Web site where you installed Alkaline to complete your installation.

## Support

No support is offered for this product. "Swim at your own risk."

## Contributing

Alkaline is no longer undergoing active development. At this time, no new features will be added, but I encourage users to submit reasonable pull requests that improve its compatibility or reliability.

## License

The MIT License (MIT)  
Copyright (c) 2010-2012 Budin Ltd.
 
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 
The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
