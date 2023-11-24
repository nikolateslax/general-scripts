<?php
$safeMode = true;
// DO NOT EDIT BELOW

echo "ZeroTier config script by NikolaTeslaX" . PHP_EOL;

$apiBase = "https://api.zerotier.com/api/v1";

if (!file_exists("/etc/nikolateslax/zerotier.json")) {
	die("Configuration not found. Script cannot continue.");
}

// Read the network ID and VM host from CloudInit
$jd = json_decode(file_get_contents("/etc/nikolateslax/zerotier.json"));
$netId = $jd->network;
$vpsName = $jd->host;
$token = $jd->token;
$fqdn = shell_exec("hostname -f");
echo "Host Name: $vpsName" . PHP_EOL . "FQDN: $fqdn" . PHP_EOL

// Install/update ZeroTier if it's not already installed
if (file_exists("/sbin/zerotier-one")) {
	echo "ZeroTier detected. Not installing." . PHP_EOL;
} else {
	echo "ZeroTier was not detected. It will be installed.";
	echo shell_exec('curl -s https://install.zerotier.com | bash');
}

// Get our new member ID from ZeroTier CLI
echo "Getting ZeroTier member ID for this node... ";
$newMbrId = explode(" ", shell_exec("zerotier-cli status"))[2];
echo "$newMbrId" . PHP_EOL;

// Register on the network using ZeroTier CLI
echo "Joining ZeroTier network $netId... ";
echo shell_exec("zerotier-cli join $netId");

// Initialise a cURL session to talk to ZeroTier's API
$ch = curl_init();
curl_setopt_array($ch, array(
	CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTPHEADER => array("Authorization: token $token")));

// Request network members from ZeroTier
curl_setopt($ch, CURLOPT_URL, "$apiBase/network/$netId/member");
echo "Retrieving member node list from ZeroTier... ";
$members = json_decode(curl_exec($ch), true);
echo "Done." . PHP_EOL;

// Iterate network members, looking for the current VM's predecessor
foreach ($members as $mbr) {
	echo "Member " . $mbr["description"] . "... ";
	if ($mbr["description"] == $fqdn) {
		echo "Matched. Selecting node." . PHP_EOL;
		// Prepare a temporary file to work with
		$mbrFile = fopen("/tmp/nikolateslax-zerotier", "w+");
		
		// Read the old VM's ID and grabbing its IP assignments
		$oldMbrId = $mbr["nodeId"];
		$mbrIp = $mbr["config"]["ipAssignments"];
		echo "Listing IP addresses for node..." . PHP_EOL;
		foreach ($mbrIp as $ip) {
			echo "  - $ip" . PHP_EOL;
		}
		
		echo "IP addresses will be transferred from $oldMbrId to $newMbrId" . PHP_EOL;
		
		// Set the URL in preparation to do something with the old VM
		curl_setopt($ch, CURLOPT_URL, "$apiBase/network/$netId/member/$oldMbrId");
		
		if ($safeMode) {
			echo "Safe mode is active. Old node will be deauthorized, renamed, and stripped of its IP address assignments." . PHP_EOL;
			// Update the old member so that it's not authorized
			$mbr["config"]["authorized"] = false;
			$mbr["name"] = "[Disabled] " . $mbr["name"];
			$mbr["config"]["ipAssignments"] = array();
			
			// Write the member data to file and upload it to ZeroTier
			fseek($mbrFile, 0);
			fwrite($mbrFile, json_encode($mbr)); fseek($mbrFile, 0);
			curl_setopt($ch, CURLOPT_URL, "$apiBase/network/$netId/member/$newMbrId");
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_UPLOAD, true);
			curl_setopt($ch, CURLOPT_INFILE, $mbrFile);
			fseek($mbrFile, 0);
		} else {
			// Remove the old VM from the ZeroTier network
			echo "Old node will be banned from $netId. To instead disable the node without deleting it permanently, enable safe mode." . PHP_EOL;
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		}
		// Whatever method of removing the previous VM, send it
		echo curl_exec($ch) . PHP_EOL;
		echo "The old node has been... \"dealt with\", boss." . PHP_EOL;
		
		// Write our new VM information to file..
		fseek($mbrFile, 0);
		fwrite($mbrFile, json_encode(array(
			"hidden" => false,
			"name" => $vpsName,
			"description" => $vpsName . "/" . shell_exec("hostname"),
			"config" => array(
				"activeBridge" => null,
				"authorized" => true,
				"capabilities" => null,
				"ipAssignments" => $mbrIp,
				"noAutoAssignIps" => null,
				"tags" => null
			),
		))); fseek($mbrFile, 0);
		
		// ...and upload to ZeroTier to authorize us
		curl_setopt($ch, CURLOPT_URL, "$apiBase/network/$netId/member/$newMbrId");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_UPLOAD, true);
		curl_setopt($ch, CURLOPT_INFILE, $mbrFile);
		fseek($mbrFile, 0);
		echo curl_exec($ch) . PHP_EOL;
		fclose($mbrFile);
	} else {
		echo "Ignored (No match)" . PHP_EOL;
	}
}

echo "ZeroTier config complete";
if (!$safeMode) {
	if (file_exists("/etc/nikolateslax/zerotier.json")) {
		echo "Deleting ZeroTier config json... ";
		unlink("/etc/nikolateslax/zerotier.json");
		echo "Done." . PHP_EOL;
	}
	if (file_exists("/tmp/nikolateslax-zerotier")) {
		echo "Deleting temporary files... ";
		unlink("/tmp/nikolateslax-zerotier");
		echo "Done." . PHP_EOL;
	}
	echo "Deleting ZeroTier script... ";
	unlink("/etc/nikolateslax/zerotier.php");
	echo "Done." . PHP_EOL;
}
echo "Goodbye" . PHP_EOL;