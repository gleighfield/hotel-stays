<?php
	//Sanity check for posted variables
	if (empty($_GET['offer'])) {
		//No posted class name, die.
		return false;
	}	

	$search = array(
		className	=> $_GET['offer'],
		packageName	=> lcfirst($_GET['offer']),
		term 		=> $_GET['term'],
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

	//Build query
	$q = $modx->newQuery($search['className']);
	$q->select($modx->getSelectColumns($search['className'], $search['className']));
	$q->innerJoin('County', 'County', $search['className'].'.countyId=County.id');
	$q->innerJoin('Country', 'Country', $search['className'].'.countryId=Country.id');
	$q->select($modx->getSelectColumns('County', 'County', 'County.'));
	$q->select($modx->getSelectColumns('Country', 'Country', 'Country.'));
	$q->where(array(array(
   		'monday' => $search['term'],
		'tuesday' => $search['term'],
		'wednesday' => $search['term'],
		'thursday' => $search['term'],
		'friday' => $search['term'],
		'saturday' => $search['term'],
   		'sunday' => $search['term']
	)),xPDOQuery::SQL_OR);
	$q->sortby('name','ASC');
	$q->limit(20, $search['offset']);

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