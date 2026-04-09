{if $redirect->id}
    {$meta_title = $redirect->name scope=global}
{else}
    {$meta_title = $btr->sviat_redirects__new scope=global}
{/if}

<div class="main_header">
    <div class="main_header__item">
        <div class="main_header__inner">
            <div class="box_heading heading_page">
                {if $redirect->id}{$redirect->name|escape}{else}{$btr->sviat_redirects__new|escape}{/if}
            </div>
        </div>
    </div>
    <div class="main_header__item">
        <div class="main_header__inner">
            {if $redirect->id}
                <a class="btn btn_small btn-info" target="_blank" href="../{$lang_link}{$redirect->from_url}">
                    {include file='svg_icon.tpl' svgId='icon_desktop'}
                    <span>{$btr->general_open|escape}</span>
                </a>
            {/if}
            {if $smarty.get.return}
                <a class="btn btn_small btn_border-info ml-1" href="{$smarty.get.return}">
                    {include file='svg_icon.tpl' svgId='return'}
                    <span>{$btr->general_back|escape}</span>
                </a>
            {/if}
        </div>
    </div>
</div>

{if $message_success}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="alert alert--center alert--icon alert--success">
                <div class="alert__content">
                    <div class="alert__title">
                        {if $message_success == 'added'}{$btr->sviat_redirects__added|escape}{else}{$btr->sviat_redirects__updated|escape}{/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}

{if $message_error}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="alert alert--center alert--icon alert--error">
                <div class="alert__content">
                    <div class="alert__title">
                        {if $message_error == 'url_exists'}
                            {$btr->general_exists|escape}
                        {elseif $message_error == 'empty_name'}
                            {$btr->sviat_redirects__error_empty_name|escape}
                        {elseif $message_error == 'empty_url_from'}
                            {$btr->sviat_redirects__error_empty_url_from|escape}
                        {elseif $message_error == 'empty_url_to'}
                            {$btr->sviat_redirects__error_empty_url_to|escape}
                        {elseif $message_error == 'used_url'}
                            {$btr->sviat_redirects__used_url|escape}
                        {else}
                            {$message_error|escape}
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}

