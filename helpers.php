<?php 

//Functions ////////////////////////////////////////////////////


//Define autoloader 
function __autoload($className) { 

	$className = explode('\\', $className);
	$filePath = './classes/' . end($className) . '.class.php';

    if (file_exists($filePath)) { 
        require_once $filePath; 
        return true; 
    } 

    return false; 
} 


function redirectIfGuest() {
	// if(empty($_SESSION['AdminID'])) {
	// 	header("location: /index.php?msg=timeout");
	// 	exit;
	// } 
}


function getFromRequest($name) {

	$value = !empty($_GET[$name]) ? $_GET[$name] : '';

	if (empty($value)) {
		$value = !empty($_POST[$name]) ? $_POST[$name] : '';
	}

	return $value;
}


function getIncomingString($name, $default = "") {

	$value = getFromRequest($name);

	if(!is_string($value) || empty(trim($value))) {
		return $default;
	}

	return htmlspecialchars($value, ENT_QUOTES);
}


function getIncomingInt($name, $default = 0) {

	$value = getFromRequest($name);

	if (!is_numeric($value) || empty($value)) {
		return $default;
	}

	return (int)$value;	
}


function getIncomingJson() {
	$jsonData = json_decode(trim(file_get_contents('php://input')), true);

	return filter_var_array($jsonData, FILTER_SANITIZE_STRING); 
}


function getPhoneAreaCode($phone) {
	$phone = preg_replace('/[^0-9]/', '', $phone);

	return substr($phone, 0, 3);
}


function getPhone($phone) {

	if(stripos($phone, 'ext') !== false) {
		$phone = substr($phone, 0, strpos($phone, 'ext'));
	}

	$phone = preg_replace('/[^0-9]/', '', $phone);
	return substr($phone, 3);
}


function getStreetNumber($address) {

	$strToRemove = array("-", "_", "#", ")", "(");
	$address = trim(str_replace($strToRemove, " ", $address));

	$addressArray = explode(' ', $address);

	return  $addressArray[0];
}


function getStreetName($address) {

	$strToRemove = array("-", "_", "#", ")", "(");
	$address = trim(str_replace($strToRemove, " ", $address));

	$addressArray = explode(' ', $address);
	array_shift($addressArray);

	return implode(' ', $addressArray);
}


function splitAddress($name, $prefix = '') {
	$pos = strpos(trim($name), ' ', 15);

	if ($pos === false) {
		return array(
		 	$prefix . '0' => $name,
		 	$prefix . '1' => null
		);
	}

	return array(
		$prefix . '0' => substr($name, 0, $pos + 1),
		$prefix . '1' => substr($name, $pos)
	);
}



function getAdditionalAddressLine($address) {
	$strToRemove = array("-", "_", "#", ")", "(", ",");
	$address = trim(str_replace($strToRemove, " ", $address));

	return $address;
}


function getPostalCode($postalCode) {
	$strToRemove = array("-", "_", "#", ")", "(", ",", " ");
	$postalCode = trim(str_replace($strToRemove, "", $postalCode));

	return $postalCode;
}


function getCorrectCityName($city) {
	$strToRemove = array("Head Office", 
						"Distribution Centre",
						"Warehouse", 
						"Band Repair Office", 
						")", 
						"("
					);

	$city = str_replace($strToRemove, "", $city);

	$city = str_replace("Web Store", "Pickering", $city);

	return trim($city);
}


function getAdminLocationID($locationID = 0) {

	if(empty($_SESSION['AdminID'])) {
		return $locationID;
	}

	$db = new Purolator\Database();

	$result = $db->query("SELECT LocationsID FROM Admin WHERE AdminID = " . $_SESSION['AdminID']);
	if($result) {
		$row = $result->fetch_assoc();
		$locationID = $row['LocationsID'];
	}

	//Set Location to WebStore if it is Pickering (Head Office)
	$locationID = ($locationID == 64) ? 76 : $locationID;

	return $locationID;
}


function getShipperLocationID($locationID = 0) {

	$db = new Purolator\Database();

	$result = $db->query("SELECT LocationsID FROM Admin WHERE ShippingAccess = 1 AND AdminID = " . $_SESSION['AdminID']);
	if($result) {
		$row = $result->fetch_assoc();
		$locationID = $row['LocationsID'];
	}

	//Set Location to WebStore if it is Pickering (Head Office)
	$locationID = ($locationID == 64) ? 76 : $locationID;

	return $locationID;
}



function setShipmentVoidedInDB($ShipmentData) {
	$db = new Purolator\Database();
	
	$pin = !empty($ShipmentData['pin']) ? $ShipmentData['pin'] : '';

	if(!empty($pin)) {

		$db->query("UPDATE TrackingInfo SET  Void = 1 WHERE TrackingCode = '" . $pin . "' LIMIT 1");
	}
}

