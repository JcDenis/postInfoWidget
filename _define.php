<?php
/**
 * @brief postInfoWidget, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Jean-Christian Denis, Pierre Van Glabeke
 *
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('DC_RC_PATH') || is_null(dcCore::app()->auth)) {
    return null;
}

$this->registerModule(
    'Entry information list',
    'Show Entry informations on a widget',
    'Jean-Christian Denis, Pierre Van Glabeke',
    '1.0',
    [
        'requires'    => [['core', '2.26']],
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_USAGE,
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]),
        'type'       => 'plugin',
        'support'    => 'http://forum.dotclear.org/viewtopic.php?pid=332974#p332974',
        'details'    => 'http://plugins.dotaddict.org/dc2/details/postInfoWidget',
        'repository' => 'https://raw.githubusercontent.com/JcDenis/postInfoWidget/master/dcstore.xml',
    ]
);
