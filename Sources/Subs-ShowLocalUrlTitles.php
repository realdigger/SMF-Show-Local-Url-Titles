<?php
/**********************************************************************************
 * Subs-ShowLocalUrlTitles.php                                                     *
 ***********************************************************************************
 * Show Local Url Titles                                                           *
 * SMF Modification by Nathaniel Baxter (nathaniel.baxter@gmail.com)               *
 * =============================================================================== *
 * Software version:        Show Local Url Titles 1.2                             *
 * Software by:                Nathaniel Baxter (nathaniel.baxter@gmail.com)         *
 * Copyright (c) 2011, Nathaniel Baxter (nathaniel.baxter@gmail.com)               *
 * All rights reserved.                                                            *
 ***********************************************************************************
 * See the included LICENSE file for details of the license for this file and mod. *
 **********************************************************************************/

if (!defined('SMF')) {
    die('Hacking attempt...');
}


/**
 * This function is the workhorse, it gets the title for a local url.
 * @param $url
 * @return mixed
 */
function get_local_url_title($url)
{
    global $smcFunc, $scripturl, $context, $modSettings, $cache_titles;

    // Here is a list of the comparisons that will be made by this mod. They are in order of importance (the first entry will be compared first, and so on).
    // You can increase the number of urls that are converted to their titles, by adding entries to this array, using the format below
    //	'get_var' => array(
    //		'data' => 'db_column as url_title',
    //		'tables' => '{db_prefix}db_table',
    //		'index' => 'db_index_column',
    //		'index_type' => 'db_index_type',
    //		'where' => 'db_where',
    //		'required_vars' => array('var1' => 'val1', 'var2' => 'val2'... 'varn' => 'valn'),
    //	),
    //
    //	get_var: The $_GET variable that will be used as an index, we will search the db_index_column to match this variable.
    //	db_column: The database column that contains the relevant title data.
    //	db_table: The database table that contains the relevant information.
    //	db_index_type: (Optional) The type of the database column. If unspecified, it will be assumed to be of type string.
    //	db_where: (Optional) Any extra conditions on the query can go here.
    //	required_vars: (Optional) A list of extra $_GET variables that must be set. If a value is specified (ie. non empty string $valn) then the value of the $_GET[$varn] will be compared to $valn.
    //
    $comparisons = array(
        'msg' => array(
            'data' => 'subject as url_title',
            'tables' => '{db_prefix}messages',
            'index' => 'id_msg',
            'index_type' => 'int',
            'required_vars' => array('topic' => ''),
        ),
        'topic' => array(
            'data' => 'm.subject as url_title',
            'tables' => '{db_prefix}topics as t 
							INNER JOIN {db_prefix}messages as m ON (m.id_msg = t.id_first_msg)',
            'index' => 't.id_topic',
            'index_type' => 'int',
        ),
        'board' => array(
            'data' => 'name as url_title',
            'tables' => '{db_prefix}boards',
            'index' => 'id_board',
            'index_type' => 'int',
        ),
        'u' => array(
            'data' => '	member_name as url_title',
            'tables' => '{db_prefix}members',
            'index' => 'id_member',
            'index_type' => 'int',
            'required_vars' => array('action' => 'profile'),
        ),
        // Add any new comparisons below here.
    );

    // These are the operators that assign a particular value to a variable. Eg. "topic=34"
    $assignment_operators = array('=', ',');

    // These are the operators that separate two variable assignments. Eg. "topic=2;happy=true"
    $separation_operators = array(';', '/');

    // The operators signal the end of the main part of the url query string. Eg. "topic=314#msg1618"
    // Only used by the 'msg' comparison.
    $end_operators = array('#', '.');

    // Firstly strip the scripturl of 'www.' and 'http://'.
    $location = $scripturl;
    if (substr($location, 0, 7) == 'http://') {
        $location = substr($location, 7);
    }
    if (substr($location, 0, 4) == 'www.') {
        $location = substr($location, 4);
    }

    // Do we have a local url? If not, then just pass the url back.
    if (!preg_match('/(http:\/\/)(www\.)?(' . addcslashes($location,
            '/`\=+*{},[]^$.-!') . ')(.)*$/i' . ($context['utf8'] ? 'u' : ''), $url, $match)
    ) {
        return $url;
    }

    // We only need the part after the boardurl for the message searching.
    $after_url = substr($url, strlen($location) + 7 + (($match[2] == 'www.') ? 4 : 0));

    // Make sure that its a valid local url, otherwise just pass the url back.
    if (!in_array(substr($after_url, 0, 1), array('/', '?'))) {
        return $url;
    }
    $query_string = substr($after_url, 1);

    // Got a cached title?
    if (!empty($modSettings['ShowLocalUrlTitles_caching']) && !empty($cache_titles[$query_string])) {
        return $cache_titles[$query_string];
    }

    // Get the query variables.
    $variables = parse_url_query_variables($query_string, $separation_operators, $assignment_operators);

    // Do each of the comparisons.
    foreach ($comparisons as $var => $query) {
        // Missing any required variables?
        if (!empty($query['required_vars'])) {
            foreach ($query['required_vars'] as $required_var => $val) {
                if (!isset($variables[$required_var]) || (!empty($val) && $variables[$required_var] != $val)) {
                    continue 2;
                }
            }
        }

        // Check for a message title, if it makes sense to. Ie. the url is for a specific message in a topic.
        if ($var == 'msg') {
            $index = 0;
            // Try to match the msg number.
            foreach ($end_operators as $operator) {
                if (($pos = strpos($after_url, $operator . 'msg')) && ($id_msg = substr($after_url, $pos + 4))) {
                    $index = intval($id_msg);
                }
            }

            // No luck? Failure then.
            if (empty($index)) {
                continue;
            }
        } // Get the index value, if we can.
        elseif (empty($variables[$var])) {
            continue;
        } else {
            $index = $variables[$var];
        }

        // Sanitize any important input types!
        if (!empty($query['index_type']) && $query['index_type'] == 'int') {
            $index = intval($index);
        }

        // Get us some data!
        $request = $smcFunc['db_query']('', '
			SELECT {raw:data}
			FROM ' . $query['tables'] . '
			WHERE {raw:index} = {' . (!empty($query['index_type']) ? $query['index_type'] : 'string') . ':index_value}' . (empty($query['where']) ? '' : '
				AND {raw:where}') . '
			LIMIT 1',
            array(
                'data' => $query['data'],
                'index' => $query['index'],
                'index_value' => $index,
                'where' => !empty($query['where']) ? $query['where'] : '',
            )
        );
        $data = $smcFunc['db_fetch_assoc']($request);
        $smcFunc['db_free_result']($request);

        // If we have have title, then we're done.
        if (!empty($data) && !empty($data['url_title'])) {
            // Do we need to cache the title?
            if (!empty($modSettings['ShowLocalUrlTitles_caching'])) {
                $cache_titles[$query_string] = $data['url_title'];
            }

            return $data['url_title'];
        }
    }

    // Return the untouched url if we have no matches.
    return $url;
}


