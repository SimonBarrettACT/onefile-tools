<?php

/**
 * @param array multidimensional 
 * @param string $search_value The value to search for, ie a specific 'Taylor'
 * @param string $key_to_search The associative key to find it in, ie first_name
 * @param string $other_matching_key The associative key to find in the matches for employed
 * @param string $other_matching_value The value to find in that matching associative key, ie true
 * 
 * @return array keys, ie all the people with the first name 'Taylor' that are employed.
 */
 function search_users($dataArray, $mulitiple, $search_value, $key_to_search, $other_matching_value = null, $other_matching_key = null) {
    // This function will search the revisions for a certain value
    // related to the associative key you are looking for.
    $keys = array();
    foreach ($dataArray as $key => $cur_value) {
        if (strtolower($cur_value[$key_to_search]) == strtolower($search_value)) {
            if (isset($other_matching_key) && isset($other_matching_value)) {
                if (strtolower($cur_value[$other_matching_key]) == strtolower($other_matching_value)) {
                    if ($mulitiple):
                        $keys[] = $key;
                    else:
                        return $key;
                    endif;
                }
            } else {
                // I must keep in mind that some searches may have multiple
                // matches and others would not, so leave it open with no continues.
                if ($mulitiple):
                    $keys[] = $key;
                else:
                    return $key;
                endif;
            }
        }
    }
    return $keys;
}