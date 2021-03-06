### How to Backup FSIP

You'll need access to both your Web and database data to perform a complete backup of FSIP.

1. Download your Web directory.
2. Download your database contents.

##### Download your Web directory

Using an FTP, SFTP, WebDAV or other client, download your entire FSIP installation. For quicker backups, the essential files are `config.php`, `config.json`, `/extensions`, `/includes`, and `/themes`. Optionally, you may or may not want to backup `/images` (likely the largest directory) depending on whether you have copies of your images elsewhere.

<div class="note">
	<strong>Tip</strong>
	<p>If you have root access, compress your directory into an archive, and then download that archive. The download will be much faster.</p>

	<p class="nm"><code>tar -c <em>your_directory</em> --exclude "images" > <em>your_archive</em>.tar</code></p>
</div>

##### Download your database contents

First, determine which database type you&#8217;re using by choosing **Settings**. Then follow the directions below for your database type.

###### MySQL

Go to your Web host&#8217;s control panel and look for an automated method to perform backups. You can also use [phpMyAdmin](http://www.phpmyadmin.net/), which is commonly offered, to download your database.

###### PostgreSQL

Go to your Web host&#8217;s control panel and look for an automated method to perform backups. You can also use [pgAdmin](http://pgadmin.org/), which is commonly offered, to download your database.

###### SQLite

Download the file `/db/fsip_??????.db`. That&#8217;s it.