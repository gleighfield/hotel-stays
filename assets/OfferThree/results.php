<?php	
	//Sanity check for posted variables
	if (empty($_GET['offer'])) {
		//No posted class name, die.
		//return false;
	}

	$search = array(
		className	=> $_GET['offer'],
		packageName	=> lcfirst($_GET['offer']),
		by		=> $_GET['by'],
		term 		=> $_GET['term'],
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

	$listings = $modx->getCollection($search['className'], $q);

	$offers = array(
        '20'    => '1 night’s pitch for £1',
        '21'    => '2 nights for the price of 1',
        '22'    => '7 nights for the price of 5',
	);

	$results = array();

	foreach($listings as $result) {
		$i = array (
			name		=> $result->get('name'),
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
			deleted		=> $result->get('deleted')
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