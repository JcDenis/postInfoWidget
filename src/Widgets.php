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
declare(strict_types=1);

namespace Dotclear\Plugin\postInfoWidget;

use dcCore;
use dcRecord;
use Dotclear\Helper\Date;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\L10n;
use Dotclear\Plugin\widgets\WidgetsStack;
use Dotclear\Plugin\widgets\WidgetsElement;

class Widgets
{
    public static function initWidgets(WidgetsStack $w): void
    {
        $w
            ->create(
                'postinfowidget',
                __('PostInfoWidget: entry information list'),
                [self::class, 'publicWidget'],
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

        if (dcCore::app()->plugins->moduleExists('tags')) {
            $w->postinfowidget->setting(
                'tag_str',
                __('Tags text (%T = tags list):'),
                __('Tags: %T'),
                'text'
            );
        }

        $w->postinfowidget
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
        dcCore::app()->callBehavior('postInfoWidgetAdmin', $w);

        $w->postinfowidget
            ->addContentOnly()
            ->addClass()
            ->addOffline();
    }

    public static function publicWidget(WidgetsElement $w): string
    {
        if ($w->offline) {
            return '';
        }

        if (dcCore::app()->url->type != 'post'
        || !dcCore::app()->ctx->posts->f('post_id')) {
            return '';
        }

        $link    = '<a href="%s">%s</a>';
        $content = '';

        if ($w->dt_str != '') {
            $content .= self::li(
                $w,
                'date',
                Date::str(
                    $w->dt_str,
                    (int) strtotime(dcCore::app()->ctx->posts->f('post_dt')),
                    dcCore::app()->blog->settings->get('system')->get('blog_timezone')
                )
            );
        }

        if ($w->creadt_str != '') {
            $content .= self::li(
                $w,
                'create',
                Date::str(
                    $w->creadt_str,
                    (int) strtotime(dcCore::app()->ctx->posts->post_creadt),
                    dcCore::app()->blog->settings->get('system')->get('blog_timezone')
                )
            );
        }

        if ($w->upddt_str != '') {
            $content .= self::li(
                $w,
                'update',
                Date::str(
                    $w->upddt_str,
                    (int) strtotime(dcCore::app()->ctx->posts->f('post_upddt')),
                    dcCore::app()->blog->settings->get('system')->get('blog_timezone')
                )
            );
        }

        if ($w->lang_str != '') {
            $ln        = L10n::getISOcodes();
            $lang_code = dcCore::app()->ctx->posts->f('post_lang') ?
                dcCore::app()->ctx->posts->f('post_lang') :
                dcCore::app()->blog->settings->get('system')->get('lang');
            $lang_name = $ln[$lang_code] ?? $lang_code;
            $lang_flag = file_exists(
                dirname(__DIR__) .
                '/img/flags/' .
                $lang_code . '.png'
            ) ?
                '<img src="' . dcCore::app()->blog->getQmarkURL() .
                    'pf=postInfoWidget/img/flags/' .
                    $lang_code . '.png" alt="' . $lang_name . '" />' :
                '';

            $content .= self::li(
                $w,
                'lang',
                str_replace(
                    ['%T', '%C', '%F'],
                    [$lang_name, $lang_code, $lang_flag],
                    Html::escapeHTML($w->lang_str)
                )
            );
        }

        if ($w->author_str != '') {
            $content .= self::li(
                $w,
                'author',
                str_replace(
                    '%T',
                    dcCore::app()->ctx->posts->getAuthorLink(),
                    Html::escapeHTML($w->author_str)
                )
            );
        }

        if ($w->category_str != '' && dcCore::app()->ctx->posts->f('cat_id')) {
            $content .= self::li(
                $w,
                'category',
                str_replace(
                    '%T',
                    sprintf(
                        $link,
                        dcCore::app()->ctx->posts->getCategoryURL(),
                        Html::escapeHTML(dcCore::app()->ctx->posts->f('cat_title'))
                    ),
                    Html::escapeHTML($w->category_str)
                )
            );
        }

        if ($w->tag_str != '' && dcCore::app()->plugins->moduleExists('tags')) {
            $meta = dcCore::app()->meta->getMetadata([
                'meta_type' => 'tag',
                'post_id'   => dcCore::app()->ctx->posts->f('post_id'),
            ]);
            $metas = [];
            while ($meta->fetch()) {
                $metas[$meta->f('meta_id')] = sprintf(
                    $link,
                    dcCore::app()->blog->url .
                        dcCore::app()->url->getBase('tag') . '/' .
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
                        Html::escapeHTML($w->tag_str)
                    )
                );
            }
        }

        if ($w->attachment_str != '') {
            $nb = dcCore::app()->ctx->posts->countMedia();
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
                    [$attachment_textual, $attachment_numeric],
                    Html::escapeHTML($w->attachment_str)
                )
            );
        }

        if ($w->comment_str != '' && dcCore::app()->ctx->posts->commentsActive()) {
            $nb = (int) dcCore::app()->ctx->posts->f('nb_comment');
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
                    [$comment_textual, $comment_numeric],
                    Html::escapeHTML($w->comment_str)
                )
            );
        }

        if ($w->trackback_str != '' && dcCore::app()->ctx->posts->trackbacksActive()) {
            $nb = (int) dcCore::app()->ctx->posts->f('nb_trackback');
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
                    [$trackback_textual, $trackback_numeric],
                    Html::escapeHTML($w->trackback_str)
                )
            );
        }

        if ($w->permalink_str) {
            $content .= self::li(
                $w,
                'permalink',
                str_replace(
                    ['%T', '%F'],
                    [
                        sprintf(
                            $link,
                            dcCore::app()->ctx->posts->getURL(),
                            __('Permalink')
                        ),
                        dcCore::app()->ctx->posts->getURL(),
                    ],
                    Html::escapeHTML($w->permalink_str)
                )
            );
        }

        if ($w->feed && dcCore::app()->ctx->posts->commentsActive()) {
            $content .= self::li(
                $w,
                'feed',
                sprintf(
                    $link,
                    dcCore::app()->blog->url .
                        dcCore::app()->url->getBase('feed') .
                        '/atom/comments/' .
                        dcCore::app()->ctx->posts->f('post_id'),
                    __("This post's comments feed")
                )
            );
        }

        if ($w->navprevpost) {
            $npp = self::nav(
                dcCore::app()->ctx->posts,
                -1,
                false,
                __('Previous entry'),
                $w->navprevpost
            );
            if ($npp) {
                $content .= self::li(
                    $w,
                    'previous',
                    $npp
                );
            }
        }
        if ($w->navnextpost) {
            $nnp = self::nav(
                dcCore::app()->ctx->posts,
                1,
                false,
                __('Next entry'),
                $w->navnextpost
            );
            if ($nnp) {
                $content .= self::li(
                    $w,
                    'next',
                    $nnp
                );
            }
        }

        if ($w->navprevcat) {
            $npc = self::nav(
                dcCore::app()->ctx->posts,
                -1,
                true,
                __('Previous entry of this category'),
                $w->navprevcat
            );
            if ($npc) {
                $content .= self::li(
                    $w,
                    'previous',
                    $npc
                );
            }
        }

        if ($w->navnextcat) {
            $nnc = self::nav(
                dcCore::app()->ctx->posts,
                1,
                true,
                __('Next entry of this category'),
                $w->navnextcat
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
        $content .= dcCore::app()->callBehavior('postInfoWidgetPublic', $w);

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
            (bool) $w->content_only,
            'postinfowidget ' . $w->class,
            '',
            ($w->title ? $w->renderTitle(Html::escapeHTML($w->title)) : '') .
                sprintf('<ul>%s</ul>', $content)
        );
    }

    public static function li(WidgetsElement $w, string $i, string $c): string
    {
        $s = ' style="padding-left:%spx;background: transparent url(\'' .
            dcCore::app()->blog->getQmarkURL() .
            'pf=postInfoWidget/img/%s%s.png\') no-repeat left center;"';
        if ($w->style == 'small') {
            $s = sprintf($s, 16, $i, '-small');
        } elseif ($w->style == 'normal') {
            $s = sprintf($s, 20, $i, '');
        } else {
            $s = '';
        }
        $l = '<li class="postinfo-%s"%s>%s</li>';

        return sprintf($l, $i, $s, $c);
    }

    public static function nav(dcRecord $p, int $d, bool $r, string $t, string $c): string
    {
        $rs = dcCore::app()->blog->getNextPost($p, $d, $r);
        if (is_null($rs)) {
            return '';
        }
        $l = '<a href="%s" title="%s">%s</a>';
        $u = $rs->getURL();
        $e = Html::escapeHTML($rs->f('post_title'));

        return str_replace(
            ['%T', '%F'],
            [sprintf($l, $u, $e, $t), sprintf($l, $u, $t, $e)],
            $c
        );
    }
}
