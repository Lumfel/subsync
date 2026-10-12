{{-- Notification bell + popover for portals that don't already have one (resident/officer). --}}
<div class="nc-wrap">
  <button class="nc-btn" id="ncButton" type="button" aria-expanded="false" aria-label="Notifications">
    &#128276;<span class="nc-badge" id="ncBadge" style="display:none;">0</span>
  </button>
  <div class="nc-pop" id="ncPop">
    <div class="nc-head">Notifications</div>
    <div class="nc-list" id="ncList"><div class="nc-empty">Loading…</div></div>
  </div>
</div>
