<?php
$safeMode = true;
// DO NOT EDIT BELOW

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

// Install/update ZeroTier if it's not already installed
if (!file_exists("/sbin/zerotier-one")) {
	echo shell_exec('curl -s https://install.zerotier.com | bash');
}

// Get our new member ID from ZeroTier CLI
$newMbrId = explode(" ", shell_exec("zerotier-cli status"))[2];

// Register on the network using ZeroTier CLI
echo shell_exec("zerotier-cli join $netId");

// Initialise a cURL session to talk to ZeroTier's API
$ch = curl_init();
curl_setopt_array($ch, array(
	CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTPHEADER => array("Authorization: token $token")));

// Request network members from ZeroTier
curl_setopt($ch, CURLOPT_URL, "$apiBase/network/$netId/member");
$members = json_decode(curl_exec($ch));

// Iterate network members, looking for the current VM's predecessor
foreach ($members as $mbr) {
	if ($mbr->description == $fqdn) {
		// Prepare a temporary file to work with
		$mbrFile = fopen("/tmp/nikolateslax-zerotier", "w+");
		
		// Read the old VM's ID and grabbing its IP assignments
		$oldMbrId = $mbr->nodeId;
		$mbrIp = $mbr->config->ipAssignments;
		
		// Set the URL in preparation to do something with the old VM
		curl_setopt($ch, CURLOPT_URL, "$apiBase/network/$netId/member/$oldMbrId");
		
		if ($safeMode) {
			// Update the old member so that it's not authorized
			$mbr->config->authorized = false;
			$mbr->description = "[Disabled] " . $mbr->description;
			$mbr->description = false;
			
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
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		}
		// Whatever method of removing the previous VM, send it
		echo curl_exec($ch) . PHP_EOL;
		
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
	}
}

echo "ZeroTier config complete";
unlink("/etc/nikolateslax/zerotier.json");
unlink("/tmp/nikolateslax-zerotier");