/**
 * Provide the parser functionality for the url and tag respectively, used by the preparsecode function.
 * @param $data
 * @return string
 */
function get_local_url_title__callback($data)
{
    // Should we parse the url? Only do this for the unparsed_content type, or the unparsed_equals_content type, where the content and the url are the same.
    if (empty($data[1]) || substr($data[1], 1) == $data[2]) {
        return '[url=' . $data[2] . ']' . get_local_url_title($data[2]) . '[/url]';
    } // Otherwise give back the text we were given.
    else {
        return '[url' . $data[1] . ']' . $data[2] . '[/url]';
    }
}


/**
 * Provide the parser functionality for the iurl and tag respectively, used by the preparsecode function.
 * @param $data
 * @return string
 */
function get_local_iurl_title__callback($data)
{
    // Should we parse the url? Only do this for the unparsed_content type, or the unparsed_equals_content type, where the content and the url are the same.
    if (empty($data[1]) || substr($data[1], 1) == $data[2]) {
        return '[iurl=' . $data[2] . ']' . get_local_url_title($data[2]) . '[/iurl]';
    } // Otherwise give back the text we were given.
    else {
        return '[iurl' . $data[1] . ']' . $data[2] . '[/iurl]';
    }
}

/**
 * Gets the query variables from a query string.
 * @param $query
 * @param $separators
 * @param $assignment_operators
 * @return array
 */
