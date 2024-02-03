<?php
/*
Plugin Name: Oscar Listings Shortcodes
Description: Use WordPress shortcodes to create listings pages for shows using the Oscar booking system by Savoy Systems Ltd. Please note that this project is not affiliated with Savoy Systems Ltd.
Version: 1.1
Author: Jonny Browning
Author URI: https://www.jonnybrowning.com
*/

// Register shortcode
add_shortcode("oscar-listings", "oscarlistings_function");

function oscarlistings_function($shortcodeParams) {

  // get Oscar listings
  $feed_url = get_option('oscarlistings_feedURL');
  $shows = fetch_listings($feed_url);

	$listingsOutput = "";
  // filter if DST / AR / SR depending on shortcode attributes
  foreach($shows as $show) {
    // if there are no parameters in the shortcode, output all shows
    if (!$shortcodeParams) {
      $listingsOutput .= output_listing($show);
    }
    // for each parameter in the shortcode, if true check to see if value is true in each show
    foreach($shortcodeParams as $key=>$shortcodeParam) {
      if($shortcodeParam and $show['@attributes'][$key] == "Y") {
        $listingsOutput .= output_listing($show);
      }
    }
  }

  // if no shows, add a note to the output
  if ($listingsOutput == "") {
    $listingsOutput = "<p class='oscar-listing-no-shows'>There are no upcoming shows in the calendar. Please check back soon!</p>";
  }

  return $listingsOutput;
}

// generate output for a show listing and return it as a string
function output_listing($show) {
  $listingOutput = "";
  $listingOutput .= "<div class='oscar-listing-outer-container'>";
  $listingOutput .=
    "<div class='oscar-listing-title'>
      <a href=" . $show['@attributes']['BookingURL'] . "><h1>" . $show['@attributes']['ReportTitle'] . "</h1></a>
    </div>
    <div class='oscar-listing-inner-container'>
      <div class='oscar-listing-image'>
        <a href=" . $show['@attributes']['BookingURL'] . ">
          <img src='" . $show['@attributes']['InternetBookingImageURL'] . "' alt='" . $show['@attributes']['ReportTitle'] . "' />
        </a>
      </div>
      <div class='oscar-listing-synopsis'>"  . $show['@attributes']['Synopsis'] ."</div>
    </div>";
    $listingOutput .=
    "<div class='oscar-listing-performances-container'>
      <h2>Book tickets</h2>
      <table class='oscar-listing-performances-table'>";

    // list performances
    foreach($show['performances'] as $performance) {
      if($performance['HideFromInternetSales'] == "N") { // don't show performance if HideFromInternetSales API flag = "Y"
        $listingOutput .= "<tr>";
        $listingOutput .=
        "<td>" . date("l jS F Y",strtotime($performance['StartDate'])) . "</td>
        <td>" . date("g.ia",strtotime($performance['StartTime'])) . "</td>";
        $listingOutput .= "<td>";
        if ($performance['IsOpenForSale'] == "Y" and $performance['AllowInternetSales'] ==  "Y") { // only show performances if allowed by API flags
          $listingOutput .= "<a href='" . $performance['BookingURL'] . "'>Book tickets</a>";
        } else if ($performance['IsOpenForSale'] == "N") {
          $listingOutput .= "Tickets not currently on sale";
        } else {
          $listingOutput .= "Online booking unavailable - please contact the box office";
        }
        $listingOutput .= "</td>
        </tr>";
      }
    }

    $listingOutput .=
      "</table>
    </div>
  </div>
  ";
  return $listingOutput;
}

function fetch_listings($feed_url) {

  // Fetch shows from Oscar
  $response = wp_remote_get($feed_url);
  $body = wp_remote_retrieve_body($response);

  // convert result to XML object
  $xml  = simplexml_load_string($body);

  // Add programmes to array
  $programmes = array();
  foreach ($xml->Programme as $programme) {
    $programme2 = (array) (object) $programme;
    $performances = (array) $xml->xpath('//Performance[@ProgrammeID="' . $programme['ID'] . '"]'); // get all performances associated with this programme;
    usort($performances, 'comparePerformanceDates'); // Sort shows by date ascending
    $programme2['performances'] = $performances;
    $programmes[] = $programme2;
  }
  usort($programmes, 'compareShowDates'); // sort programmes by date of earliest performance
  return $programmes;

}

// settings page stuff

// register settings with Wordpress
function oscarlistings_register_settings() {
  add_option('oscarlistings_feedURL', '');
  register_setting('oscarlistings_options_group', 'oscarlistings_feedURL', 'oscarlistings_callback');
}
add_action('admin_init', 'oscarlistings_register_settings');

// register options page
function oscarlistings_register_options_page() {
  add_options_page('Oscar Listings', 'Oscar Listings', 'manage_options', 'oscarlistings', 'oscarlistings_options_page');
}
add_action('admin_menu', 'oscarlistings_register_options_page');

// options page
function oscarlistings_options_page() {
  ?>
  <div>
    <?php screen_icon(); ?>
    <h2>Oscar Listings Options</h2>
    <form method="post" action="options.php">
    <?php settings_fields( 'oscarlistings_options_group' ); ?>
    <table>
    <tr valign="top">
    <th scope="row"><label for="oscarlistings_feedURL">Oscar API URL</label></th>
    <td><input type="text" id="oscarlistings_feedURL" name="oscarlistings_feedURL" value="<?php echo get_option('oscarlistings_feedURL'); ?>" /></td>
    </tr>
    </table>
    <?php  submit_button(); ?>
    </form>
  </div>
  <?php
}

// function to compare performance dates (for use with usort)
function comparePerformanceDates($a, $b) {
	if ((string)$a['StartDate'] == (string)$b['StartDate']) {
		if ((string)$a['StartTime'] == (string)$b['StartTime']) {
			return 0;
		}
		$a_time = strtotime((string)$a['StartTime']);
		$b_time = strtotime((string)$b['StartTime']);
		return ($a_time < $b_time) ? -1 : 1;
	}
	$a_date = strtotime((string)$a['StartDate']);
	$b_date = strtotime((string)$b['StartDate']);

	return ($a_date < $b_date) ? -1 : 1;
}

// function to compare show dates for use with usort (note - assumes that performances are already sorted!)
function compareShowDates($a, $b) {
  return comparePerformanceDates($a['performances'][0], $b['performances'][0]);
}
?>