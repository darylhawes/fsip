/*!
 * FSIP based on Alkaline
 * Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
 * http://www.alkalineapp.com/
 */

var BASE = $('meta[name="base"]').attr('content');
var FOLDER_PREFIX = $('meta[name="folder_prefix"]').attr('content');
var PERMISSIONS = $('meta[name="permissions"]').attr('content');

var ADMIN = FOLDER_PREFIX + 'admin/';
var IMAGES = FOLDER_PREFIX + 'images/';
var JS = FOLDER_PREFIX + 'js/';
var WATERMARKS = FOLDER_PREFIX + 'watermarks/';

var slideshow;
var slideshow_image;
var slideshow_image_prev;
var slideshow_working = 0;
var slideshow_play = 1;

$.expr[':'].containsIgnoreCase = function(n,i,m){
	return jQuery(n).text().toUpperCase().indexOf(m[3].toUpperCase())>=0;
};

function slideshow_first(){
	if(slideshow_working == 1){ return; }
	slideshow_image = slideshow_images.first('li');
	slideshow_update();
}

function slideshow_last(){
	if(slideshow_working == 1){ return; }
	slideshow_image = slideshow_images.last('li');
	slideshow_update();
}

function slideshow_next(){
	if(slideshow_working == 1){ return; }
	slideshow_image_next = slideshow_image.next();
	if(slideshow_image_next.length == 0){
		slideshow_first();
	}
	else{
		slideshow_image = slideshow_image_next;
		slideshow_update();
	}
}

function slideshow_prev(){
	if(slideshow_working == 1){ return; }
	slideshow_image_next = slideshow_image.prev();
	if(slideshow_image_next.length == 0){
		slideshow_last();
	}
	else{
		slideshow_image = slideshow_image_next;
		slideshow_update();
	}
}

function reset(){
	if(slideshow_image){
		image = slideshow_image.find('img');
		height = image.innerHeight();
	    width = image.innerWidth();
		doc_height = $(document).height();
		if(doc_height > 700){
			if(width > height){
				padding = ((640 - height) / 2) + ((doc_height - 700) / 2);
			}
			else{
				padding = ((doc_height - 700) / 2);
			}
		}
		else{
			if(width > height){
				padding = ((640 - height) / 2);
			}
			else{
				padding = 0;
			}
		}
		slideshow_image.css('padding-top', padding + 'px');
	}
}

function slideshow_update(){
	slideshow_working = 1;
	
	slideshow.fadeOut(100, function(){ uncomment(slideshow_image); $('ul#slideshow li').hide(); slideshow_image.show(); }).delay(0).hide(100, function(){ reset(); }).fadeIn(100, function(){ slideshow_image_prev = slideshow_image; slideshow_image_next = slideshow_image.next(); slideshow_image_next.hide(0, function(){ uncomment(slideshow_image_next); } ); slideshow_working = 0; });
}

function slideshow_play_now(){
	if(slideshow_play == 1){
		setTimeout("slideshow_play_next()", 3000);
	}
}

function slideshow_play_next(){
	if(slideshow_play == 1){
		slideshow_play_now();
		slideshow_next();
	}
}

function slideshow_pause(){
	if(slideshow_play == 1){
		slideshow_play = 0;
	}
	else{
		slideshow_play = 1;
		slideshow_play_next();
	}
}

function slideshow_play(){
	slideshow_play = 1;
	slideshow_play_next();
}

function slideshow_stop(){
	slideshow_play = 0;
}

function uncomment(slideshow_image){
	slideshow_image_html = slideshow_image.html();
	slideshow_image_html = slideshow_image_html.replace(/^\<\!-- /gi, '');
	slideshow_image_html = slideshow_image_html.replace(/ --\>$/gi, '');
	slideshow_image.html(slideshow_image_html);
}

var shift = 0;
var task;
var page;
var progress;
var progress_step;

// SHOEBOX

function shortNum(num){
	app = '';
	if(num >= 1000){
		num /= 1000;
		app = 'k';
		if(num >= 1000){
			num /= 1000;
			app = 'm';
			if(num >= 1000){
				num /= 1000;
				app = 'b';
			}
		}
	}
	num = num.toString();
	num = num.slice(0, 4);
	if(num.charAt(3) == '.'){
		num = num.slice(0, 3);
	}
	num += app;
	
	return num;
}

function static_html(div_id, image_id){
	var block = $('#' + div_id).html();
	image_id = image_id.toString();
	static_html_regex = new RegExp('--', 'gim');
	block = block.replace(static_html_regex, '-' + image_id + '-');
	return block;
}

function now(){
	var time = new Date();
	var hour = time.getHours();
	var minute = time.getMinutes();
	var second = time.getSeconds();
	var temp = "" + ((hour > 12) ? hour - 12 : hour);
	if(hour == 0){ temp = "12"; }
	temp += ((minute < 10) ? ":0" : ":") + minute;
	temp += ((second < 10) ? ":0" : ":") + second;
	temp += (hour >= 12) ? " P.M." : " A.M.";
	return temp;
}

var now = now();

function empty(mixed_var){
    var key;
    
    if (mixed_var === "" ||
        mixed_var === 0 ||
        mixed_var === "0" ||
        mixed_var === null ||
        mixed_var === false ||
        typeof mixed_var === 'undefined'
    ){
        return true;
    }
 
    if (typeof mixed_var == 'object') {
        for (key in mixed_var) {
            return false;
        }
        return true;
    }
 
    return false;
}

function imageArray(task, input){
	$('#progress').slideDown();
	image_count = input.length;
	progress = 0;
	progress_step = 100 / input.length;
	if(page == 'Shoebox'){
		if(empty(input)){
			updateProgress(100); return;
		}
		for(item in input){
			$.ajaxq("default", {
				type: "POST",
			    url: BASE + ADMIN + "tasks/" + task + ".php",
				data: { image_file: input[item] },
			    cache: false,
			    success: function(data)
			    {
					if(!empty(data)){
						appendImage(data);
					}
					updateProgress();
			    }
			});
		}
	}
	else if(page == 'Maintenance'){
		for(item in input){
			$.ajaxq("default", {
				type: "POST",
			    url: BASE + ADMIN + "tasks/" + task + ".php",
				data: { image_id: input[item] },
			    cache: false,
			    success: function(data)
			    {
			        updateMaintProgress();
			    }
			});
		}
	}
	else{
		for(item in input){
			$.ajaxq("default", {
				type: "POST",
			    url: BASE + ADMIN + "tasks/" + task + ".php",
				data: { image_id: input[item] },
			    cache: false,
			    success: function(data)
			    {
			        updateMaintProgress(false);
			    }
			});
		}
	}
}

function updateMaintProgress(redirect){
	if(!empty(progress_step)){
		progress += progress_step;
	}
	progress_int = parseInt(progress);
	$("#progress").progressbar({ value: progress_int });
	if(progress > 99.99999){
		if(redirect === false){ $("#progress").slideUp(); return; }
		$.ajaxq("default", {
			type: "POST",
		    url: BASE + ADMIN + "tasks/add-notification.php",
			data: { message: "Your maintenance task is complete.", type: "success" },
		    cache: false,
		    success: function(data)
		    {
				window.location = BASE + ADMIN;
		    }
		});
	}
}

