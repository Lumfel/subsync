<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Guards the Quick Wins batch:
 *  - login pages are real forms (Enter submits) with announced messages,
 *  - the JS uses the correct loader/ids that were previously broken,
 *  - the accessibility/confirmation helpers exist.
 */
class FrontendRegressionTest extends TestCase
{
    private function js(string $file): string
    {
        return file_get_contents(public_path('js/'.$file));
    }

    private function css(string $file): string
    {
        return file_get_contents(public_path('css/'.$file));
    }

    public function test_login_page_is_a_form_with_accessible_messages(): void
    {
        $res = $this->get('/login')->assertOk();

        $res->assertSee('<form class="form-panel active" id="loginPanel"', false);
        $res->assertSee('<form class="form-panel" id="registerPanel"', false);
        $res->assertSee('autofocus', false);
        $res->assertSee('role="alert"', false);
        $res->assertSee('aria-live="polite"', false);
        // The fake "reset link sent" message must be gone.
        $res->assertDontSee('Password reset link sent', false);
    }

    public function test_admin_login_page_has_accessible_messages_and_autofocus(): void
    {
        $this->get('/admin-access')->assertOk()
            ->assertSee('role="alert"', false)
            ->assertSee('autofocus', false);
    }

    public function test_resident_js_uses_correct_thread_function_and_badge_id(): void
    {
        $js = $this->js('residents.js');

        $this->assertStringContainsString('loadResidentThreads(', $js);
        $this->assertStringNotContainsString('loadThreads(', $js);
        $this->assertStringContainsString("getElementById('badge-messages')", $js);
        $this->assertStringNotContainsString('msg-badge', $js);
    }

    public function test_officer_js_uses_correct_badge_id_and_no_dead_expense_loader(): void
    {
        $js = $this->js('officer-portal.js');

        $this->assertStringContainsString("getElementById('badge-messages')", $js);
        $this->assertStringNotContainsString('msg-badge', $js);
        $this->assertStringNotContainsString('loadExpenses(', $js);
    }

    public function test_admin_js_uses_real_realtime_loaders(): void
    {
        $js = $this->js('admin-dashboard.js');

        $this->assertStringContainsString('loadAdminPolicies', $js);
        $this->assertStringContainsString('loadAdminExpenses', $js);
        $this->assertStringNotContainsString('typeof loadPolicies', $js);
        $this->assertStringNotContainsString('typeof loadExpenses', $js);
    }

    public function test_ui_helpers_provide_a11y_and_busy_controls(): void
    {
        $js = $this->js('ui.js');

        $this->assertStringContainsString('aria-live', $js);
        $this->assertStringContainsString('function busy(', $js);
        $this->assertStringContainsString('aria-modal', $js);
        $this->assertStringContainsString('busy,', $js);
    }

    public function test_destructive_admin_actions_are_confirmed(): void
    {
        $js = $this->js('admin-dashboard.js');

        foreach (['deleteAnn', 'removeOfficer', 'deleteHousehold', 'deactivateResident', 'deleteFamily'] as $fn) {
            $this->assertMatchesRegularExpression(
                '/async function '.preg_quote($fn, '/').'\([^)]*\)\{[^}]*SubUI\.confirm/s',
                $js,
                "{$fn}() should ask for confirmation before mutating."
            );
        }
    }

    public function test_mobile_css_keeps_logout_visible(): void
    {
        $this->assertStringContainsString(':not(.keep-mobile)', $this->css('admin-dashboard.css'));
    }

    public function test_conversation_delete_is_reachable_on_touch(): void
    {
        foreach (['residents.css', 'officer-portal.css'] as $file) {
            $css = $this->css($file);
            $this->assertStringContainsString('@media (hover: none)', $css, "{$file} must show row actions on touch devices.");
            $this->assertStringNotContainsString('[contenteditable]:focus { outline: none; }', $css);
        }
    }

    public function test_busy_and_required_helpers_are_wired(): void
    {
        $ui = $this->js('ui.js');
        $this->assertStringContainsString('function busyClick(', $ui);
        $this->assertStringContainsString('function required(', $ui);
        $this->assertStringContainsString('enhanceNavA11y', $ui);

        $this->assertStringContainsString('SubUI.required(', $this->js('residents.js'));
        $this->assertStringContainsString('SubUI.required(', $this->js('officer-portal.js'));

        $admin = $this->js('admin-dashboard.js');
        $this->assertStringContainsString('SubUI.required(', $admin);
        $this->assertStringContainsString('let payments = [];', $admin);
        $this->assertStringContainsString('let paymentStatuses = [];', $admin);
    }

    public function test_submit_buttons_use_the_busy_click_guard(): void
    {
        foreach (['residents.blade.php', 'officer-portal.blade.php', 'admin-dashboard.blade.php'] as $view) {
            $html = file_get_contents(resource_path('views/layouts/'.$view));
            $this->assertStringContainsString('SubUI.busyClick(this,', $html, "{$view} should guard submit buttons against double-submit.");
        }
    }

