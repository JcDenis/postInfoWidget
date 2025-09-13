<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postInfoWidget;

use Dotclear\App;
use Dotclear\Database\MetaRecord;
use Dotclear\Helper\Date;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\L10n;
use Dotclear\Plugin\widgets\WidgetsStack;
use Dotclear\Plugin\widgets\WidgetsElement;

/**
 * @brief       postInfoWidget widgets class.
 * @ingroup     postInfoWidget
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Widgets
{
    public static function initWidgets(WidgetsStack $w): void
    {
        $w
            ->create(
                'postinfowidget',
                __('Entry information list'),
                self::publicWidget(...),
                null,
                __('Show Entry informations on a widget')
            )
            ->addTitle(__('About this entry'))
            ->setting(
                'dt_str',
                __('Publish date text:'),
                __('Publish on %Y-%m-%d %H:%M'),
                'text'
            )
            ->setting(
                'creadt_str',
                __('Create date text:'),
                __('Create on %Y-%m-%d %H:%M'),
                'text'
            )
            ->setting(
                'upddt_str',
                __('Update date text:'),
                __('Update on %Y-%m-%d %H:%M'),
                'text'
            )
            ->setting(
                'lang_str',
                __('Language (%T = name, %C = code, %F = flag):'),
                __('Language: %T %F'),
                'text'
            )
            ->setting(
                'author_str',
                __('Author text (%T = author):'),
                __('Author: %T'),
                'text'
            )
            ->setting(
                'category_str',
                __('Category text (%T = category):'),
                __('Category: %T'),
                'text'
            );

        if (App::plugins()->moduleExists('tags')) {
            $w->get('postinfowidget')->setting(
                'tag_str',
                __('Tags text (%T = tags list):'),
                __('Tags: %T'),
                'text'
            );
        }

        $w->get('postinfowidget')
            ->setting(
                'attachment_str',
                __('Attachments text (%T = text, %D = numeric):'),
                __('Attachments: %T'),
                'text'
            )
            ->setting(
                'comment_str',
                __('Comments text (%T = text, %D = numeric):'),
                __('Comments: %T'),
                'text'
            )
            ->setting(
                'trackback_str',
                __('Trackbacks text (%T = text, %D = numeric):'),
                __('Trackbacks: %T'),
                'text'
            )
            ->setting(
                'permalink_str',
                __('Permalink text (%T = text link, %F = full link):'),
                __('%T'),
                'text'
            )
            ->setting(
                'feed',
                __('Show comment feed url'),
                1,
                'check'
            )
            ->setting(
                'navprevpost',
                __('Link to previous entry (%T = navigation text, %F = entry title):'),
                __('%T'),
                'text'
            )
            ->setting(
                'navnextpost',
                __('Link to next entry (%T = navigation text, %F = entry title):'),
                __('%T'),
                'text'
            )
            ->setting(
                'navprevcat',
                __('Link to previous entry of this category (%T = navigation text, %F = entry title):'),
                __('%T'),
                'text'
            )
            ->setting(
                'navnextcat',
                __('Link to next entry of this category (%T = navigation text, %F = entry title):'),
                __('%T'),
                'text'
            )
            ->setting(
                'style',
                __('Try to adapt style'),
                'small',
                'combo',
                [
                    __('No style')    => '-',
                    __('Small icon')  => 'small',
                    __('Normal icon') => 'normal',
                ]
            );
        /*
                $w->postinfowidget
                    ->setting(
                        'rmvinfo',
                        __('Try to remove entry information'),
                        1,
                        'check'
                    )
                    ->setting(
                        'rmvtags',
                        __('Try to remove entry tags'),
                        1,
                        'check'
                    )
                    ->setting(
                        'rmvnav',
                        __('Try to remove entry navigation'),
                        1,
                        'check'
                    );
        //*/
        # --BEHAVIOR-- postInfoWidgetAdmin
        App::behavior()->callBehavior('postInfoWidgetAdmin', $w);

        $w->get('postinfowidget')
            ->addContentOnly()
            ->addClass()
            ->addOffline();
    }

    public static function publicWidget(WidgetsElement $w): string
    {
        if (!App::blog()->isDefined()
            || $w->get('offline')
            || App::url()->type != 'post'
            || !App::frontend()->context()->__get('posts')->f('post_id')
        ) {
            return '';
        }

        $link    = '<a href="%s">%s</a>';
        $content = '';

        if ($w->get('dt_str') != '') {
            $content .= self::li(
                $w,
                'date',
                Date::str(
                    $w->get('dt_str'),
                    (int) strtotime(App::frontend()->context()->__get('posts')->f('post_dt')),
                    App::blog()->settings()->get('system')->get('blog_timezone')
                )
            );
        }

        if ($w->get('creadt_str') != '') {
            $content .= self::li(
                $w,
                'create',
                Date::str(
                    $w->get('creadt_str'),
                    (int) strtotime(App::frontend()->context()->__get('posts')->post_creadt),
                    App::blog()->settings()->get('system')->get('blog_timezone')
                )
            );
        }

        if ($w->get('upddt_str') != '') {
            $content .= self::li(
                $w,
                'update',
                Date::str(
                    $w->get('upddt_str'),
                    (int) strtotime(App::frontend()->context()->__get('posts')->f('post_upddt')),
                    App::blog()->settings()->get('system')->get('blog_timezone')
                )
            );
        }

        if ($w->get('lang_str') != '') {
            $ln        = L10n::getISOcodes();
            $lang_code = App::frontend()->context()->__get('posts')->f('post_lang') ?
                App::frontend()->context()->__get('posts')->f('post_lang') :
                App::blog()->settings()->get('system')->get('lang');
            $lang_name = $ln[$lang_code] ?? $lang_code;
            $lang_flag = file_exists(
                dirname(__DIR__) .
                '/img/flags/' .
                $lang_code . '.png'
            ) ?
                '<img src="' . App::blog()->getQmarkURL() .
                    'pf=postInfoWidget/img/flags/' .
                    $lang_code . '.png" alt="' . $lang_name . '" />' :
                '';

            $content .= self::li(
                $w,
                'lang',
                str_replace(
                    ['%T', '%C', '%F'],
                    [$lang_name, $lang_code, $lang_flag],
                    Html::escapeHTML($w->get('lang_str'))
                )
            );
        }

        if ($w->get('author_str') != '') {
            $content .= self::li(
                $w,
                'author',
                str_replace(
                    '%T',
                    App::frontend()->context()->__get('posts')->getAuthorLink(),
                    Html::escapeHTML($w->get('author_str'))
                )
            );
        }

        if ($w->get('category_str') != '' && App::frontend()->context()->__get('posts')->f('cat_id')) {
            $content .= self::li(
                $w,
                'category',
                str_replace(
                    '%T',
                    sprintf(
                        $link,
                        App::frontend()->context()->__get('posts')->__call('getCategoryURL', []),
                        Html::escapeHTML(App::frontend()->context()->__get('posts')->f('cat_title'))
                    ),
                    Html::escapeHTML($w->get('category_str'))
                )
            );
        }

        if ($w->get('tag_str') != '' && App::plugins()->moduleExists('tags')) {
            $meta = App::meta()->getMetadata([
                'meta_type' => 'tag',
                'post_id'   => App::frontend()->context()->__get('posts')->f('post_id'),
            ]);
            $metas = [];
            while ($meta->fetch()) {
                $metas[$meta->f('meta_id')] = sprintf(
                    $link,
                    App::blog()->url() .
                        App::url()->getBase('tag') . '/' .
                        rawurlencode($meta->f('meta_id')),
                    $meta->f('meta_id')
                );
            }
            if (!empty($metas)) {
                $content .= self::li(
                    $w,
                    'tag',
                    str_replace(
                        '%T',
                        implode(', ', $metas),
                        Html::escapeHTML($w->get('tag_str'))
                    )
                );
            }
        }

        if ($w->get('attachment_str') != '') {
            $nb = App::frontend()->context()->__get('posts')->__call('countMedia', []);
            if ($nb == 0) {
                $attachment_numeric = 0;
                $attachment_textual = __('no attachment');
            } elseif ($nb == 1) {
                $attachment_numeric = sprintf(
                    $link,
                    '#attachment',
                    1
                );
                $attachment_textual = sprintf(
                    $link,
                    '#attachment',
                    __('one attachment')
                );
            } else {
                $attachment_numeric = sprintf(
                    $link,
                    '#attachment',
                    $nb
                );
                $attachment_textual = sprintf(
                    $link,
                    '#attachment',
                    sprintf(__('%d attachments'), $nb)
                );
            }

            $content .= self::li(
                $w,
                'attachment',
                str_replace(
                    ['%T', '%D'],
                    [(string) $attachment_textual, (string) $attachment_numeric],
                    Html::escapeHTML($w->get('attachment_str'))
                )
            );
        }

        if ($w->get('comment_str') != '' && App::frontend()->context()->__get('posts')->__call('commentsActive', [])) {
            $nb = (int) App::frontend()->context()->__get('posts')->f('nb_comment');
            if ($nb == 0) {
                $comment_numeric = 0;
                $comment_textual = __('no comment');
            } elseif ($nb == 1) {
                $comment_numeric = sprintf(
                    $link,
                    '#comments',
                    1
                );
                $comment_textual = sprintf(
                    $link,
                    '#comments',
                    __('one comment')
                );
            } else {
                $comment_numeric = sprintf(
                    $link,
                    '#comments',
                    $nb
                );
                $comment_textual = sprintf(
                    $link,
                    '#comments',
                    sprintf(__('%d comments'), $nb)
                );
            }

            $content .= self::li(
                $w,
                'comment',
                str_replace(
                    ['%T', '%D'],
                    [(string) $comment_textual, (string) $comment_numeric],
                    Html::escapeHTML($w->get('comment_str'))
                )
            );
        }

        if ($w->get('trackback_str') != '' && App::frontend()->context()->__get('posts')->__call('trackbacksActive', [])) {
            $nb = (int) App::frontend()->context()->__get('posts')->f('nb_trackback');
            if ($nb == 0) {
                $trackback_numeric = 0;
                $trackback_textual = __('no trackback');
            } elseif ($nb == 1) {
                $trackback_numeric = sprintf(
                    $link,
                    '#pings',
                    1
                );
                $trackback_textual = sprintf(
                    $link,
                    '#pings',
                    __('one trackback')
                );
            } else {
                $trackback_numeric = sprintf(
                    $link,
                    '#pings',
                    $nb
                );
                $trackback_textual = sprintf(
                    $link,
                    '#pings',
                    sprintf(__('%d trackbacks'), $nb)
                );
            }

            $content .= self::li(
                $w,
                'trackback',
                str_replace(
                    ['%T', '%D'],
                    [(string) $trackback_textual, (string) $trackback_numeric],
                    Html::escapeHTML($w->get('trackback_str'))
                )
            );
        }

        if ($w->get('permalink_str')) {
            $content .= self::li(
                $w,
                'permalink',
                str_replace(
                    ['%T', '%F'],
                    [
                        sprintf(
                            $link,
                            App::frontend()->context()->__get('posts')->__call('getURL', []),
                            __('Permalink')
                        ),
                        App::frontend()->context()->__get('posts')->__call('getURL', []),
                    ],
                    Html::escapeHTML($w->get('permalink_str'))
                )
            );
        }

        if ($w->get('feed') && App::frontend()->context()->__get('posts')->__call('commentsActive', [])) {
            $content .= self::li(
                $w,
                'feed',
                sprintf(
                    $link,
                    App::blog()->url() .
                        App::url()->getBase('feed') .
                        '/atom/comments/' .
                        App::frontend()->context()->__get('posts')->f('post_id'),
                    __("This post's comments feed")
                )
            );
        }

        if ($w->get('navprevpost')) {
            $npp = self::nav(
                App::frontend()->context()->__get('posts'),
                -1,
                false,
                __('Previous entry'),
                $w->get('navprevpost')
            );
            if ($npp) {
                $content .= self::li(
                    $w,
                    'previous',
                    $npp
                );
            }
        }
        if ($w->get('navnextpost')) {
            $nnp = self::nav(
                App::frontend()->context()->__get('posts'),
                1,
                false,
                __('Next entry'),
                $w->get('navnextpost')
            );
            if ($nnp) {
                $content .= self::li(
                    $w,
                    'next',
                    $nnp
                );
            }
        }

        if ($w->get('navprevcat')) {
            $npc = self::nav(
                App::frontend()->context()->__get('posts'),
                -1,
                true,
                __('Previous entry of this category'),
                $w->get('navprevcat')
            );
            if ($npc) {
                $content .= self::li(
                    $w,
                    'previous',
                    $npc
                );
            }
        }

        if ($w->get('navnextcat')) {
            $nnc = self::nav(
                App::frontend()->context()->__get('posts'),
                1,
                true,
                __('Next entry of this category'),
                $w->get('navnextcat')
            );
            if ($nnc) {
                $content .= self::li(
                    $w,
                    'next',
                    $nnc
                );
            }
        }

        # --BEHAVIOR-- postInfoWidgetPublic
        $content .= (string) App::behavior()->callBehavior('postInfoWidgetPublic', $w);

        if (empty($content)) {
            return '';
        }
        /*
                $rmv = '';
                if ($w->rmvinfo || $w->rmvtags || $w->rmvnav) {
                    $rmv .=
                    '<script type="text/javascript">'."\n".
                    '$(function() {'."\n";
                    if ($w->rmvinfo) {
                        $rmv .=
                        'var piw_pi=$("#content .post-info");'."\n".
                        'if ($(piw_pi).length!=0){$(piw_pi).hide();}'."\n";
                    }
                    if ($w->rmvtags) {
                        $rmv .=
                        'var piw_pt=$("#content .post-tags");'."\n".
                        'if ($(piw_pt).length!=0){$(piw_pt).hide();}'."\n";
                    }
                    if ($w->rmvnav) {
                        $rmv .=
                        'var piw_pn=$("#content #navlinks");'."\n".
                        'if ($(piw_pn).length!=0){$(piw_pn).hide();}'."\n";
                    }
                    $rmv .=
                    '});'."\n".
                    "</script>\n";
                }
        //*/
        return $w->renderDiv(
            (bool) $w->get('content_only'),
            'postinfowidget ' . $w->get('class'),
            '',
            ($w->get('title') ? $w->renderTitle(Html::escapeHTML($w->get('title'))) : '') .
                sprintf('<ul>%s</ul>', $content)
        );
    }

    public static function li(WidgetsElement $w, string $i, string $c): string
    {
        if (!App::blog()->isDefined()) {
            return '';
        }

        $s = ' style="padding-left:%spx;background: transparent url(\'' .
            App::blog()->getQmarkURL() .
            'pf=postInfoWidget/img/%s%s.png\') no-repeat left center;"';
        if ($w->get('style') == 'small') {
            $s = sprintf($s, 16, $i, '-small');
        } elseif ($w->get('style') == 'normal') {
            $s = sprintf($s, 20, $i, '');
        } else {
            $s = '';
        }
        $l = '<li class="postinfo-%s"%s>%s</li>';

        return sprintf($l, $i, $s, $c);
    }

    public static function nav(MetaRecord $p, int $d, bool $r, string $t, string $c): string
    {
        if (!App::blog()->isDefined()) {
            return '';
        }

        $rs = App::blog()->getNextPost($p, $d, $r);
        if (is_null($rs)) {
            return '';
        }
        $l = '<a href="%s" title="%s">%s</a>';
        $u = $rs->__call('getURL', []);
        $e = Html::escapeHTML($rs->f('post_title'));

        return str_replace(
            ['%T', '%F'],
            [sprintf($l, $u, $e, $t), sprintf($l, $u, $t, $e)],
            $c
        );
    }
}
