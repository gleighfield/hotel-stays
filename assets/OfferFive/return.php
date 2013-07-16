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
	$criteria = array(
		$search['by'].':LIKE' => '%'. $search['term'] .'%'
	);

	//Build query
	$q = $modx->newQuery($search['className']);
	$q->select($modx->getSelectColumns($search['className'], $search['className']));
	//$q->innerJoin('Offer', 'Offer', $search['className'].'.offerId=Offer.id');
	$q->innerJoin('County', 'County', $search['className'].'.countyId=County.id');
	$q->innerJoin('Country', 'Country', $search['className'].'.countryId=Country.id');
	//$q->select($modx->getSelectColumns('Offer', 'Offer', 'Offer.'));
	$q->select($modx->getSelectColumns('County', 'County', 'County.'));
	$q->select($modx->getSelectColumns('Country', 'Country', 'Country.'));
	$q->where($criteria);
	$q->sortby('name','ASC');

	$offers = $modx->getCollection($search['className'], $q);

	$results = array();

	function in_array_r($needle, $haystack, $strict = false) {
    		foreach ($haystack as $item) {
        		if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            			return true;
        		}
    		}
    		return false;
	}

	foreach($offers as $result) {
		if (!in_array_r($result->get($search['by']), $results)) {
			$i = array (
				label			=> $result->get($search['by']),
				value			=> $result->get($search['by']),
				//vardump		=> $result->toArray()
			);
			array_push($results, $i);
		}
	}

	echo json_encode($results);
?>