function focusTags(that){
	var container = $(that).closest('.image_tag_container');
	tags = container.children('.image_tags_load').text();
	
	if(empty(tags)){
		tags = new Array();
	}
	else{
		tags = $.evalJSON(tags);
	}
}

function updateTags(that){
	var container = $(that).closest('.image_tag_container');		
	var tags_html = tags.map(function(item) { return '<img src="' + BASE + ADMIN + 'images/icons/tag.png" alt="" /> <a class="tag">' + item + '</a>'; });
	container.children('.image_tags_input').val($.toJSON(tags));
	container.children('.image_tags_load').text($.toJSON(tags));
	container.children('.image_tags').html(tags_html.join(', '));
}

function updateAllTags(){
	$('.image_tag_container').each(function(index){
		focusTags(this);
	
		$(this).find('.image_tag_add').click(function(event){
			focusTags(this);
			var tag = $(this).siblings('.image_tag').val();
			tag = jQuery.trim(tag);
			if((tags.indexOf(tag) == -1) && tag != ''){
				tags.push(tag);
				updateTags(this);
			}
			$(this).siblings('.image_tag').val('');
			event.preventDefault();
		});

		$(this).find('.image_tag').keydown(function(event){
			focusTags(this);
			if(event.keyCode == '13'){
				var tag = $(this).val();
				tag = jQuery.trim(tag);
				if((tags.indexOf(tag) == -1) && tag != ''){
					tags.push(tag);
					updateTags(this);
				}
				$(this).val('');
				event.preventDefault();
			}
		});
	
		$(this).find('a.tag').live('click', function(){
			focusTags(this);
			var tag = $(this).contents().text();
			tag = jQuery.trim(tag);
			var index = tags.lastIndexOf(tag);
			if(index > -1){
				tags.splice(index, 1);
				$(this).fadeOut();
			}
			updateTags(this);
			event.preventDefault();
		});
	
		tags = $(this).find('.image_tags_load').text();
	
		if(empty(tags)){
			tags = new Array();
		}
		else{
			tags = $.evalJSON(tags);
		}
	
		updateTags(this);
	});
}

function appendImage(image){
	var image = $.evalJSON(image);
	if(empty(image.image_id)){
		addNote('You have an invalid file in your shoebox folder.', 'error');
		return;
	}
	image_ids = $("#shoebox_image_ids").val();
	image_ids += image.image_id + ',';
	$("#shoebox_image_ids").attr("value", image_ids);
	var privacy = static_html('privacy_html', image.image_id);
	var rights = static_html('rights_html', image.image_id);
	image.image_tags = $.toJSON(image.image_tags);
	if(empty(image.image_geo_lat) && empty(image.image_geo_long)){
		var geo = '';
	}
	else{
		var geo = '<br /><img src="' + BASE + ADMIN + 'images/icons/geo.png" alt="" /> ' + image.image_geo_lat + ', ' + image.image_geo_long;
	}
	$("#shoebox_images").append('<div id="image-' + image.image_id + '" class="id span-24 last"><div class="span-15 append-1"><img src="' + image.image_src_admin + '" alt="" /><p><input type="text" id="image-' + image.image_id + '-title" name="image-' + image.image_id + '-title" value="' + image.image_title + '" class="title bottom-border" placeholder="Title" /><textarea id="image-' + image.image_id + '-description-raw" name="image-' + image.image_id + '-description-raw" placeholder="Description">' + image.image_description_raw + '</textarea></p></div><div class="span-8 last"><div class="image_tag_container"><label for="image_tag">Tags:</label><br /><input type="text" id="image_tag" name="image_tag" class="image_tag" style="width: 40%;" /><input type="submit" id="image_tag_add" class="image_tag_add" value="Add" /><br /><div id="image_tags" class="image_tags"></div><div id="image_tags_load" class="image_tags_load none">' + image.image_tags + '</div><input type="hidden" name="image-' + image.image_id + '-tags_input" id="image_tags_input" class="image_tags_input" value="" /></div><br /><p><label for="">Location:</label><br /><input type="text" id="image-' + image.image_id + '-geo" name="image-' + image.image_id + '-geo" class="image_geo get_location_result l" value="' + image.image_geo + '" />&#0160; <a href="#get_location" class="get_location"><img src="' + BASE + ADMIN + 'images/icons/location.png" alt="" style="vertical-align: middle;" /></a>' + geo + '</p><p><label for="">Publish date:</label><br /><input type="text" id="image-' + image.image_id + '-published" name="image-' + image.image_id + '-published" value="' + image.image_published + '" placeholder="Unpublished" /></p><p><label for="">Privacy level:</label><br />' + privacy + '</p><p><label for="">Rights set:</label><br />' + rights + '</p><hr /><table><tr><td class="right" style="width: 5%"><input type="checkbox" id="image-' + image.image_id + '-delete" name="image-' + image.image_id + '-delete" value="delete" /></td><td><strong><label for="image-' + image.image_id + '-delete">Delete this image.</label></strong></td></tr></table></div></div><hr />');
	updateAllTags();
}

function updateProgress(val){
	progress += progress_step;
	if(!empty(val)){ progress = val; }
	progress_int = parseInt(progress);
	$("#progress").progressbar({ value: progress_int });
	if(progress > 99.99999){
		$("#progress").slideUp(1000);
		$("#shoebox_add").delay(1000).removeAttr("disabled");
	}
}

function executeTask(task, data){
	if(empty(data)){
		$.ajax({
			url: BASE + ADMIN + "tasks/" + task + ".php",
			cache: false,
			error: function(data){ alert(data); },
			dataType: "json",
			success: function(data){
				if(empty(data)){
					progress = 100; updateMaintProgress();
				}
				else{
					imageArray(task, data);
				}
			}
		});
	}
	else{
		imageArray(task, data);
	}
	
	$("#tasks").slideUp(500);
	$("#note").slideUp(500);
	$("#progress").delay(500).slideDown(500);
	$("#progress").progressbar({ value: 0 });
}

function setSort(set){
	images = new Array();
	image_id_regex = new RegExp('image-', 'gim');
	set.children('img').each(function(){
		id = $(this).attr('id');
		id = id.replace(image_id_regex, '');
		images.push(id);
	});
	set.siblings('#set_images').val(images.join(', '));
};

function addNote(note, type){
	if(empty(type)){ type = 'notice'; }
	clearNotes();
	html = $('<p class="' + type + ' js_gen_error none">' + note + '</p>');
	$('#content').prepend(html);
	html.slideDown('fast');
}

function clearNotes(){
	$('.js_gen_error').css('position', 'absolute').css('z-index', '100').fadeOut();
}

window.launchQuickpic = function(context){
	var start = new Date();
	setTimeout(function() {
		if(new Date() - start > 2000){
			return;
		}
		window.location = 'http://www.cliqcliq.com/quickpic/install/';
	}, 1000);
	
	var getParams = ['action=' + BASE + ADMIN + 'upload.php',
		'continue=' + BASE + ADMIN + 'upload.php',
		'contact=0',
		'images=1+',
		'flickr=0',
		'context=' + context,
		'passcontext=1',
		'video=0',
		'edit=1',
		'v=1.2'];
	
	window.location = 'vquickpic://?' + getParams.join('&');
};

