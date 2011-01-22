CREATE TABLE "cities" ("city_id" INTEGER PRIMARY KEY, "city_name" TEXT, "city_state" TEXT, "country_code" TEXT, "city_name_raw" TEXT, "city_name_alt" TEXT, "city_pop" INTEGER, "city_lat" REAL, "city_long" REAL, "city_class" TEXT, "city_code" TEXT);
CREATE TABLE "comments" ("comment_id" SERIAL PRIMARY KEY, "photo_id" INTEGER, "user_id" INTEGER, "comment_created" TIMESTAMP, "comment_status" INTEGER, "comment_text" TEXT, "comment_text_raw" TEXT, "comment_markup" TEXT, "comment_author_name" TEXT, "comment_author_uri" TEXT, "comment_author_email" TEXT, "comment_author_ip" TEXT, "comment_author_avatar" TEXT);
CREATE TABLE "countries" ("country_id" SMALLINT PRIMARY KEY, "country_code" TEXT, "country_name" TEXT);
CREATE TABLE "exifs" ("exif_id" SERIAL PRIMARY KEY, "photo_id" INTEGER, "exif_key" TEXT, "exif_name" TEXT, "exif_value" TEXT);
CREATE TABLE "extensions" ("extension_id" SERIAL PRIMARY KEY, "extension_uid" TEXT, "extension_class" TEXT, "extension_title" TEXT, "extension_status" INTEGER, "extension_build" INTEGER, "extension_build_latest" INTEGER, "extension_version" TEXT, "extension_version_latest" TEXT, "extension_hooks" TEXT, "extension_preferences" TEXT, "extension_folder" TEXT, "extension_file" TEXT, "extension_description" TEXT, "extension_creator_name" TEXT, "extension_creator_uri" TEXT);
CREATE TABLE "guests" ("guest_id" SERIAL PRIMARY KEY, "guest_title" TEXT, "guest_key" TEXT, "guest_piles" TEXT, "guest_last_login" TIMESTAMP, "guest_created" TIMESTAMP, "guest_views" INTEGER);
CREATE TABLE "links" ("link_id" SERIAL PRIMARY KEY, "photo_id" INTEGER, "tag_id" INTEGER);
CREATE TABLE "pages" ("page_id" SERIAL PRIMARY KEY, "page_title" TEXT, "page_title_url" TEXT, "page_text" TEXT, "page_text_raw" TEXT, "page_markup" TEXT, "page_photos" TEXT, "page_views" INTEGER, "page_words" INTEGER, "page_created" TIMESTAMP, "page_modified" TIMESTAMP);
CREATE TABLE "photos" ("photo_id" SERIAL PRIMARY KEY, "user_id" INTEGER, "right_id" INTEGER, "photo_ext" TEXT, "photo_mime" TEXT, "photo_title" TEXT, "photo_description" TEXT, "photo_description_raw" TEXT, "photo_markup" TEXT, "photo_privacy" INTEGER, "photo_name" TEXT, "photo_colors" TEXT, "photo_color_r" INTEGER, "photo_color_g" INTEGER, "photo_color_b" INTEGER, "photo_color_h" INTEGER, "photo_color_s" INTEGER, "photo_color_l" INTEGER, "photo_taken" TIMESTAMP, "photo_uploaded" TIMESTAMP, "photo_published" TIMESTAMP, "photo_updated" TIMESTAMP, "photo_geo" TEXT, "photo_geo_lat" REAL, "photo_geo_long" REAL, "photo_views" INTEGER, "photo_comment_count" INTEGER, "photo_height" INTEGER, "photo_width" INTEGER);
CREATE TABLE "piles" ("pile_id" SERIAL PRIMARY KEY, "pile_title" TEXT, "pile_title_url" TEXT, "pile_type" TEXT, "pile_description" TEXT, "pile_photos" TEXT, "pile_views" INTEGER, "pile_photo_count" INTEGER, "pile_call" TEXT, "pile_modified" TIMESTAMP, "pile_created" TIMESTAMP);
CREATE TABLE "rights" ("right_id" SERIAL PRIMARY KEY, "right_title" TEXT, "right_uri" TEXT, "right_image" TEXT, "right_description" TEXT, "right_modified" TIMESTAMP, "right_photo_count" INTEGER);
CREATE TABLE "sizes" ("size_id" SERIAL PRIMARY KEY, "size_title" TEXT, "size_label" TEXT, "size_height" INTEGER, "size_width" INTEGER, "size_type" TEXT, "size_append" TEXT, "size_prepend" TEXT, "size_watermark" INTEGER);
CREATE TABLE "stats" ("stat_id" SERIAL PRIMARY KEY, "stat_session" TEXT, "stat_date" TIMESTAMP, "stat_duration" INTEGER, "stat_referrer" TEXT, "stat_page" TEXT, "stat_page_type" TEXT, "stat_local" INTEGER, "user_id" INTEGER, "guest_id" INTEGER);
CREATE TABLE "tags" ("tag_id" SERIAL PRIMARY KEY, "tag_name" TEXT);
CREATE TABLE "themes" ("theme_id" SERIAL PRIMARY KEY, "theme_uid" TEXT, "theme_title" TEXT, "theme_build" INTEGER, "theme_build_latest" INTEGER, "theme_version" TEXT, "theme_version_latest" TEXT, "theme_folder" TEXT, "theme_creator" TEXT, "theme_creator_uri" TEXT);
CREATE TABLE "users" ("user_id" SERIAL PRIMARY KEY, "user_user" TEXT, "user_pass" TEXT, "user_pass_salt" TEXT, "user_key" TEXT, "user_name" TEXT, "user_email" TEXT, "user_last_login" TIMESTAMP, "user_created" TIMESTAMP, "user_permissions" TEXT, "user_preferences" TEXT, "user_photo_count" INTEGER);