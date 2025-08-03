php<?php
// File: uninstall.php (separate file, not in your main plugin file)

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Clean up options when plugin is deleted
delete_option('pempo_citation_style');
delete_option('pempo_source_reliability');