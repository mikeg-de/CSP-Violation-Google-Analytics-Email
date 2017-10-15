<?php
ini_set("display_errors", "On");
error_reporting(E_ALL);

// Required for Wordpress only
// require_once( "./wp-load.php" );

// Start configure
// 2 Do: Write into /var/log/nginx to properly rotate with logrotate
$log_file = dirname(__FILE__) . "/csp-violations.log";
$log_file_size_limit = 1000000; // bytes - once exceeded no further entries are added
$recepient_email = "YOUR@EMAIL";
$sender_email = "SENDER@EMAIL";
$email_subject = "Content-Security-Policy violation";
// End configuration

$current_domain = preg_replace("/www\./i", "", $_SERVER["SERVER_NAME"]);
$email_subject = $email_subject . " on " . $current_domain;

http_response_code(204); // HTTP 204 No Content

$json_data = file_get_contents("php://input");
$allCookies = json_encode($_COOKIE);

// Sources: https://gist.github.com/chrisblakley/e1f3d79b6cecb463dd8a & https://gearside.com/using-server-side-google-analytics-sending-pageviews-event-tracking/
// Get GA Property ID
// 2 Do: On first load no UA Property Cookie is set --> switch case based on Domain not elegant but more reliable
// 2 Do: Check for multiple GA Property ID"s
function gaPropertyID($allCookies) {
    if (preg_match("/(UA\-\d+\-\d+)/i", $allCookies, $search_result)) {
        $gaPropertyID = $search_result[1];
    } else {
        $gaPropertyID = "UA-1234567-0"; // Fallback
    }
    return $gaPropertyID;
}

// Parse the GA Cookie
function gaParseCookie() {
	if (isset($_COOKIE["_ga"])) {
		list($version, $domainDepth, $cid1, $cid2) = explode(".", $_COOKIE["_ga"], 4);
		$contents = array("version" => $version, "domainDepth" => $domainDepth, "cid" => $cid1 . "." . $cid2);
		$cid = $contents["cid"];
	} else {
		$cid = gaGenerateUUID();
	}
	return $cid;
}

//Generate UUID
//Special thanks to stumiller.me for this formula.
function gaGenerateUUID() {
	return sprintf("%04x%04x-%04x-%04x-%04x-%04x%04x%04x",
		mt_rand(0, 0xffff), mt_rand(0, 0xffff),
		mt_rand(0, 0xffff),
		mt_rand(0, 0x0fff) | 0x4000,
		mt_rand(0, 0x3fff) | 0x8000,
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
	);
}

//Send Data to Google Analytics
//https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#event
function gaSendData($data) {
	$getString = "https://ssl.google-analytics.com/collect";
	$getString .= "?payload_data&";
	$getString .= http_build_query($data);
	$result = wp_remote_get($getString);
	return $result;
}

//Send Event Function for Server-Side Google Analytics
function ga_send_event($category=null, $action=null, $label=null, $current_domain=null, $allCookies) {
	$data = array(
		"v" => 1,
		"tid" => gaPropertyID($allCookies), //@TODO: Change this to your Google Analytics Tracking ID.
		"cid" => gaParseCookie(),
		"t" => "event",
		"ec" => $category, // Category (Required)
		"ea" => $action, // Action (Required)
		"el" => $label, // Label
    "dh" => $current_domain // Hostname
	);
	gaSendData($data);
}

// We pretty print the JSON before adding it to the log file
if ($json_data = json_decode($json_data, true)) {
    $json_data_encoded = json_encode($json_data);

    if (!file_exists($log_file) || filesize($log_file) < $log_file_size_limit) {
        // Send an email
        $message = "The following Content-Security-Policy violation occurred on " .
        $current_domain . ":\n\n" .
        "Document-uri: {$json_data["csp-report"]["document-uri"]} \n" .
        "Referrer: {$json_data["csp-report"]["referrer"]} \n\n" .

        "Blocked-uri: {$json_data["csp-report"]["blocked-uri"]} \n" .
        "Violated-directive: {$json_data["csp-report"]["violated-directive"]} \n" .
        "Effective-directive: {$json_data["csp-report"]["effective-directive"]} \n\n" .

        "Source-file: {$json_data["csp-report"]["source-file"]} \n" .
        "Line-number: {$json_data["csp-report"]["line-number"]} \n" .
        "Script-sample: {$json_data["csp-report"]["script-sample"]} \n\n" .

        "Disposition: {$json_data["csp-report"]["disposition"]} \n" .
        "Status-code: {$json_data["csp-report"]["status-code"]} \n\n" .

        // Commented: For debug purpose only
/*        "Cookies: {$allCookies} \n\n" .

        "GA Propery ID: {$gaPropertyID} \n" .
        "GA data: {$dataEncoded} \n\n" .
*/
        "Original CSP Message\n" .
        $json_data_encoded .

        "\n\nFurther CPS violations will be logged to the following log file, but no further email notifications will be sent until this log file is deleted:\n\n" .
        $log_file;

        mail($sender_email, $email_subject, $message,$recepient_email,"Content-Type: text/plain;charset=utf-8");
    } else if (filesize($log_file) > $log_file_size_limit) {
        exit(0);
    }

  file_put_contents($log_file, $json_data_encoded, FILE_APPEND | LOCK_EX);
}

// 2 Do: For each UA Property
ga_send_event("CSP-Violation", "{$json_data["csp-report"]["violated-directive"]}", "{$json_data["csp-report"]["blocked-uri"]}", $current_domain, $allCookies);

?>
