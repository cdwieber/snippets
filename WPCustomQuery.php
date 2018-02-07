<?php
/**
 * ===WORDPRESS===
 * A client needed to be able to generate a custom WP_Query object based on 
 * an uncertain amount and type of input criteria. I had to come up with a way
 * to handle this, and it does provided the front-end input fields follow
 * specific naming conventions, which I provided
 */

function build_custom_query($criteria) {
	//Instantiate the arguments array for WP_Query
		$args = array( 'post_type' => 'listing',
		               'posts_per_page' => -1,
			);


	//Loop through fields provided and add them to an array provided not null
	if ($criteria) {
		//Specify that we want ALL conditions to be true
		$args['meta_query'] = array('relation' => 'AND');
		foreach($criteria as $key => $value) {
			switch($value) {
				//Do nothing if the var is null
				case null || 0 :
					break;
				//is it a numeric value?
				case is_numeric($value) :
					//if so, append the proper array to the query
					$args['meta_query'][] = array(
						'compare' => strpos($key, 'min') ? '>=' : '<=', //Is this key wanting a min or max value?
						'key' => str_replace(['min-', 'max-'], '', $key), //trim the key down to the database field name
						'value' => (int)$value,
						'type' => 'NUMERIC',
					);
					break;
				default :
					//if not, it's gotta be a string.
					$args['meta_query'][] = array(
						'key' => $key,
						'value' => $value,
						'compare' => 'LIKE'
					);
			}
		}
	}
	//The client additionally wanted a boolean flag that marks certain posts as "Featured".
	//This could be hard coded, and a bit of a hack allows for display in all circumstances
	//(i.e. Whether they are featured or not, but we ORDERBY featured)
	//Nest an additional query that displays featured listings first
	$args['meta_query']['meta_query'] = array('relation' => 'OR');
	$args['meta_query']['meta_query'][] = array ('key' => 'featured', 'compare' => 'EXISTS');
	$args['meta_query']['meta_query'][] = array ('key' => 'featured', 'compare' => 'NOT EXISTS');

	$args['orderby'] = array('featured' => 'ASC');

	return $args;
}