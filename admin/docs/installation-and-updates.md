### Installation and Updates

##### Installation

Experienced individuals, such as Web developers, should be able to install FSIP pretty quickly. 
Novices should allocate an hour or more for the process.

1. Take a deep breath.
2. Download FSIP from <a href="https://github.com/darylhawes/fsip"> GitHub</a>.
3. Unpack the archive by double-clicking on it.
4. Use an FTP application to move the contents of the folder `/fsip/` from your computer to your Web site.
	- Set the permissions on the folders: `/cache/`, `/data/db/`, `/data/images/`, and `/shoebox/` to 777 
	(read, write, and execute)
	- Delete the `/update/` folder
5. Once your done uploading the files, using your Web browser, visit the `/install/` directory 
of your Web site where you installed FSIP.


From here, FSIP will ask you to supply information to complete the installation. Afterwards, you may want:

- **To enable vector support,** choose Dashboard > Configuration and enable ImageMagick if available.
- **To enable smart URLs,** [read our quick how-to guide](/docs/howto/enable-url-rewriting.md).
- **To load the internal geo database,** choose Dashboard > Maintenance and click "Rebuild geographic library".

You might want to delete the `/admin/install.php` file once you're happy with your new FSIP installation.

###### Choosing the database type

FSIP supports [MySQL](http://www.mysql.com/), [PostgreSQL](http://www.postgresql.org/), and [SQLite](http://www.sqlite.org/), 
but you should only use the database types that were indicated satisfactory in the compatibility suite. 
(For instance, having access to a PostgreSQL database does not mean you have the PDO driver necessary for FSIP to utilize it.) 
When it's available we recommend choosing MySQL.

###### A quick note on security

Your Web server may allow for more restrictive file and folder permissions than those indicated above. 
FSIP only checks for (and warns of) incorrect permissions during installation. 
If you so desire and your Web server allows, you may make these permissions more restrictive once installed.

If you're using SQLite, you should ensure wherever you located your database file (`fsip`) 
that it cannot be accessed or downloaded from the outside world. You should move this file to at 
least one level below the public HTTP/public_html directory.

##### Updates

The method of installing updates varies from update to update. Most of the time, 
it simply requires replacing files. Be careful not to overwrite your installation's 
themes and extensions, or your `config.php` file unless specifically directed to do so. 
Refer to the update's documentation for instructions.