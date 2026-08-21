<form class="sviat_redirects_reincarnation_form fn_sviat_redirects_reincarnation_form"
      method="post"
      action="{$smarty.server.REQUEST_URI|escape}">
    <input type="hidden" name="session_id" value="{$smarty.session.id}">
    <input type="hidden" name="reincarnation_action" value="scan">

    <button type="submit"
            class="btn btn_small btn-success sviat_redirects_reincarnation_button fn_sviat_redirects_reincarnation_button"
            data-running-text="{$btr->sviat_redirects__reincarnation_running|escape}"
            data-error-text="{$btr->sviat_redirects__reincarnation_error|escape}"
            data-busy-text="{$btr->sviat_redirects__reincarnation_busy|escape}"
            title="{$btr->sviat_redirects__reincarnation_hint|escape}">
        <span class="sviat_redirects_reincarnation_icon" aria-hidden="true">
            <i class="fa fa-refresh"></i>
        </span>
        <span class="fn_sviat_redirects_reincarnation_label">{$btr->sviat_redirects__reincarnation|escape}</span>
        <span class="sviat_redirects_reincarnation_count fn_sviat_redirects_reincarnation_count">
            {$reincarnation_status.found|default:0}
        </span>
    </button>

    {if $reincarnation_status.last_checked_at}
        <span class="sviat_redirects_reincarnation_last_check"
              title="{$btr->sviat_redirects__reincarnation_last_check|escape}: {$reincarnation_status.last_checked_at|escape}">
            {$reincarnation_status.last_checked_at|escape}
        </span>
    {/if}
</form>
