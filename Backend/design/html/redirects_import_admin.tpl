{$meta_title=$btr->sviat_redirects__import_title scope=global}
{$report=$import_report|default:[]}

<div class="main_header">
    <div class="main_header__item">
        <div class="main_header__inner">
            <div class="box_heading heading_page">
                {$btr->sviat_redirects__import_title|escape}
            </div>
        </div>
    </div>
    <div class="main_header__item">
        <div class="main_header__inner">
            <a class="btn btn_small btn-warning" href="{url controller='Sviat.Redirects.RedirectsImportTemplateAdmin'}">
                <span>{$btr->sviat_redirects__import_download_template|escape}</span>
            </a>
            <a class="btn btn_small btn_border-info ml-1" href="{url controller='Sviat.Redirects.RedirectsAdmin'}">
                {include file='svg_icon.tpl' svgId='return'}
                <span>{$btr->sviat_redirects__back_to_list|escape}</span>
            </a>
        </div>
    </div>
</div>

{if $message_success}
    <div class="alert alert--success alert--center">
        <div class="alert__content">
            <div class="alert__title">
                {$btr->sviat_redirects__import_done|escape}
            </div>
            {if $import_report}
                <div>
                    {$btr->sviat_redirects__import_total|escape}: {$report.total|default:0},
                    {$btr->sviat_redirects__import_created|escape}: {$report.created|default:0},
                    {$btr->sviat_redirects__import_updated|escape}: {$report.updated|default:0},
                    {$btr->sviat_redirects__import_duplicates|escape}: {$report.duplicates|default:0},
                    {$btr->sviat_redirects__import_invalid|escape}: {$report.invalid|default:0}
                </div>
            {/if}
        </div>
    </div>
{/if}

{if $message_error}
    <div class="alert alert--error alert--center">
        <div class="alert__content">
            {$btr->sviat_redirects__import_upload_error|escape}
        </div>
    </div>
{/if}

<div class="boxed">
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="session_id" value="{$smarty.session.id}">
        <div class="row">
            <div class="col-lg-8 col-md-10">
                <div class="heading_box">
                    {$btr->sviat_redirects__import_hint|escape}
                </div>
                <div class="row mt-1">
                    <div class="col-lg-7 col-md-7 col-sm-12 my-h">
                        <div class="input_file_container">
                            <input name="csv_file" class="file_upload input_file" id="redirects-import-file" type="file" accept=".csv,text/csv">
                            <label tabindex="0" for="redirects-import-file" class="input_file_trigger">
                                {* {include file='svg_icon.tpl' svgId='upload'} *}
                                <span>{$btr->sviat_redirects__button_select_file|escape}</span>
                            </label>
                        </div>
                        <p class="input_file_return"></p>
                    </div>
                </div>
                <div class="mt-1 text_grey">
                    {$btr->sviat_redirects__import_columns|escape}
                </div>
                <div class="mt-1">
                    <label class="switch switch-default">
                        <input class="switch-input" name="update_existing" value="1" type="checkbox" {if $update_existing}checked{/if}>
                        <span class="switch-label"></span>
                        <span class="switch-handle"></span>
                    </label>
                    <span class="ml-q">{$btr->sviat_redirects__import_update_existing|escape}</span>
                </div>
                <div class="mt-1">
                    <button type="submit" class="btn btn_small btn_blue">
                        {include file='svg_icon.tpl' svgId='import'}
                        {$btr->sviat_redirects__import_submit|escape}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="alert alert--icon alert--info">
    <div class="alert__content">
        <div class="alert__title">{$btr->general_instructions|default:'Інструкція'|escape}</div>
        <ul class="mb-0 pl-1">
            <li>{$btr->sviat_redirects__import_instruction_format|escape}</li>
            <li>{$btr->sviat_redirects__import_instruction_header|escape}:
                <code>from_url;to_url;name;status;enabled;is_lang;type</code>.</li>
            <li>{$btr->sviat_redirects__import_instruction_required|escape}</li>
            <li>{$btr->sviat_redirects__import_instruction_relative_urls|escape}</li>
            <li>{$btr->sviat_redirects__import_instruction_status|escape}</li>
            <li>{$btr->sviat_redirects__import_instruction_enabled|escape}</li>
            <li>{$btr->sviat_redirects__import_instruction_is_lang|escape}</li>
            <li>{$btr->sviat_redirects__import_instruction_type|escape}</li>
            <li>{$btr->sviat_redirects__import_instruction_pattern_slug|escape}:
                <code>category/$slug;products/$slug</code>.</li>
            <li>{$btr->sviat_redirects__import_instruction_pattern_fixed|escape}:
                <code>category/$slug;contact</code>.</li>
            <li>{$btr->sviat_redirects__import_instruction_priority|escape}</li>
            <li>{$btr->sviat_redirects__import_instruction_example_exact|escape}:
                <code>old-page;new-page;Example redirect;301;1;0;exact</code>.</li>
            <li>{$btr->sviat_redirects__import_instruction_example_is_lang|escape}:
                <code>category/iphone-15;catalog/iphone-15;is_lang redirect;301;1;1;exact</code>.</li>
            <li>{$btr->sviat_redirects__import_instruction_example_pattern|escape}:
                <code>category/$slug;products/$slug;Pattern example;301;1;0;pattern</code>.</li>
        </ul>
    </div>
</div>
