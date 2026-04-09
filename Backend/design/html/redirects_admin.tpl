{$meta_title=$btr->sviat_redirects__title scope=global}

<div class="main_header">
    <div class="main_header__item">
        <div class="main_header__inner">
            <div class="box_heading heading_page">
                {$btr->sviat_redirects__title|escape} - {$redirects_count}
            </div>
            <div class="box_btn_heading">
                <a class="btn btn_small btn-info" href="{url controller='Sviat.Redirects.RedirectAdmin' return=$smarty.server.REQUEST_URI}">
                    {include file='svg_icon.tpl' svgId='plus'}
                    <span>{$btr->sviat_redirects__add|escape}</span>
                </a>
                <a class="btn btn_small btn-warning" href="{url controller='Sviat.Redirects.RedirectsImportAdmin'}">
                    <span>{$btr->sviat_redirects__import|escape}</span>
                </a>
                <a class="btn btn_small btn-outline-warning" href="{url controller='Sviat.Redirects.RedirectsExportAdmin'}">
                    <span>{$btr->sviat_redirects__export|escape}</span>
                </a>
            </div>
        </div>
    </div>
    <div class="main_header__item">
        <div class="main_header__inner">
            <form class="search" method="get">
                <input type="hidden" name="controller" value="Sviat.Redirects.RedirectsAdmin">
                <input type="hidden" name="filter" value="{$filter|escape}">
                <input type="hidden" name="limit" value="{$current_limit|escape}">
                <div class="input-group input-group--search">
                    <input name="keyword" class="form-control" placeholder="{$btr->sviat_redirects__search_placeholder|escape}" type="text" value="{$keyword|escape}">
                    <span class="input-group-btn">
                        <button type="submit" class="btn"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="boxed fn_toggle_wrap">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="fn_toggle_wrap">
                <div class="heading_box visible_md">
                    {$btr->general_filter|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;"><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="boxed_sorting toggle_body_wrap off fn_card">
                    <div class="row">
                        <div class="col-lg-3 col-md-4 col-sm-12">
                            <select onchange="location = this.value;" class="selectpicker form-control">
                                <option value="{url filter=null page=null}" {if !$filter}selected{/if}>{$btr->general_all|escape}</option>
                                <option value="{url filter=301 page=null}" {if $filter == 301}selected{/if}>{$btr->sviat_redirects__filter_301|escape}</option>
                                <option value="{url filter=302 page=null}" {if $filter == 302}selected{/if}>{$btr->sviat_redirects__filter_302|escape}</option>
                                <option value="{url filter=enabled page=null}" {if $filter == 'enabled'}selected{/if}>{$btr->sviat_redirects__filter_enabled|escape}</option>
                                <option value="{url filter=disabled page=null}" {if $filter == 'disabled'}selected{/if}>{$btr->sviat_redirects__filter_disabled|escape}</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-12">
                            <select onchange="location = this.value;" class="selectpicker form-control">
                                <option value="{url limit=10 page=null}" {if $current_limit == 10}selected{/if}>10</option>
                                <option value="{url limit=25 page=null}" {if $current_limit == 25}selected{/if}>25</option>
                                <option value="{url limit=50 page=null}" {if $current_limit == 50}selected{/if}>50</option>
                                <option value="{url limit=100 page=null}" {if $current_limit == 100}selected{/if}>100</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-12">
                            <a class="btn btn_small btn-light" href="{url keyword=null filter=null page=1}">{$btr->sviat_redirects__reset_filters|escape}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {if !$redirects}
        <div class="heading_box mt-1">
            <div class="text_grey">{$btr->sviat_redirects__empty_list|escape}</div>
        </div>
    {else}
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <form class="fn_form_list fn_fast_button" method="post">
                    <input type="hidden" name="session_id" value="{$smarty.session.id}">

                    <div class="okay_list products_list">
                        <div class="okay_list_head">
                            <div class="okay_list_heading okay_list_check">
                                <input class="hidden_check fn_check_all" type="checkbox" id="check_all_1" value="">
                                <label class="okay_ckeckbox" for="check_all_1"></label>
                            </div>
                            <div class="okay_list_heading" style="width: 20%;">
                                {if $sort == 'name'}{$sort_link='name_desc'}{else}{$sort_link='name'}{/if}
                                <a href="{url sort=$sort_link page=null}" class="{if $sort == 'name' || $sort == 'name_desc'}active{/if}">
                                    {$btr->general_name|escape} {include file='svg_icon.tpl' svgId='sorting'}
                                </a>
                            </div>
                            <div class="okay_list_heading" style="width: 22%;">
                                {if $sort == 'from_url'}{$sort_link='from_url_desc'}{else}{$sort_link='from_url'}{/if}
                                <a href="{url sort=$sort_link page=null}" class="{if $sort == 'from_url' || $sort == 'from_url_desc'}active{/if}">
                                    {$btr->sviat_redirects__label_from|escape} {include file='svg_icon.tpl' svgId='sorting'}
                                </a>
                            </div>
                            <div class="okay_list_heading" style="width: 22%;">
                                {if $sort == 'to_url'}{$sort_link='to_url_desc'}{else}{$sort_link='to_url'}{/if}
                                <a href="{url sort=$sort_link page=null}" class="{if $sort == 'to_url' || $sort == 'to_url_desc'}active{/if}">
                                    {$btr->sviat_redirects__label_to|escape} {include file='svg_icon.tpl' svgId='sorting'}
                                </a>
                            </div>
                            <div class="okay_list_heading" style="width: 8%;">
                                {if $sort == 'hits'}{$sort_link='hits_desc'}{else}{$sort_link='hits'}{/if}
                                <a href="{url sort=$sort_link page=null}" class="{if $sort == 'hits' || $sort == 'hits_desc'}active{/if}">
                                    {$btr->sviat_redirects__hits|escape} {include file='svg_icon.tpl' svgId='sorting'}
                                </a>
                            </div>
                            <div class="okay_list_heading" style="width: 10%;">
                                {if $sort == 'status'}{$sort_link='status_desc'}{else}{$sort_link='status'}{/if}
                                <a href="{url sort=$sort_link page=null}" class="{if $sort == 'status' || $sort == 'status_desc'}active{/if}">
                                    {$btr->sviat_redirects__status|escape} {include file='svg_icon.tpl' svgId='sorting'}
                                </a>
                            </div>
                            <div class="okay_list_heading" style="width: 8%;">
                                {if $sort == 'enabled'}{$sort_link='enabled_desc'}{else}{$sort_link='enabled'}{/if}
                                <a href="{url sort=$sort_link page=null}" class="{if $sort == 'enabled' || $sort == 'enabled_desc'}active{/if}">
                                    {$btr->sviat_redirects__activity|escape} {include file='svg_icon.tpl' svgId='sorting'}
                                </a>
                            </div>
                            <div class="okay_list_heading" style="width: 6%;">
                                {$btr->sviat_redirects__is_lang_short|default:'Lang'|escape}
                            </div>
                            <div class="okay_list_heading okay_list_close"></div>
                        </div>

                        <div class="okay_list_body">
                            {foreach $redirects as $redirect}
                                <div class="okay_list_body_item fn_row">
                                    <div class="okay_list_row">
                                        <div class="okay_list_boding okay_list_check">
                                            <input class="hidden_check" type="checkbox" id="id_{$redirect->id}" name="check[]" value="{$redirect->id}">
                                            <label class="okay_ckeckbox" for="id_{$redirect->id}"></label>
                                        </div>
                                        <div class="okay_list_boding" style="width: 20%; text-align: left;">
                                            <a href="{url controller='Sviat.Redirects.RedirectAdmin' id=$redirect->id return=$smarty.server.REQUEST_URI}">{$redirect->name|escape}</a>
                                        </div>
                                        <div class="okay_list_boding" style="width: 22%; word-break: break-all; text-align: left;">{$redirect->from_url|escape}</div>
                                        <div class="okay_list_boding" style="width: 22%; word-break: break-all; text-align: left;">{$redirect->to_url|escape}</div>
                                        <div class="okay_list_boding" style="width: 8%;">{$redirect->hits|default:0}</div>
                                        <div class="okay_list_boding" style="width: 10%;">
                                            {if $redirect->status == 301}
                                                <span class="tag tag-info">301</span>
                                            {else}
                                                <span class="tag tag-warning">302</span>
                                            {/if}
                                        </div>
                                        <div class="okay_list_boding" style="width: 8%;">
                                            <label class="switch switch-default">
                                                <input class="switch-input fn_ajax_action {if $redirect->enabled}fn_active_class{/if}" data-controller="Sviat.Redirects.RedirectsEntity" data-action="enabled" data-id="{$redirect->id}" name="enabled" value="1" type="checkbox" {if $redirect->enabled}checked{/if}>
                                                <span class="switch-label"></span>
                                                <span class="switch-handle"></span>
                                            </label>
                                        </div>
                                        <div class="okay_list_boding" style="width: 6%;">
                                            {if $redirect->is_lang}
                                                <span class="tag tag-success" title="{$btr->sviat_redirects__yes|default:'Yes'|escape}">✓</span>
                                            {else}
                                                <span class="tag" title="{$btr->sviat_redirects__no|default:'No'|escape}">—</span>
                                            {/if}
                                        </div>
                                        <div class="okay_list_boding okay_list_close">
                                            <button data-hint="{$btr->sviat_redirects__delete|escape}" type="button" class="btn_close fn_remove hint-bottom-right-t-info-s-small-mobile hint-anim" data-toggle="modal" data-target="#fn_action_modal" onclick="success_action($(this));">
                                                {include file='svg_icon.tpl' svgId='delete'}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>

                        <div class="okay_list_footer fn_action_block">
                            <div class="okay_list_foot_left">
                                <div class="okay_list_heading okay_list_check">
                                    <input class="hidden_check fn_check_all" type="checkbox" id="check_all_2" value="">
                                    <label class="okay_ckeckbox" for="check_all_2"></label>
                                </div>
                                <div class="okay_list_option">
                                    <select name="action" class="selectpicker form-control">
                                        <option value="disable">{$btr->general_do_disable|escape}</option>
                                        <option value="enable">{$btr->general_do_enable|escape}</option>
                                        <option value="status_301">{$btr->sviat_redirects__set_status_301|default:'Встановити статус 301'|escape}</option>
                                        <option value="status_302">{$btr->sviat_redirects__set_status_302|default:'Встановити статус 302'|escape}</option>
                                        <option value="delete">{$btr->general_delete|escape}</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn_small btn_blue">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_apply|escape}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 txt_center">
                {include file='pagination.tpl'}
            </div>
        </div>
    {/if}
</div>