function parse_url_query_variables($query, $separators, $assignment_operators)
{
    $variables = array();
    $vars = array();
    // Make a list of raw variable data to process, entries should look like this "var=val".
    foreach ($separators as $separator) {
        if (strpos($query, $separator) !== false) {
            $vars += explode($separator, $query);
        }
    }

    // Could there be just one variable?
    if (empty($vars)) {
        $vars[] = $query;
    }

    // Make the list of variables.
    foreach ($vars as $var) {
        foreach ($assignment_operators as $operator) {
            $temp = explode($operator, $var);

            if (empty($temp) || count($temp) < 2) {
                continue;
            } else {
                $variables[$temp[0]] = $temp[1];
            }
        }
    }
    return $variables;
}

/**
 * This function performs the conversion of any local urls in existing posts.
 */
function ParseLocalURLTitles()
{
    global $modSettings, $smcFunc, $context, $sourcedir, $cache_titles;

    // Are you meant to be here?
    isAllowedTo('admin_forum');

    // Check their session.
    checkSession('request');

    // Load the required parsing functions.
    require_once($sourcedir . '/Subs-Post.php');

    // These variables force the parsing functions to parse the urls as required.
    $modSettings['ShowLocalUrlTitles_posting'] = true;
    $modSettings['ShowLocalUrlTitles_autolink'] = true;

    // Load the cache.
    $modSettings['ShowLocalUrlTitles_caching'] = true;
    $cache_titles = cache_get_data('show_local_url_titles');
    $cache_titles = !empty($cache_titles) ? $cache_titles : array();

    // Some starting values.
    $context['msg'] = empty($_REQUEST['msg']) ? 0 : (int)$_REQUEST['msg'];
    $context['start_time'] = time();
    $context['first_step'] = !isset($_REQUEST[$context['session_var']]);
    $context['last_step'] = false;

    // Use the generic "not done" template.
    $context['sub_template'] = 'not_done';
    $context['continue_post_data'] = '';
    $context['continue_countdown'] = 3;

    // Get the maximum message id.
    $request = $smcFunc['db_query']('', '
		SELECT MAX(id_msg)
		FROM {db_prefix}messages',
        array()
    );
    list($max_msg) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Parse one message at a time.
    while ($context['msg'] < $max_msg) {
        // Get the next message.
        $request = $smcFunc['db_query']('', '
			SELECT body, id_msg
			FROM {db_prefix}messages
			WHERE id_msg >= {int:next_msg}
				ORDER BY id_msg
			LIMIT 1',
            array(
                'next_msg' => $context['msg'] + 1,
            )
        );
        list($body, $context['msg']) = $smcFunc['db_fetch_row']($request);
        $smcFunc['db_free_result']($request);

        // Parse the body.
        // Note: This could be improved, by avoiding use of the preparsing functions.
        $body = un_preparsecode($body);
        preparsecode($body);

        // Save the message.
        $smcFunc['db_query']('', '
			UPDATE {db_prefix}messages
			SET body = {string:body}
			WHERE id_msg = {int:id_msg}
			LIMIT 1',
            array(
                'body' => $body,
                'id_msg' => $context['msg'],
            )
        );

        // After ten seconds interrupt.
        if (time() - $context['start_time'] > 10) {
            // Update the cache.
            cache_put_data('show_local_url_titles', $cache_titles);

            // Calculate an approximation of the percentage done.
            $context['continue_percent'] = round(100 * $context['msg'] / $modSettings['totalMessages'], 1);
            $context['continue_get_data'] = '?action=admin;area=modsettings;sa=parsetitles;msg=' . $context['msg'] . ';' . $context['session_var'] . '=' . $context['session_id'];
            return;
        }
    }

    // If we're here, we must be done.
    $context['continue_percent'] = 100;
    $context['continue_get_data'] = '?action=admin;area=modsettings';
    $context['last_step'] = true;
    $context['continue_countdown'] = -1;

    // Kill the cache, otherwise it will be storing a lot of data!
    cache_put_data('show_local_url_titles', null);
}

