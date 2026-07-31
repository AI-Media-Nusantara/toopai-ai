<?php
/**
 * Excel Reader Helper - Tanpa Composer (Versi Debug)
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Baca file Excel .xlsx atau CSV
 */
function read_excel_native($file_path) {
    $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
    log_message('debug', 'Reading file: ' . $file_path . ', extension: ' . $extension);
    
    if ($extension == 'csv') {
        return read_csv_native($file_path);
    }
    
    if ($extension == 'xlsx') {
        return read_xlsx_simple($file_path);
    }
    
    return [];
}

/**
 * Baca CSV dengan berbagai delimiter
 */
function read_csv_native($file_path) {
      $data = [];
    
    // Baca file dengan delimiter ; (titik koma) karena FastMoss pakai itu
    $delimiter = ';';
    
    log_message('debug', 'CSV Debug - File: ' . $file_path);
    log_message('debug', 'Using delimiter: "' . $delimiter . '"');
    
    $handle = fopen($file_path, 'r');
    if ($handle === false) {
        log_message('error', 'Cannot open file');
        return [];
    }
    
    $row_index = 0;
    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        // Hapus BOM dari kolom pertama
        if ($row_index == 0 && !empty($row[0])) {
            $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
            // Juga hapus karakter BOM yang muncul sebagai string
            $row[0] = str_replace('﻿', '', $row[0]);
        }
        
        // Hapus kolom kosong di akhir
        while (count($row) > 0 && empty($row[count($row) - 1])) {
            array_pop($row);
        }
        
        if (count($row) > 0 && !empty(array_filter($row))) {
            $data[] = $row;
        }
        $row_index++;
    }
    fclose($handle);
    
    log_message('debug', 'Total rows parsed: ' . count($data));
    if (!empty($data)) {
        log_message('debug', 'First row: ' . json_encode($data[0]));
        log_message('debug', 'First row columns count: ' . count($data[0]));
    }
    
    return $data;
}
/**
 * Baca XLSX dengan metode sederhana
 */
function read_xlsx_simple($file_path) {
    $data = [];
    $temp_dir = sys_get_temp_dir() . '/excel_' . uniqid();
    
    if (!mkdir($temp_dir, 0777, true)) {
        log_message('error', 'Cannot create temp directory: ' . $temp_dir);
        return [];
    }
    
    try {
        $zip = new ZipArchive();
        if ($zip->open($file_path) === true) {
            $zip->extractTo($temp_dir);
            $zip->close();
            
            // Dapatkan shared strings
            $shared_strings = [];
            $shared_file = $temp_dir . '/xl/sharedStrings.xml';
            if (file_exists($shared_file)) {
                $content = file_get_contents($shared_file);
                
                // Simple XML parsing dengan regex (lebih robust)
                preg_match_all('/<si>.*?<t[^>]*>(.*?)<\/t>.*?<\/si>/s', $content, $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $match) {
                        // Clean XML tags
                        $clean = strip_tags($match);
                        $shared_strings[] = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    }
                }
            }
            
            // Baca sheet data
            $sheet_file = $temp_dir . '/xl/worksheets/sheet1.xml';
            if (!file_exists($sheet_file)) {
                // Coba cari sheet lain
                $sheets_dir = $temp_dir . '/xl/worksheets/';
                if (is_dir($sheets_dir)) {
                    $files = scandir($sheets_dir);
                    foreach ($files as $file) {
                        if (strpos($file, '.xml') !== false) {
                            $sheet_file = $sheets_dir . $file;
                            break;
                        }
                    }
                }
            }
            
            if (file_exists($sheet_file)) {
                $content = file_get_contents($sheet_file);
                
                // Extract rows using regex (lebih robust dari SimpleXML untuk file besar)
                preg_match_all('/<row[^>]*>(.*?)<\/row>/s', $content, $row_matches);
                
                if (!empty($row_matches[1])) {
                    foreach ($row_matches[1] as $row_xml) {
                        $row_data = [];
                        
                        // Extract cells
                        preg_match_all('/<c[^>]*>(.*?)<\/c>/s', $row_xml, $cell_matches);
                        
                        if (!empty($cell_matches[1])) {
                            foreach ($cell_matches[1] as $cell_xml) {
                                $value = '';
                                
                                // Cek tipe cell
                                if (preg_match('/t="s"/', $cell_xml)) {
                                    // Shared string
                                    if (preg_match('/<v>(.*?)<\/v>/', $cell_xml, $v_match)) {
                                        $idx = intval($v_match[1]);
                                        $value = isset($shared_strings[$idx]) ? $shared_strings[$idx] : '';
                                    }
                                } elseif (preg_match('/<v>(.*?)<\/v>/', $cell_xml, $v_match)) {
                                    $value = $v_match[1];
                                } elseif (preg_match('/<is>.*?<t[^>]*>(.*?)<\/t>.*?<\/is>/s', $cell_xml, $t_match)) {
                                    $value = strip_tags($t_match[1]);
                                }
                                
                                $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                $row_data[] = trim($value);
                            }
                        }
                        
                        if (!empty($row_data)) {
                            $data[] = $row_data;
                        }
                    }
                }
            } else {
                log_message('error', 'Sheet file not found in: ' . $temp_dir);
            }
        } else {
            log_message('error', 'Cannot open zip file: ' . $file_path);
        }
    } catch (Exception $e) {
        log_message('error', 'XLSX read error: ' . $e->getMessage());
    }
    
    // Cleanup
    delete_directory($temp_dir);
    
    log_message('debug', 'XLSX read: ' . count($data) . ' rows');
    
    // Debug: log first row jika ada
    if (!empty($data)) {
        log_message('debug', 'First row: ' . json_encode($data[0]));
    }
    
    return $data;
}

/**
 * Delete directory recursively
 */
function delete_directory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        if (!delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    
    return rmdir($dir);
}