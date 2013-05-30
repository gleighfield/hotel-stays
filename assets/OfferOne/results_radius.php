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
		term 		=> $_GET['term'],
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

	//Search distance in miles
	$dist = $search['radius'];

	$address = urlencode($search['term']);
	$link = "http://maps.googleapis.com/maps/api/geocode/json?address=$address&sensor=false";
	
	$gps = file_get_contents($link);

        $gps = $modx->fromJSON($gps);

       $gps = $gps['results'][0];
       $lat = $gps['geometry']['location']['lat'];
       $lng = $gps['geometry']['location']['lng'];

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

	$offers = $modx->getIterator($search['className'], $c);

	$results = array();

	foreach($offers as $result) {
		$i = array (
			name			=> $result->get('name') . ' (' . substr($result->get('distance'), 0,4) . ' miles away)',
			addOne			=> $result->get('addressLineOne'),
			addTwo			=> $result->get('addressLineTwo'),
			addThree		=> $result->get('addressLineThree'),
			county			=> $result->get('CountyName'),
			pc			=> $result->get('postCode'),
			country		=> $result->get('CountryName'),
			url			=> $result->get('url'),
			telephone		=> $result->get('telephoneNumber'),
			monday			=> $modx->getObject('Offer', $result->get('monday'))->get('name'),
			tuesday		=> $modx->getObject('Offer', $result->get('tuesday'))->get('name'),
			wednesday		=> $modx->getObject('Offer', $result->get('wednesday'))->get('name'),
			thursday		=> $modx->getObject('Offer', $result->get('thursday'))->get('name'),
			friday			=> $modx->getObject('Offer', $result->get('friday'))->get('name'),
			saturday		=> $modx->getObject('Offer', $result->get('saturday'))->get('name'),
			sunday			=> $modx->getObject('Offer', $result->get('sunday'))->get('name'), 
			availability		=> htmlentities($result->get('availability')),
			exclusions		=> htmlentities($result->get('exclusions')),
			description		=> htmlentities($result->get('description')),
			photo			=> $result->get('photo'),
			published		=> $result->get('published'),
			deleted		=> $result->get('deleted')
		);

		array_push($results, $i);
	}

	header('Content-type: application/json');
	echo json_encode($results);

?>