<?php

$lang['sviat_redirects__menu_title'] = 'Редіректи';

$lang['sviat_redirects__title'] = 'Редіректи';
$lang['sviat_redirects__empty_list'] = 'Редіректів поки що немає';
$lang['sviat_redirects__search_placeholder'] = 'Пошук за назвою або URL';

$lang['sviat_redirects__add'] = 'Додати редірект';
$lang['sviat_redirects__import'] = 'Імпорт';
$lang['sviat_redirects__export'] = 'Експорт';
$lang['sviat_redirects__import_title'] = 'Імпорт редіректів з CSV';
$lang['sviat_redirects__button_select_file'] = 'Вибрати файл';
$lang['sviat_redirects__import_submit'] = 'Імпортувати';
$lang['sviat_redirects__import_download_template'] = 'Завантажити шаблон';
$lang['sviat_redirects__import_hint'] = 'Завантажте CSV-файл. Підтримуються розділювачі "," або ";".';
$lang['sviat_redirects__import_columns'] = 'Колонки: from_url, to_url, name, status, enabled, is_lang (обов\'язкові: from_url, to_url).';
$lang['sviat_redirects__import_done'] = 'Імпорт завершено';
$lang['sviat_redirects__import_upload_error'] = 'Не вдалося завантажити CSV-файл';
$lang['sviat_redirects__import_total'] = 'Рядків у файлі';
$lang['sviat_redirects__import_created'] = 'Додано';
$lang['sviat_redirects__import_updated'] = 'Оновлено';
$lang['sviat_redirects__import_update_existing'] = 'Оновлювати існуючі редіректи за from_url';
$lang['sviat_redirects__import_duplicates'] = 'Пропущено дублікатів';
$lang['sviat_redirects__import_invalid'] = 'Пропущено некоректних';
$lang['sviat_redirects__back_to_list'] = 'До списку редіректів';
$lang['sviat_redirects__new'] = 'Новий редірект';
$lang['sviat_redirects__added'] = 'Редірект створено';
$lang['sviat_redirects__updated'] = 'Редірект оновлено';
$lang['sviat_redirects__delete'] = 'Видалити редірект';
$lang['sviat_redirects__used_url'] = 'Цей URL-джерело вже використовується в редіректі';

$lang['sviat_redirects__label_from'] = 'URL звідки';
$lang['sviat_redirects__label_to'] = 'URL куди';
$lang['sviat_redirects__url_hint'] = 'Використовуйте відносний URL без домену, наприклад: catalog/old-product';
$lang['sviat_redirects__url_hint_from'] = 'Використовуйте відносний URL без домену, наприклад: catalog/old-product';
$lang['sviat_redirects__url_hint_to'] = 'Використовуйте відносний URL без домену, наприклад: catalog/new-product';
$lang['sviat_redirects__error_empty_name'] = 'Вкажіть назву редіректу';
$lang['sviat_redirects__error_empty_url_from'] = 'Вкажіть URL-джерело';
$lang['sviat_redirects__error_empty_url_to'] = 'Вкажіть URL-призначення';

$lang['sviat_redirects__status'] = 'Код статусу';
$lang['sviat_redirects__status_301'] = '301 (Постійний)';
$lang['sviat_redirects__status_302'] = '302 (Тимчасовий)';

$lang['sviat_redirects__filter_301'] = 'Лише 301';
$lang['sviat_redirects__filter_302'] = 'Лише 302';
$lang['sviat_redirects__filter_enabled'] = 'Увімкнені';
$lang['sviat_redirects__filter_disabled'] = 'Вимкнені';
$lang['sviat_redirects__activity'] = 'Активний';
$lang['sviat_redirects__reset_filters'] = 'Скинути фільтри';
$lang['sviat_redirects__stats'] = 'Статистика';
$lang['sviat_redirects__hits'] = 'Переходи';
$lang['sviat_redirects__last_hit'] = 'Останній перехід';

