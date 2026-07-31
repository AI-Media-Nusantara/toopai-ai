<?php
// application/config/performance.php

// Database caching
$config['cache_path'] = APPPATH . 'cache/';
$config['cache_query_string'] = FALSE;

// Compression
$config['compress_output'] = TRUE;

// Minify HTML output
$config['minify_html'] = TRUE;

// Enable profiling in development
$config['enable_profiler'] = ENVIRONMENT === 'development';
?>