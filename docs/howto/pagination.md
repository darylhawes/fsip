### Change Pagination of Images

FSIP lets you paginate images however you choose regardless of the theme you're using. 
Let's take a look at the `page()` method of the `Find` classes, the method works the same for images.

Let's open `index.php`, you should see a line like this:

	$image_ids->page(null, 12, 1);

This tells the `Find` object to automatically determine the page (`null`), 
display 12 images per page (`12`), and to display 1 image on the first page (`1`). 
You can change these values and save/upload the file to observe the changes to your Web site. Here are a few examples:

##### Images

Auto-determine the page number with 25 images on every page:

	$image_ids->page(null, 25);
	
Show page one with all the images (here, `0` is infinity):

	$image_ids->page(1, 0);