    public function test_success_is_only_reported_on_success(): void
    {
        $admin = $this->js('admin-dashboard.js');
        $this->assertStringContainsString("if(!res||!res.success){ showToast('Could not add household.'); return; }", $admin);
        $this->assertStringContainsString("if(!res||!res.success){ showToast('Could not update status.'); return; }", $admin);

        $officer = $this->js('officer-portal.js');
        $this->assertStringContainsString('if(!statusRes||!statusRes.success){', $officer);
    }

    public function test_policy_preview_numbering_is_consistent(): void
    {
        $html = file_get_contents(resource_path('views/layouts/residents.blade.php'));

        $this->assertStringContainsString('3. Noise, Conduct &amp; Community Standards', $html);
        $this->assertStringNotContainsString('4. Quiet Hours &amp; Noise Control', $html);
    }

    public function test_mapping_page_uses_the_brand_logo(): void
    {
        $html = file_get_contents(resource_path('views/layouts/mapping.blade.php'));

        $this->assertStringContainsString("asset('emblem_1.svg')", $html);
        $this->assertStringNotContainsString('dashbaord_2.svg', $html);
    }

    public function test_known_light_on_light_text_is_gone(): void
    {
        $residents = file_get_contents(resource_path('views/layouts/residents.blade.php'));
        $this->assertStringNotContainsString('rgba(250,246,240,0.6)', $residents);
        $this->assertStringNotContainsString('background:var(--surface,#1e2530)', $residents);

        // Announcement banners use a dark surface in both themes, so text must be light.
        $this->assertStringContainsString('color: #F5F1EA;', $this->css('residents.css'));
        $this->assertStringContainsString('color:#F5F1EA;', $this->css('officer-portal.css'));

        // Mapping page hamburger was white on a cream background.
        $this->assertMatchesRegularExpression('/\.menu-toggle \{[^}]*color:\s*#2B2927;/s', file_get_contents(public_path('styles.css')));
    }

    public function test_dark_theme_badge_and_fff_overrides_exist(): void
    {
        $ui = $this->css('ui-polish.css');

        $this->assertStringContainsString('html[data-theme="dark"] .sidebar .tab-badge', $ui);
        $this->assertStringContainsString('[style*="background:#fff"]', $ui);
    }

    public function test_realtime_retry_is_bounded(): void
    {
        foreach (['residents.js', 'officer-portal.js', 'admin-dashboard.js'] as $file) {
            $js = $this->js($file);
            $this->assertStringContainsString('initSubSyncRealtime(attempt', $js, "{$file} should bound the Echo retry loop.");
            $this->assertStringNotContainsString('return setTimeout(initSubSyncRealtime, 300);', $js, "{$file} should not retry the Echo loop forever.");
        }
    }

    public function test_admin_lists_are_paginated_client_side(): void
    {
        $js = $this->js('admin-dashboard.js');

        $this->assertStringContainsString('residentCap', $js);
        $this->assertStringContainsString('issueCap', $js);
        $this->assertStringContainsString('more (${_more} remaining)', $js);
    }

    public function test_resident_announcement_banner_exists_and_is_guarded(): void
    {
        $html = file_get_contents(resource_path('views/layouts/residents.blade.php'));
        $this->assertStringContainsString('id="annNotif"', $html);
        $this->assertStringContainsString('id="annNotifTitle"', $html);
        $this->assertStringContainsString('id="annNotifViewBtn"', $html);

        $this->assertStringContainsString('if(!notif || !ann) return;', $this->js('residents.js'));
    }

    public function test_terminology_is_consistent_across_portals(): void
    {
        $admin = file_get_contents(resource_path('views/layouts/admin-dashboard.blade.php'));
        $this->assertStringContainsString('Household Members', $admin);
        $this->assertStringContainsString('Households &amp; Users', $admin);
        $this->assertStringContainsString('</span> Documents ', $admin);
        $this->assertStringContainsString('</span> Issues ', $admin);
        $this->assertStringNotContainsString('</span> Issue Reports', $admin);
        $this->assertStringNotContainsString('</span> Manage Users', $admin);

        $officer = file_get_contents(resource_path('views/layouts/officer-portal.blade.php'));
        $this->assertStringContainsString(' Documents</li>', $officer);
        $this->assertStringContainsString(' Issues</li>', $officer);
        $this->assertStringNotContainsString('Send Files', $officer);

        $resident = file_get_contents(resource_path('views/layouts/residents.blade.php'));
        $this->assertStringContainsString(' Issues</li>', $resident);
        $this->assertStringNotContainsString('Concerns', $resident);

        $this->assertStringContainsString("manageusers:'Households & Users'", $this->js('admin-dashboard.js'));
    }

    public function test_shared_money_and_date_formatters_exist(): void
    {
        $ui = $this->js('ui.js');
        $this->assertStringContainsString('function money(', $ui);
        $this->assertStringContainsString('function dateFmt(', $ui);
        $this->assertStringContainsString('money, dateFmt', $ui);

        $this->assertStringContainsString('SubUI.money(r.current_balance)', $this->js('admin-dashboard.js'));
    }

