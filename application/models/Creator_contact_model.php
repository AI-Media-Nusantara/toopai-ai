<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Creator_contact_model extends CI_Model
{
    private $table = 'creator_contacts';

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->_ensure_table();
    }

    private function _ensure_table() {
        if (!$this->db->table_exists($this->table)) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `{$this->table}` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `creator_username` varchar(100) NOT NULL,
                    `creator_oecuid` varchar(100) DEFAULT NULL,
                    `display_name` varchar(255) DEFAULT NULL,
                    `whatsapp` varchar(50) DEFAULT NULL,
                    `email` varchar(255) DEFAULT NULL,
                    `avatar_url` text DEFAULT NULL,
                    `status` enum('PENDING','FOUND','NO_CONTACT','NOT_FOUND','ERROR') DEFAULT 'PENDING',
                    `raw_response` text DEFAULT NULL,
                    `crawled_at` datetime DEFAULT NULL,
                    `created_at` datetime DEFAULT current_timestamp(),
                    `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uk_creator_username` (`creator_username`),
                    KEY `idx_status` (`status`),
                    KEY `idx_oecuid` (`creator_oecuid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }
    }

    /**
     * 🔥 AMBIL CREATOR YANG SUDAH PUNYA TIKTOK_OPEN_ID
     * HANYA yang sudah punya oecuid dan belum punya kontak
     * Untuk diproses oleh cron_contact
     */
    public function get_creators_with_oecuid($limit = 20)
    {
        return $this->db->query("
            SELECT 
                c.id,
                c.username,
                c.full_name,
                c.phone,
                c.email,
                c.tiktok_open_id,
                c.avatar_url
            FROM creators c
            LEFT JOIN creator_contacts cc 
                ON cc.creator_username = c.username
            WHERE 
                c.tiktok_open_id IS NOT NULL
                AND c.tiktok_open_id != ''
                AND (
                    cc.id IS NULL
                    OR cc.status = 'PENDING'
                    OR cc.status = 'NO_CONTACT'
                    OR (
                        (cc.email IS NULL OR cc.email = '')
                        AND (cc.whatsapp IS NULL OR cc.whatsapp = '')
                    )
                )
                AND (
                    c.phone IS NULL 
                    OR c.phone = '' 
                    OR c.email IS NULL 
                    OR c.email = ''
                )
            GROUP BY c.username
            ORDER BY c.created_at ASC
            LIMIT ?
        ", [$limit])->result();
    }

    /**
     * Get pending creators from 'creators' table
     * Yang belum punya kontak dan belum dicrawler
     * (Method lama, tetap dipertahankan)
     */
    public function get_pending_from_creators($limit = 10)
    {
        return $this->db->query("
            SELECT c.username
            FROM creators c
            LEFT JOIN creator_contacts cc 
                ON cc.creator_username = c.username
            WHERE c.username IS NOT NULL
                AND c.username != ''
                AND (
                    cc.id IS NULL
                    OR cc.email IS NULL
                    OR cc.email = ''
                    OR cc.whatsapp IS NULL
                    OR cc.whatsapp = ''
                )
            GROUP BY c.username
            LIMIT ?
        ", [$limit])->result();
    }

    /**
     * Get creators that need contact crawling (dari creator_contacts)
     */
    public function get_pending_contacts($limit = 20)
    {
        return $this->db->where('status', 'PENDING')
            ->or_where('status', 'NO_CONTACT')
            ->order_by('created_at', 'ASC')
            ->limit($limit)
            ->get($this->table)
            ->result();
    }

    /**
     * Insert or update creator contact
     */
    public function upsert($data)
    {
        $existing = $this->db
            ->where('creator_username', $data['creator_username'])
            ->get($this->table)
            ->row();

        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($existing) {
            $this->db
                ->where('id', $existing->id)
                ->update($this->table, $data);

            return $existing->id;
        }

        $data['created_at'] = date('Y-m-d H:i:s');

        $this->db->insert($this->table, $data);

        return $this->db->insert_id();
    }

    /**
     * Get stats untuk monitoring
     */
    public function get_stats()
    {
        $total = $this->db->count_all($this->table);
        $pending = $this->db->where('status', 'PENDING')->count_all_results($this->table);
        $found = $this->db->where('status', 'FOUND')->count_all_results($this->table);
        $no_contact = $this->db->where('status', 'NO_CONTACT')->count_all_results($this->table);
        $not_found = $this->db->where('status', 'NOT_FOUND')->count_all_results($this->table);
        
        $has_whatsapp = $this->db->where('whatsapp IS NOT NULL')
            ->where('whatsapp !=', '')
            ->count_all_results($this->table);
        
        $has_email = $this->db->where('email IS NOT NULL')
            ->where('email !=', '')
            ->count_all_results($this->table);
        
        return [
            'total' => $total,
            'pending' => $pending,
            'found' => $found,
            'no_contact' => $no_contact,
            'not_found' => $not_found,
            'has_whatsapp' => $has_whatsapp,
            'has_email' => $has_email
        ];
    }

    /**
     * Get creator contact by username
     */
    public function get_by_username($username)
    {
        return $this->db->where('creator_username', $username)
            ->get($this->table)
            ->row();
    }

    /**
     * Get creator contact by oecuid
     */
    public function get_by_oecuid($oecuid)
    {
        return $this->db->where('creator_oecuid', $oecuid)
            ->get($this->table)
            ->row();
    }
    
    public function get_creators_with_fastmoss_uid($limit = 10)
    {
        return $this->db->query("
            SELECT 
                c.id,
                c.username,
                c.full_name,
                c.phone,
                c.email,
                c.tiktok_open_id,
                c.fastmoss_uid,
                c.brand_id,
                c.avatar_url
            FROM creators c
            LEFT JOIN creator_contacts cc 
                ON cc.creator_username = c.username
            WHERE 
                c.fastmoss_uid IS NOT NULL
                AND c.fastmoss_uid != ''
                AND (
                    cc.id IS NULL
                    OR cc.status != 'COMPLETE'
                )
            GROUP BY c.username
            ORDER BY c.created_at ASC
            LIMIT ?
        ", [$limit])->result();
    }

    /**
     * Get creators that already have products synced
     */
    public function get_creators_with_products_synced($limit = 10)
    {
        return $this->db->query("
            SELECT 
                c.id,
                c.username,
                c.fastmoss_uid,
                COUNT(bcp.id) as product_count
            FROM creators c
            JOIN brand_creators bc ON bc.creator_username = c.username
            JOIN brand_creator_products bcp ON bcp.brand_creator_id = bc.id
            WHERE c.fastmoss_uid IS NOT NULL
            GROUP BY c.id
            HAVING product_count > 0
            ORDER BY product_count DESC
            LIMIT ?
        ", [$limit])->result();
    }
    
    
    
}