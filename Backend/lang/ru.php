<?php

$lang['sviat_redirects__menu_title'] = 'Редиректы';

$lang['sviat_redirects__title'] = 'Редиректы';
$lang['sviat_redirects__empty_list'] = 'Редиректов пока нет';
$lang['sviat_redirects__search_placeholder'] = 'Поиск по названию или URL';

$lang['sviat_redirects__add'] = 'Добавить редирект';
$lang['sviat_redirects__import'] = 'Импорт';
$lang['sviat_redirects__export'] = 'Экспорт';
$lang['sviat_redirects__import_title'] = 'Импорт редиректов из CSV';
$lang['sviat_redirects__button_select_file'] = 'Выбрать файл';
$lang['sviat_redirects__import_submit'] = 'Импортировать';
$lang['sviat_redirects__import_download_template'] = 'Скачать шаблон';
$lang['sviat_redirects__import_hint'] = 'Загрузите CSV-файл. Поддерживаются разделители "," и ";".';
$lang['sviat_redirects__import_columns'] = 'Колонки: from_url, to_url, name, status, enabled, is_lang (обязательные: from_url, to_url).';
$lang['sviat_redirects__import_done'] = 'Импорт завершен';
$lang['sviat_redirects__import_upload_error'] = 'Не удалось загрузить CSV-файл';
$lang['sviat_redirects__import_total'] = 'Строк в файле';
$lang['sviat_redirects__import_created'] = 'Добавлено';
$lang['sviat_redirects__import_updated'] = 'Обновлено';
$lang['sviat_redirects__import_update_existing'] = 'Обновлять существующие редиректы по from_url';
$lang['sviat_redirects__import_duplicates'] = 'Пропущено дубликатов';
$lang['sviat_redirects__import_invalid'] = 'Пропущено некорректных';
$lang['sviat_redirects__back_to_list'] = 'К списку редиректов';
$lang['sviat_redirects__new'] = 'Новый редирект';
$lang['sviat_redirects__added'] = 'Редирект создан';
$lang['sviat_redirects__updated'] = 'Редирект обновлен';
$lang['sviat_redirects__delete'] = 'Удалить редирект';
$lang['sviat_redirects__used_url'] = 'Этот URL-источник уже используется в редиректе';

$lang['sviat_redirects__label_from'] = 'URL откуда';
$lang['sviat_redirects__label_to'] = 'URL куда';
$lang['sviat_redirects__url_hint'] = 'Используйте относительный URL без домена, например: catalog/old-product';
$lang['sviat_redirects__url_hint_from'] = 'Используйте относительный URL без домена, например: catalog/old-product';
$lang['sviat_redirects__url_hint_to'] = 'Используйте относительный URL без домена, например: catalog/new-product';
$lang['sviat_redirects__error_empty_name'] = 'Укажите название редиректа';
$lang['sviat_redirects__error_empty_url_from'] = 'Укажите URL-источник';
$lang['sviat_redirects__error_empty_url_to'] = 'Укажите URL-назначение';

$lang['sviat_redirects__status'] = 'Код статуса';
$lang['sviat_redirects__status_301'] = '301 (Постоянный)';
$lang['sviat_redirects__status_302'] = '302 (Временный)';

$lang['sviat_redirects__filter_301'] = 'Только 301';
$lang['sviat_redirects__filter_302'] = 'Только 302';
$lang['sviat_redirects__filter_enabled'] = 'Включены';
$lang['sviat_redirects__filter_disabled'] = 'Выключены';
$lang['sviat_redirects__activity'] = 'Активен';
$lang['sviat_redirects__reset_filters'] = 'Сбросить фильтры';
$lang['sviat_redirects__stats'] = 'Статистика';
$lang['sviat_redirects__hits'] = 'Переходы';
$lang['sviat_redirects__last_hit'] = 'Последний переход';

$lang['sviat_redirects__set_status_301'] = 'Установить статус 301';
$lang['sviat_redirects__set_status_302'] = 'Установить статус 302';

$lang['sviat_redirects__type'] = 'Тип совпадения';
$lang['sviat_redirects__type_exact'] = 'Точный (exact)';
$lang['sviat_redirects__type_prefix'] = 'Шаблон (pattern)';
$lang['sviat_redirects__is_lang'] = 'Мультиязычный редирект';
$lang['sviat_redirects__is_lang_short'] = 'Язык';
$lang['sviat_redirects__is_lang_hint'] = 'Если включено, правило также срабатывает для URL с префиксом языка: /en, /ua и т.д.';
$lang['sviat_redirects__instruction_exact_title'] = 'Точный (exact)';
$lang['sviat_redirects__instruction_exact_text'] = '— для одного конкретного URL.';
$lang['sviat_redirects__instruction_exact_example'] = 'Пример';
$lang['sviat_redirects__instruction_prefix_title'] = 'Шаблон (pattern)';
$lang['sviat_redirects__instruction_prefix_text'] = '— для группы URL одного типа (шаблон).';
$lang['sviat_redirects__instruction_slug_text'] = 'Если указать $slug, система подставит часть адреса автоматически.';
$lang['sviat_redirects__instruction_prefix_example'] = 'Пример';
$lang['sviat_redirects__instruction_prefix_result'] = 'Результат';
$lang['sviat_redirects__instruction_prefix_fixed_text'] = 'Если $slug указан только слева, справа будет фиксированная страница:';
$lang['sviat_redirects__instruction_is_lang_title'] = 'Мультиязычность';
$lang['sviat_redirects__instruction_is_lang_text'] = 'Если включено, правило без языкового префикса работает и для /en, /ua и других языковых URL.';
$lang['sviat_redirects__instruction_priority_title'] = 'Важно:';
$lang['sviat_redirects__instruction_priority_text'] = 'если подходят оба правила, сработает Точный (exact).';


$lang['sviat_redirects__import_instruction_format'] = 'Файл должен быть в формате CSV (разделитель ; или ,).';
$lang['sviat_redirects__import_instruction_header'] = 'Первая строка — заголовок';
$lang['sviat_redirects__import_instruction_required'] = 'Обязательные поля: from_url и to_url.';
$lang['sviat_redirects__import_instruction_relative_urls'] = 'URL указывайте без домена (например: old-page, catalog/new-page).';
$lang['sviat_redirects__import_instruction_status'] = 'status: 301 или 302 (если пусто — будет 301).';
$lang['sviat_redirects__import_instruction_enabled'] = 'enabled: 1 (включено) или 0 (выключено).';
$lang['sviat_redirects__import_instruction_is_lang'] = 'is_lang: 1 - применять правило также для URL с префиксом языка (/en, /ua...), 0 - только без префикса.';
$lang['sviat_redirects__import_instruction_type'] = 'type: exact или pattern.';
$lang['sviat_redirects__import_instruction_pattern_slug'] = 'Для pattern можно использовать $slug';
$lang['sviat_redirects__import_instruction_pattern_fixed'] = 'Если $slug только слева, справа будет фиксированная страница';
$lang['sviat_redirects__import_instruction_priority'] = 'Важно: если подходят и exact, и pattern, сработает exact.';
$lang['sviat_redirects__import_instruction_example_exact'] = 'Пример exact';
$lang['sviat_redirects__import_instruction_example_is_lang'] = 'Пример is_lang exact';
$lang['sviat_redirects__import_instruction_example_pattern'] = 'Пример pattern';

$lang['sviat_redirects__yes'] = 'Да, включено';
$lang['sviat_redirects__no'] = 'Нет, выключено';