function buttonize(){
	$("button").each(function(){
		if($(this).hasClass('buttonized')){
			
		}
		else{
			button = $(this).text();
			disabled = $(this).attr('disabled');
			button = button.replace(/(\w+).*/, "$1").toLowerCase();
			width = '12';
			if(empty(button)){ button = 'act'; width = '22'; }
			if(disabled != 'disabled'){
				$(this).prepend('<img src="' + BASE + ADMIN + 'images/actions/' + button + '.png" alt="" height="12" width="' + width + '" /> ');
			}
			else{
				$(this).prepend('<img src="' + BASE + ADMIN + 'images/actions/' + button + '.png" alt="" height="12" width="' + width + '" style="opacity:.5;" /> ');
			}
			$(this).addClass('buttonized');
		}
	});
}

function findID(string){
	string = string.replace(/[^0-9]/gi, '');
	return parseInt(string);
}

function updateFluid(){
	$.get(BASE + ADMIN + 'tasks/update-fluid.php', null, function(data){
		data = $.evalJSON(data);
		if(data.dockBadge != 0){
			window.fluid.dockBadge = data.dockBadge;
		}
		else{
			window.fluid.dockBadge = null;
		}
		for (var i = data.showGrowlNotification.length - 1; i >= 0; i--){
			window.fluid.showGrowlNotification({
				title: data.showGrowlNotification[i].title,
				description: data.showGrowlNotification[i].description
			});
		};
	});
}

// AUTOSAVE

function autosave_save(){
	$('.autosave_delete').click(autosave_delete);
	autosave_title_new = $('input.title').slice(0,1).val();
	autosave_text_new = $('textarea').slice(0,1).val();
	if(autosave_text_new == ''){ return; }
	if(typeof autosave_title == 'undefined'){
		autosave_title = autosave_title_new;
		autosave_text = autosave_text_new;
		return;
	}
	else if((autosave_title == autosave_title_new) && (autosave_text = autosave_text_new)){
		return;
	}
	autosave_title = autosave_title_new;
	autosave_text = autosave_text_new;
	document.cookie = 'autosave_title=' + encodeURIComponent(autosave_title) + '; max-age=2592000'
	document.cookie = 'autosave_text=' + encodeURIComponent(autosave_text) + '; max-age=2592000'
	url = location.href;
	pos = url.indexOf('#');
	if(pos != -1){
		url = url.substring(0, pos);
	}
	document.cookie = 'autosave_uri=' + encodeURIComponent(url) + '; max-age=2592000'
}

function autosave_delete(){
	document.cookie = 'autosave_uri=; max-age=0';
	document.cookie = 'autosave_title=; max-age=0';
	document.cookie = 'autosave_text=; max-age=0';
	clearNotes();
}

function autosave_exists(){
	cookies = getCookies();
	if(cookies.hasOwnProperty('autosave_uri') && cookies.hasOwnProperty('autosave_text')){
		addNote('You have an unsaved item. You may <a href="' + cookies.autosave_uri + '#recover">recover the item</a> or <a href="" id="autosave_delete">delete the unsaved changes</a>.', 'error');
	}
	else{
		autosave_delete();
	}
}

