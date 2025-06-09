<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Capture the content using output buffering
ob_start();
?>

<form id="searchform" method="get" action="https://portal.bestlinks.fun/">
    <input type="search" name="s" class="search-input" placeholder="Search" autocomplete="on">
    <button type="submit" class="search-submit">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="3">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
    </button>
</form>

<?php
// Return the captured content
echo ob_get_clean();