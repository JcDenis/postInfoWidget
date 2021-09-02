<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of postInfoWidget, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2021 Jean-Christian Denis and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {
    return null;
}

$this->registerModule(
    'postInfoWidget',
    'Show Entry informations on a widget',
    'Jean-Christian Denis, Pierre Van Glabeke',
    '0.6.1',
    [
        'permissions' => 'usage,contentadmin',
        'type' => 'plugin',
        'dc_min' => '2.19',
        'support' => 'http://forum.dotclear.org/viewtopic.php?pid=332974#p332974',
        'details' => 'http://plugins.dotaddict.org/dc2/details/postInfoWidget',
        'repository' => 'https://raw.githubusercontent.com/JcDenis/postInfoWidget/master/dcstore.xml'
    ]
);