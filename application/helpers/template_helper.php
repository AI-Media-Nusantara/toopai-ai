<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Template Helper
 * Fungsi-fungsi untuk membantu tampilan template
 */

if (!function_exists('render_header')) {
    function render_header($data = []) {
        $CI =& get_instance();
        $CI->load->view('templates/header', $data);
    }
}

if (!function_exists('render_footer')) {
    function render_footer() {
        $CI =& get_instance();
        $CI->load->view('templates/footer');
    }
}

if (!function_exists('render_sidebar')) {
    function render_sidebar() {
        $CI =& get_instance();
        $CI->load->view('templates/sidebar');
    }
}

if (!function_exists('active_menu')) {
    function active_menu($segment, $value, $class = 'active') {
        $CI =& get_instance();
        return ($CI->uri->segment($segment) == $value) ? $class : '';
    }
}

if (!function_exists('format_currency')) {
    function format_currency($amount) {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('format_date')) {
    function format_date($date, $format = 'd M Y') {
        if (!$date || $date == '0000-00-00') return '-';
        return date($format, strtotime($date));
    }
}

if (!function_exists('get_status_badge')) {
    function get_status_badge($status) {
        $badges = [
            'ACTIVE' => '<span class="badge badge-success">Active</span>',
            'INACTIVE' => '<span class="badge badge-secondary">Inactive</span>',
            'PENDING' => '<span class="badge badge-warning">Pending</span>',
            'ONGOING' => '<span class="badge badge-success">Ongoing</span>',
            'COMPLETED' => '<span class="badge badge-info">Completed</span>',
            'CANCELLED' => '<span class="badge badge-danger">Cancelled</span>'
        ];
        return $badges[$status] ?? '<span class="badge badge-secondary">' . $status . '</span>';
    }
}