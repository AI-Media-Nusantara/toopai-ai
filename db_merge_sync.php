#!/usr/bin/env php
<?php
/**
 * Database Merge & Sync Tool
 * Auto-sync schema & data dari Development DB ke Production DB
 * 
 * Usage:
 *   php db_merge_sync.php --dry-run
 *   php db_merge_sync.php --table=brands
 *   php db_merge_sync.php --phase=2
 *   php db_merge_sync.php --skip-backup
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');
set_time_limit(0);

class DatabaseMergeTool
{
    private $source_conn;
    private $target_conn;
    private $config;
    private $log = [];
    private $dry_run = false;
    private $brand_id_mapping = [];
    private $stats = [
        'tables_created' => 0,
        'columns_added' => 0,
        'indexes_added' => 0,
        'records_inserted' => 0,
        'records_skipped' => 0,
        'errors' => []
    ];
    
    // Tabel yang di-skip dari data migration
    private $skip_data_tables = [
        'sessions',
        'ci_sessions',
        'cache',
        'logs',
        'audit_log',
        'activity_log'
    ];
    
    public function __construct($config, $dry_run = false)
    {
        $this->config = $config;
        $this->dry_run = $dry_run;
        $this->log("=== DATABASE MERGE TOOL STARTED ===");
        $this->log("Mode: " . ($dry_run ? "DRY RUN (no actual changes)" : "LIVE EXECUTION"));
        $this->log("Timestamp: " . date('Y-m-d H:i:s'));
    }
    
    private function log($message, $level = 'INFO')
    {
        $timestamp = date('Y-m-d H:i:s');
        $line = "[$timestamp] [$level] $message";
        $this->log[] = $line;
        echo $line . PHP_EOL;
    }
    
    public function connect()
    {
        $this->log("Connecting to SOURCE database: {$this->config['source']['database']}");
        $this->source_conn = new mysqli(
            $this->config['source']['host'],
            $this->config['source']['username'],
            $this->config['source']['password'],
            $this->config['source']['database']
        );
        
        if ($this->source_conn->connect_error) {
            throw new Exception("SOURCE connection failed: " . $this->source_conn->connect_error);
        }
        $this->source_conn->set_charset('utf8mb4');
        $this->source_conn->query("SET SESSION sql_mode = ''");
        
        $this->log("Connecting to TARGET database: {$this->config['target']['database']}");
        $this->target_conn = new mysqli(
            $this->config['target']['host'],
            $this->config['target']['username'],
            $this->config['target']['password'],
            $this->config['target']['database']
        );
        
        if ($this->target_conn->connect_error) {
            throw new Exception("TARGET connection failed: " . $this->target_conn->connect_error);
        }
        $this->target_conn->set_charset('utf8mb4');
        $this->target_conn->query("SET SESSION sql_mode = ''");
        
        $this->log("✓ Database connections established");
    }
    
    public function phase1_backup()
    {
        $this->log("\n=== PHASE 1: PRE-FLIGHT CHECKS & BACKUP ===");
        
        if ($this->dry_run) {
            $this->log("[DRY-RUN] Would create backup of TARGET database");
            return;
        }
        
        $backup_file = "backups/holasync_toopai_before_merge_backup_" . date('Y-m-d_His') . ".sql";
        
        if (!is_dir('backups')) {
            mkdir('backups', 0755, true);
        }
        
        $this->log("Creating backup: $backup_file");
        
        // Try mysqldump first
        $mysqldump_path = trim((string)shell_exec('which mysqldump 2>/dev/null'));
        
        if (!empty($mysqldump_path) && file_exists($mysqldump_path)) {
            // Use mysqldump
            $tmp_cnf = tempnam(sys_get_temp_dir(), 'mysql_');
            file_put_contents($tmp_cnf, sprintf(
                "[client]\nhost=%s\nuser=%s\npassword=%s\n",
                $this->config['target']['host'],
                $this->config['target']['username'],
                $this->config['target']['password']
            ));
            chmod($tmp_cnf, 0600);
            
            $cmd = sprintf(
                "mysqldump --defaults-extra-file=%s %s > %s 2>&1",
                escapeshellarg($tmp_cnf),
                escapeshellarg($this->config['target']['database']),
                escapeshellarg($backup_file)
            );
            
            exec($cmd, $output, $return_code);
            unlink($tmp_cnf);
            
            if ($return_code === 0 && file_exists($backup_file) && filesize($backup_file) > 0) {
                $size = filesize($backup_file);
                $this->log("✓ Backup created successfully (" . number_format($size / 1024 / 1024, 2) . " MB)");
            } else {
                $this->log("⚠ mysqldump failed, using PHP fallback", 'WARN');
                $this->createBackupPHP($backup_file);
            }
        } else {
            // mysqldump not available — use PHP fallback
            $this->log("⚠ mysqldump not found, using PHP fallback", 'WARN');
            $this->createBackupPHP($backup_file);
        }
        
        // Disable foreign key checks di TARGET
        $this->target_conn->query("SET FOREIGN_KEY_CHECKS = 0");
        $this->log("✓ Foreign key checks disabled on TARGET");
    }
    
    private function createBackupPHP($backup_file)
    {
        $this->log("  → Creating PHP-based backup (structure only)...");
        
        $fp = fopen($backup_file, 'w');
        if (!$fp) {
            throw new Exception("Cannot create backup file: $backup_file");
        }
        
        fwrite($fp, "-- Database Backup (PHP-based)\n");
        fwrite($fp, "-- Created: " . date('Y-m-d H:i:s') . "\n");
        fwrite($fp, "-- Database: {$this->config['target']['database']}\n\n");
        fwrite($fp, "SET FOREIGN_KEY_CHECKS = 0;\n\n");
        
        // Get all tables
        $tables = $this->getTables($this->target_conn);
        
        foreach ($tables as $table) {
            // Get CREATE TABLE statement
            $result = $this->target_conn->query("SHOW CREATE TABLE `$table`");
            if ($result) {
                $row = $result->fetch_assoc();
                fwrite($fp, "DROP TABLE IF EXISTS `$table`;\n");
                fwrite($fp, $row['Create Table'] . ";\n\n");
            }
        }
        
        fwrite($fp, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fwrite($fp, "-- Backup completed\n");
        
        fclose($fp);
        
        $size = filesize($backup_file);
        $this->log("  ✓ PHP backup created (" . number_format($size / 1024, 2) . " KB, structure only)");
        $this->log("  ⚠ Note: Data not included in PHP backup (structure only)", 'WARN');
    }
    
    public function phase2_schemaSync($limit_table = null)
    {
        $this->log("\n=== PHASE 2: SCHEMA SYNCHRONIZATION ===");
        
        // Run custom migration files first
        $this->runCustomMigrations();
        
        // Get all tables from SOURCE
        $source_tables = $this->getTables($this->source_conn);
        $target_tables = $this->getTables($this->target_conn);
        
        $this->log("SOURCE tables: " . count($source_tables));
        $this->log("TARGET tables: " . count($target_tables));
        
        foreach ($source_tables as $table) {
            if ($limit_table && $table !== $limit_table) {
                continue;
            }
            
            $this->log("\n--- Processing table: $table ---");
            
            if (!in_array($table, $target_tables)) {
                // Tabel baru — copy entire structure
                $this->createNewTable($table);
            } else {
                // Tabel sudah ada — sync columns & indexes
                $this->syncTableSchema($table);
            }
        }
        
        $this->log("\n✓ Schema synchronization completed");
        $this->log("  Tables created: {$this->stats['tables_created']}");
        $this->log("  Columns added: {$this->stats['columns_added']}");
        $this->log("  Indexes added: {$this->stats['indexes_added']}");
    }
    
    private function getTables($conn)
    {
        $result = $conn->query("SHOW TABLES");
        $tables = [];
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        return $tables;
    }
    
    private function runCustomMigrations()
    {
        $this->log("\n--- Running Custom Migrations ---");
        
        if (!is_dir('migrations')) {
            $this->log("  → No migrations directory found, skipping");
            return;
        }
        
        $migration_files = glob('migrations/*.sql');
        if (empty($migration_files)) {
            $this->log("  → No migration files found");
            return;
        }
        
        sort($migration_files); // Ensure order by filename
        
        foreach ($migration_files as $file) {
            $filename = basename($file);
            $this->log("  → Executing: $filename");
            
            $sql = file_get_contents($file);
            
            if ($this->dry_run) {
                $this->log("    [DRY-RUN] Would execute migration: $filename");
                continue;
            }
            
            // Split by semicolon and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                // Skip comments and empty statements
                if (empty($statement) || strpos($statement, '--') === 0) {
                    continue;
                }
                
                if ($this->target_conn->query($statement)) {
                    $this->log("    ✓ Statement executed");
                } else {
                    // Don't fail on "column already exists" or "index already exists"
                    $error = $this->target_conn->error;
                    if (strpos($error, 'Duplicate column') !== false || 
                        strpos($error, 'Duplicate key') !== false ||
                        strpos($error, 'already exists') !== false) {
                        $this->log("    ⚠ Statement skipped (already exists)", 'WARN');
                    } else {
                        $this->log("    ✗ Error: $error", 'ERROR');
                    }
                }
            }
        }
        
        $this->log("  ✓ Custom migrations completed");
    }
    
    private function createNewTable($table)
    {
        $this->log("  → Table does not exist in TARGET, creating...");
        
        // Get CREATE TABLE statement from SOURCE
        $result = $this->source_conn->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch_assoc();
        $create_sql = $row['Create Table'];
        
        if ($this->dry_run) {
            $this->log("  [DRY-RUN] Would execute:");
            $this->log("    " . substr($create_sql, 0, 200) . "...");
        } else {
            if ($this->target_conn->query($create_sql)) {
                $this->log("  ✓ Table created successfully");
                $this->stats['tables_created']++;
            } else {
                $this->log("  ✗ Failed to create table: " . $this->target_conn->error, 'ERROR');
                $this->stats['errors'][] = "Failed to create table $table: " . $this->target_conn->error;
            }
        }
    }
    
    private function syncTableSchema($table)
    {
        // Get columns from both databases
        $source_columns = $this->getColumns($this->source_conn, $table);
        $target_columns = $this->getColumns($this->target_conn, $table);
        
        $source_col_names = array_keys($source_columns);
        $target_col_names = array_keys($target_columns);
        
        // Find new columns
        $new_columns = array_diff($source_col_names, $target_col_names);
        
        if (empty($new_columns)) {
            $this->log("  → Schema already in sync");
        } else {
            $this->log("  → Found " . count($new_columns) . " new column(s): " . implode(', ', $new_columns));
            
            $prev_column = null;
            foreach ($source_col_names as $col_name) {
                if (in_array($col_name, $new_columns)) {
                    $this->addColumn($table, $col_name, $source_columns[$col_name], $prev_column);
                }
                $prev_column = $col_name;
            }
        }
        
        // Check for column definition changes and auto-sync TARGET schema if needed
        foreach ($target_col_names as $col_name) {
            if (isset($source_columns[$col_name])) {
                $s = $source_columns[$col_name];
                $t = $target_columns[$col_name];
                
                if ($s['Type'] !== $t['Type'] || $s['Null'] !== $t['Null'] || $s['Default'] !== $t['Default']) {
                    $this->log("  ⚠ Column '$col_name' has different definition:", 'WARN');
                    $this->log("    SOURCE: {$s['Type']} " . ($s['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . " DEFAULT {$s['Default']}", 'WARN');
                    $this->log("    TARGET: {$t['Type']} " . ($t['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . " DEFAULT {$t['Default']}", 'WARN');
                    
                    // Auto-sync column definition (expand ENUM, update types)
                    $this->syncColumnDefinition($table, $col_name, $s, $t);
                }
            }
        }
        
        // Sync indexes
        $this->syncIndexes($table);
    }
    
    private function syncColumnDefinition($table, $col_name, $source_col, $target_col)
    {
        $s_type = $source_col['Type'];
        $t_type = $target_col['Type'];
        
        if ($s_type !== $t_type) {
            // If both are ENUMs, combine values
            if (strpos($s_type, 'enum(') === 0 && strpos($t_type, 'enum(') === 0) {
                preg_match_all("/'([^']+)'/", $s_type, $s_matches);
                preg_match_all("/'([^']+)'/", $t_type, $t_matches);
                
                $s_vals = $s_matches[1] ?? [];
                $t_vals = $t_matches[1] ?? [];
                
                $combined_vals = array_unique(array_merge($t_vals, $s_vals));
                $escaped_vals = array_map(function($v) {
                    return "'" . $this->target_conn->real_escape_string($v) . "'";
                }, $combined_vals);
                
                $new_enum = "enum(" . implode(',', $escaped_vals) . ")";
                
                if ($new_enum !== $t_type) {
                    $null_clause = ($source_col['Null'] === 'YES' || $target_col['Null'] === 'YES') ? 'NULL' : 'NOT NULL';
                    $default_clause = '';
                    if ($target_col['Default'] !== null) {
                        $default_clause = " DEFAULT '" . $this->target_conn->real_escape_string($target_col['Default']) . "'";
                    }
                    
                    $sql = "ALTER TABLE `$table` MODIFY COLUMN `$col_name` $new_enum $null_clause$default_clause";
                    $this->log("  → Expanding ENUM column $table.$col_name to: $new_enum");
                    
                    if ($this->dry_run) {
                        $this->log("    [DRY-RUN] Would execute: $sql");
                    } else {
                        if ($this->target_conn->query($sql)) {
                            $this->log("    ✓ Column $col_name ENUM updated");
                        } else {
                            $this->log("    ✗ Failed to update ENUM $col_name: " . $this->target_conn->error, 'WARN');
                        }
                    }
                }
            } else {
                // General column type modification to match SOURCE
                $null_clause = ($source_col['Null'] === 'YES' || $target_col['Null'] === 'YES') ? 'NULL' : 'NOT NULL';
                $sql = "ALTER TABLE `$table` MODIFY COLUMN `$col_name` $s_type $null_clause";
                $this->log("  → Modifying column $table.$col_name type to match SOURCE: $s_type");
                
                if ($this->dry_run) {
                    $this->log("    [DRY-RUN] Would execute: $sql");
                } else {
                    if ($this->target_conn->query($sql)) {
                        $this->log("    ✓ Column $col_name modified successfully");
                    } else {
                        $this->log("    ✗ Failed to modify column $col_name: " . $this->target_conn->error, 'WARN');
                    }
                }
            }
        } elseif ($source_col['Null'] === 'YES' && $target_col['Null'] === 'NO') {
            // Allow NULL if SOURCE allows NULL
            $sql = "ALTER TABLE `$table` MODIFY COLUMN `$col_name` $t_type NULL";
            $this->log("  → Modifying column $table.$col_name to allow NULL");
            if (!$this->dry_run) {
                $this->target_conn->query($sql);
            }
        }
    }
    
    private function getColumns($conn, $table)
    {
        $result = $conn->query("SHOW COLUMNS FROM `$table`");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = $row;
        }
        return $columns;
    }
    
    private function addColumn($table, $column, $definition, $after_column = null)
    {
        // Build column definition
        $col_def = "`$column` {$definition['Type']}";
        
        if ($definition['Null'] === 'NO') {
            // Jika NOT NULL di SOURCE, tambahkan sebagai NULL dulu (agar tidak error di data existing)
            $col_def .= " NULL";
            $this->log("  ⚠ Column '$column' is NOT NULL in SOURCE, adding as NULL in TARGET (backfill required)", 'WARN');
        } else {
            $col_def .= " NULL";
        }
        
        if ($definition['Default'] !== null) {
            // Quote string defaults, leave numeric/NULL as-is
            $default = $definition['Default'];
            if (strtoupper($default) === 'NULL' || strtoupper($default) === 'CURRENT_TIMESTAMP') {
                $col_def .= " DEFAULT $default";
            } else {
                $col_def .= " DEFAULT '" . $this->target_conn->real_escape_string($default) . "'";
            }
        }
        
        if ($definition['Extra']) {
            $col_def .= " {$definition['Extra']}";
        }
        
        $after_clause = $after_column ? " AFTER `$after_column`" : " FIRST";
        $sql = "ALTER TABLE `$table` ADD COLUMN $col_def$after_clause";
        
        if ($this->dry_run) {
            $this->log("  [DRY-RUN] Would execute: $sql");
        } else {
            if ($this->target_conn->query($sql)) {
                $this->log("  ✓ Added column: $column");
                $this->stats['columns_added']++;
            } else {
                $this->log("  ✗ Failed to add column $column: " . $this->target_conn->error, 'ERROR');
                $this->stats['errors'][] = "Failed to add column $table.$column: " . $this->target_conn->error;
            }
        }
    }
    
    private function syncIndexes($table)
    {
        $source_indexes = $this->getIndexes($this->source_conn, $table);
        $target_indexes = $this->getIndexes($this->target_conn, $table);
        
        $source_idx_names = array_keys($source_indexes);
        $target_idx_names = array_keys($target_indexes);
        
        $new_indexes = array_diff($source_idx_names, $target_idx_names);
        
        if (!empty($new_indexes)) {
            $this->log("  → Found " . count($new_indexes) . " new index(es)");
            
            foreach ($new_indexes as $idx_name) {
                $idx = $source_indexes[$idx_name];
                
                if ($idx_name === 'PRIMARY') {
                    continue; // Skip PRIMARY KEY (sudah ada dari CREATE TABLE)
                }
                
                $columns = implode(', ', array_map(function($col) {
                    return "`$col`";
                }, $idx['columns']));
                
                if ($idx['unique']) {
                    $sql = "ALTER TABLE `$table` ADD UNIQUE KEY `$idx_name` ($columns)";
                } else {
                    $sql = "ALTER TABLE `$table` ADD KEY `$idx_name` ($columns)";
                }
                
                if ($this->dry_run) {
                    $this->log("  [DRY-RUN] Would execute: $sql");
                } else {
                    if ($this->target_conn->query($sql)) {
                        $this->log("  ✓ Added index: $idx_name");
                        $this->stats['indexes_added']++;
                    } else {
                        // Jangan throw error jika index sudah ada
                        if (strpos($this->target_conn->error, 'Duplicate key name') === false) {
                            $this->log("  ⚠ Failed to add index $idx_name: " . $this->target_conn->error, 'WARN');
                        }
                    }
                }
            }
        }
    }
    
    private function getIndexes($conn, $table)
    {
        $result = $conn->query("SHOW INDEXES FROM `$table`");
        $indexes = [];
        while ($row = $result->fetch_assoc()) {
            $idx_name = $row['Key_name'];
            if (!isset($indexes[$idx_name])) {
                $indexes[$idx_name] = [
                    'unique' => $row['Non_unique'] == 0,
                    'columns' => []
                ];
            }
            $indexes[$idx_name]['columns'][] = $row['Column_name'];
        }
        return $indexes;
    }
    
    public function phase3_dataSync($limit_table = null)
    {
        $this->log("\n=== PHASE 3: DATA MIGRATION ===");
        
        // Priority order: master tables → brands → relasi
        $migration_order = [
            // Master/lookup tables
            'categories',
            'statuses',
            'config',
            'roles',
            'permissions',
            
            // Brand & related
            'brands',
            'brand_requirements',
            'brand_claims',
            'brand_products',
            'brand_contacts',
            'brand_notes',
            'brand_files',
            'brand_collaborators',
            
            // Creator & related
            'creators',
            'creator_categories',
            'creator_notes',
            
            // Products
            'products',
            'product_categories',
            
            // Other tables akan diproses setelah ini
        ];
        
        if ($limit_table) {
            $migration_order = [$limit_table];
        } else {
            // Tambahkan tabel lain yang belum ada di list
            $all_tables = $this->getTables($this->source_conn);
            foreach ($all_tables as $table) {
                if (!in_array($table, $migration_order) && !in_array($table, $this->skip_data_tables)) {
                    $migration_order[] = $table;
                }
            }
        }
        
        foreach ($migration_order as $table) {
            if (in_array($table, $this->skip_data_tables)) {
                $this->log("\n--- Skipping table: $table (in skip list) ---");
                continue;
            }
            
            // Check if table exists in SOURCE
            $check = $this->source_conn->query("SHOW TABLES LIKE '$table'");
            if ($check->num_rows === 0) {
                continue;
            }
            
            $this->log("\n--- Migrating data: $table ---");
            
            if ($table === 'brands') {
                $this->migrateBrands();
            } elseif (strpos($table, 'brand_') === 0) {
                $this->migrateBrandRelations($table);
            } else {
                $this->migrateGenericTable($table);
            }
        }
        
        $this->log("\n✓ Data migration completed");
        $this->log("  Records inserted: {$this->stats['records_inserted']}");
        $this->log("  Records skipped (duplicates): {$this->stats['records_skipped']}");
    }
    
    private function migrateBrands()
    {
        $this->log("  → Migrating brands with deduplication...");
        
        // Get all brands from SOURCE
        $source_brands = $this->source_conn->query("SELECT * FROM brands");
        
        if (!$source_brands) {
            $this->log("  ⚠ Table 'brands' not found in SOURCE", 'WARN');
            return;
        }
        
        $inserted = 0;
        $matched = 0;
        $skipped = 0;
        
        while ($brand = $source_brands->fetch_assoc()) {
            $source_id = $brand['id'];
            
            $target_id  = null;
            $brand_name = trim($brand['name'] ?? '');
            $shop_name  = trim($brand['shop_name'] ?? '');
            $email      = trim($brand['email'] ?? '');
            
            // 1. Match by Brand Name (case-insensitive)
            if (!empty($brand_name)) {
                $escaped_name = $this->target_conn->real_escape_string($brand_name);
                $existing = $this->target_conn->query("SELECT id FROM brands WHERE LOWER(TRIM(name)) = LOWER('$escaped_name') LIMIT 1");
                if ($existing && $existing->num_rows > 0) {
                    $target_id = $existing->fetch_assoc()['id'];
                }
            }
            
            // 2. Fallback: Match by Shop Name if name match not found
            if (!$target_id && !empty($shop_name)) {
                $escaped_shop = $this->target_conn->real_escape_string($shop_name);
                $existing = $this->target_conn->query("SELECT id FROM brands WHERE LOWER(TRIM(shop_name)) = LOWER('$escaped_shop') OR LOWER(TRIM(name)) = LOWER('$escaped_shop') LIMIT 1");
                if ($existing && $existing->num_rows > 0) {
                    $target_id = $existing->fetch_assoc()['id'];
                }
            }
            
            // 3. Fallback: Match by Email ONLY if valid non-generic email and name/shop match failed
            if (!$target_id && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $generic_emails = ['info@toopai.ai', 'admin@toopai.ai', 'bd@toopai.ai', 'test@gmail.com', 'none@none.com'];
                if (!in_array(strtolower($email), $generic_emails)) {
                    $escaped_email = $this->target_conn->real_escape_string($email);
                    $existing = $this->target_conn->query("SELECT id FROM brands WHERE LOWER(TRIM(email)) = LOWER('$escaped_email') LIMIT 1");
                    if ($existing && $existing->num_rows > 0) {
                        $target_id = $existing->fetch_assoc()['id'];
                    }
                }
            }
            
            if ($target_id) {
                $this->brand_id_mapping[$source_id] = $target_id;
                $matched++;
                continue;
            }
            
            // Brand belum ada — insert ke TARGET
            if (!$this->dry_run) {
                $insert_data = $brand;
                unset($insert_data['id']); // Let TARGET auto-increment
                
                // Get TARGET columns to filter out incompatible fields
                $target_columns_result = $this->target_conn->query("SHOW COLUMNS FROM brands");
                $target_columns_info = [];
                while ($col = $target_columns_result->fetch_assoc()) {
                    $target_columns_info[$col['Field']] = $col;
                }
                
                // Filter insert data — only include columns that exist in TARGET
                $filtered_data = [];
                foreach ($insert_data as $col => $val) {
                    if (isset($target_columns_info[$col])) {
                        // Check if ENUM/SET type and value is valid
                        $col_type = $target_columns_info[$col]['Type'];
                        if (strpos($col_type, 'enum(') === 0 || strpos($col_type, 'set(') === 0) {
                            // Extract valid values from enum('val1','val2',...)
                            preg_match_all("/'([^']+)'/", $col_type, $matches);
                            $valid_values = $matches[1];
                            
                            if ($val !== null && !in_array($val, $valid_values)) {
                                // Value not valid in TARGET — use default or skip
                                $default = $target_columns_info[$col]['Default'];
                                if ($default !== null && $default !== '') {
                                    $filtered_data[$col] = $default;
                                    $this->log("    ⚠ Transformed $col: '$val' → '$default' (not in TARGET ENUM)", 'WARN');
                                } else {
                                    // Skip this column — will use TARGET default
                                    $this->log("    ⚠ Skipped $col: '$val' (not in TARGET ENUM, no default)", 'WARN');
                                    continue;
                                }
                            } else {
                                $filtered_data[$col] = $val;
                            }
                        } else {
                            $filtered_data[$col] = $val;
                        }
                    }
                }
                
                $columns = array_keys($filtered_data);
                $values = array_map([$this, 'formatSqlValue'], array_values($filtered_data));
                
                $sql = "INSERT INTO brands (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ")";
                
                try {
                    if ($this->target_conn->query($sql)) {
                        $target_id = $this->target_conn->insert_id;
                        $this->brand_id_mapping[$source_id] = $target_id;
                        $inserted++;
                    } else {
                        $this->log("  ✗ Failed to insert brand ID $source_id ({$brand['name']}): " . $this->target_conn->error, 'ERROR');
                        $skipped++;
                    }
                } catch (Throwable $e) {
                    $this->log("  ⚠ Failed to insert brand ID $source_id ({$brand['name']}): " . $e->getMessage(), 'WARN');
                    $skipped++;
                }
            } else {
                $this->log("  [DRY-RUN] Would insert brand: {$brand['name']}");
            }
        }
        
        $this->log("  ✓ Brands: $matched matched, $inserted inserted, $skipped skipped");
        $this->stats['records_inserted'] += $inserted;
        $this->stats['records_skipped'] += $matched + $skipped;
    }
    
    private function formatSqlValue($val)
    {
        if ($val === null) {
            return 'NULL';
        }
        if ($val === '0000-00-00 00:00:00' || $val === '0000-00-00' || strpos($val, '0000-00-00') === 0) {
            return 'NULL';
        }
        return "'" . $this->target_conn->real_escape_string($val) . "'";
    }
    
    private function migrateBrandRelations($table)
    {
        $this->log("  → Migrating $table (with brand_id remapping)...");
        
        $source_records = $this->source_conn->query("SELECT * FROM `$table`");
        
        if (!$source_records) {
            $this->log("  ⚠ Table '$table' not found in SOURCE", 'WARN');
            return;
        }
        
        // Check if table exists in TARGET
        $check_table = $this->target_conn->query("SHOW TABLES LIKE '$table'");
        if (!$check_table || $check_table->num_rows === 0) {
            $this->log("  ⚠ Table '$table' not found in TARGET, skipping data migration", 'WARN');
            return;
        }
        
        // Get TARGET columns
        $target_columns_result = $this->target_conn->query("SHOW COLUMNS FROM `$table`");
        if (!$target_columns_result) {
            $this->log("  ⚠ Cannot get TARGET columns for $table", 'WARN');
            return;
        }
        
        $target_columns = [];
        while ($col = $target_columns_result->fetch_assoc()) {
            $target_columns[] = $col['Field'];
        }
        
        $inserted = 0;
        
        while ($record = $source_records->fetch_assoc()) {
            // Remap brand_id
            if (isset($record['brand_id']) && isset($this->brand_id_mapping[$record['brand_id']])) {
                $record['brand_id'] = $this->brand_id_mapping[$record['brand_id']];
            } elseif (isset($record['brand_id'])) {
                // Brand ID tidak ada di mapping — skip record ini
                $this->log("  ⚠ Skipping record with unmapped brand_id: {$record['brand_id']}", 'WARN');
                $this->stats['records_skipped']++;
                continue;
            }
            
            // Check duplicate
            $unique_check = $this->buildUniqueCheck($table, $record);
            if ($unique_check) {
                $existing = $this->target_conn->query($unique_check);
                if ($existing && $existing->num_rows > 0) {
                    $this->stats['records_skipped']++;
                    continue;
                }
            }
            
            // Insert
            if (!$this->dry_run) {
                $insert_data = $record;
                unset($insert_data['id']);
                
                // Filter — only columns that exist in TARGET
                $filtered_data = [];
                foreach ($insert_data as $col => $val) {
                    if (in_array($col, $target_columns)) {
                        $filtered_data[$col] = $val;
                    }
                }
                
                $columns = array_keys($filtered_data);
                $values = array_map([$this, 'formatSqlValue'], array_values($filtered_data));
                
                $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ")";
                
                try {
                    if ($this->target_conn->query($sql)) {
                        $inserted++;
                    } else {
                        // Skip duplicate errors silently
                        if (strpos($this->target_conn->error, 'Duplicate entry') !== false) {
                            $this->stats['records_skipped']++;
                        } else {
                            $this->log("  ⚠ Failed to insert into $table: " . $this->target_conn->error, 'WARN');
                        }
                    }
                } catch (Throwable $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $this->stats['records_skipped']++;
                    } else {
                        $this->log("  ⚠ Failed to insert row into $table: " . $e->getMessage(), 'WARN');
                        $this->stats['errors'][] = "Error inserting row into $table: " . $e->getMessage();
                    }
                }
            }
        }
        
        $this->log("  ✓ Inserted $inserted records");
        $this->stats['records_inserted'] += $inserted;
    }
    
    private function migrateGenericTable($table)
    {
        $this->log("  → Migrating $table...");
        
        $source_records = $this->source_conn->query("SELECT * FROM `$table`");
        
        if (!$source_records) {
            $this->log("  ⚠ Table '$table' not found in SOURCE", 'WARN');
            return;
        }
        
        // Check if table exists in TARGET
        $check_table = $this->target_conn->query("SHOW TABLES LIKE '$table'");
        if (!$check_table || $check_table->num_rows === 0) {
            $this->log("  ⚠ Table '$table' not found in TARGET, skipping data migration", 'WARN');
            return;
        }
        
        // Get TARGET columns
        $target_columns_result = $this->target_conn->query("SHOW COLUMNS FROM `$table`");
        if (!$target_columns_result) {
            $this->log("  ⚠ Cannot get TARGET columns for $table", 'WARN');
            return;
        }
        
        $target_columns = [];
        while ($col = $target_columns_result->fetch_assoc()) {
            $target_columns[] = $col['Field'];
        }
        
        $inserted = 0;
        
        while ($record = $source_records->fetch_assoc()) {
            // Check duplicate by unique keys
            $unique_check = $this->buildUniqueCheck($table, $record);
            if ($unique_check) {
                $existing = $this->target_conn->query($unique_check);
                if ($existing && $existing->num_rows > 0) {
                    $this->stats['records_skipped']++;
                    continue;
                }
            }
            
            // Insert
            if (!$this->dry_run) {
                $insert_data = $record;
                unset($insert_data['id']);
                
                // Filter — only columns that exist in TARGET
                $filtered_data = [];
                foreach ($insert_data as $col => $val) {
                    if (in_array($col, $target_columns)) {
                        $filtered_data[$col] = $val;
                    }
                }
                
                $columns = array_keys($filtered_data);
                $values = array_map([$this, 'formatSqlValue'], array_values($filtered_data));
                
                $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ")";
                
                try {
                    if ($this->target_conn->query($sql)) {
                        $inserted++;
                    } else {
                        // Skip duplicate errors silently
                        if (strpos($this->target_conn->error, 'Duplicate entry') !== false) {
                            $this->stats['records_skipped']++;
                        } else {
                            $this->log("  ⚠ Failed to insert into $table: " . $this->target_conn->error, 'WARN');
                        }
                    }
                } catch (Throwable $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $this->stats['records_skipped']++;
                    } else {
                        $this->log("  ⚠ Failed to insert row into $table: " . $e->getMessage(), 'WARN');
                        $this->stats['errors'][] = "Error inserting row into $table: " . $e->getMessage();
                    }
                }
            }
        }
        
        $this->log("  ✓ Inserted $inserted records");
        $this->stats['records_inserted'] += $inserted;
    }
    
    private function buildUniqueCheck($table, $record)
    {
        // For creators table, check username if non-empty
        if ($table === 'creators') {
            if (!empty($record['username']) && trim($record['username']) !== '') {
                $user = $this->target_conn->real_escape_string(trim($record['username']));
                return "SELECT id FROM creators WHERE LOWER(TRIM(username)) = LOWER('$user') LIMIT 1";
            }
            return null;
        }
        
        // Detect unique keys untuk duplicate check
        $unique_fields = [
            'categories' => ['name'],
            'statuses' => ['code'],
            'config' => ['key'],
            'roles' => ['name'],
            'products' => ['sku'],
        ];
        
        if (!isset($unique_fields[$table])) {
            return null;
        }
        
        $conditions = [];
        foreach ($unique_fields[$table] as $field) {
            if (isset($record[$field]) && $record[$field] !== null && trim($record[$field]) !== '') {
                $val = $this->target_conn->real_escape_string(trim($record[$field]));
                $conditions[] = "`$field` = '$val'";
            }
        }
        
        if (empty($conditions)) {
            return null;
        }
        
        return "SELECT id FROM `$table` WHERE " . implode(' AND ', $conditions) . " LIMIT 1";
    }
    
    public function phase4_verify()
    {
        $this->log("\n=== PHASE 4: POST-SYNC VERIFICATION ===");
        
        // Integrity checks
        $this->log("\n--- Foreign Key Integrity Check ---");
        
        // Check orphaned brand relations
        $orphaned_checks = [
            'brand_claims' => 'brand_id',
            'brand_products' => 'brand_id',
            'brand_collaborators' => 'brand_id',
        ];
        
        foreach ($orphaned_checks as $table => $fk_column) {
            $check = $this->target_conn->query("SHOW TABLES LIKE '$table'");
            if ($check->num_rows === 0) continue;
            
            $result = $this->target_conn->query("
                SELECT COUNT(*) as orphaned
                FROM `$table` t
                LEFT JOIN brands b ON t.$fk_column = b.id
                WHERE b.id IS NULL
            ");
            
            if ($result) {
                $orphaned = $result->fetch_assoc()['orphaned'];
                if ($orphaned > 0) {
                    $this->log("  ⚠ Found $orphaned orphaned records in $table", 'WARN');
                } else {
                    $this->log("  ✓ No orphaned records in $table");
                }
            }
        }
        
        // Check duplicate active claims
        $check = $this->target_conn->query("SHOW TABLES LIKE 'brand_claims'");
        if ($check && $check->num_rows > 0) {
            $result = $this->target_conn->query("
                SELECT brand_id, COUNT(*) as cnt
                FROM brand_claims
                WHERE status = 'active'
                GROUP BY brand_id
                HAVING cnt > 1
            ");
            
            if ($result && $result->num_rows > 0) {
                $this->log("  ⚠ Found " . $result->num_rows . " brands with duplicate active claims", 'WARN');
            } else {
                $this->log("  ✓ No duplicate active claims found");
            }
        }
        
        // Summary
        $this->log("\n=== EXECUTION SUMMARY ===");
        $this->log("Tables created: {$this->stats['tables_created']}");
        $this->log("Columns added: {$this->stats['columns_added']}");
        $this->log("Indexes added: {$this->stats['indexes_added']}");
        $this->log("Records inserted: {$this->stats['records_inserted']}");
        $this->log("Records skipped: {$this->stats['records_skipped']}");
        $this->log("Errors: " . count($this->stats['errors']));
        
        if (!empty($this->stats['errors'])) {
            $this->log("\n--- Errors Detail ---");
            foreach ($this->stats['errors'] as $error) {
                $this->log("  • $error", 'ERROR');
            }
        }
        
        $this->log("\n--- Brand ID Mapping Summary ---");
        $this->log("Total brand mappings: " . count($this->brand_id_mapping));
        if (count($this->brand_id_mapping) > 0 && count($this->brand_id_mapping) <= 20) {
            foreach ($this->brand_id_mapping as $source => $target) {
                $this->log("  $source → $target");
            }
        }
    }
    
    public function cleanup()
    {
        // Re-enable foreign key checks
        if ($this->target_conn && !$this->dry_run) {
            $this->target_conn->query("SET FOREIGN_KEY_CHECKS = 1");
            $this->log("\n✓ Foreign key checks re-enabled");
        }
        
        // Save log file
        $log_file = "logs/merge_report_" . date('Y-m-d_His') . ".log";
        if (!is_dir('logs')) {
            mkdir('logs', 0755, true);
        }
        file_put_contents($log_file, implode(PHP_EOL, $this->log));
        $this->log("✓ Log saved to: $log_file");
        
        // Close connections
        if ($this->source_conn) $this->source_conn->close();
        if ($this->target_conn) $this->target_conn->close();
        
        $this->log("\n=== DATABASE MERGE TOOL FINISHED ===");
    }
}

// ============================================================================
// CLI ENTRY POINT
// ============================================================================

// Parse arguments
$options = getopt('', ['dry-run', 'table:', 'phase:', 'skip-backup', 'help']);

if (isset($options['help'])) {
    echo <<<HELP
Database Merge & Sync Tool
Usage: php db_merge_sync.php [options]

Options:
  --dry-run        Show what would be executed without making changes
  --table=TABLE    Limit sync to specific table
  --phase=N        Run specific phase only (1-4)
  --skip-backup    Skip database backup (not recommended)
  --help           Show this help

Examples:
  php db_merge_sync.php --dry-run
  php db_merge_sync.php --table=brands
  php db_merge_sync.php --phase=2
  php db_merge_sync.php --skip-backup

HELP;
    exit(0);
}

$dry_run = isset($options['dry-run']);
$limit_table = isset($options['table']) ? $options['table'] : null;
$phase = isset($options['phase']) ? (int)$options['phase'] : null;
$skip_backup = isset($options['skip-backup']);

// Load configuration
if (file_exists('.env.merge')) {
    $env = parse_ini_file('.env.merge');
    $config = [
        'source' => [
            'host' => $env['SOURCE_HOST'],
            'username' => $env['SOURCE_USER'],
            'password' => $env['SOURCE_PASS'],
            'database' => $env['SOURCE_DB'],
        ],
        'target' => [
            'host' => $env['TARGET_HOST'],
            'username' => $env['TARGET_USER'],
            'password' => $env['TARGET_PASS'],
            'database' => $env['TARGET_DB'],
        ]
    ];
} else {
    // Default config
    $config = [
        'source' => [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'root',
            'database' => 'holasync_dev_toopai_before_merge',
        ],
        'target' => [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'root',
            'database' => 'holasync_toopai_before_merge',
        ]
    ];
}

// Execute
try {
    $tool = new DatabaseMergeTool($config, $dry_run);
    $tool->connect();
    
    if ($phase === null || $phase === 1) {
        if (!$skip_backup) {
            $tool->phase1_backup();
        }
    }
    
    if ($phase === null || $phase === 2) {
        $tool->phase2_schemaSync($limit_table);
    }
    
    if ($phase === null || $phase === 3) {
        $tool->phase3_dataSync($limit_table);
    }
    
    if ($phase === null || $phase === 4) {
        $tool->phase4_verify();
    }
    
    $tool->cleanup();
    
} catch (Exception $e) {
    echo "\n[FATAL ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