<form method="post" class="fn_fast_button">
    <input type="hidden" name="session_id" value="{$smarty.session.id}">
    <input type="hidden" name="id" value="{$redirect->id|escape}">

    <div class="row">
        <div class="col-lg-9 col-md-8 col-sm-12">
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">{$btr->sviat_redirects__title|escape}</div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-12 col-md-12">
                            <div class="heading_label">{$btr->general_name|escape}</div>
                            <div class="form-group mb-1">
                                <input class="form-control" name="name" type="text" value="{$redirect->name|escape}">
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="heading_label">{$btr->sviat_redirects__label_from|escape}</div>
                            <div class="form-group mb-1">
                                <input name="from_url" class="form-control" type="text" value="{$redirect->from_url|escape}">
                                <p class="mb-0 mt-h"><small>{$btr->sviat_redirects__url_hint_from|escape}</small></p>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="heading_label">{$btr->sviat_redirects__label_to|escape}</div>
                            <div class="form-group mb-1">
                                <input name="to_url" class="form-control" type="text" value="{$redirect->to_url|escape}">
                                <p class="mb-0 mt-h"><small>{$btr->sviat_redirects__url_hint_to|escape}</small></p>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="heading_label">{$btr->sviat_redirects__type|default:'Type'|escape}</div>
                            <div class="form-group mb-1">
                                <select name="type" class="selectpicker form-control">
                                    <option value="exact" {if !$redirect->type || $redirect->type == 'exact'}selected{/if}>{$btr->sviat_redirects__type_exact|default:'Exact match'|escape}</option>
                                    <option value="pattern" {if $redirect->type == 'pattern'}selected{/if}>{$btr->sviat_redirects__type_prefix|default:'Pattern match'|escape}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="heading_label">{$btr->sviat_redirects__is_lang|escape}</div>
                            <div class="form-group mb-1">
                                <label class="switch switch-default">
                                    <input class="switch-input" name="is_lang" value="1" type="checkbox" {if $redirect->is_lang}checked{/if}>
                                    <span class="switch-label"></span>
                                    <span class="switch-handle"></span>
                                </label>
                                <p class="mb-0 mt-h"><small>{$btr->sviat_redirects__is_lang_hint|escape}</small></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="alert alert--icon alert--info mb-1">
                <div class="alert__content">
                    <div class="alert__title">{$btr->general_instructions|default:'Інструкція'|escape}</div>
                    <p class="mb-0">
                        <b>{$btr->sviat_redirects__instruction_exact_title|escape}</b> {$btr->sviat_redirects__instruction_exact_text|escape}<br>
                        {$btr->sviat_redirects__instruction_exact_example|escape}: <code>category/iphone-15</code> → <code>promo</code>.<br><br>
                        <b>{$btr->sviat_redirects__instruction_prefix_title|escape}</b> {$btr->sviat_redirects__instruction_prefix_text|escape}<br>
                        {$btr->sviat_redirects__instruction_slug_text|escape}<br>
                        {$btr->sviat_redirects__instruction_prefix_example|escape}: <code>category/$slug</code> → <code>products/$slug</code><br>
                        {$btr->sviat_redirects__instruction_prefix_result|escape}: <code>category/iphone-15</code> → <code>products/iphone-15</code>.<br><br>
                        {$btr->sviat_redirects__instruction_prefix_fixed_text|escape}<br>
                        <code>category/$slug</code> → <code>contact</code>.<br><br>
                        <b>{$btr->sviat_redirects__instruction_is_lang_title|escape}</b> {$btr->sviat_redirects__instruction_is_lang_text|escape}<br>
                        {$btr->sviat_redirects__instruction_prefix_example|escape}: <code>category/$slug</code> → <code>catalog/$slug</code><br>
                        {$btr->sviat_redirects__instruction_prefix_result|escape}: <code>en/category/iphone-15</code> → <code>en/catalog/iphone-15</code>.<br><br>
                        <b>{$btr->sviat_redirects__instruction_priority_title|escape}</b> {$btr->sviat_redirects__instruction_priority_text|escape}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-12">
            <div class="boxed fn_toggle_wrap mb-1">
                <div class="heading_box">{$btr->sviat_redirects__status|escape}</div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="form-group mb-1">
                        <select name="status" class="selectpicker form-control">
                            <option value="301" {if $redirect->status == 301}selected{/if}>{$btr->sviat_redirects__status_301|escape}</option>
                            <option value="302" {if $redirect->status == 302}selected{/if}>{$btr->sviat_redirects__status_302|escape}</option>
                        </select>
                    </div>
                    <div class="activity_of_switch activity_of_switch--left">
                        <div class="activity_of_switch_item">
                            <div class="okay_switch clearfix">
                                <label class="switch_label">{$btr->sviat_redirects__activity|escape}</label>
                                <label class="switch switch-default">
                                    <input class="switch-input" name="enabled" value="1" type="checkbox" {if $redirect->enabled}checked{/if}>
                                    <span class="switch-label"></span>
                                    <span class="switch-handle"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">{$btr->sviat_redirects__stats|escape}</div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="mb-h">{$btr->sviat_redirects__hits|escape}: <b>{$redirect->hits|default:0}</b></div>
                    <div>{$btr->sviat_redirects__last_hit|escape}: <b>{if $redirect->last_hit_at}{$redirect->last_hit_at|escape}{else}-{/if}</b></div>
                </div>
            </div>
            <div class="boxed">
                <div class="text-xs-right py-h px-h">
                    <button type="submit" class="btn btn_small btn_blue">
                        {include file='svg_icon.tpl' svgId='checked'}
                        <span>{$btr->general_apply|escape}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
