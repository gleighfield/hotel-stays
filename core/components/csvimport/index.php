<?php

require_once('includes/header.php');

//Offers
$offers = array(
    OfferOne => "The Sun's Hotels from £10 - 4th May - 30th June 2013",
    OfferTwo => "The Sun's Meal Deals from £9.50 - 1st June - 31st July 2013",
);

$html .='
	<div id="csvImport">
		<h2>CSV Import</h2>

		<form action="" method="POST" enctype="multipart/form-data">
			<p>It is very important that you keep the structure of the csv file the same. A base CSV file is <a href="'. $csvImportPath .'includes/basecsv.csv">here</a>. If you are importing listings that do not currently have lng and lat information, limit each import to 350 listings, otherwise timeouts might occur.</p>
			<p>If you are inserting more than one offer on a single day, this is done with <strong>double pipes</strong> i.e <strong>"offerIdOne||offerIdTwo"</strong></p>
			<label for="offer">Select offer</label>
			<select name="offer" id="offer">
				<option value="empty">Please select an offer...</option>';
foreach ($offers as $k => $v) {
    $html .= '		<option value="'. $k .'">'.  htmlentities($v) .'</option>';
}
$html .='
			</select>
			<br>
			<label for="file">Select .csv file</label>
			<input type="file" name="file" id="file"/><br>
			<label for="geo">Obtain Co-ords (Slower)?</label>
			<input type="checkbox" name="geo" id="geo" value="1"><br>
			<label for="newImport">New import?</label>
			<input type="checkbox" name="newimport" id="newImport" value="1">
			<input type="hidden" name="submit" />
			<button id="import" type="submit">Import</button>
		</form>
	</div>';

//Hold posted vars
if (isset($_POST['submit'])) {

    $import = array(
        className 	=> $_POST['offer'],
        packageName	=> lcfirst($_POST['offer']),
        count		=> 0,
        status		=> $_POST['newimport'],
        geo         => $_POST['geo'],
        fileName 	=> $_FILES['file']['name'],
        fileType 	=> $_FILES['file']['type'],
        fileSize 	=> $_FILES['file']['size'],
        fileLocation 	=> $_FILES['file']['tmp_name']
    );

    //Init required package
    $modx->addPackage($import[packageName], MODX_CORE_PATH . 'components/'. $import[packageName] . '/' . 'model/','modx_');
    $offers = $modx->getCollection($import[className]);

    $listings = array();

    $csv = fopen($import['fileLocation'], 'r');

    while($line = fgetcsv($csv)) {
        //Skip header line
        if ($import[count] != 0) {
            $listing = array (
                name			=> $line[1],
                addressLineOne	=> $line[2],
                addressLineTwo	=> $line[3],
                addressLineThree	=> $line[4],
                countyId		=> $line[5],
                postCode		=> $line[6],
                countryId		=> $line[7],
                url			=> $line[8],
                telephoneNumber	=> $line[9],
                monday			=> $line[10],
                tuesday		=> $line[11],
                wednesday		=> $line[12],
                thursday		=> $line[13],
                friday			=> $line[14],
                saturday		=> $line[15],
                sunday			=> $line[16],
                availability		=> $line[17],
                exclusions		=> $line[18],
                description		=> $line[19],
                photo			=> $line[20],
                published		=> $line[21],
                deleted		=> 0,			//Forcefully setting as zero regardless of user input.
                lng			=> $line[23],
                lat			=> $line[24],
            );

            if ($import['geo'] == "1") {
                if (!empty($line[23]) && !empty($line24)) {
                    $listing['lng']	= $line[22];
                    $listing['lat']	= $line[23];
                }
                else {
                    //OK We need to get this address's co-ordinates, we know to use a postcode and a valid address line one, because we've said a valid postcode and address line one MUST be included.

                    $address = $listing['addressLineOne'];
                    if (!empty($listing['addressLineTwo'])) {
                        $address .= ', ' . $listing['addressLineTwo'];
                    }
                    if (!empty($listing['addressLineThree'])) {
                        $address .= ', ' . $listing['addressLineThree'];
                    }
                    $address .= ', '. $listing['postCode'];
                    $address=urlencode($address);

                    $link = "http://maps.googleapis.com/maps/api/geocode/json?address=$address&sensor=false";

                    $gps = file_get_contents($link);
                    $gps = $modx->fromJSON($gps);
                    $gps = $gps['results'][0];

                    $listing['lng'] = $gps['geometry']['location']['lng'];
                    $listing['lat'] = $gps['geometry']['location']['lat'];
                }
            }

//Hide the above until api for lng and lat resolved

            array_push($listings, $listing);
        }

        $import[count] ++;
    }

    if ($import['status'] == "1") {
        //Truncate the table
        $modx->exec("TRUNCATE TABLE modx_". $import[packageName]);
    }

    foreach ($listings as $listing) {
        $i = $modx->newObject($import[className], $listing);
        $i->save();
    }

    //Adjust for the removal of the header line
    $import[count] --;

    $html .= "<h3>Imported ". $import[count]. " listing(s)</h3>";
}

return $html;