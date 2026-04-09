<?php

$lang['sviat_redirects__menu_title'] = 'Redirects';

$lang['sviat_redirects__title'] = 'Redirects';
$lang['sviat_redirects__empty_list'] = 'No redirects yet';
$lang['sviat_redirects__search_placeholder'] = 'Search by name or URL';

$lang['sviat_redirects__add'] = 'Add redirect';
$lang['sviat_redirects__import'] = 'Import';
$lang['sviat_redirects__export'] = 'Export';
$lang['sviat_redirects__import_title'] = 'Import redirects from CSV';
$lang['sviat_redirects__import_submit'] = 'Import';
$lang['sviat_redirects__import_download_template'] = 'Download template';
$lang['sviat_redirects__import_hint'] = 'Upload a CSV file. Delimiters "," and ";" are supported.';
$lang['sviat_redirects__import_columns'] = 'Columns: from_url, to_url, name, status, enabled, is_lang (required: from_url, to_url).';
$lang['sviat_redirects__import_done'] = 'Import completed';
$lang['sviat_redirects__import_upload_error'] = 'Failed to upload CSV file';
$lang['sviat_redirects__import_total'] = 'Rows in file';
$lang['sviat_redirects__import_created'] = 'Created';
$lang['sviat_redirects__import_updated'] = 'Updated';
$lang['sviat_redirects__import_update_existing'] = 'Update existing redirects by from_url';
$lang['sviat_redirects__import_duplicates'] = 'Skipped duplicates';
$lang['sviat_redirects__import_invalid'] = 'Skipped invalid rows';
$lang['sviat_redirects__back_to_list'] = 'Back to redirects list';
$lang['sviat_redirects__new'] = 'New redirect';
$lang['sviat_redirects__added'] = 'Redirect created';
$lang['sviat_redirects__updated'] = 'Redirect updated';
$lang['sviat_redirects__delete'] = 'Delete redirect';
$lang['sviat_redirects__used_url'] = 'This source URL is already used in redirect';

$lang['sviat_redirects__label_from'] = 'From URL';
$lang['sviat_redirects__label_to'] = 'To URL';
$lang['sviat_redirects__url_hint'] = 'Use a relative URL without domain, e.g. catalog/old-product';
$lang['sviat_redirects__url_hint_from'] = 'Use a relative URL without domain, e.g. catalog/old-product';
$lang['sviat_redirects__url_hint_to'] = 'Use a relative URL without domain, e.g. catalog/new-product';
$lang['sviat_redirects__error_empty_name'] = 'Enter redirect name';
$lang['sviat_redirects__error_empty_url_from'] = 'Enter source URL';
$lang['sviat_redirects__error_empty_url_to'] = 'Enter destination URL';

$lang['sviat_redirects__status'] = 'Status code';
$lang['sviat_redirects__status_301'] = '301 (Permanent)';
$lang['sviat_redirects__status_302'] = '302 (Temporary)';

$lang['sviat_redirects__filter_301'] = 'Only 301';
$lang['sviat_redirects__filter_302'] = 'Only 302';
$lang['sviat_redirects__filter_enabled'] = 'Enabled';
$lang['sviat_redirects__filter_disabled'] = 'Disabled';
$lang['sviat_redirects__activity'] = 'Active';
$lang['sviat_redirects__reset_filters'] = 'Reset filters';
$lang['sviat_redirects__stats'] = 'Statistics';
$lang['sviat_redirects__hits'] = 'Hits';
$lang['sviat_redirects__last_hit'] = 'Last hit';

$lang['sviat_redirects__set_status_301'] = 'Set status 301';
$lang['sviat_redirects__set_status_302'] = 'Set status 302';

$lang['sviat_redirects__type'] = 'Match type';
$lang['sviat_redirects__type_exact'] = 'Exact';
$lang['sviat_redirects__type_prefix'] = 'Pattern';
$lang['sviat_redirects__is_lang'] = 'Multilingual redirect';
$lang['sviat_redirects__is_lang_short'] = 'Lang';
$lang['sviat_redirects__is_lang_hint'] = 'When enabled, the rule also works for language-prefixed URLs: /en, /ua, etc.';
$lang['sviat_redirects__instruction_exact_title'] = 'Exact';
$lang['sviat_redirects__instruction_exact_text'] = '— for one specific URL.';
$lang['sviat_redirects__instruction_exact_example'] = 'Example';
$lang['sviat_redirects__instruction_prefix_title'] = 'Pattern';
$lang['sviat_redirects__instruction_prefix_text'] = '— for a group of similar URLs (pattern).';
$lang['sviat_redirects__instruction_slug_text'] = 'If you use $slug, the system inserts the URL part automatically.';
$lang['sviat_redirects__instruction_prefix_example'] = 'Example';
$lang['sviat_redirects__instruction_prefix_result'] = 'Result';
$lang['sviat_redirects__instruction_prefix_fixed_text'] = 'If $slug is only on the left, the right side is a fixed page:';
$lang['sviat_redirects__instruction_is_lang_title'] = 'Multilingual';
$lang['sviat_redirects__instruction_is_lang_text'] = 'When enabled, a rule without language prefix also works for /en, /ua and other language URLs.';
$lang['sviat_redirects__instruction_priority_title'] = 'Important:';
$lang['sviat_redirects__instruction_priority_text'] = 'if both rules match, Exact has priority over Prefix.';


$lang['sviat_redirects__import_instruction_format'] = 'File must be in CSV format (delimiter ; or ,).';
$lang['sviat_redirects__import_instruction_header'] = 'First row must be a header';
$lang['sviat_redirects__import_instruction_required'] = 'Required fields: from_url and to_url.';
$lang['sviat_redirects__import_instruction_relative_urls'] = 'Use relative URLs without domain (e.g. old-page, catalog/new-page).';
$lang['sviat_redirects__import_instruction_status'] = 'status: 301 or 302 (if empty, 301 is used).';
$lang['sviat_redirects__import_instruction_enabled'] = 'enabled: 1 (enabled) or 0 (disabled).';
$lang['sviat_redirects__import_instruction_is_lang'] = 'is_lang: 1 - also apply rule for language-prefixed URLs (/en, /ua...), 0 - only without prefix.';
$lang['sviat_redirects__import_instruction_type'] = 'type: exact or pattern.';
$lang['sviat_redirects__import_instruction_pattern_slug'] = 'For pattern you can use $slug';
$lang['sviat_redirects__import_instruction_pattern_fixed'] = 'If $slug is only on the left, the right side is fixed';
$lang['sviat_redirects__import_instruction_priority'] = 'Important: if both exact and pattern match, exact wins.';
$lang['sviat_redirects__import_instruction_example_exact'] = 'Exact example';
$lang['sviat_redirects__import_instruction_example_is_lang'] = 'Multilingual exact example';
$lang['sviat_redirects__import_instruction_example_pattern'] = 'Pattern example';

$lang['sviat_redirects__yes'] = 'Yes, enabled';
$lang['sviat_redirects__no'] = 'No, disabled';
