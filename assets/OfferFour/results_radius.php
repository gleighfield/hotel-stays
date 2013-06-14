<?php	
	//Sanity check for posted variables
	if (empty($_GET['offer'])) {
		//No posted class name, die.
		return false;
	}

	$search = array(
		className	=> $_GET['offer'],
		packageName	=> lcfirst($_GET['offer']),
		by		=> $_GET['by'],
		term 		=> strtoupper($_GET['term']),
		radius		=> $_GET['radius'],
		offset		=> $_GET['offset'],
	);

	//Init MODX
	require_once '../../core/config/config.inc.php';
	require_once MODX_CORE_PATH.'model/modx/modx.class.php';
	$modx = new modX();
	$modx->initialize('web');
	$modx->getService('error','error.modError', '', '');

	//Init required package
	$modx->addPackage($search['packageName'], MODX_CORE_PATH . 'components/'. $search['packageName'] .'/' . 'model/','modx_');
	$modx->addPackage('offers', MODX_CORE_PATH . 'components/offers/' . 'model/','modx_');
	$modx->addPackage('counties', MODX_CORE_PATH . 'components/counties/' . 'model/','modx_');
	$modx->addPackage('countries', MODX_CORE_PATH . 'components/countries/' . 'model/','modx_');
	$modx->addPackage('postcodes', MODX_CORE_PATH . 'components/postcodes/' . 'model/','modx_');

	//Search distance in miles
	$dist = $search['radius'];

	$postcode = $modx->getObject('Postcode', array(
		'name' => $search['term']
	));

       $lat = $postcode->get('lat');
       $lng = $postcode->get('lng');

	$tableName = $modx->getTableName($search['className']);
	$countyName = $modx->getTableName('County');
	$countryName = $modx->getTableName('Country');
	$offerName = $modx->getTableName('Offer');

	$query = "SELECT 	
		$tableName.name, 
		$tableName.addressLineOne, $tableName.addressLineTwo, $tableName.addressLineThree, $tableName.countyId, $tableName.postCode, $tableName.countryId, 
		$tableName.url, 
		$tableName.telephoneNumber, 
		$tableName.monday, $tableName.tuesday, $tableName.wednesday, $tableName.thursday, $tableName.friday, $tableName.saturday, $tableName.sunday, 
		$tableName.availability, $tableName.exclusions, $tableName.description, 
		$tableName.photo, $tableName.published, $tableName.deleted, 
		$tableName.lng, $tableName.lat,
		$countyName.name AS CountyName,
		$countryName.name AS CountryName,
		(3959 * acos(cos(radians($lat)) * cos(radians($tableName.lat)) * cos(radians($tableName.lng) - radians($lng) ) + sin(radians($lat)) * sin(radians($tableName.lat)))) AS distance 
		FROM $tableName
		INNER JOIN $countyName ON countyId = $countyName.id
     		INNER JOIN $countryName ON countryId = $countryName.id
		HAVING distance < {$dist} 
		ORDER BY distance LIMIT {$search['offset']},20";

	$c = new xPDOCriteria($modx, $query);
	
	//echo $c->toSql();
	//return false;

	$listings = $modx->getIterator($search['className'], $c);

	$offers = array(
		'6' 	=> '(Lunch) 2 courses, for 2 people for &#163;9.50',
		'7' 	=> '(Dinner) 2 courses, for 2 people for &#163;9.50',
		'8' 	=> '(Lunch) 2 courses for 2 adults for &#163;15.00',
		'9' 	=> '(Dinner) 2 courses for 2 adults &#163;15.00',
		'15' 	=> '(Lunch) 2 courses, for 2 adults/2 children &#163;15.00',
		'16' 	=> '(Dinner) 2 courses, for 2 adults/2 children &#163;15.00',
		'17' 	=> '(Lunch) 2 courses, for 2 adults/2 children &#163;20.00',
		'18' 	=> '(Dinner) 2 courses, for 2 adults/2 children &#163;20.00',
		'19' 	=> 'A starter and 3 Tapas Dishes for &#163;9.50 each',
        '23'    => 'Free Taster Session',
        '24'    => '2 for 1 sessions/lessons/entry/time',
	);

	$results = array();

	foreach($listings as $result) {
		$i = array (
            name		=> $result->get('name') . ' <span class="distanceAway">(' . substr($result->get('distance'), 0,4) . ' miles away)</span>',
            addOne		=> $result->get('addressLineOne'),
            addTwo		=> ucfirst(strtolower($result->get('addressLineTwo'))),
            addThree	=> ucfirst(strtolower($result->get('addressLineThree'))),
            county		=> ucfirst(strtolower($result->get('County.name'))),
            pc		    => $result->get('postCode'),
            country		=> ucfirst(strtolower($result->get('Country.name'))),
            url		    => $result->get('url'),
            telephone	=> $result->get('telephoneNumber'),
            monday		=> explode('||', $result->get('monday')),
            tuesday		=> explode('||', $result->get('tuesday')),
            wednesday	=> explode('||', $result->get('wednesday')),
            thursday	=> explode('||', $result->get('thursday')),
            friday		=> explode('||', $result->get('friday')),
            saturday	=> explode('||', $result->get('saturday')),
            sunday		=> explode('||', $result->get('sunday')),
            availability	=> htmlentities($result->get('availability')),
            exclusions	=> htmlentities($result->get('exclusions')),
            description	=> htmlentities($result->get('description')),
            photo		=> $result->get('photo'),
            published	=> $result->get('published'),
            deleted		=> $result->get('deleted'),
		);

		$dayOffers = array();
		
		foreach($offers as $offer => $offerKey) {
			$included = 0;
			
			$html = "<tr>";
			$html .="<td>" . $offerKey . "</td>";
			
			if (in_array($offer, $i['monday'])) {
				$html .= "<td><span class='tick'></span></td>";
				$included = 1;
			}
			else {
				$html .= "<td></td>";	
			}
			
			if (in_array($offer, $i['tuesday'])) {
				$html .= "<td><span class='tick'></span></td>";
				$included = 1;
			}
			else {
				$html .= "<td></td>";	
			}
			
			if (in_array($offer, $i['wednesday'])) {
				$html .= "<td><span class='tick'></span></td>";
				$included = 1;
			}
			else {
				$html .= "<td></td>";	
			}
			
			if (in_array($offer, $i['thursday'])) {
				$html .= "<td><span class='tick'></span></td>";
				$included = 1;
			}
			else {
				$html .= "<td></td>";	
			}
			if (in_array($offer, $i['friday'])) {
				$html .= "<td><span class='tick'></span></td>";
				$included = 1;
			}
			else {
				$html .= "<td></td>";	
			}
			if (in_array($offer, $i['saturday'])) {
				$html .= "<td><span class='tick'></span></td>";
				$included = 1;
			}
			else {
				$html .= "<td></td>";	
			}
			if (in_array($offer, $i['sunday'])) {
				$html .= "<td><span class='tick'></span></td>";
				$included = 1;
			}
			else {
				$html .= "<td></td>";	
			}
			
			$html .= "</tr>";
			
			if ($included === 1) {
				array_push($dayOffers, $html);
			}
		}

		$i['rows'] = $dayOffers;
		array_push($results, $i);
	}

	header('Content-type: application/json');
	echo json_encode($results);

?>