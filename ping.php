<?php
$server = "https://app-a241f7f9-0b4d-473f-bccf-5bd8051b932f.cleverapps.io/"; // Change this to the server you want to ping

// Execute the ping command
$pingResult = shell_exec("ping -c 4 $server"); // For Linux/macOS, use -c to specify the number of pings
// On Windows, use -n instead, e.g., "ping -n 4 $server"

// Output the result
echo "<pre>$pingResult</pre>";
?>
