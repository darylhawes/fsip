### Frequently Asked Questions
<h5>General</h5>
<ul>
	<li><a href="#what-is">What is FSIP?</a></li>
	<li><a href="#who-uses">Who uses FSIP?</a></li>
	<li><a href="#why-choose">Why choose FSIP?</a></li>
	<li><a href="#how-much">How much does FSIP cost?</a></li>
	<li><a href="#why-web-site">Why do I need my own Web site?</a></li>
	<li><a href="#how-difficult">How difficult is it to install? Can I do it myself?</a></li>
	<li><a href="#how-many-images">How many images can my library contain?</a></li>
</ul>

<h5>Compatibility</h5>

<ul>
	<li><a href="#image-types">What kinds of images are supported?</a></li>
	<li><a href="#how-compatible">How do I know if my Web host is compatible?</a></li>
	<li><a href="#which-rbdms">Which supported database server should I use?</a></li>
	<li><a href="#alkaline-on-windows">Does Alkaline work on Windows&#0174; servers?</a></li>
	<li><a href="#do-i-need-adobe-flash">Do I need Adobe&#0174; Flash installed?</a></li>
	<li><a href="#file-size-limit-uploads">Why is there a file size limit on some types of uploads?</a></li>
	<li><a href="#gd-plus-im">Why do I need GD if ImageMagick is installed?</a></li>
</ul>


<h5>Legal</h5>

<ul>
	<li><a href="#eula">Does FSIP have a End User Licensing Agreement (EULA)?</a></li>
	<li><a href="#privacy">What&#8217;s your privacy policy?</a></li>
</ul>

<hr />

##### General
<h6 id="what-is">What is FSIP?</h6>

<p>FSIP is an application to upload, organize, and showcase images on the Internet. It's intended to be installed on your Web site--just like [WordPress](http://www.wordpress.org/). Most Web hosting providers are compatible with FSIP, and we have a [compatibility suite](/compatibility/) to automatically check for you. Installing and using FSIP does not require a Ph.D in computer science; if you know some HTML and have experience transferring files via FTP, you already know everything you need to hit the ground running.</p>

<h6 id="who-uses">Who uses FSIP?</h6>

<p>FSIP is intended for any person or organization that has a large collection of images. While originally built for photographers, the application can be used by illustrators, painters, and archivists as well as most industries from realtors to universities.</p>

<h6 id="why-choose">Why choose FSIP?</h6>

<p>Unlike most content management systems, FSIP was built for images, not text. As such, it works "out of the box" and requires no modification to efficiently handle large image collections. The project was developed in 2010 as Alkaline and then open sourced, under the MIT license, in 2012 and forked to FSIP in 2013. It has a larger, more robust toolkit of features for images than most other CMS tools.</p>

<h6 id="how-much">How much does FSIP cost?</h6>

<p>FSIP is open source software and <a href="https://github.com/darylhawes/fsip"> available for download for free on GitHub</a>.</p>

<h6 id="#how-difficult">How difficult is it to install? Can I do it myself?</h6>

<p>Installing FSIP is about as difficult as installing a CMS such as <a href="http://www.wordpress.org/">WordPress</a>.</p>


<h6 id="how-many-images">How many images can my library contain?</h6>

<p>FSIP is designed to handle millions of images. (Just make sure you have enough disk space!)</p>

<hr />

##### Compatibility

<h6 id="image-types">What kinds of images are supported?</h6>

FSIP supports any image kind (whether it be a photo, illustration, etc.) and the most common image types including both raster and vector images. Supported file formats include: JPG, GIF, and PNG as well as EPS and SVG where ImageMagick is available. 

<h6 id="how-compatible">How do I know if my Web host is compatible?</h6>

<p>Use the included <a href="/compatibility/">compatibility suite</a> in your installation which will give you compatibility information.</p>

<h6 id="fsip-on-windows">Does FSIP work on Windows&#0174; servers?</h6>

<p>FSIP has been tested and is compatible with Windows&#0174; Server 2008. Be sure to use the <a href="/compatibility/">compatibility suite</a> to make sure you meet all the other requirements. You might want to also install the URL Rewrite 2 module.</p>

<h6 id="do-i-need-adobe-flash">Do I need Adobe&#0174; Flash installed?</h6>

<p>No, neither users nor visitors require Adobe&#0174; Flash or any other proprietary browser plug-in to use FSIP. Slideshows and real-time functionality are powered by JavaScript which should function in any modern Web browser.</p>

<h6 id="file-size-limit-uploads">Why is there a file size limit on some types of uploads?</h6>

<p>Most Web servers are configured to reject uploads above a certain file size. In many cases, you can change your Web server&#8217;s PHP configuration to increase the limit. If you can&#8217;t, you can still upload larger files using FTP, SFTP, or WebDAV directly into your shoebox folder.</p>

<h6 id="gd-plus-im">Why do I need GD if ImageMagick is installed?</h6>

<p>Certain ImageMagick versions contain a software bug that affects image size calculation, in which case FSIP can fallback on GD.</p>

<hr />


##### Legal

<h6 id="privacy">What&#8217;s your privacy policy?</h6>

<p>We abide by the <a href="http://www.budinltd.com/privacy/">privacy policy</a> issued by our parent company, Budin Ltd.</p>