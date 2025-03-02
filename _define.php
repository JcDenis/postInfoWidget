<?php
/**
 * @file
 * @brief       The plugin postInfoWidget definition
 * @ingroup     postInfoWidget
 *
 * @defgroup    postInfoWidget Plugin postInfoWidget.
 *
 * Show Entry informations on a widget.
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

$this->registerModule(
    'Entry information list',
    'Show Entry informations on a widget',
    'Jean-Christian Denis, Pierre Van Glabeke',
    '1.2.1',
    [
        'requires'    => [['core', '2.28']],
        'permissions' => 'My',
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/' . $this->id . '/issues',
        'details'     => 'https://github.com/JcDenis/' . $this->id . '/',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . $this->id . '/master/dcstore.xml',
        'date'        => '2025-03-02T17:51:31+00:00',
    ]
);
