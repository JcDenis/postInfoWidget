<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of postInfoWidget, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2015 Jean-Christian Denis and contributors
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
	/* Name */
	"postInfoWidget",
	/* Description*/
	"Show Entry informations on a widget",
	/* Author */
	"Jean-Christian Denis, Pierre Van Glabeke",
	/* Version */
	'0.5.1',
	array(
		'permissions' => 'usage,contentadmin',
		'type' => 'plugin',
		'dc_min' => '2.7',
		'support' => 'http://forum.dotclear.org/viewtopic.php?pid=332974#p332974',
		'details' => 'http://plugins.dotaddict.org/dc2/details/postInfoWidget'
	)
);