<?php
/**
 * @package Show Local Url Titles NG
 * @file Mod-ShowLocalUrlTitles.php
 * @author digger @ http://mysmf.ru
 */

/**
 * Load all needed hooks
 */
function loadShowLocalUrlTitlesHooks()
{
    add_integration_function('integrate_admin_areas', 'addShowLocalUrlTitlesAdminArea', false);
    add_integration_function('integrate_modify_modifications', 'addShowLocalUrlTitlesAdminAction', false);
}


/**
 * Add mod admin area
 * @param $admin_areas
 */
function addShowLocalUrlTitlesAdminArea(&$admin_areas)
{
    global $txt;
    loadLanguage('ShowLocalUrlTitles/');

    $admin_areas['config']['areas']['modsettings']['subsections']['show_local_url_titles'] = array($txt['ShowLocalUrlTitles_tab']);
}


/**
 * Add mod admin action
 * @param $subActions
 */
function addShowLocalUrlTitlesAdminAction(&$subActions)
{
    $subActions['show_local_url_titles'] = 'addShowLocalUrlTitlesAdminSettings';
}


/**
 * Add mod settings area
 * @param bool $return_config
 * @return array
 */
function addShowLocalUrlTitlesAdminSettings($return_config = false)
{
    global $txt, $scripturl, $context;
    loadLanguage('ShowLocalUrlTitles/');

    $context['page_title'] = $context['settings_title'] = $txt['ShowLocalUrlTitles_heading'];
    $context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=show_local_url_titles';

    $config_vars = array(
        array('check', 'ShowLocalUrlTitles_parsebbc', 'subtext' => $txt['ShowLocalUrlTitles_parsebbc_sub']),
        array('check', 'ShowLocalUrlTitles_posting', 'subtext' => $txt['ShowLocalUrlTitles_posting_sub']),
        array('check', 'ShowLocalUrlTitles_autolink', 'subtext' => $txt['ShowLocalUrlTitles_autolink_sub']),
        array('check', 'ShowLocalUrlTitles_remove_re'),
        /*
        '',
        array(
            'message',
            'ShowLocalUrlTitles_parse_existing',
            'text_label' => '<a href="' . $scripturl . '?action=admin;area=modsettings;sa=parsetitles;' . $context['session_var'] . '=' . $context['session_id'] . '" onClick="return confirm(\'' . $txt['ShowLocalUrlTitles_parse_existing_confirmation'] . '\');">' . $txt['ShowLocalUrlTitles_parse_existing'] . '</a>'
        )
        */
    );

    if ($return_config) {
        return $config_vars;
    }

    if (isset($_GET['save'])) {
        checkSession();
        saveDBSettings($config_vars);
        redirectexit('action=admin;area=modsettings;sa=show_local_url_titles');
    }

    prepareDBSettingContext($config_vars);
}