    public function test_sse_uses_a_fetch_based_polyfill_so_ngrok_works(): void
    {
        $ui = $this->js('ui.js');
        $this->assertStringContainsString('function FetchEventSource(', $ui);
        $this->assertStringContainsString('window.EventSource = FetchEventSource', $ui);
        $this->assertStringContainsString("'Accept': 'text/event-stream'", $ui);
    }

    public function test_officer_role_fields_are_removed_everywhere(): void
    {
        $login = file_get_contents(resource_path('views/layouts/login_residen_officers.blade.php'));
        $this->assertStringNotContainsString('id="off-role"', $login);
        $this->assertStringNotContainsString('Officer Role', $login);
        $this->assertStringNotContainsString('role_description', $login);

        $admin = file_get_contents(resource_path('views/layouts/admin-dashboard.blade.php'));
        $this->assertStringNotContainsString('newOfficerRole', $admin);
        $this->assertStringNotContainsString('editOfficerRole', $admin);
        $this->assertStringNotContainsString('Role / Position', $admin);

        $officer = file_get_contents(resource_path('views/layouts/officer-portal.blade.php'));
        $this->assertStringNotContainsString('id="roleText"', $officer);
        $this->assertStringNotContainsString('$officerRole', $officer);

        $js = $this->js('admin-dashboard.js').$this->js('officer-portal.js');
        $this->assertStringNotContainsString('role_description', $js);
    }

    public function test_message_requests_ui_is_wired(): void
    {
        foreach (['residents.js' => 'acceptResRequest', 'officer-portal.js' => 'acceptOfficerRequest'] as $file => $fn) {
            $js = $this->js($file);
            $this->assertStringContainsString('/api/messages/requests', $js);
            $this->assertStringContainsString($fn, $js);
            $this->assertStringContainsString('msg-req-block', $js);
            $this->assertStringContainsString("'/accept'", $js);
            $this->assertStringContainsString("'/reject'", $js);
        }

        $this->assertStringContainsString('.msg-req-block', $this->css('ui-polish.css'));
    }

    public function test_sidebar_does_not_scroll_horizontally(): void
    {
        $ui = $this->css('ui-polish.css');
        $this->assertStringContainsString('.sidebar, .sidebar-nav { overflow-x: hidden; }', $ui);
        $this->assertStringContainsString('::-webkit-scrollbar { width: 8px; height: 8px; background: transparent; }', $ui);

        // The admin sheet used to paint a dark (near-black) scrollbar track.
        $admin = $this->css('admin-dashboard.css');
        $this->assertStringNotContainsString('::-webkit-scrollbar { width: 6px; background: rgba(0,0,0,0.2); }', $admin);
    }

    public function test_sidebar_nav_rows_are_left_aligned(): void
    {
        $ui = $this->css('ui-polish.css');
        $this->assertStringContainsString('.sidebar .tab-btn {', $ui);
        $this->assertStringContainsString('justify-content: flex-start !important;', $ui);
        $this->assertStringContainsString('.sidebar .tab-btn .tab-badge { margin-left: auto; }', $ui);
    }

    public function test_reports_ui_is_wired(): void
    {
        $residents = file_get_contents(resource_path('views/layouts/residents.blade.php'));
        $this->assertStringContainsString('id="panel-reports"', $residents);
        $this->assertStringContainsString('id="reportTemplateSelect"', $residents);
        $this->assertStringContainsString('id="myReportsList"', $residents);

        $js = $this->js('residents.js');
        $this->assertStringContainsString('function generateReport(', $js);
        $this->assertStringContainsString('function loadMyReports(', $js);
        $this->assertStringContainsString('/api/reports/my', $js);

        $admin = file_get_contents(resource_path('views/layouts/admin-dashboard.blade.php'));
        $this->assertStringContainsString('id="reportTemplateAdminList"', $admin);
        $this->assertStringContainsString('function saveReportTemplate(', $this->js('admin-dashboard.js'));
    }

    public function test_p1_features_ui_is_wired(): void
    {
        $residents = file_get_contents(resource_path('views/layouts/residents.blade.php'));
        $this->assertStringContainsString('id="panel-calendar"', $residents);
        $this->assertStringContainsString('id="eventsList"', $residents);
        $this->assertStringContainsString('function loadEvents(', $this->js('residents.js'));

        $admin = file_get_contents(resource_path('views/layouts/admin-dashboard.blade.php'));
        $this->assertStringContainsString('id="adminEventList"', $admin);
        $this->assertStringContainsString('id="residentImportFile"', $admin);
        $this->assertStringContainsString('id="issueResidentSelect"', $admin);

        $js = $this->js('admin-dashboard.js');
        $this->assertStringContainsString('function importResidents(', $js);
        $this->assertStringContainsString('function issueReport(', $js);
        $this->assertStringContainsString('function saveEvent(', $js);
    }
}
