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
		radius		=> $_GET['radius']
		offset		=> $_GET['offset'],
	);

	//Init MODX
	require_once '../core/config/config.inc.php';
	require_once MODX_CORE_PATH.'model/modx/modx.class.php';
	$modx = new modX();
	$modx->initialize('web');
	$modx->getService('error','error.modError', '', '');

	//Init required package
	$modx->addPackage($search['packageName'], MODX_CORE_PATH . 'components/'. $search['packageName'] .'/' . 'model/','modx_');
	$modx->addPackage('offers', MODX_CORE_PATH . 'components/offers/' . 'model/','modx_');
	$modx->addPackage('counties', MODX_CORE_PATH . 'components/counties/' . 'model/','modx_');
	$modx->addPackage('countries', MODX_CORE_PATH . 'components/countries/' . 'model/','modx_');

	//Null check for search criteria
	if ($search['term'] != null) {
		if ($search['by'] == 'postCode') {
			$criteria = array(
				$search['by'].':LIKE' => '%'. $search['term'] .'%'
			);
		}
		else {
			$criteria = array(
				$search['by'].':LIKE' => '%'. $search['term'] .'%'
			);
		}
	}

	//Build query
	$q = $modx->newQuery($search['className']);
	$q->select($modx->getSelectColumns($search['className'], $search['className']));
	$q->innerJoin('County', 'County', $search['className'].'.countyId=County.id');
	$q->innerJoin('Country', 'Country', $search['className'].'.countryId=Country.id');
	$q->select($modx->getSelectColumns('County', 'County', 'County.'));
	$q->select($modx->getSelectColumns('Country', 'Country', 'Country.'));
	$q->where($criteria);
	$q->sortby('name','ASC');
	$q->limit(20, $search['offset']);

/*	
	//If postcode, or town
	if ($search['by'] == 'postCode' || $search['by'] == 'county' || $search['by'] == 'town') {
		//Search distance in miles
		$dist = $search['radius'];

		$address = urlencode($search['term']);
		$link = "http://maps.googleapis.com/maps/api/geocode/json?address=$address&sensor=false";

		$gps = file_get_contents($link);
        	$gps = $modx->fromJSON($gps);
        	$gps = $gps['results'][0];
        	$lat = $gps['geometry']['location']['lat'];
        	$lng = $gps['geometry']['location']['lng'];
        
        	$lng1 = $lng - $dist / (cos(deg2rad($lat)) * 69);
		$lng2 = $lng + $dist / (cos(deg2rad($lat)) * 69);
		$lat1 = $lat - ($dist / 69);
		$lat2 = $lat + ($dist / 69);

		$tableName = $modx->getTableName('modx_' . $search['packageName']);

		$query = "SELECT name, addOne, addTwo, addThree, county, pc, country, url, telephone, monday, tuesday, wednesday, thursday, friday, saturday, sunday, availability, exclusions, description, photo, published, deleted,
		    3956 * 2 * ASIN( SQRT(POWER(SIN((abs({$modx->quote($lat)}) - abs(lat)) * pi() / 180 / 2), 2) + 
		    COS(abs({$modx->quote($lat)}) * pi() / 180) * COS(abs(lat) * pi() / 180) * 
		    POWER(SIN(({$modx->quote($lng)} - lng) * pi() / 180 / 2), 2))) AS distance 
		    FROM {$tableName} WHERE (lat BETWEEN {$modx->quote($lat1)} AND {$modx->quote($lat2)} 
		    AND lng BETWEEN {$modx->quote($lng1)} AND {$modx->quote($lng2)}) AND (published = 1 $catsql) HAVING distance < {$dist} ORDER BY distance ASC";

		$c = new xPDOCriteria($modx, $query);
		$q = $modx->getIterator($search['className'], $c);
	}
*/

	$offers = $modx->getCollection($search['className'], $q);

	$results = array();

	foreach($offers as $result) {
		$i = array (
			name			=> $result->get('name'),
			addOne			=> $result->get('addressLineOne'),
			addTwo			=> $result->get('addressLineTwo'),
			addThree		=> $result->get('addressLineThree'),
			county			=> $result->get('County.name'),
			pc			=> $result->get('postCode'),
			country		=> $result->get('Country.name'),
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