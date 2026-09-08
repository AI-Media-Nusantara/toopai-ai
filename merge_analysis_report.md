# DB Merge Analysis Report
Generated: 2026-09-08 04:00:02
Source: before_merge_holasync_dev_toopai_new
Target: before_merge_holasync_toopai

---
## Tahap 1A — Perbandingan Daftar Tabel

### Tabel hanya di SOURCE (dev) — perlu CREATE di production
```
(tidak ada)
```

### Tabel hanya di TARGET (prod) — TIDAK disentuh
```
(tidak ada)
```

### Tabel ada di keduanya: 47 tabel

---
## Tahap 1B — Perbedaan Kolom per Tabel

### Tabel: `affiliate_creator_links`

**Kolom BERBEDA definisi (source vs target) — hanya laporan, tidak auto-modify:**
| Kolom | Source Type | Target Type | Source Null | Target Null | Source Default | Target Default |
|-------|------------|------------|-------------|-------------|----------------|----------------|
| `showcase_status` | `enum('unknown','not_added','added')` | `enum('unknown','not_added','added')` | NO | YES | `unknown` | `unknown` |

### Tabel: `brands`

**Kolom BERBEDA definisi (source vs target) — hanya laporan, tidak auto-modify:**
| Kolom | Source Type | Target Type | Source Null | Target Null | Source Default | Target Default |
|-------|------------|------------|-------------|-------------|----------------|----------------|
| `status` | `enum('PENDING','FOLLOW_UP','CAMPAIGN_READY','NEED_CLAIM','ACTIVE','REJECTED')` | `enum('PENDING','FOLLOW_UP','CAMPAIGN_READY','ACTIVE','REJECTED','NEED_CLAIM')` | YES | YES | `` | `PENDING` |

---
## Tahap 1C — Perbedaan Index

(Tidak ada perbedaan index)

---
## Tahap 2A — Perbandingan COUNT(*) per Tabel

| Tabel | Dev (source) | Prod (target) | Selisih |
|-------|-------------|---------------|---------|
| `activity_logs` | 1 | 4 | -3 ← prod punya lebih |
| `affiliate_campaigns` | 7 | 7 | 0 |
| `affiliate_creator_links` | 811 | 1360 | -549 ← prod punya lebih |
| `affiliate_orders` | 30006 | 53868 | -23862 ← prod punya lebih |
| `affiliate_products` | 3331 | 4005 | -674 ← prod punya lebih |
| `affiliate_sync_logs` | 4517 | 19939 | -15422 ← prod punya lebih |
| `affiliate_sync_queue` | 0 | 0 | 0 |
| `ai_hook_generations` | 11 | 44 | -33 ← prod punya lebih |
| `ai_scout_results` | 0 | 0 | 0 |
| `analytics_cache` | 0 | 0 | 0 |
| `app_cache` | 617 | 617 | 0 |
| `app_config` | 2 | 2 | 0 |
| `ba_reminder_reads` | 1 | 1 | 0 |
| `ba_reminders` | 1 | 3 | -2 ← prod punya lebih |
| `bd_affiliate_links` | 1960 | 5972 | -4012 ← prod punya lebih |
| `bd_task_progress` | 2 | 6 | -4 ← prod punya lebih |
| `brand_contact_queue` | 306 | 918 | -612 ← prod punya lebih |
| `brand_contacts` | 213 | 213 | 0 |
| `brand_creator_products` | 0 | 0 | 0 |
| `brand_creators` | 1825 | 1827 | -2 ← prod punya lebih |
| `brand_products` | 1 | 1 | 0 |
| `brand_search_queue` | 415 | 1272 | -857 ← prod punya lebih |
| `brands` | 4567 | 4513 | 54 ← **+54** |
| `campaign_creator_performance` | 4515 | 5702 | -1187 ← prod punya lebih |
| `ci_sessions` | 149 | 30 | 119 ← **+119** |
| `creator_contacts` | 87 | 87 | 0 |
| `creator_content_statistics` | 0 | 0 | 0 |
| `creator_link_assignments` | 0 | 0 | 0 |
| `creator_products` | 10445 | 10447 | -2 ← prod punya lebih |
| `creator_scouting` | 903 | 1306 | -403 ← prod punya lebih |
| `creator_videos` | 0 | 0 | 0 |
| `creators` | 4945 | 5005 | -60 ← prod punya lebih |
| `message_templates` | 2 | 6 | -4 ← prod punya lebih |
| `product_creator_relations` | 16 | 48 | -32 ← prod punya lebih |
| `product_recommendations` | 0 | 0 | 0 |
| `product_video_relations` | 0 | 0 | 0 |
| `sample_requests` | 76 | 154 | -78 ← prod punya lebih |
| `scraped_creator_products` | 6 | 18 | -12 ← prod punya lebih |
| `scraped_creators` | 1 | 3 | -2 ← prod punya lebih |
| `scraped_product_creators` | 0 | 0 | 0 |
| `target_plan_requests` | 0 | 0 | 0 |
| `tts_tokens` | 2 | 6 | -4 ← prod punya lebih |
| `unknown_webhooks` | 58 | 174 | -116 ← prod punya lebih |
| `user_logs` | 5465 | 20070 | -14605 ← prod punya lebih |
| `users` | 18 | 18 | 0 |
| `webhook_logs` | 0 | 0 | 0 |
| `whatsapp_logs` | 774 | 2297 | -1523 ← prod punya lebih |

---
## Tahap 2B — Identifikasi Baris Beda (Source punya lebih)

### `brands` — source: 4567, target: 4513, selisih: +54

**Kunci pembanding:** `slug`
**Alasan:** Business key (override manual — PK auto-increment tidak konsisten antar DB)

⚠️ Business key tidak ada di TARGET, fallback ke PK: `id`

**Baris di source yang BELUM ada di production:** 66 baris
Contoh (max 10):
```
  {"id":"8595"}
  {"id":"8596"}
  {"id":"8597"}
  {"id":"8598"}
  {"id":"8599"}
  {"id":"8600"}
  {"id":"8601"}
  {"id":"8602"}
  {"id":"8603"}
  {"id":"8604"}
```

### `ci_sessions` — source: 149, target: 30, selisih: +119

**Kunci pembanding:** `?`
**Alasan:** ⚠️ TIDAK ADA PK/UNIQUE — tidak bisa auto-deduplicate, perlu review manual


---
## Tahap 2C — Ringkasan Rencana Migrasi

| Tabel | Baris baru dari dev | Baris production aman | Kunci |
|-------|--------------------|-----------------------|-------|
| `brands` | **66** | 4513 | `id` |

**Total baris akan diinsert:** 66