/**
 * Autolinks any urls in the text given. Extremely useful, but annoying to code all the same.
 * Assumes that the text contains no "code" bbc.
 * @param $text
 * @return string
 */
function autolink_urls($text)
{
    global $context;

    // Don't attempt to autolink anything within these tags.
    $no_autolink_tags = array(
        'url',
        'iurl',
        'ftp',
        'email',
    );

    $final_pos = strlen($text);
    $parsed_text = '';
    $pos = -1;
    $last_pos = 0;
    // Parse text, while there is still text to parse.
    while ($pos < $final_pos && $last_pos < $final_pos) {
        // Assume that we are going to parse the rest of the text.
        $pos = $final_pos;

        // Try to find an earlier position, that starts a block of text we cannot parse.
        $tag = '';
        foreach ($no_autolink_tags as $no_autolink_tag) {
            if (($temp_pos = strpos($text, '[' . $no_autolink_tag, $last_pos + 1)) !== false && $temp_pos < $pos) {
                $pos = $temp_pos;
                $tag = $no_autolink_tag;
            }
        }

        // Get us the relevant data to parse.
        $data = substr($text, $last_pos, $pos - $last_pos);

        // Parse any urls that we can.
        if (strlen($data) > 4 && (!isset($disabled['url']) && (strpos($data, '://') !== false || strpos($data,
                        'www.') !== false) && strpos($data, '[url') === false)
        ) {
            // Get rid of quotes.
            $data = strtr($data, array(
                '&#039;' => '\'',
                '&nbsp;' => $context['utf8'] ? "\xC2\xA0" : "\xA0",
                '&quot;' => '>">',
                '"' => '<"<',
                '&lt;' => '<lt<'
            ));

            // Only do this if the preg survives.
            if (is_string($result = preg_replace(array(
                '~(?<=[\s>\.(;\'"]|^)((?:http|https)://[\w\-_%@:|]+(?:\.[\w\-_%]+)*(?::\d+)?(?:/[\w\-_\~%\.@,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i',
                '~(?<=[\s>(\'<]|^)(www(?:\.[\w\-_]+)+(?::\d+)?(?:/[\w\-_\~%\.@,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i'
            ), array(
                '[url]$1[/url]',
                '[url=http://$1]http://$1[/url]'
            ), $data))) {
                $data = $result;
            }

            // Put back the quotes.
            $data = strtr($data, array(
                '\'' => '&#039;',
                $context['utf8'] ? "\xC2\xA0" : "\xA0" => '&nbsp;',
                '>">' => '&quot;',
                '<"<' => '"',
                '<lt<' => '&lt;'
            ));
        }

        // Find where we can start parsing again.
        if (!empty($tag) && ($last_pos = strpos($text, '[/' . $tag . ']', $pos)) !== false) {
            $last_pos += strlen('[/' . $tag . ']');
        } // Otherwise we've reached the end of the line...
        else {
            $last_pos = $final_pos;
        }

        // Give back any parsed and unparsed content.
        $parsed_text .= $data . substr($text, $pos, $last_pos - $pos);
    }

    return $parsed_text;
}

?>