$lang['sviat_redirects__set_status_301'] = 'Встановити статус 301';
$lang['sviat_redirects__set_status_302'] = 'Встановити статус 302';

$lang['sviat_redirects__type'] = 'Тип збігу';
$lang['sviat_redirects__type_exact'] = 'Точний (exact)';
$lang['sviat_redirects__type_prefix'] = 'Шаблон (pattern)';
$lang['sviat_redirects__is_lang'] = 'Мультимовний редірект';
$lang['sviat_redirects__is_lang_short'] = 'Мова';
$lang['sviat_redirects__is_lang_hint'] = 'Якщо увімкнено, правило спрацює також для URL з префіксом мови: /en, /ua тощо.';
$lang['sviat_redirects__instruction_exact_title'] = 'Точний (exact)';
$lang['sviat_redirects__instruction_exact_text'] = '— для одного конкретного URL.';
$lang['sviat_redirects__instruction_exact_example'] = 'Приклад';
$lang['sviat_redirects__instruction_prefix_title'] = 'Шаблон (pattern)';
$lang['sviat_redirects__instruction_prefix_text'] = '— для групи URL одного типу (шаблон).';
$lang['sviat_redirects__instruction_slug_text'] = 'Якщо вказати $slug, система підставить частину адреси автоматично.';
$lang['sviat_redirects__instruction_prefix_example'] = 'Приклад';
$lang['sviat_redirects__instruction_prefix_result'] = 'Результат';
$lang['sviat_redirects__instruction_prefix_fixed_text'] = 'Якщо $slug вказаний тільки зліва, праворуч буде фіксована сторінка:';
$lang['sviat_redirects__instruction_is_lang_title'] = 'Мультимовність';
$lang['sviat_redirects__instruction_is_lang_text'] = 'Якщо увімкнено, правило без префікса мови працює і для /en, /ua та інших мовних URL.';
$lang['sviat_redirects__instruction_priority_title'] = 'Важливо:';
$lang['sviat_redirects__instruction_priority_text'] = 'якщо підходять обидва правила, спрацює Точний (exact).';


$lang['sviat_redirects__import_instruction_format'] = 'Файл має бути у форматі CSV (розділювач ; або ,).';
$lang['sviat_redirects__import_instruction_header'] = 'Перший рядок — заголовок';
$lang['sviat_redirects__import_instruction_required'] = 'Обовʼязково заповнюйте: from_url і to_url.';
$lang['sviat_redirects__import_instruction_relative_urls'] = 'URL вказуйте без домену (наприклад: old-page, catalog/new-page).';
$lang['sviat_redirects__import_instruction_status'] = 'status: 301 або 302 (якщо порожньо — буде 301).';
$lang['sviat_redirects__import_instruction_enabled'] = 'enabled: 1 (увімкнено) або 0 (вимкнено).';
$lang['sviat_redirects__import_instruction_is_lang'] = 'is_lang: 1 — застосовувати правило також для URL з префіксом мови (/en, /ua...), 0 — лише без префікса.';
$lang['sviat_redirects__import_instruction_type'] = 'type: exact (точний) або pattern (за шаблоном).';
$lang['sviat_redirects__import_instruction_pattern_slug'] = 'Для pattern можна використовувати $slug';
$lang['sviat_redirects__import_instruction_pattern_fixed'] = 'Якщо $slug є тільки зліва, праворуч буде фіксована сторінка';
$lang['sviat_redirects__import_instruction_priority'] = 'Важливо: якщо підходять і exact, і pattern, спрацює exact.';
$lang['sviat_redirects__import_instruction_example_exact'] = 'Приклад exact';
$lang['sviat_redirects__import_instruction_example_is_lang'] = 'Приклад is_lang exact';
$lang['sviat_redirects__import_instruction_example_pattern'] = 'Приклад pattern';

$lang['sviat_redirects__yes'] = 'Так, увімкнено';
$lang['sviat_redirects__no'] = 'Ні, вимкнено';
