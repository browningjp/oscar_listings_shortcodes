=== Oscar Listings Shortcodes ===
Tags: oscar,savoy,savoysystems,theatre,listings,shows
Requires at least: 5.0
Tested up to: 5.2.2
Requires PHP: 5.6
License: GNU GPLv3

Use WordPress shortcodes to create listings pages for shows using the Oscar booking system by Savoy Systems Ltd. Please note that this project is not affiliated with Savoy Systems Ltd.

== Description ==
Using a WordPress shortcode, this plugin enables you to create a listings page for shows pulled from the Oscar API. You can specify optional parameters in the shortcode to filter shows by parameters in the API.

Please note that this project is not affiliated with Savoy Systems Ltd.

== Installation ==
#Configuring the API feed

Once you have installed and activate the plugin, you need to specify the URL of your Oscar API feed. This can be done under Settings => Oscar Listings. The URL should look something like this:

https://MyTheatre.savoysystems.co.uk/MyTheatre.dll/XMLPerformances?APIVersion=1

#Creating a listings page

To create a listings page, you will need to add the shortcode to a page.

`[oscar-listings]` will display all the shows listed in the API

You can specify more parameters to filter your results further.

`[oscar-listings is_comedy=1]` will display all the shows listed in the API with the (custom) parameter `is_comedy` set to \"Y\".

The use of multiple flags is treated as an inclusive OR. i.e.

`[oscar-listings is_comedy=1 is_musical=1]` will display all the shows listed in the API with the (custom) parameter `is_comedy` set to \"Y\" AND all the shows listed in the API with the (custom) parameter `is_musical` set to \"Y\".

*PLEASE NOTE* - shortcodes in WordPress are all run through strtolower(), therefore any flags in the Oscar API that have upper-case characters *will not work* with this plugin. Make sure the API flags you want to filter by are all lower-case.

#Styling

You can add styling to the listings page by customising your theme with additional CSS. In the WordPress back-end, go to Appearance => Customise => Additional CSS

== Changelog ==
1.1

Update show name to use "ReportTitle" field from API (instead of shorter "Title" field)

1.0

Initial release