function autosave_recover(){
	url = location.href;
	if(url.search(/\#recover/) > 0){
		cookies = getCookies();
		
		if(cookies.hasOwnProperty('autosave_title')){
			title = $('input.title').slice(0,1).val(cookies.autosave_title);
		}
		if(cookies.hasOwnProperty('autosave_text')){
			text = $('textarea').slice(0,1).val(cookies.autosave_text);
		}
	}
}

function autosave(){
	autosave_recover();
	setInterval(autosave_save, 10000);
}

function getCookies(){
	cookies = {};
	all = document.cookie;
	if(all === '')
		return cookies;
	list = all.split("; ");
	for (var i=0; i < list.length; i++) {
		cookie = list[i];
		pos = cookie.indexOf("=");
		name = cookie.substring(0, pos);
		value = cookie.substring(pos+1);
		value = decodeURIComponent(value);
		cookies[name] = value;
	};
	return cookies;
}

$(document).ready(function(){
	// NAVIGATION
	fold = $('#navigation ul li').height();
	fold += 7;
	$('#navigation ul ul').css('position', 'absolute').css('top', fold + 'px').hide();
	$('#navigation ul li').hover(function() {
		if($(this).find('a').hasClass('selected')){
			bgcolor = "#fff";
		}
		else{
			bgcolor = "";
		}
		$(this).find('ul').css('background-color', bgcolor).show();
	}, function() {
		$(this).find('ul').hide();
	});
	
	$('#navigation ul ul').hover(function(){
		parent = $(this).closest('ul').siblings('a');
		if(!parent.hasClass('selected')){
			parent.addClass('blue');
		}
	}, function(){
		parent = $(this).closest('ul').siblings('a');
		if(!parent.hasClass('selected')){
			parent.removeClass('blue');
		}
	});
	
	// PERMISSIONS
	if(!empty(PERMISSIONS)){
		perms = PERMISSIONS.split(', ');
		$('#navigation ul').find('li').each(function(){
			id = $(this).attr('id');
			prefix = id.slice(0, 4);
			if(prefix == 'tab_'){
				if(id == 'tab_dashboard'){ return; }
				is_perm = perms.some(function(item){ return 'tab_' + item == id; });
				if(is_perm === false){
					$(this).hide();
				}
			}
			else if(prefix == 'sub_'){
				if(id == 'sub_preferences'){ return; }
				is_perm = perms.some(function(item){ return 'sub_' + item == id; });
				if(is_perm === false){
					$(this).hide();
				}				
			}
		});
	}
	
	// AUTOSAVE
	autosave_exists();
	$('#autosave_recover').live('click', function(){
		autosave_recover();
	});
	$('#autosave_delete').live('click', function(){
		autosave_delete();
	});
	
	// TASKS & DEFAULT PROGRESS BAR
	
	if($('#progress').length == 0){
		$('#content').prepend('<p id="progress"></p>');
		$("#progress").progressbar({ value: 0 }).hide();
	}
	
	orbit_tasks = $('#fsip_tasks').text();
	
	if(!empty(orbit_tasks)){
		orbit_tasks = $.evalJSON(orbit_tasks);
		executeTask('execute-orbit-tasks', orbit_tasks);
	}
	
	if($(document).has('ul#slideshow').length){
		$('ul#slideshow').hide();
		
		slideshow = $('ul#slideshow');
		slideshow_images = slideshow.children('li');
		slideshow_image = slideshow_images.first('li');
		slideshow_image_prev = slideshow_image;
		slideshow_update();
		slideshow_play_now();
		
		$('.slideshow_pause').click(function() {
			slideshow_pause();
		});
		
		$('.slideshow_prev').click(function() {
			slideshow_prev();
		});
		
		$('.slideshow_next').click(function() {
			slideshow_next();
		});
				
		$('.slideshow_play').click(function() {
			slideshow_play();
		});
		
		$('.slideshow_stop').click(function() {
			slideshow_stop();
		});
		
		$(document).keydown(function(event){
			if(event.keyCode == '38'){
				slideshow_play = 0;
				slideshow_first();
			}
			if(event.keyCode == '37'){
				slideshow_play = 0;
				slideshow_prev();
			}
			if(event.keyCode == '40'){
				slideshow_play = 0;
				slideshow_last();
			}
			if(event.keyCode == '39'){
				slideshow_play = 0;
				slideshow_next();
			}
			
			if(event.keyCode == '80'){
				slideshow_pause();
			}
		});
	}
	
	$(window).resize(function () { reset(); });
	
	// PRIMARY - COLORKEY
	
	$('div.colorkey_data').each(function(){
		colors = $(this).children('.colors').text();
		colors = $.evalJSON(colors);
	
		percents = $(this).children('.percents').text();
		percents = $.evalJSON(percents);
	
		canvas = $(this).siblings('canvas');
		canvas_width = canvas.attr("width");
		canvas_height = canvas.attr("height");
		canvas_var = canvas.get(0);
	
		context = canvas_var.getContext("2d");
	
		x_pos = 0;
	
		for (var i = 0; i < colors.length; i++) {
			context.fillStyle = "rgb(" + colors[i] + ")";
			width = parseInt((percents[i] * canvas_width) / 100);
			if(i == (colors.length - 1)){
				width += 1000;
			}
			context.fillRect(x_pos, 0, width, canvas_height);
			x_pos += width;
		}
	});
	
	// PRIMARY
	page = $("h1").first().text();
	page = page.replace(/New/, '');
	page = page.replace(/.*?(\w+).*/, "$1");
	
	updateAllTags();
	
	// PRIMARY - BUTTON ICONS
	
	buttonize();
	
	// TIPTIP
	
	if(jQuery().tipTip){
		$('button.tip').tipTip({defaultPosition: 'bottom', activation: 'click', keepAlive: 'true', delay: 0});
		$('a.tip').each(function(index){
			if($(this).parents('.actions').length){
				$(this).tipTip({defaultPosition: 'left', delay: 200});
			}
			else{
				$(this).tipTip({defaultPosition: 'right', delay: 200});
			}
		});
		$('a.tip').live('mouseenter mouseleave', function(){
			if($(this).parents('.actions').length){
				$(this).tipTip({defaultPosition: 'left', delay: 200});
			}
			else{
				$(this).tipTip({defaultPosition: 'right', delay: 200});
			}
		});
		$('img.tip').tipTip({defaultPosition: 'top', delay: 200});
	}
	
	// PRIMARY - ROLLOVER TABLE ROWS
	$('table tr.ro').hover(function() {
		$(this).css('background-color', '#eee');
	}, function() {
		$(this).css('background-color', '');
	});
	
	// PRIMARY - SHOW/HIDE PANELS
	$(".reveal").hide();
	
	$("a.show").toggle(
		function() {
			var original = $(this).text();
			if (original.match('Show')) {
				var re = /Show(.*)/;
				var modified = 'Hide' + original.replace(re, "$1");
			} else {
				var modified = original;
			}
			
			$(this).parent().next(".reveal").slideDown();
			$(this).siblings(".switch").html('&#9662;');
			$(this).text(modified);
			event.preventDefault();
		},
		function() {
			var new_original = $(this).text();
			if (new_original.match('Hide')) {
				var new_re = /Hide(.*)/;
				var new_modified = 'Show' + new_original.replace(new_re, "$1");
			} else {
				var new_modified = new_original;
			}
			
			$(this).parent().next(".reveal").slideUp();
			$(this).siblings(".switch").html('&#9656;');
			$(this).text(new_modified);
			event.preventDefault();
		}
	);
	
	$("input[name='install']").click(function() {
		$(this).hide();
		$(this).after('<input type="submit" name="install" value="Installing..." disabled="disabled" />');
	});
	
	// GEOLOCATION
	$('a.get_location').live('click', function(event) {
		pos = $('.get_location_set').text();
		if (pos.length > 0) {
			pos = pos.trim();
			$(this).siblings('input.get_location_result').val(pos);
		} else {
			if (navigator.geolocation) {
				geo_selector = $(this);
				geo_selector.siblings('input.get_location_result').attr('placeholder', 'Locating...');
				navigator.geolocation.getCurrentPosition(function(pos){
					latitude = pos.coords.latitude;
					longitude = pos.coords.longitude;
					$.post(BASE + ADMIN + 'tasks/set-location.php', { latitude: latitude, longitude: longitude }, function(pos){
						pos = pos.trim();
						geo_selector.siblings('input.get_location_result').attr('placeholder', '').val(pos);
					});
				});
			}
			else{
				addNote('Your Web browser does not support automatic geolocation.', 'error');
			}
		}
		event.preventDefault();
	});
	
	// FLUID APP
	
	if(window.fluid){
		updateFluid();
		setInterval(updateFluid, 30000);
	}
	
	// ADVANCED SEARCH
	/*
	if(page == 'Images'){
		$('a.advanced_link').click(function(){
			$('a.advanced').click();
		});
		url = location.href;
		task_in_url = /\#([a-z0-9_\-]+)$/i;
		task = url.match(task_in_url);
		if(!empty(task)){
			if(task[1] == 'advanced'){
				$('a.advanced').click();
			}
		}
	}
	*/
	
	if(page == 'Image'){
		autosave();
	}
	
	// PRIMARY - LABEL SELECT CHECKBOXES
	
	$("label select").click(
		function(){
			event.preventDefault();
			$(this).parent("tr").find("input:checkbox").attr("checked", "checked");
		}
	);
	
	// PRIMARY - DATEPICKER
	
	$(".date").datepicker({
		showOn: 'button',
		buttonImage: BASE + ADMIN + 'images/icons/calendar.png',
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		constrainInput: false,
		showAnim: null
	});
	
	// PRIMARY - TABLE FILTERING
	
	$("input[name='filter']").bind('keyup click', function(){
	    table = $('table.filter');
		list = $('p.filter');
		if(table){
			table.find('tr:has(td)').hide();
		    var data = this.value.split(" ");
		    var match = table.find("tr");
		    $.each(data, function(i, v){
		         //Use the new containsIgnoreCase function instead
		         match = match.filter("*:containsIgnoreCase('"+v+"')");
		    });
		    match.show();
		}
		if(list){
			list.find('span.tag').hide();
		    var data = this.value.split(" ");
		    var match = list.find('span.tag');
		    $.each(data, function(i, v){
		         //Use the new containsIgnoreCase function instead
		         match = match.filter("*:containsIgnoreCase('"+v+"')");
		    });
		    match.show();
		}
	});
	
	// PRIMARY - GEO HINTING
	$(".image_geo").live('focus', function(){
		$(this).autocomplete({
			source: BASE + ADMIN + 'tasks/hint-geo.php',
			delay: 200,
			minLength: 3
		});
	});
	
	// PRIMARY - TAG HINTING
	$(".image_tag").live('focus', function(){
		$(this).autocomplete({
			source: BASE + ADMIN + 'tasks/hint-tag.php',
			delay: 200,
			minLength: 2,
			select: function(event, ui) { $(this).parent().submit(); }
		});
	});
	
	// PRIMARY - PAGE CATEGORY HINTING
	$(".page_category").live('focus', function(){
		$(this).autocomplete({
			source: BASE + ADMIN + 'tasks/hint-page-cat.php',
			delay: 200,
			minLength: 2
		});
	});
	
	// PRIMARY - MARKUP
	$('select[name$="markup_ext"]').each(function() {
		ext = $(this).attr("title");
		if(!empty(ext)){
			$(this).find('option[value="' + ext + '"]').attr("selected", "selected");
		}
	});
	
	// PRIMARY - SORTABLE
	
	$("#set_image_sort").sortable({ cursor: 'pointer', opacity: 0.6, tolerance: 'pointer', update: function() { set = $(this); setSort(set); } });
	
	// PRIMARY - PHOTO DROPPABLE
	
	$(".image_click a").hover(function() {
		$(this).css('cursor', 'pointer');
	}, function() {
		$(this).css('cursor', 'inherit');
	}).live('click', function() {
		src = $(this).attr('href');
		alt = $(this).children('img').attr('alt');
		id = $(this).children('img').attr('id');
		uri_rel = $('.uri_rel.' + id).text();
		if($('.none.wrap_class').length > 0){
			wrap_class = $('.none.wrap_class').text();
			text = '<div class="' + wrap_class + '"><a href="' + uri_rel + '"><img src="' + src + '" alt="' + alt + '" /></a></div>';
		}
		else{
			text = '<a href="' + uri_rel + '"><img src="' + src + '" alt="' + alt + '" /></a>';
		}
	
		var input = $('textarea[id$="text_raw"]');
		var range = input.caret();
		
		var value = input.val();
		if(range.start > 0){
			input.val(value.substr(0, range.start) + text + value.substr(range.end, value.length));
			input.caret(range.start + text.length);
		}
		else{
			input.caret(0);
			input.val(text + value.substr(range.end, value.length));
			input.caret(text.length);
		}
		event.preventDefault();
	});
	
	// UPLOAD
	
	if(page == 'Upload'){
		var upload_count = 0;
		var upload_count_text;
		var no_of_files;
		var upload_options = {
			url: BASE + ADMIN + 'upload.php',
			sendBoundary: window.FormData || $.browser.mozilla,
			onStart: function(event, total) {
				no_of_files = total;
				$('.actions').find('button').attr('disabled', 'disabled').find('img').css('opacity', '0.5');
				// if(total == 1){
				// 	return confirm("You are about to upload 1 file. Are you sure?");
				// }
				// else{
				// 	return confirm("You are about to upload " + total + " files. Are you sure?");
				// }
				return true;
			},
			setName: function(text) {
				$("#h2_shoebox").slideUp(500);
				$("#progress").delay(500).slideDown(500);
			},
			setProgress: function(val) {
				$("#progress").progressbar({ value: Math.ceil(((val*(1/no_of_files)) + (upload_count/no_of_files))*100) });
			},
			onFinishOne: function(event, response, name, number, total) {
				file = number;
				upload_count = upload_count + 1;
				if(upload_count == 1){
					upload_count_text = upload_count + ' file';
				}
				else{
					upload_count_text = upload_count + ' files';
				}
				$("#upload_count_text").text(upload_count_text);
				if(number == (total - 1)){
					$('.actions').find('button').removeAttr('disabled').find('img').css('opacity', '');
					$("#progress").slideUp(500);
					$("#h2_shoebox").delay(500).slideDown(500);
				}
			}
		};
		
		$("#upload").html5_upload(upload_options);
		
		if($.browser.mozilla){
			$("#upload").parent().each(function(){
				top = $(this).height();
				top -= 75;
				$('#upload').css('padding', '0').css('position', 'relative').css('top', top + 'px').css('left', '50px');
				$(this).html5_upload(upload_options);
				$this = $(this);
				this.addEventListener('dragover', function(event){
				  	event.preventDefault();
				}, true);
				this.addEventListener('drop', function(event){
				  	event.preventDefault();
					this.files = event.dataTransfer.files;
					$(this).change();
				}, true);
			});
		}
	}
	
	// GUESTS
	
	if(page == 'Guest'){
		function guestKeyUpdate(){
			val = $('#guest_key').val();
			newval = val.replace(/[^a-z0-9\-\_]/i, '');
			if(val != newval){
				$('#guest_key').val(newval);
			}
			if(!empty(newval)){
				newval = '<span class="highlight">' + newval + '</span>';
				$('#guest_key_link').html(newval);
			}
			else{
				$('#guest_key_link').html('');
			}
		}
		
		guestKeyUpdate();
		
		$('#guest_key').keyup(function(event){
			guestKeyUpdate();
		});
	}
	
	// PREVIEWING
	
	if((page == 'Page') || (page == 'Image')){
		$('#preview').click(function(){
			newwin = window.open(null, 'preview', null, true);
			object = {};
			$('input').each(function(index) {
				key = $(this).attr('id');
				val = $(this).val();
				object[key] = val;
			});
			$('textarea').each(function(index) {
				key = $(this).attr('id');
				val = $(this).val();
				object[key] = val;
			});
			$('select').each(function(index) {
				key = $(this).attr('id');
				val = $(this).val();
				object[key] = val;
			});
			act = page.toLowerCase();
			$.post(BASE + ADMIN + 'preview.php', { act: act, object: object }, function(){
				newwin.location = BASE + ADMIN + 'preview.php';
			});
		});
	}
	
	// VERSIONS & CITATIONS
	
	if (page == 'Page') {
		autosave();
		
		$('a[href="#revert"]').live('click', function(){
			version_id = $(this).attr('id');
			version_id = findID(version_id);
			$.post(BASE + ADMIN + 'tasks/get-version.php', { id: version_id }, function(data){
				version = $.evalJSON(data);
				title = $('input[id$="title"]').val(version.version_title);
				text_raw = $('textarea[id$="text_raw"]').val(version.version_text_raw);
			});
			event.preventDefault();
		});
		
		$('.search_bar input[type="submit"]').live('click', function(){
			q = $(this).siblings('input').val();
			$('#recent_images').slideUp();
			$.post(BASE + ADMIN + 'tasks/load-images.php', { q: q }, function(data){
				$('#recent_images .load').html(data);
				$('#recent_images').delay(0).slideDown();
			});
			event.preventDefault();
		});
		
		
		
		$('#compare').click(function(event){
			title = $('input[id$="title"]').val();
			text_raw = $('textarea[id$="text_raw"]').val();
			version_id = $('#version_id').val();
			$('#comparison').hide();
			$.post(BASE + ADMIN + 'tasks/show-differences.php',  { title: title, text_raw: text_raw, version_id: version_id }, function(data) {
				if(empty(data)){
					addNote('No changes calculated. Try changing the version you&#8217;re comparing to.', 'notice');
				}
				else{
					clearNotes();
					$('#comparison').html(data).slideDown();
					buttonize();
				}
			}, 'html');
			event.preventDefault();
		});
		
		citations = $('#' + page.toLowerCase() + '_citations').val();
		citation_count = $('#citation_count').text();
		citation_count = parseInt(citation_count);
		field_id = $('#' + page.toLowerCase() + '_id').val();
		field = page.toLowerCase() + '_id';
		text_raw = $('#' + page.toLowerCase() + '_text_raw');
		look_for_uri = /\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.])(?:[^\s()<>]+|\([^\s()<>]+\))+(?:\([^\s()<>]+\)|[^`!()\[\]{};:'".,<>?«»“”‘’\s]))/gi;
		// Count words
		contents = text_raw.val();
		
		chars = parseInt(contents.length);
		chars = chars.toLocaleString();
		contents = contents.trim();
		words = contents.match(/\s+/gi);
		if(!empty(words)){
			words = parseInt(words.length + 1);
			words = words.toLocaleString();
		}
		else{
			if(empty(contents)){
				words = 0;
			}
			else{
				words = 1;
			}
		}
		
		paras = contents.match(/\n{2,}/gi);
		if(!empty(paras)){
			paras = parseInt(paras.length + 1);
			paras = paras.toLocaleString();
		}
		else{
			if(empty(contents)){
				paras = 0;
			}
			else{
				paras = 1;
			}
		}

		$('.info_bar').text(chars + ' characters, ' + words + ' words, ' + paras + ' paragraphs');

		text_raw.keyup(function(event){
			// Count words
			contents = text_raw.val();

			chars = parseInt(contents.length);
			chars = chars.toLocaleString();
			contents = contents.trim();
			words = contents.match(/\s+/gi);
			if(!empty(words)){
				words = parseInt(words.length + 1);
				words = words.toLocaleString();
			}
			else{
				if(empty(contents)){
					words = 0;
				}
				else{
					words = 1;
				}
			}

			paras = contents.match(/\n{2,}/gi);
			if(!empty(paras)){
				paras = parseInt(paras.length + 1);
				paras = paras.toLocaleString();
			}
			else{
				if(empty(contents)){
					paras = 0;
				}
				else{
					paras = 1;
				}
			}
			$('.info_bar').text(chars + ' characters, ' + words + ' words, ' + paras + ' paragraphs');
			
			if(event.which != 13){ return; }
			uris = look_for_uri.exec(contents);
			if(!empty(uris)){
				for (var i = uris.length - 1; i >= 1; i--){
					if(citations.indexOf(uris[i]) == -1){
						citations = citations + ' ' + uris[i];
						$('#' + page.toLowerCase() + '_citations').val(citations);
						$.post(BASE + ADMIN + 'tasks/load-citation.php', {uri: uris[i], field: field, field_id: field_id }, function(data, textStatus, xhr) {
							citation = $.evalJSON(data);
							if(!empty(citation)){
								$('#citation_count').text(++citation_count);
								html = '<tr><td style="width:16px;">';
								if(!empty(citation.citation_favicon_uri)){
									html += '<img src="' + citation.citation_favicon_uri + '" height="16" width="16" alt="" />';
								}
								html += '</td><td>';
								html += '<a href="';
								if(!empty(citation.citation_uri)){
									html += citation.citation_uri;
								}
								else{
									html += citation.citation_uri_requested;
								}
								html += '" title="';
								if(!empty(citation.citation_description)){
									html += citation.citation_description;
								}
								html += '" class="tip" target="_new">&#8220;' + citation.citation_title + '&#8221;</a>';
								if(!empty(citation.citation_site_name)){
									html += ' <span class="quiet">(' + citation.citation_site_name + ')</span>';
								}
								html += '</td></tr>';
								$('table#citations').append(html);
							}
						});
					}
				};
			}
		});
	}
	
	// CUSTOM LINK
	
	function titleUrlUpdate(selector){
		id = selector.attr('id');
		id_link = id + '_link';
		val = selector.val();
		newval = val.replace(/[^a-z0-9\-\_]/i, '');
		if(val != newval){
			selector.val(newval);
		}
		if(!empty(newval)){
			$('#' + id_link).html(newval);
		}
		else{
			$('#' + id_link).html('');
		}
	}
	
	function titleUrlPlaceholderUpdate(selector){
		id = selector.attr('id');
		id_link = id + '_url';
		val = selector.val();
		newval = val.replace(/\s+/gmi, '-');
		newval = newval.replace(/[^a-z0-9\-\_]/gmi, '-');
		newval = newval.replace(/\-+/gmi, '-');
		newval = newval.replace(/^-/gmi, '');
		newval = newval.replace(/-$/gmi, '');
		newval = newval.toLowerCase();
		if(!empty(newval)){
			$('#' + id_link).attr('placeholder', newval);
		}
		else{
			$('#' + id_link).attr('placeholder', '');
		}
	}
	
	$('input[id$="_title_url"]').each(function(){
		titleUrlUpdate($(this));
		$(this).keyup(function(event){
			titleUrlUpdate($(this));
		});
	});
	
	$('input[id$="_title"]').each(function(){
		titleUrlPlaceholderUpdate($(this));
		$(this).keyup(function(event){
			titleUrlPlaceholderUpdate($(this));
		});
	});
	
	// WATERMARK
	
	if(page == 'Thumbnail'){
		
		function sizeLabelUpdate(selector){
			id = selector.attr('id');
			val = selector.val();
			newval = val.replace(/[^a-z0-9\-\_]/i, '');
			if(val != newval){
				selector.val(newval);
			}
			if(!empty(newval)){
				$('#size_watermark_link').html(BASE + WATERMARKS + newval + '.png');
			}
			else{
				$('#size_watermark_link').html('');
			}
		}
		
		sizeLabelUpdate($('#size_label'));
		$('#size_label').keyup(function(event){
			sizeLabelUpdate($(this));
		});
		
		function sizeWatermarkNote(selector, dofalse){
			val = selector.attr('checked');
			if(val == true){
				$('#size_watermark_note').slideDown();
			}
			else{
				if(dofalse == true){
					$('#size_watermark_note').slideUp();
				}
			}
		}
		
		sizeWatermarkNote($('#size_watermark'), false);
		$('#size_watermark').click(function(){
			val = $(this).attr('checked');
			sizeWatermarkNote($(this), true);
		});
		
		$('input[type="submit"]').click(function(event){
			append = $('#size_append').val();
			prepend = $('#size_prepend').val();
			if(empty(append) && empty(prepend)){
				$('#size_prepend').css('background-color', '#FFF6BF');
				$('#size_append').css('background-color', '#FFF6BF');
				addNote('Fill in append to or prepend to filename fields to save. (Otherwise, delete or press cancel.)', 'error');
				event.preventDefault();
			}
		});
	}
	
	// NOTEMPTY
	$('input[type="submit"]').click(function(event){
		stop = false;
		$('input.notempty').each(function(index){
			$(this).css('background-color', '');
			val = $(this).val();
			if(val == ''){ stop = true; }
		});
		$('input.nonzero').each(function(index){
			$(this).css('background-color', '');
			val = $(this).val();
			if(val == 0){ stop = true; }
		});
		is_delete = $('input[id$="_delete"]').attr('checked');
		if(stop && (is_delete != true)){
			$('input.notempty').each(function(index){
				val = $(this).val();
				if(val == ''){
					$(this).css('background-color', '#FFF6BF');
				}
			});
			$('input.nonzero').each(function(index){
				val = $(this).val();
				if(val == 0){
					$(this).css('background-color', '#FFF6BF');
				}
			});
			
			addNote('Fill in the required fields to save. (Otherwise, delete or press cancel.)', 'error');
			event.preventDefault();
		}
	});
	
	// MAINTENANCE
	
	if(page == 'Maintenance'){
		url = location.href;
		task_in_url = /\#([a-z0-9_\-]+)$/i;
		task = url.match(task_in_url);
		if(!empty(task)){
			task = task[1];
			executeTask(task);
		}
		$("#tasks a").click(function(event){
			if($(this).attr("href").slice(0,1) == '#'){
				task = $(this).attr("href").slice(1);
				executeTask(task);
			}
		});
	}
	
	// SHOEBOX
	
	if(page == 'Shoebox'){
		executeTask('add-images');
		
		$("#shoebox_add").attr("disabled", "disabled");
		$("#progress").progressbar({ value: 0 });
	}
	
	// FEATURES EDITOR
	if(page == 'Editor'){
		function actEditor(){
			$('#act_geo').hide();
			$('#act_publish').hide();
			$('#act_tag_name').hide();
			$('#act_set_id').hide();
			$('#act_right_id').hide();
			$('#act_privacy_id').hide();
			$('#act_send').hide();
			
			act = $('#act').val();
			if(act == 'tag_add'){
				$('#act_tag_name').show();
			}
			else if(act == 'tag_remove'){
				$('#act_tag_name').show();
			}
			else if(act == 'set_add'){
				$('#act_set_id').show();
			}
			else if(act == 'set_remove'){
				$('#act_set_id').show();
			}
			else if(act == 'right'){
				$('#act_right_id').show();
			}
			else if(act == 'geo'){
				$('#act_geo').show();
			}
			else if(act == 'privacy'){
				$('#act_privacy_id').show();
			}
			else if(act == 'publish'){
				$('#act_publish').show();
			}
			else if(act == 'send'){
				$('#act_send').show();
			}
		}
		
		function selectedCount(){
			count = $('img.frame_fade_selected').length;
			$('#image_count_selected').text(count);
			
			ids = new Array();
			
			$('img.frame_fade_selected').each(function(index) {
				id = $(this).attr('id');
				id_find = /([0-9]+)/;
				id = id.match(id_find);
				ids.push(id[1]);
			});
			
			$('#image_ids').val(ids.join(', '));
		}
		
		$('#act_do').click(function(event){
			if(count == 0){
				addNote('Select at least one image to perform an action.', 'error');
				event.preventDefault();
			}
		});
		
		$('#act').change(function(){
			actEditor();
		});
		
		$('#select_all').click(function() {
			$('img.frame_fade').each(function(index) {
				$(this).removeClass('frame_fade').addClass('frame_fade_selected');
			});
			selectedCount();
			event.preventDefault();
		});
		
		$('#deselect_all').click(function() {
			$('img.frame_fade_selected').each(function(index) {
				$(this).removeClass('frame_fade_selected').addClass('frame_fade');
			});
			selectedCount();
			event.preventDefault();
		});
		
		$('img.frame_fade').live('click', function() {
			if((last_selected.length > 0) && (shift == 1) && (ids.length > 0)){
				group = $(this).prevUntil('img[id="' + last_selected + '"]').andSelf();
				group_first = group.first().attr('id');
				if(group_first != first_image){
					group.removeClass('frame_fade').addClass('frame_fade_selected');
					last_selected = $(this).attr('id');
				}
			}
			else if(shift == 1){
				$(this).prevAll('img.frame_fade').andSelf().removeClass('frame_fade').addClass('frame_fade_selected');
				last_selected = $(this).attr('id');
			}
			else{
				$(this).removeClass('frame_fade').addClass('frame_fade_selected');
				last_selected = $(this).attr('id');
			}
			selectedCount();
		});
		
		$('img.frame_fade_selected').live('click', function() {
			$(this).removeClass('frame_fade_selected').addClass('frame_fade');
			selectedCount();
		});
		
		$('img.frame_fade').hover(function(){
			$(this).css('cursor', 'pointer');
		}, function(){
			$(this).css('cursor', '');
		});
		
		$('img.frame_fade_selected').hover(function(){
			$(this).css('cursor', 'pointer');
		}, function(){
			$(this).css('cursor', '');
		});
		
		$(document).keydown(function(event) {
			if(event.keyCode == '16'){
				shift = 1;
			}
		});
		
		$(document).keyup(function(event) {
			if(event.keyCode == '16'){
				shift = 0;
			}
		});
		
		first_image = $('img.frame_fade').parent().children().first().attr('id');
		last_selected = '';
		actEditor();
		selectedCount();
	}
	
	// DASHBOARD
	if(page == 'Dashboard'){
		var statistics_views = $("#statistics_views").attr("title");
		statistics_views = $.evalJSON(statistics_views);
	
		var statistics_visitors = $("#statistics_visitors").attr("title");
		statistics_visitors = $.evalJSON(statistics_visitors);
	
		var stats = $.plot($("#statistics_holder"),[{
			label: "Page views",
			data: statistics_views,
			bars: { show: true, lineWidth: 15 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		},
		{
			label: "Unique visitors",
			data: statistics_visitors,
			bars: { show: true, lineWidth: 15 },
			shadowSize: 0,
			hoverable: true,
			yaxis: 1
		}],{
			legend: { show: true, backgroundOpacity: 0, labelBoxBorderColor: "#ddd", position: "ne", margin: 10 },
			colors: ["#0096db", "#8dc9e8"],
			xaxis: { mode: "time", tickLength: 0, autoscaleMargin: 0 },
			yaxis: { tickDecimals: 0, min: 0, tickFormatter: function toShortNum(val, axis){ return shortNum(val); } },
			grid: { color: "#777", borderColor: "transparent", tickColor: "#eee", labelMargin: 10, hoverable: true, autoHighlight: true }
		});
	
		$.each(stats.getData()[0].data, function(i, el){
			var o = stats.pointOffset({x: el[0], y: el[1]});
			if(el[1] > 0){
			  $('<div class="point">' + shortNum(el[1]) + '</div>').css( {
			    position: 'absolute',
			    left: o.left - 12,
			    top: o.top - 20,
			  }).appendTo(stats.getPlaceholder());
			}
		});
	
		var time = new Date();
		var month = time.getMonth();
	
		if(month == 0){ month = 'Jan'; }
		if(month == 1){ month = 'Feb'; }
		if(month == 2){ month = 'Mar'; }
		if(month == 3){ month = 'Apr'; }
		if(month == 4){ month = 'May'; }
		if(month == 5){ month = 'Jun'; }
		if(month == 6){ month = 'Jul'; }
		if(month == 7){ month = 'Aug'; }
		if(month == 8){ month = 'Sep'; }
		if(month == 9){ month = 'Oct'; }
		if(month == 10){ month = 'Nov'; }
		if(month == 11){ month = 'Dec'; }

		var day = time.getDate();
	
		$(".tickLabel").each(function(index){
			var text = $(this).text();
			if(text == (month + ' ' + day)){
				$(this).text('Today').css('color', '#000');
			}
		});
	
		$(".tickLabels").css('font-size', '');
		$('.legend table').removeAttr('left').css('top', '0px').css('right', '0px');
	}
	
	// COMMENTS
	
	if(page == 'Comment'){
		$('#comment_spam').click(function(){
			checked = $(this).attr('checked');
			if(checked == true){
				$('#comment_delete').attr('checked', 'checked');
			}
		});
	}
	
	// STATISTICS
	
	if(page == 'Statistics'){
		var h_statistics_views = $("#h_views").attr("title");
		h_statistics_views = $.evalJSON(h_statistics_views);
	
		var h_statistics_visitors = $("#h_visitors").attr("title");
		h_statistics_visitors = $.evalJSON(h_statistics_visitors);
	
		var h_stats = $.plot($("#h_holder"),[{
			label: "Page views",
			data: h_statistics_views,
			bars: { show: true, lineWidth: 16 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		},
		{
			label: "Unique visitors",
			data: h_statistics_visitors,
			bars: { show: true, lineWidth: 16 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		}],{
			legend: { show: true, backgroundOpacity: 0, labelBoxBorderColor: "#ddd", position: "ne", margin: 0 },
			colors: ["#0096db", "#8dc9e8"],
			xaxis: { mode: "time", tickLength: 0, autoscaleMargin: 0, timeformat: "%h %p" },
			yaxis: { tickDecimals: 0, min: 0, tickFormatter: function toShortNum(val, axis){ return shortNum(val); } },
			grid: { color: "#777", borderColor: "transparent", tickColor: "#eee", labelMargin: 10, hoverable: true, autoHighlight: true }
		});
		
		
	
		var d_statistics_views = $("#d_views").attr("title");
		d_statistics_views = $.evalJSON(d_statistics_views);
	
		var d_statistics_visitors = $("#d_visitors").attr("title");
		d_statistics_visitors = $.evalJSON(d_statistics_visitors);
	
		var d_stats = $.plot($("#d_holder"),[{
			label: "Page views",
			data: d_statistics_views,
			bars: { show: true, lineWidth: 15 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		},
		{
			label: "Unique visitors",
			data: d_statistics_visitors,
			bars: { show: true, lineWidth: 15 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		}],{
			legend: { show: true, backgroundOpacity: 0, labelBoxBorderColor: "#ddd", position: "ne", margin: 0 },
			colors: ["#0096db", "#8dc9e8"],
			xaxis: { mode: "time", tickLength: 0, autoscaleMargin: 0, minTickSize: [3, "day"] },
			yaxis: { tickDecimals: 0, min: 0, tickFormatter: function toShortNum(val, axis){ return shortNum(val); } },
			grid: { color: "#777", borderColor: "transparent", tickColor: "#eee", labelMargin: 10, hoverable: true, autoHighlight: true }
		});
		
		var m_statistics_views = $("#m_views").attr("title");
		m_statistics_views = $.evalJSON(m_statistics_views);
	
		var m_statistics_visitors = $("#m_visitors").attr("title");
		m_statistics_visitors = $.evalJSON(m_statistics_visitors);
	
		var m_stats = $.plot($("#m_holder"),[{
			label: "Page views",
			data: m_statistics_views,
			bars: { show: true, lineWidth: 30 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		},
		{
			label: "Unique visitors",
			data: m_statistics_visitors,
			bars: { show: true, lineWidth: 30 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		}],{
			legend: { show: true, backgroundOpacity: 0, labelBoxBorderColor: "#ddd", position: "ne", margin: 0 },
			colors: ["#0096db", "#8dc9e8"],
			xaxis: { mode: "time", tickLength: 0, autoscaleMargin: 0 },
			yaxis: { tickDecimals: 0, min: 0, tickFormatter: function toShortNum(val, axis){ return shortNum(val); } },
			grid: { color: "#777", borderColor: "transparent", tickColor: "#eee", labelMargin: 10, hoverable: true, autoHighlight: true }
		});
		
		$.each(h_stats.getData()[0].data, function(i, el){
			var o = h_stats.pointOffset({x: el[0], y: el[1]});
			if(el[1] > 0){
			  $('<div class="point">' + shortNum(el[1]) + '</div>').css( {
			    position: 'absolute',
			    left: o.left - 12,
			    top: o.top - 20,
			  }).appendTo(h_stats.getPlaceholder());
			}
		});
		
		$.each(d_stats.getData()[0].data, function(i, el){
			var o = d_stats.pointOffset({x: el[0], y: el[1]});
			if(el[1] > 0){
			  $('<div class="point">' + shortNum(el[1]) + '</div>').css( {
			    position: 'absolute',
			    left: o.left - 12,
			    top: o.top - 20,
			  }).appendTo(d_stats.getPlaceholder());
			}
		});
		
		$.each(m_stats.getData()[0].data, function(i, el){
			var o = m_stats.pointOffset({x: el[0], y: el[1]});
			if(el[1] > 0){
			  $('<div class="point">' + shortNum(el[1]) + '</div>').css( {
			    position: 'absolute',
			    left: o.left - 12,
			    top: o.top - 20,
			  }).appendTo(m_stats.getPlaceholder());
			}
		});
		
		$(".tickLabel").each(function(index){
			var text = $(this).text();
			if(text == ('12 am')){
				$(this).text('Midnight');
			}
			else if(text == ('12 pm')){
				$(this).text('Noon');
			}
		});
		
		var time = new Date();
		var month = time.getMonth();
	
		if(month == 0){ month = 'Jan'; }
		if(month == 1){ month = 'Feb'; }
		if(month == 2){ month = 'Mar'; }
		if(month == 3){ month = 'Apr'; }
		if(month == 4){ month = 'May'; }
		if(month == 5){ month = 'Jun'; }
		if(month == 6){ month = 'Jul'; }
		if(month == 7){ month = 'Aug'; }
		if(month == 8){ month = 'Sep'; }
		if(month == 9){ month = 'Oct'; }
		if(month == 10){ month = 'Nov'; }
		if(month == 11){ month = 'Dec'; }

		var day = time.getDate();
	
		$(".tickLabel").each(function(index){
			var text = $(this).text();
			if(text == (month + ' ' + day)){
				$(this).text('Today').css('color', '#000');
			}
		});
	
		$(".tickLabels").css('font-size', '');
		$('.legend table').removeAttr('left').css('top', '0px').css('right', '0px');
		
	}
});

$(window).load(function(){
	reset();
});

$(window).scroll(function() {
    $('#header_home').css('top', "-" + $(this).scrollTop() + "px");
});