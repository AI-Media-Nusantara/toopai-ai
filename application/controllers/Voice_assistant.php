<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Voice_assistant extends CI_Controller
{
      private $gemini_api_keys = [
        'AIzaSyAimgbIISxJgWKV9ZM3ApIXNwT_2dFcXyo',
        'AIzaSyD1twUjCWv37oiZJT7b7SWds77eI8sGF78'
    ];
    private $current_key_index = 0;

    private $gemini_models = [
        'gemini-2.5-flash',
        'gemini-2.0-flash-exp',
        'gemini-2.0-flash'
    ];

    private $gemini_url = 'https://generativelanguage.googleapis.com/v1beta/models/';

    private $max_limit = 10;
    private $user_role = 'admin';

    private $allowed_tables = [
        'affiliate_orders',
        'affiliate_campaigns',
        'affiliate_products',
        'affiliate_creator_links',
        'creators',
        'users',
        'brands',
        'campaign_creator_performance',
        'creator_content_statistics',
        'sample_requests',
        'affiliate_sync_logs'
    ];

    private $allowed_columns = [
        'affiliate_orders' => [
            'order_id',
            'campaign_id',
            'campaign_name',
            'product_id',
            'product_name',
            'creator_username',
            'gmv',
            'estimated_commission',
            'actual_commission',
            'order_time',
            'order_date_local',
            'order_status',
            'quantity',
            'price'
        ],
        'affiliate_campaigns' => [
            'campaign_id',
            'campaign_name',
            'status',
            'total_gmv',
            'total_orders',
            'total_creators'
        ],
        'affiliate_products' => [
            'product_id',
            'campaign_id',
            'product_name',
            'price',
            'image_url',
            'shop_name',
            'review_status',
            'open_commission_rate',
            'sales_count',
            'gmv',
            'inventory',
            'sample_quota'
        ],
        'creators' => [
            'id',
            'username',
            'full_name',
            'category',
            'phone',
            'email',
            'status',
            'is_id',
            'total_gmv',
            'total_orders',
            'total_commission',
            'auto_activated_at',
            'created_at'
        ],
        'users' => [
            'id',
            'username',
            'full_name',
            'role',
            'created_at'
        ],
        'brands' => [
            'id',
            'name',
            'shop_name'
        ],
        'campaign_creator_performance' => [
            'campaign_id',
            'product_id',
            'creator_username',
            'follower_count',
            'paid_amount',
            'video_count'
        ],
        'creator_content_statistics' => [
            'creator_username',
            'content_type',
            'view_count',
            'like_count',
            'comment_count',
            'paid_order_count',
            'paid_amount',
            'published_date'
        ]
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->helper('url');
        $this->load->library('session');

        date_default_timezone_set('Asia/Jakarta');

        $keys = $this->config->item('gemini_api_keys');
        $this->gemini_api_keys = is_array($keys) ? array_values(array_filter($keys)) : [];

        if ($this->session->userdata('logged_in')) {
            $this->user_role = $this->session->userdata('role') ?: 'admin';
        }

        $cache_dir = APPPATH . 'cache/gemini/';
        if (!is_dir($cache_dir)) {
            @mkdir($cache_dir, 0777, true);
        }
    }

    /**
     * Endpoint utama voice assistant.
     * Wajib JSON only.
     */
    public function process_query()
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $this->output->set_content_type('application/json');

            $question_raw = $this->input->post('question');
            $question = strtolower(trim((string) $question_raw));
            $role = $this->input->post('role') ?: $this->user_role;

            if ($question === '') {
                return $this->json_response([
                    'success' => false,
                    'answer' => 'Maaf, saya tidak mendengar pertanyaan.',
                    'action' => 'show_answer',
                    'data' => []
                ]);
            }

            log_message('info', "[Toopai Voice][{$role}] Q: {$question}");

            $result = $this->understand_and_answer($question, $role);

            if (!is_array($result)) {
                $result = [
                    'success' => false,
                    'answer' => 'Maaf, hasil analisa tidak valid.',
                    'action' => 'show_answer',
                    'data' => []
                ];
            }

            $result = $this->normalize_response($result);

            return $this->json_response($result);

        } catch (Throwable $e) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            log_message('error', '[Toopai Voice ERROR] ' . $e->getMessage());
            log_message('error', '[Toopai Voice FILE] ' . $e->getFile() . ':' . $e->getLine());
            log_message('error', '[Toopai Voice TRACE] ' . $e->getTraceAsString());

            return $this->json_response([
                'success' => false,
                'answer' => 'Maaf, terjadi error di server saat memproses permintaan.',
                'action' => 'show_error',
                'data' => [
                    'debug' => ENVIRONMENT === 'development' ? [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ] : null
                ]
            ]);

        } finally {
            restore_error_handler();
        }
    }

    private function json_response($payload)
    {
        $this->output->set_content_type('application/json');
        return $this->output->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    private function normalize_response($result)
    {
        if (!isset($result['success'])) {
            $result['success'] = true;
        }

        if (!isset($result['answer'])) {
            $result['answer'] = '';
        }

        if (!isset($result['action']) || empty($result['action'])) {
            $result['action'] = 'show_answer';
        }

        if (!isset($result['data']) || !is_array($result['data'])) {
            $result['data'] = [];
        }

        return $result;
    }

    private function understand_and_answer($question, $role)
    {
        $specific = $this->handle_specific_intents($question, $role);
        if ($specific) {
            return $specific;
        }

        $dynamic = $this->handle_dynamic_query($question, $role);
        if ($dynamic) {
            return $dynamic;
        }

        return $this->get_general_summary($role);
    }

    private function handle_specific_intents($question, $role)
    {
        if (preg_match('/^(halo|hai|hi|hei|pagi|siang|malam|apa kabar|selamat)/i', $question)) {
            $hour = date('H');
            $greet = $hour < 12 ? 'pagi' : ($hour < 15 ? 'siang' : ($hour < 19 ? 'sore' : 'malam'));

            return [
                'success' => true,
                'answer' => "Selamat {$greet}! Saya Toopai. Ada yang bisa saya bantu analisa?",
                'action' => 'show_answer',
                'data' => []
            ];
        }

        if (preg_match('/analisa.*ai|ai.*analisa|gemini|deep.*analysis|analisa.*mendalam|analisa.*detail|analisis.*mendalam|analisa lebih|analisis lebih|lebih detail|detail/i', $question)) {
            return $this->ai_deep_analysis($question);
        }

        if (preg_match('/anomali|anomaly|aneh|tidak biasa|lonjakan|penurunan drastis/i', $question)) {
            return $this->detect_anomalies('30days');
        }

        if (preg_match('/rekomendasi|saran|action|aksi|apa yang harus/i', $question)) {
            return $this->generate_recommendations('7days');
        }

        if (preg_match('/grafik|chart|tampilkan.*grafik|visualisasi|plot/i', $question)) {
            if (preg_match('/creator|kreator/i', $question)) {
                return $this->show_chart('creator_performance', '7days', $question);
            }

            if (preg_match('/campaign|kampanye/i', $question)) {
                return $this->show_chart('campaign_comparison', '7days', $question);
            }

            return $this->show_chart('gmv_daily', '7days', $question);
        }

        if (preg_match('/top.*creator|creator.*(top|terbaik|terbesar)|creator paling|top.*kreator|kreator.*top/i', $question)) {
            return $this->answer_top_creators($question);
        }

        if (preg_match('/creator\s+@?([\w.]+)/i', $question, $m)) {
            return $this->answer_creator_detail($m[1]);
        }

        if (preg_match('/gmv|penjualan|revenue|omzet|pendapatan/i', $question)) {
            if (preg_match('/tanggal|kapan|tertinggi|paling tinggi|terbesar/i', $question)) {
                return $this->answer_highest_gmv_date();
            }

            return $this->answer_gmv($question);
        }

        if (preg_match('/order|pesanan|transaksi/i', $question)) {
            if (preg_match('/terakhir|last|kapan/i', $question)) {
                return $this->answer_last_order();
            }

            return $this->answer_orders($question);
        }

        if (preg_match('/campaign|kampanye/i', $question)) {
            return $this->answer_campaigns($question);
        }

        if (preg_match('/brand|merek|toko|shop/i', $question)) {
            return $this->answer_brands($question);
        }

        if (preg_match('/komisi|commission|earning/i', $question)) {
            return $this->answer_commission($question);
        }

        if (preg_match('/produk|product|barang/i', $question)) {
            return $this->answer_products($question);
        }

        if (preg_match('/sync|sinkron|update data/i', $question)) {
            return $this->answer_sync_status();
        }

        if (preg_match('/banding|perbandingan|vs|versus|membedakan|perbedaan/i', $question)) {
            return $this->answer_comparison($question);
        }

        if (preg_match('/7 hari|seminggu|minggu ini|sepekan/i', $question)) {
            return $this->get_weekly_summary();
        }

        if (preg_match('/kemarin|yesterday/i', $question)) {
            return $this->get_yesterday_summary();
        }

        if (preg_match('/kenapa|mengapa|kenaikan|penurunan|turun|naik|analisa|analisis|insight/i', $question)) {
            return $this->answer_insight_analysis($question);
        }

        if (preg_match('/performa|ringkasan|rangkuman|bagaimana|gimana|kondisi/i', $question)) {
            return $this->get_general_summary($role);
        }

        if (preg_match('/bantuan|help|bisa apa/i', $question)) {
            return [
                'success' => true,
                'answer' => 'Saya bisa menganalisa GMV, order, top creator, campaign, komisi, brand, produk, grafik, anomali, rekomendasi aksi, dan performa 7 hari terakhir.',
                'action' => 'show_answer',
                'data' => []
            ];
        }

        if (preg_match('/terima kasih|thanks|makasih/i', $question)) {
            return [
                'success' => true,
                'answer' => 'Sama-sama! Saya siap membantu analisa data Toopai kapan saja.',
                'action' => 'show_answer',
                'data' => []
            ];
        }

        return null;
    }

    private function ai_deep_analysis($question)
    {
        $data_context = $this->build_analysis_context();

        $ai_prompt = "Analisa pertanyaan user: {$question}.

Gunakan hanya data yang diberikan.
Jawab ringkas, lengkap, dan wajib selesai.

Format:
1. Insight utama: maksimal 2 kalimat.
2. Penyebab paling mungkin: 2 poin.
3. Rekomendasi aksi: 3 langkah praktis.
4. Risiko/anomali: 1 hal yang perlu dicek.

Maksimal 220 kata.
Wajib akhiri dengan kalimat: Analisa selesai.";

        $ai_result = $this->ask_gemini(
            $ai_prompt,
            $data_context,
            'voice_ai_analysis_v3_' . md5($question . date('Y-m-d-H')),
            1800
        );

        if ($ai_result['success']) {
            return [
                'success' => true,
                'answer' => $ai_result['answer'],
                'action' => 'show_insight',
                'data' => $this->build_insight_modal_data($data_context, [
                    'ai_model' => $ai_result['model'] ?? null,
                    'finish_reason' => $ai_result['finish_reason'] ?? null,
                    'source' => !empty($ai_result['cached']) ? 'cache' : 'gemini'
                ])
            ];
        }

        $fallback = "Gemini belum berhasil merespons. Analisa lokal: GMV hari ini Rp " .
            number_format($data_context['today_gmv'], 0, ',', '.') .
            ", growth {$data_context['gmv_growth_percent']}% dibanding kemarin, dengan {$data_context['today_orders']} order. " .
            "Fokus cek creator aktif, produk penyumbang GMV, campaign yang turun, dan aktivitas promosi hari ini. Analisa selesai.";

        return [
            'success' => true,
            'answer' => $fallback,
            'action' => 'show_insight',
            'data' => $this->build_insight_modal_data($data_context, [
                'source' => 'local_fallback'
            ])
        ];
    }

    private function build_analysis_context()
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $week_start = date('Y-m-d', strtotime('-7 days'));
        $month_start = date('Y-m-d', strtotime('-30 days'));

        $today_data = $this->db->select('COALESCE(SUM(gmv),0) as gmv, COUNT(DISTINCT order_id) as orders, COUNT(DISTINCT creator_username) as creators')
            ->from('affiliate_orders')
            ->where('order_date_local', $today)
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->get()
            ->row();

        $yesterday_data = $this->db->select('COALESCE(SUM(gmv),0) as gmv, COUNT(DISTINCT order_id) as orders, COUNT(DISTINCT creator_username) as creators')
            ->from('affiliate_orders')
            ->where('order_date_local', $yesterday)
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->get()
            ->row();

        $weekly_data = $this->db->select('COALESCE(SUM(gmv),0) as gmv, COUNT(DISTINCT order_id) as orders, COUNT(DISTINCT creator_username) as creators')
            ->from('affiliate_orders')
            ->where('order_date_local >=', $week_start)
            ->where('order_date_local <=', $today)
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->get()
            ->row();

        $top_creators = $this->db->select('creator_username, COALESCE(SUM(gmv),0) as gmv, COUNT(DISTINCT order_id) as orders')
            ->from('affiliate_orders')
            ->where('order_date_local >=', $month_start)
            ->where('creator_username IS NOT NULL')
            ->where("creator_username != ''")
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->group_by('creator_username')
            ->order_by('gmv', 'DESC')
            ->limit(5)
            ->get()
            ->result();

        $top_products = $this->db->select('product_name, COALESCE(SUM(gmv),0) as gmv, COUNT(DISTINCT order_id) as orders')
            ->from('affiliate_orders')
            ->where('order_date_local >=', $month_start)
            ->where('product_name IS NOT NULL')
            ->where("product_name != ''")
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->group_by('product_name')
            ->order_by('gmv', 'DESC')
            ->limit(5)
            ->get()
            ->result();

        $top_campaigns = $this->db->select('campaign_name, COALESCE(SUM(gmv),0) as gmv, COUNT(DISTINCT order_id) as orders')
            ->from('affiliate_orders')
            ->where('order_date_local >=', $month_start)
            ->where('campaign_name IS NOT NULL')
            ->where("campaign_name != ''")
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->group_by('campaign_name')
            ->order_by('gmv', 'DESC')
            ->limit(5)
            ->get()
            ->result();

        $gmv_growth = 0;
        if (($yesterday_data->gmv ?? 0) > 0) {
            $gmv_growth = round((($today_data->gmv - $yesterday_data->gmv) / $yesterday_data->gmv) * 100, 1);
        }

        return [
            'today_date' => $today,
            'yesterday_date' => $yesterday,
            'today_gmv' => (float) ($today_data->gmv ?? 0),
            'today_orders' => (int) ($today_data->orders ?? 0),
            'today_active_creators' => (int) ($today_data->creators ?? 0),
            'yesterday_gmv' => (float) ($yesterday_data->gmv ?? 0),
            'yesterday_orders' => (int) ($yesterday_data->orders ?? 0),
            'yesterday_active_creators' => (int) ($yesterday_data->creators ?? 0),
            'gmv_growth_percent' => $gmv_growth,
            'weekly_gmv' => (float) ($weekly_data->gmv ?? 0),
            'weekly_orders' => (int) ($weekly_data->orders ?? 0),
            'weekly_active_creators' => (int) ($weekly_data->creators ?? 0),
            'active_creators' => (int) $this->db->where('status', 'ACTIVE')->count_all_results('creators'),
            'active_campaigns' => (int) $this->db->where('status', 'ONGOING')->count_all_results('affiliate_campaigns'),
            'top_creators_30d' => $top_creators,
            'top_products_30d' => $top_products,
            'top_campaigns_30d' => $top_campaigns
        ];
    }

    private function build_insight_modal_data($context, $extra = [])
    {
        return array_merge([
            'today_gmv' => $context['today_gmv'] ?? 0,
            'weekly_gmv' => $context['weekly_gmv'] ?? 0,
            'total_orders_today' => $context['today_orders'] ?? 0,
            'active_creators' => $context['active_creators'] ?? 0,
            'active_campaigns' => $context['active_campaigns'] ?? 0,
            'gmv_growth_percent' => $context['gmv_growth_percent'] ?? 0,
            'today_active_creators' => $context['today_active_creators'] ?? 0,
            'yesterday_gmv' => $context['yesterday_gmv'] ?? 0,
            'yesterday_orders' => $context['yesterday_orders'] ?? 0,
            'yesterday_active_creators' => $context['yesterday_active_creators'] ?? 0,
            'top_creators' => $context['top_creators_30d'] ?? [],
            'top_products' => $context['top_products_30d'] ?? [],
            'top_campaigns' => $context['top_campaigns_30d'] ?? []
        ], $extra);
    }

    private function show_chart($type, $period, $question)
    {
        $chart_data = $this->get_chart_data($type, $period);

        if (!$chart_data) {
            return [
                'success' => true,
                'answer' => 'Maaf, data untuk grafik tidak tersedia.',
                'action' => 'show_answer',
                'data' => []
            ];
        }

        $summary_context = [
            'chart_type' => $type,
            'title' => $chart_data['title'] ?? '',
            'period' => $period,
            'labels_count' => isset($chart_data['labels']) ? count($chart_data['labels']) : 0,
            'first_label' => $chart_data['labels'][0] ?? null,
            'last_label' => !empty($chart_data['labels']) ? end($chart_data['labels']) : null,
            'datasets' => []
        ];

        if (!empty($chart_data['datasets'])) {
            foreach ($chart_data['datasets'] as $dataset) {
                $values = $dataset['data'] ?? [];
                $summary_context['datasets'][] = [
                    'label' => $dataset['label'] ?? '',
                    'total' => array_sum($values),
                    'max' => !empty($values) ? max($values) : 0,
                    'min' => !empty($values) ? min($values) : 0,
                    'last_value' => !empty($values) ? end($values) : 0
                ];
            }
        }

        $ai_prompt = "Jelaskan grafik {$summary_context['title']} dalam 3 kalimat. Fokus tren, angka penting, dan rekomendasi singkat. Akhiri dengan: Analisa selesai.";

        $ai_result = $this->ask_gemini(
            $ai_prompt,
            $summary_context,
            'voice_chart_v2_' . md5($type . $period . date('Y-m-d-H')),
            1800
        );

        $explanation = $ai_result['success']
            ? $ai_result['answer']
            : "Berikut grafik {$summary_context['title']}. Data berhasil ditampilkan untuk membantu melihat tren periode ini. Analisa selesai.";

        return [
            'success' => true,
            'answer' => $explanation,
            'action' => 'show_chart',
            'chart' => $chart_data,
            'data' => $summary_context
        ];
    }

    private function answer_gmv($question)
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        if (preg_match('/kemarin|yesterday/i', $question)) {
            $data = $this->db->select('COALESCE(SUM(gmv),0) as total_gmv, COUNT(DISTINCT order_id) as orders')
                ->from('affiliate_orders')
                ->where('order_date_local', $yesterday)
                ->group_start()
                    ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                    ->or_where('order_status IS NULL')
                ->group_end()
                ->get()
                ->row();

            return [
                'success' => true,
                'answer' => "GMV kemarin Rp " . number_format($data->total_gmv ?? 0, 0, ',', '.') . " dari " . number_format($data->orders ?? 0) . " pesanan.",
                'action' => 'show_metric',
                'data' => [
                    'title' => 'GMV Kemarin',
                    'metrics' => [
                        ['label' => 'GMV', 'value' => 'Rp ' . number_format($data->total_gmv ?? 0, 0, ',', '.')],
                        ['label' => 'Orders', 'value' => number_format($data->orders ?? 0)]
                    ]
                ]
            ];
        }

        if (preg_match('/bulan ini|30 hari|sebulan/i', $question)) {
            $data = $this->db->select('COALESCE(SUM(gmv),0) as total_gmv, COUNT(DISTINCT order_id) as orders')
                ->from('affiliate_orders')
                ->where('order_date_local >=', date('Y-m-d', strtotime('-30 days')))
                ->group_start()
                    ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                    ->or_where('order_status IS NULL')
                ->group_end()
                ->get()
                ->row();

            return [
                'success' => true,
                'answer' => "GMV 30 hari terakhir Rp " . number_format($data->total_gmv ?? 0, 0, ',', '.') . " dari " . number_format($data->orders ?? 0) . " pesanan.",
                'action' => 'show_metric',
                'data' => [
                    'title' => 'GMV 30 Hari',
                    'metrics' => [
                        ['label' => 'GMV', 'value' => 'Rp ' . number_format($data->total_gmv ?? 0, 0, ',', '.')],
                        ['label' => 'Orders', 'value' => number_format($data->orders ?? 0)]
                    ]
                ]
            ];
        }

        $today_data = $this->db->select('COALESCE(SUM(gmv),0) as total_gmv, COUNT(DISTINCT order_id) as orders')
            ->from('affiliate_orders')
            ->where('order_date_local', $today)
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->get()
            ->row();

        $yesterday_gmv = $this->db->select('COALESCE(SUM(gmv),0) as gmv')
            ->from('affiliate_orders')
            ->where('order_date_local', $yesterday)
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->get()
            ->row()
            ->gmv ?? 0;

        $growth = $yesterday_gmv > 0
            ? round((($today_data->total_gmv - $yesterday_gmv) / $yesterday_gmv) * 100, 1)
            : (($today_data->total_gmv ?? 0) > 0 ? 100 : 0);

        $dir = $growth >= 0 ? 'naik' : 'turun';

        return [
            'success' => true,
            'answer' => "GMV hari ini Rp " . number_format($today_data->total_gmv ?? 0, 0, ',', '.') . " dari " . number_format($today_data->orders ?? 0) . " pesanan. {$dir} " . abs($growth) . "% dibanding kemarin.",
            'action' => 'show_metric',
            'data' => [
                'title' => 'GMV Hari Ini',
                'metrics' => [
                    ['label' => 'GMV', 'value' => 'Rp ' . number_format($today_data->total_gmv ?? 0, 0, ',', '.')],
                    ['label' => 'Orders', 'value' => number_format($today_data->orders ?? 0)],
                    ['label' => 'Growth', 'value' => $growth . '%']
                ]
            ]
        ];
    }

    private function answer_orders($question)
    {
        $today = date('Y-m-d');

        $data = $this->db->select('COUNT(DISTINCT order_id) as orders, COALESCE(SUM(gmv),0) as total_gmv')
            ->from('affiliate_orders')
            ->where('order_date_local', $today)
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->get()
            ->row();

        return [
            'success' => true,
            'answer' => "Hari ini ada " . number_format($data->orders ?? 0) . " pesanan dengan total GMV Rp " . number_format($data->total_gmv ?? 0, 0, ',', '.') . ".",
            'action' => 'show_metric',
            'data' => [
                'title' => 'Orders Hari Ini',
                'metrics' => [
                    ['label' => 'Orders', 'value' => number_format($data->orders ?? 0)],
                    ['label' => 'GMV', 'value' => 'Rp ' . number_format($data->total_gmv ?? 0, 0, ',', '.')]
                ]
            ]
        ];
    }

    private function answer_top_creators($question)
    {
        $limit = 5;
        if (preg_match('/(\d+)/', $question, $m)) {
            $limit = min((int) $m[1], 10);
        }

        $creators = $this->db->select('creator_username, COALESCE(SUM(gmv),0) as total_gmv, COUNT(DISTINCT order_id) as orders')
            ->from('affiliate_orders')
            ->where('order_date_local >=', date('Y-m-d', strtotime('-30 days')))
            ->where('creator_username IS NOT NULL')
            ->where("creator_username != ''")
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->group_by('creator_username')
            ->order_by('total_gmv', 'DESC')
            ->limit($limit)
            ->get()
            ->result();

        if (empty($creators)) {
            return [
                'success' => true,
                'answer' => 'Belum ada data creator.',
                'action' => 'show_answer',
                'data' => []
            ];
        }

        $names = array_map(function ($c) {
            return "@{$c->creator_username} Rp " . number_format($c->total_gmv ?? 0, 0, ',', '.');
        }, $creators);

        return [
            'success' => true,
            'answer' => "Top {$limit} creator 30 hari terakhir: " . implode(', ', $names) . ".",
            'action' => 'show_table',
            'data' => [
                'title' => "Top {$limit} Creator",
                'columns' => ['Creator', 'GMV', 'Orders'],
                'rows' => array_map(function ($c) {
                    return [
                        '@' . $c->creator_username,
                        'Rp ' . number_format($c->total_gmv ?? 0, 0, ',', '.'),
                        number_format($c->orders ?? 0)
                    ];
                }, $creators)
            ]
        ];
    }

    private function answer_creator_detail($username)
    {
        $username = ltrim(trim($username), '@');

        if ($username === '') {
            return [
                'success' => true,
                'answer' => 'Username creator belum jelas. Coba sebutkan nama creator dengan format @username.',
                'action' => 'show_answer',
                'data' => []
            ];
        }

        $creator = $this->db->select('username, full_name, category, phone, email, status, total_gmv, total_orders')
            ->from('creators')
            ->where('username', $username)
            ->get()
            ->row();

        if (!$creator) {
            return [
                'success' => true,
                'answer' => "Creator @{$username} tidak ditemukan di database.",
                'action' => 'show_answer',
                'data' => []
            ];
        }

        $gmv_30 = $this->db->select('COALESCE(SUM(gmv),0) as gmv, COUNT(DISTINCT order_id) as orders')
            ->from('affiliate_orders')
            ->where('creator_username', $username)
            ->where('order_date_local >=', date('Y-m-d', strtotime('-30 days')))
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->get()
            ->row();

        $answer = "@{$username} - {$creator->full_name}. Kategori: {$creator->category}. GMV 30 hari Rp " .
            number_format($gmv_30->gmv ?? 0, 0, ',', '.') .
            " dari " . number_format($gmv_30->orders ?? 0) .
            " orders. Status: {$creator->status}.";

        return [
            'success' => true,
            'answer' => $answer,
            'action' => 'show_metric',
            'data' => [
                'title' => 'Detail Creator @' . $username,
                'metrics' => [
                    ['label' => 'Nama', 'value' => $creator->full_name],
                    ['label' => 'Kategori', 'value' => $creator->category],
                    ['label' => 'Status', 'value' => $creator->status],
                    ['label' => 'GMV 30 Hari', 'value' => 'Rp ' . number_format($gmv_30->gmv ?? 0, 0, ',', '.')],
                    ['label' => 'Orders 30 Hari', 'value' => number_format($gmv_30->orders ?? 0)]
                ]
            ]
        ];
    }

    private function answer_campaigns($question)
    {
        $campaigns = $this->db->select('campaign_name, total_gmv, total_orders, total_creators, status')
            ->from('affiliate_campaigns')
            ->where('status', 'ONGOING')
            ->order_by('total_gmv', 'DESC')
            ->limit(5)
            ->get()
            ->result();

        if (empty($campaigns)) {
            return [
                'success' => true,
                'answer' => 'Tidak ada campaign aktif.',
                'action' => 'show_answer',
                'data' => []
            ];
        }

        $list = array_map(function ($c) {
            return "{$c->campaign_name} GMV Rp " . number_format($c->total_gmv ?? 0, 0, ',', '.');
        }, $campaigns);

        return [
            'success' => true,
            'answer' => 'Campaign aktif: ' . implode('; ', $list) . '.',
            'action' => 'show_table',
            'data' => [
                'title' => 'Campaign Aktif',
                'columns' => ['Campaign', 'GMV', 'Orders', 'Creators'],
                'rows' => array_map(function ($c) {
                    return [
                        $c->campaign_name,
                        'Rp ' . number_format($c->total_gmv ?? 0, 0, ',', '.'),
                        number_format($c->total_orders ?? 0),
                        number_format($c->total_creators ?? 0)
                    ];
                }, $campaigns)
            ]
        ];
    }

    private function answer_brands($question)
    {
        $brands = $this->db->select('p.shop_name, COALESCE(SUM(o.gmv),0) as total_gmv, COUNT(DISTINCT o.order_id) as orders')
            ->from('affiliate_products p')
            ->join('affiliate_orders o', 'p.product_id = o.product_id', 'left')
            ->where('p.shop_name IS NOT NULL')
            ->where("p.shop_name != ''")
            ->group_by('p.shop_name')
            ->order_by('total_gmv', 'DESC')
            ->limit(5)
            ->get()
            ->result();

        if (empty($brands)) {
            return [
                'success' => true,
                'answer' => 'Belum ada data brand.',
                'action' => 'show_answer',
                'data' => []
            ];
        }

        return [
            'success' => true,
            'answer' => 'Top brand: ' . implode(', ', array_map(function ($b) {
                return "{$b->shop_name} Rp " . number_format($b->total_gmv ?? 0, 0, ',', '.');
            }, $brands)) . '.',
            'action' => 'show_table',
            'data' => [
                'title' => 'Top Brand',
                'columns' => ['Brand', 'GMV', 'Orders'],
                'rows' => array_map(function ($b) {
                    return [
                        $b->shop_name,
                        'Rp ' . number_format($b->total_gmv ?? 0, 0, ',', '.'),
                        number_format($b->orders ?? 0)
                    ];
                }, $brands)
            ]
        ];
    }

    private function answer_commission($question)
    {
        $today = date('Y-m-d');

        $data = $this->db->select('COALESCE(SUM(estimated_commission),0) as estimated, COALESCE(SUM(actual_commission),0) as actual')
            ->from('affiliate_orders')
            ->where('order_date_local', $today)
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->get()
            ->row();

        return [
            'success' => true,
            'answer' => 'Estimasi komisi hari ini Rp ' . number_format($data->estimated ?? 0, 0, ',', '.') . '. Actual commission Rp ' . number_format($data->actual ?? 0, 0, ',', '.') . '.',
            'action' => 'show_metric',
            'data' => [
                'title' => 'Komisi Hari Ini',
                'metrics' => [
                    ['label' => 'Estimated', 'value' => 'Rp ' . number_format($data->estimated ?? 0, 0, ',', '.')],
                    ['label' => 'Actual', 'value' => 'Rp ' . number_format($data->actual ?? 0, 0, ',', '.')]
                ]
            ]
        ];
    }

    private function answer_products($question)
    {
        $products = $this->db->select('product_name, COALESCE(SUM(gmv),0) as total_gmv, COUNT(DISTINCT order_id) as orders')
            ->from('affiliate_orders')
            ->where('order_date_local >=', date('Y-m-d', strtotime('-30 days')))
            ->where('product_name IS NOT NULL')
            ->where("product_name != ''")
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->group_by('product_name')
            ->order_by('total_gmv', 'DESC')
            ->limit(5)
            ->get()
            ->result();

        if (empty($products)) {
            return [
                'success' => true,
                'answer' => 'Belum ada data produk.',
                'action' => 'show_answer',
                'data' => []
            ];
        }

        return [
            'success' => true,
            'answer' => 'Produk terlaris: ' . implode('; ', array_map(function ($p) {
                return substr($p->product_name, 0, 50) . ' Rp ' . number_format($p->total_gmv ?? 0, 0, ',', '.');
            }, $products)) . '.',
            'action' => 'show_table',
            'data' => [
                'title' => 'Produk Terlaris 30 Hari',
                'columns' => ['Produk', 'GMV', 'Orders'],
                'rows' => array_map(function ($p) {
                    return [
                        $p->product_name,
                        'Rp ' . number_format($p->total_gmv ?? 0, 0, ',', '.'),
                        number_format($p->orders ?? 0)
                    ];
                }, $products)
            ]
        ];
    }

    private function answer_sync_status()
    {
        $last = $this->db->select('MAX(end_time) as last_sync')
            ->from('affiliate_sync_logs')
            ->where('status', 'success')
            ->get()
            ->row();

        $pending = $this->db->where('status', 'pending')->count_all_results('affiliate_sync_queue');
        $time = $last && $last->last_sync ? date('d M H:i', strtotime($last->last_sync)) : 'belum pernah';

        return [
            'success' => true,
            'answer' => "Sync terakhir berhasil pada {$time}. " . ($pending > 0 ? "Ada {$pending} item dalam antrian." : "Semua data sudah tersinkron."),
            'action' => 'show_metric',
            'data' => [
                'title' => 'Status Sync',
                'metrics' => [
                    ['label' => 'Last Sync', 'value' => $time],
                    ['label' => 'Pending Queue', 'value' => number_format($pending)]
                ]
            ]
        ];
    }

    private function answer_insight_analysis($question)
    {
        $context = $this->build_analysis_context();

        $growth = $context['gmv_growth_percent'];
        $dir = $growth >= 0 ? 'naik' : 'turun';

        $answer = "Berikut analisa saya: GMV hari ini {$dir} " . abs($growth) . "% dibanding kemarin. ";

        if ($growth < 0) {
            if (($context['yesterday_active_creators'] ?? 0) > ($context['today_active_creators'] ?? 0)) {
                $answer .= "Salah satu faktornya adalah creator aktif harian turun dari {$context['yesterday_active_creators']} menjadi {$context['today_active_creators']}. ";
            } else {
                $answer .= "Jumlah creator relatif stabil, jadi kemungkinan nilai transaksi atau performa produk/campaign menurun. ";
            }
        } elseif ($growth > 0) {
            $answer .= "Performa sedang positif. Pertahankan dorongan creator dan campaign yang memberi kontribusi tertinggi. ";
        } else {
            $answer .= "GMV relatif stabil dibanding kemarin. ";
        }

        $answer .= "Untuk analisa lebih lengkap, katakan: analisa lebih detail.";

        return [
            'success' => true,
            'answer' => $answer,
            'action' => 'show_insight',
            'data' => $this->build_insight_modal_data($context, [
                'source' => 'local_insight'
            ])
        ];
    }

    private function answer_highest_gmv_date()
    {
        $result = $this->db->select('order_date_local, COALESCE(SUM(gmv),0) as total_gmv, COUNT(DISTINCT order_id) as orders')
            ->from('affiliate_orders')
            ->where('order_date_local >=', date('Y-m-d', strtotime('-30 days')))
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->group_by('order_date_local')
            ->order_by('total_gmv', 'DESC')
            ->limit(1)
            ->get()
            ->row();

        if (!$result) {
            return [
                'success' => true,
                'answer' => 'Data GMV tertinggi belum tersedia.',
                'action' => 'show_answer',
                'data' => []
            ];
        }

        $date = date('d M Y', strtotime($result->order_date_local));

        return [
            'success' => true,
            'answer' => "GMV tertinggi dalam 30 hari terjadi pada {$date}, sebesar Rp " . number_format($result->total_gmv, 0, ',', '.') . " dari " . number_format($result->orders) . " pesanan.",
            'action' => 'show_metric',
            'data' => [
                'title' => 'GMV Tertinggi 30 Hari',
                'metrics' => [
                    ['label' => 'Tanggal', 'value' => $date],
                    ['label' => 'GMV', 'value' => 'Rp ' . number_format($result->total_gmv, 0, ',', '.')],
                    ['label' => 'Orders', 'value' => number_format($result->orders)]
                ]
            ]
        ];
    }

    private function answer_last_order()
    {
        $last = $this->db->select('order_id, creator_username, product_name, gmv, order_time')
            ->from('affiliate_orders')
            ->order_by('order_time', 'DESC')
            ->limit(1)
            ->get()
            ->row();

        if (!$last) {
            return [
                'success' => true,
                'answer' => 'Belum ada order.',
                'action' => 'show_answer',
                'data' => []
            ];
        }

        $time = date('d M H:i', strtotime($last->order_time));

        return [
            'success' => true,
            'answer' => "Order terakhir: {$last->order_id} oleh @{$last->creator_username}, produk {$last->product_name}, sebesar Rp " . number_format($last->gmv, 0, ',', '.') . " pada {$time}.",
            'action' => 'show_metric',
            'data' => [
                'title' => 'Order Terakhir',
                'metrics' => [
                    ['label' => 'Order ID', 'value' => $last->order_id],
                    ['label' => 'Creator', 'value' => '@' . $last->creator_username],
                    ['label' => 'GMV', 'value' => 'Rp ' . number_format($last->gmv, 0, ',', '.')],
                    ['label' => 'Waktu', 'value' => $time]
                ]
            ]
        ];
    }

    private function get_weekly_summary()
    {
        $context = $this->build_analysis_context();

        $best_day = $this->db->select('order_date_local, COALESCE(SUM(gmv),0) as gmv, COUNT(DISTINCT order_id) as orders')
            ->from('affiliate_orders')
            ->where('order_date_local >=', date('Y-m-d', strtotime('-7 days')))
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->group_by('order_date_local')
            ->order_by('gmv', 'DESC')
            ->limit(1)
            ->get()
            ->row();

        $answer = "Performa 7 hari terakhir: total GMV Rp " .
            number_format($context['weekly_gmv'] ?? 0, 0, ',', '.') .
            " dari " . number_format($context['weekly_orders'] ?? 0) .
            " pesanan oleh " . number_format($context['weekly_active_creators'] ?? 0) .
            " creator.";

        if ($best_day) {
            $answer .= " Hari terbaik adalah " . date('d M', strtotime($best_day->order_date_local)) .
                " dengan GMV Rp " . number_format($best_day->gmv, 0, ',', '.') . ".";
        }

        return [
            'success' => true,
            'answer' => $answer,
            'action' => 'show_insight',
            'data' => $this->build_insight_modal_data($context, [
                'source' => 'weekly_summary'
            ])
        ];
    }

    private function get_yesterday_summary()
    {
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $data = $this->db->select('COALESCE(SUM(gmv),0) as gmv, COUNT(DISTINCT order_id) as orders, COUNT(DISTINCT creator_username) as creators')
            ->from('affiliate_orders')
            ->where('order_date_local', $yesterday)
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->get()
            ->row();

        return [
            'success' => true,
            'answer' => "Kemarin GMV Rp " . number_format($data->gmv ?? 0, 0, ',', '.') . " dari " . number_format($data->orders ?? 0) . " pesanan oleh " . number_format($data->creators ?? 0) . " creator.",
            'action' => 'show_metric',
            'data' => [
                'title' => 'Ringkasan Kemarin',
                'metrics' => [
                    ['label' => 'GMV', 'value' => 'Rp ' . number_format($data->gmv ?? 0, 0, ',', '.')],
                    ['label' => 'Orders', 'value' => number_format($data->orders ?? 0)],
                    ['label' => 'Creators', 'value' => number_format($data->creators ?? 0)]
                ]
            ]
        ];
    }

    private function answer_comparison($question)
    {
        $context = $this->build_analysis_context();

        $answer = "Perbandingan hari ini vs kemarin: GMV hari ini Rp " .
            number_format($context['today_gmv'], 0, ',', '.') .
            " vs kemarin Rp " . number_format($context['yesterday_gmv'], 0, ',', '.') .
            ", growth {$context['gmv_growth_percent']}%. Orders hari ini {$context['today_orders']} vs kemarin {$context['yesterday_orders']}. Creator aktif hari ini {$context['today_active_creators']} vs kemarin {$context['yesterday_active_creators']}.";

        return [
            'success' => true,
            'answer' => $answer,
            'action' => 'show_insight',
            'data' => $this->build_insight_modal_data($context, [
                'source' => 'comparison'
            ])
        ];
    }

    private function get_general_summary($role)
    {
        $context = $this->build_analysis_context();

        $answer = "Ringkasan hari ini: GMV Rp " .
            number_format($context['today_gmv'], 0, ',', '.') .
            " dari " . number_format($context['today_orders']) .
            " pesanan. Ada " . number_format($context['active_creators']) .
            " creator aktif dan " . number_format($context['active_campaigns']) .
            " campaign berjalan.";

        return [
            'success' => true,
            'answer' => $answer,
            'action' => 'show_insight',
            'data' => $this->build_insight_modal_data($context, [
                'source' => 'general_summary'
            ])
        ];
    }

    public function ai_analyze()
    {
        $this->output->set_content_type('application/json');

        $topic = $this->input->post('topic') ?: 'general';
        $period = $this->input->post('period') ?: '7days';
        $role = $this->input->post('role') ?: 'admin';

        $result = $this->perform_ai_analysis($topic, $period, $role);
        $result = $this->normalize_response($result);

        return $this->json_response($result);
    }

    private function perform_ai_analysis($topic, $period, $role)
    {
        switch ($topic) {
            case 'gmv_decline':
                return $this->analyze_gmv_decline($period);

            case 'top_performers':
                return $this->analyze_top_performers($period);

            case 'trend':
                return $this->analyze_trend($period);

            case 'recommendations':
                return $this->generate_recommendations($period);

            case 'anomaly':
                return $this->detect_anomalies($period);

            default:
                return $this->full_analysis($period);
        }
    }

    private function analyze_gmv_decline($period)
    {
        $context = $this->build_analysis_context();
        $days = $period === '30days' ? 30 : 7;
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        $today = date('Y-m-d');

        $daily = $this->db->select('order_date_local as date, COALESCE(SUM(gmv),0) as gmv, COUNT(DISTINCT order_id) as orders, COUNT(DISTINCT creator_username) as creators')
            ->from('affiliate_orders')
            ->where('order_date_local >=', $start_date)
            ->where('order_date_local <=', $today)
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->group_by('order_date_local')
            ->order_by('order_date_local', 'ASC')
            ->get()
            ->result();

        $trend_3 = 0;
        $last_3 = array_slice($daily, -3);

        if (count($last_3) >= 2) {
            $first = (float) $last_3[0]->gmv;
            $last = (float) end($last_3)->gmv;
            if ($first > 0) {
                $trend_3 = round((($last - $first) / $first) * 100, 1);
            }
        }

        $answer = "Analisa penurunan GMV: dalam {$days} hari terakhir, tren 3 hari terakhir berubah {$trend_3}%. GMV hari ini Rp " .
            number_format($context['today_gmv'], 0, ',', '.') .
            ", growth {$context['gmv_growth_percent']}% dibanding kemarin. Rekomendasi: reaktivasi creator tidak aktif, dorong produk terbaik, dan cek campaign dengan kontribusi turun. Analisa selesai.";

        return [
            'success' => true,
            'answer' => $answer,
            'action' => 'show_insight',
            'data' => $this->build_insight_modal_data($context, [
                'trend_3' => $trend_3,
                'daily' => $daily,
                'source' => 'gmv_decline'
            ])
        ];
    }

    private function analyze_top_performers($period)
    {
        $days = $period === '30days' ? 30 : 7;
        $start_date = date('Y-m-d', strtotime("-{$days} days"));

        $top_creators = $this->db->select('creator_username, COALESCE(SUM(gmv),0) as total_gmv, COUNT(DISTINCT order_id) as orders, COUNT(DISTINCT order_date_local) as active_days')
            ->from('affiliate_orders')
            ->where('order_date_local >=', $start_date)
            ->where('creator_username IS NOT NULL')
            ->where("creator_username != ''")
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->group_by('creator_username')
            ->order_by('total_gmv', 'DESC')
            ->limit(10)
            ->get()
            ->result();

        if (empty($top_creators)) {
            return [
                'success' => true,
                'answer' => 'Belum ada data top performer.',
                'action' => 'show_answer',
                'data' => []
            ];
        }

        $top1 = $top_creators[0];

        return [
            'success' => true,
            'answer' => "Top performer periode ini adalah @{$top1->creator_username} dengan GMV Rp " . number_format($top1->total_gmv, 0, ',', '.') . " dari {$top1->orders} orders. Analisa selesai.",
            'action' => 'show_table',
            'data' => [
                'title' => 'Top Performers',
                'columns' => ['Creator', 'GMV', 'Orders', 'Active Days'],
                'rows' => array_map(function ($c) {
                    return [
                        '@' . $c->creator_username,
                        'Rp ' . number_format($c->total_gmv ?? 0, 0, ',', '.'),
                        number_format($c->orders ?? 0),
                        number_format($c->active_days ?? 0)
                    ];
                }, $top_creators)
            ]
        ];
    }

    private function full_analysis($period)
    {
        return $this->ai_deep_analysis('full analysis ' . $period);
    }

    private function generate_recommendations($period)
    {
        $context = $this->build_analysis_context();

        $low_stock = $this->db->select('product_name, sample_quota, inventory')
            ->from('affiliate_products')
            ->where('review_status', 'APPROVED')
            ->where('inventory <', 10)
            ->where('inventory >', 0)
            ->limit(5)
            ->get()
            ->result();

        $answer = "Rekomendasi action plan: pertama, reaktivasi creator yang belum aktif hari ini. Kedua, dorong top product dan top campaign 30 hari terakhir. Ketiga, cek stok produk karena ada " . count($low_stock) . " produk dengan stok menipis. Analisa selesai.";

        return [
            'success' => true,
            'answer' => $answer,
            'action' => 'show_insight',
            'data' => $this->build_insight_modal_data($context, [
                'low_stock_products' => $low_stock,
                'source' => 'recommendations'
            ])
        ];
    }

    private function analyze_trend($period)
    {
        $days = $period === '30days' ? 30 : 7;
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        $today = date('Y-m-d');

        $daily = $this->db->select('order_date_local as date, COALESCE(SUM(gmv),0) as gmv, COUNT(DISTINCT order_id) as orders')
            ->from('affiliate_orders')
            ->where('order_date_local >=', $start_date)
            ->where('order_date_local <=', $today)
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->group_by('order_date_local')
            ->order_by('order_date_local', 'ASC')
            ->get()
            ->result();

        $answer = "Tren {$days} hari berhasil dianalisa. Gunakan grafik untuk melihat pergerakan GMV dan orders. Analisa selesai.";

        return [
            'success' => true,
            'answer' => $answer,
            'action' => 'show_chart',
            'chart' => $this->get_chart_data('gmv_daily', $period),
            'data' => [
                'daily' => $daily
            ]
        ];
    }

    private function detect_anomalies($period)
    {
        $days = $period === '7days' ? 7 : 30;
        $start_date = date('Y-m-d', strtotime("-{$days} days"));

        $daily = $this->db->select('order_date_local as date, COALESCE(SUM(gmv),0) as gmv')
            ->from('affiliate_orders')
            ->where('order_date_local >=', $start_date)
            ->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end()
            ->group_by('order_date_local')
            ->order_by('order_date_local', 'ASC')
            ->get()
            ->result();

        $values = array_map('floatval', array_column($daily, 'gmv'));
        $avg = count($values) > 0 ? array_sum($values) / count($values) : 0;
        $std_dev = 0;

        if (count($values) > 1) {
            $sum_squared_diff = 0;
            foreach ($values as $v) {
                $sum_squared_diff += pow($v - $avg, 2);
            }
            $std_dev = sqrt($sum_squared_diff / count($values));
        }

        $anomalies = [];
        foreach ($daily as $day) {
            $gmv = (float) $day->gmv;

            if ($std_dev > 0 && $gmv > $avg + (2 * $std_dev)) {
                $anomalies[] = [
                    'date' => $day->date,
                    'gmv' => $gmv,
                    'type' => 'high',
                    'label' => 'Lonjakan'
                ];
            } elseif ($std_dev > 0 && $gmv < $avg - (1.5 * $std_dev) && $gmv > 0) {
                $anomalies[] = [
                    'date' => $day->date,
                    'gmv' => $gmv,
                    'type' => 'low',
                    'label' => 'Penurunan'
                ];
            }
        }

        $answer = "Anomaly detection selesai. Rata-rata GMV Rp " . number_format($avg, 0, ',', '.') . ". ";
        $answer .= empty($anomalies)
            ? "Tidak ada anomali signifikan dalam {$days} hari terakhir."
            : "Ditemukan " . count($anomalies) . " anomali. Silakan cek detail di modal.";

        return [
            'success' => true,
            'answer' => $answer,
            'action' => 'show_anomaly',
            'data' => [
                'period' => $period,
                'days' => $days,
                'average_gmv' => $avg,
                'std_dev' => $std_dev,
                'anomalies' => $anomalies
            ]
        ];
    }

    private function handle_dynamic_query($question, $role)
    {
        if (preg_match('/berapa (banyak|jumlah|total) (creator|kreator|brand|campaign|produk|order)/i', $question, $m)) {
            $entity = $m[2];

            $map = [
                'creator' => ['table' => 'creators', 'column' => 'id'],
                'kreator' => ['table' => 'creators', 'column' => 'id'],
                'brand' => ['table' => 'brands', 'column' => 'id'],
                'campaign' => ['table' => 'affiliate_campaigns', 'column' => 'campaign_id', 'where' => "status='ONGOING'"],
                'produk' => ['table' => 'affiliate_products', 'column' => 'product_id'],
                'order' => ['table' => 'affiliate_orders', 'column' => 'order_id', 'date_column' => 'order_date_local', 'date' => date('Y-m-d')]
            ];

            if (isset($map[$entity])) {
                $mapping = $map[$entity];

                $sql = "SELECT COUNT(DISTINCT {$mapping['column']}) as total FROM {$mapping['table']}";

                if (!empty($mapping['where'])) {
                    $sql .= " WHERE {$mapping['where']}";
                }

                if (!empty($mapping['date_column'])) {
                    $sql .= (strpos($sql, 'WHERE') !== false ? ' AND ' : ' WHERE ') . "{$mapping['date_column']} = '{$mapping['date']}'";
                }

                $query = $this->safe_read_query($sql);
                if (!$query) {
                    return [
                        'success' => true,
                        'answer' => 'Maaf, query ditolak karena tidak memenuhi aturan keamanan read-only.',
                        'action' => 'show_error',
                        'data' => []
                    ];
                }

                $result = $query->row();
                $count = $result->total ?? 0;

                return [
                    'success' => true,
                    'answer' => "Jumlah {$entity} saat ini adalah " . number_format($count) . ".",
                    'action' => 'show_metric',
                    'data' => [
                        'title' => 'Total ' . ucfirst($entity),
                        'metrics' => [
                            ['label' => ucfirst($entity), 'value' => number_format($count)]
                        ]
                    ]
                ];
            }
        }

        if (preg_match('/kapan (order|pesanan|sync) terakhir/i', $question, $m)) {
            if ($m[1] === 'order' || $m[1] === 'pesanan') {
                return $this->answer_last_order();
            }

            if ($m[1] === 'sync') {
                return $this->answer_sync_status();
            }
        }

        if (preg_match('/siapa (creator|kreator)\s+(@?[\w.]+)/i', $question, $m)) {
            return $this->answer_creator_detail($m[2]);
        }

        if (preg_match('/campaign (terbesar|terbaik|paling besar)/i', $question)) {
            $top = $this->db->select('campaign_name, total_gmv')
                ->from('affiliate_campaigns')
                ->where('status', 'ONGOING')
                ->order_by('total_gmv', 'DESC')
                ->limit(1)
                ->get()
                ->row();

            if ($top) {
                return [
                    'success' => true,
                    'answer' => "Campaign terbesar adalah {$top->campaign_name} dengan total GMV Rp " . number_format($top->total_gmv ?? 0, 0, ',', '.') . ".",
                    'action' => 'show_metric',
                    'data' => [
                        'title' => 'Campaign Terbesar',
                        'metrics' => [
                            ['label' => 'Campaign', 'value' => $top->campaign_name],
                            ['label' => 'GMV', 'value' => 'Rp ' . number_format($top->total_gmv ?? 0, 0, ',', '.')]
                        ]
                    ]
                ];
            }
        }

        return null;
    }

    private function ask_gemini($prompt, $context_data = [], $cache_key = null, $cache_ttl = 1800)
    {
        if (empty($this->gemini_api_keys)) {
            log_message('error', '[TOOPAI GEMINI] API keys empty. Set config gemini_api_keys.');
            return [
                'success' => false,
                'answer' => 'Gemini API key belum dikonfigurasi.'
            ];
        }

        $safe_context = $this->compact_ai_context($context_data);

        $full_prompt =
            "Kamu adalah Toopai AI Analyst untuk affiliate marketing.\n" .
            "Gunakan hanya data yang diberikan. Jangan mengarang angka.\n" .
            "Jawab Bahasa Indonesia, ringkas, lengkap, dan actionable.\n" .
            "Jangan berhenti di tengah kalimat.\n\n" .
            "DATA:\n" . json_encode($safe_context, JSON_UNESCAPED_UNICODE) .
            "\n\nPERTANYAAN:\n" . $prompt;

        if ($cache_key) {
            $cached = $this->get_gemini_cache($cache_key);
            if ($cached !== null) {
                return [
                    'success' => true,
                    'answer' => $cached,
                    'cached' => true
                ];
            }
        }

        for ($attempt = 0; $attempt < count($this->gemini_api_keys); $attempt++) {
            $api_key = $this->get_next_api_key();

            foreach ($this->gemini_models as $model) {
                $url = $this->gemini_url . $model . ':generateContent?key=' . $api_key;

                $payload = [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $full_prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.25,
                        'maxOutputTokens' => 2500,
                        'topP' => 0.9,
                        'topK' => 40
                    ]
                ];

                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_TIMEOUT => 60
                ]);

                $response = curl_exec($ch);
                $curl_error = curl_error($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                log_message('error', '[TOOPAI GEMINI] MODEL=' . $model . ' HTTP=' . $http_code);

                if ($http_code === 200 && $response) {
                    $result = json_decode($response, true);

                    $text = '';
                    $parts = $result['candidates'][0]['content']['parts'] ?? [];

                    foreach ($parts as $part) {
                        if (!empty($part['text'])) {
                            $text .= $part['text'];
                        }
                    }

                    $finish_reason = $result['candidates'][0]['finishReason'] ?? null;

                    if (!empty($text)) {
                        $text = trim(preg_replace('/```json\s*|\s*```/', '', $text));

                        if ($finish_reason === 'MAX_TOKENS' || !$this->is_valid_ai_answer($text)) {
                            log_message('error', '[TOOPAI GEMINI WARNING] Invalid/truncated answer. Model=' . $model . ' Finish=' . $finish_reason);
                            log_message('error', '[TOOPAI GEMINI TEXT] ' . substr($text, 0, 500));
                            continue;
                        }

                        if ($cache_key) {
                            $this->set_gemini_cache($cache_key, $text, $cache_ttl);
                        }

                        return [
                            'success' => true,
                            'answer' => $text,
                            'model' => $model,
                            'finish_reason' => $finish_reason
                        ];
                    }
                }

                log_message('error', '[TOOPAI GEMINI FAILED] MODEL=' . $model);
                log_message('error', '[TOOPAI GEMINI CURL] ' . $curl_error);
                log_message('error', '[TOOPAI GEMINI RESPONSE] ' . substr((string) $response, 0, 1000));
            }
        }

        return [
            'success' => false,
            'answer' => 'Gemini belum berhasil merespons dari semua API key dan model.'
        ];
    }

    private function get_next_api_key()
    {
        if (empty($this->gemini_api_keys)) {
            return '';
        }

        $key = $this->gemini_api_keys[$this->current_key_index];
        $this->current_key_index = ($this->current_key_index + 1) % count($this->gemini_api_keys);

        return $key;
    }

    private function get_gemini_cache($key)
    {
        $cache_dir = APPPATH . 'cache/gemini/';
        $cache_file = $cache_dir . md5($key) . '.json';

        if (!file_exists($cache_file)) {
            return null;
        }

        if ((time() - filemtime($cache_file)) >= 1800) {
            @unlink($cache_file);
            return null;
        }

        $data = json_decode(file_get_contents($cache_file), true);
        $cached = $data['data'] ?? null;

        if (!$this->is_valid_ai_answer($cached)) {
            @unlink($cache_file);
            log_message('error', '[TOOPAI GEMINI CACHE] Invalid cache deleted: ' . $cache_file);
            return null;
        }

        return $cached;
    }

    private function set_gemini_cache($key, $data, $ttl = 1800)
    {
        if (!$this->is_valid_ai_answer($data)) {
            return;
        }

        $cache_dir = APPPATH . 'cache/gemini/';
        if (!is_dir($cache_dir)) {
            @mkdir($cache_dir, 0777, true);
        }

        $cache_file = $cache_dir . md5($key) . '.json';

        file_put_contents($cache_file, json_encode([
            'data' => $data,
            'expires' => time() + $ttl
        ], JSON_UNESCAPED_UNICODE));
    }

    private function is_valid_ai_answer($text)
    {
        if (empty($text) || !is_string($text)) {
            return false;
        }

        $clean = trim($text);

        if (strlen($clean) < 80) {
            return false;
        }

        $bad_endings = [
            ': K',
            ': k',
            ' dari',
            ' dan',
            ' dengan',
            ' untuk',
            ' pada',
            ' oleh',
            ' yang',
            ' atau',
            ' serta',
            ' karena',
            ' sebagai',
            ' dalam'
        ];

        foreach ($bad_endings as $ending) {
            if (substr($clean, -strlen($ending)) === $ending) {
                return false;
            }
        }

        return true;
    }

    private function compact_ai_context($data)
    {
        if (empty($data)) {
            return [];
        }

        if (is_object($data)) {
            $data = (array) $data;
        }

        if (!is_array($data)) {
            return $data;
        }

        $compact = [];

        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $value = (array) $value;
            }

            if (is_array($value)) {
                $items = array_slice($value, 0, 5);
                $compact[$key] = [];

                foreach ($items as $item) {
                    if (is_object($item)) {
                        $item = (array) $item;
                    }

                    if (is_array($item)) {
                        $compact_item = [];
                        foreach ($item as $item_key => $item_value) {
                            if (is_scalar($item_value) || is_null($item_value)) {
                                $compact_item[$item_key] = $item_value;
                            }
                        }
                        $compact[$key][] = $compact_item;
                    } else {
                        $compact[$key][] = $item;
                    }
                }
            } else {
                $compact[$key] = $value;
            }
        }

        return $compact;
    }

    private function get_chart_data($type, $period)
    {
        $days = $period === '30days' ? 30 : 7;
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        $today = date('Y-m-d');

        switch ($type) {
            case 'gmv_daily':
                $data = $this->db->select('order_date_local as label, COALESCE(SUM(gmv),0) as gmv, COUNT(DISTINCT order_id) as orders')
                    ->from('affiliate_orders')
                    ->where('order_date_local >=', $start_date)
                    ->where('order_date_local <=', $today)
                    ->group_start()
                        ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                        ->or_where('order_status IS NULL')
                    ->group_end()
                    ->group_by('order_date_local')
                    ->order_by('order_date_local', 'ASC')
                    ->get()
                    ->result_array();

                return [
                    'type' => 'line',
                    'title' => 'GMV Harian (' . $days . ' Hari)',
                    'labels' => array_column($data, 'label'),
                    'datasets' => [
                        [
                            'label' => 'GMV (Rp)',
                            'data' => array_column($data, 'gmv'),
                            'borderColor' => '#8b39ff',
                            'backgroundColor' => 'rgba(139,57,255,0.1)',
                            'tension' => 0.4
                        ],
                        [
                            'label' => 'Orders',
                            'data' => array_column($data, 'orders'),
                            'borderColor' => '#4ade80',
                            'backgroundColor' => 'rgba(74,222,128,0.1)',
                            'tension' => 0.4,
                            'yAxisID' => 'orders'
                        ]
                    ]
                ];

            case 'creator_performance':
                $data = $this->db->select('creator_username as label, COALESCE(SUM(gmv),0) as gmv, COUNT(DISTINCT order_id) as orders')
                    ->from('affiliate_orders')
                    ->where('order_date_local >=', $start_date)
                    ->where('creator_username IS NOT NULL')
                    ->where("creator_username != ''")
                    ->group_start()
                        ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                        ->or_where('order_status IS NULL')
                    ->group_end()
                    ->group_by('creator_username')
                    ->order_by('gmv', 'DESC')
                    ->limit(10)
                    ->get()
                    ->result_array();

                return [
                    'type' => 'bar',
                    'title' => 'Top Creator Performance',
                    'labels' => array_map(function ($d) {
                        return '@' . $d['label'];
                    }, $data),
                    'datasets' => [
                        [
                            'label' => 'GMV (Rp)',
                            'data' => array_column($data, 'gmv'),
                            'backgroundColor' => ['#8b39ff', '#7f30ee', '#6d28d9', '#5a20c0', '#4a18a8', '#3b1090', '#2d0878', '#1f0460', '#120248', '#080030']
                        ]
                    ]
                ];

            case 'campaign_comparison':
                $data = $this->db->select('c.campaign_name as label, COALESCE(SUM(o.gmv),0) as gmv, COUNT(DISTINCT o.order_id) as orders')
                    ->from('affiliate_campaigns c')
                    ->join('affiliate_orders o', 'c.campaign_id = o.campaign_id', 'left')
                    ->where('c.status', 'ONGOING')
                    ->group_by('c.campaign_id')
                    ->order_by('gmv', 'DESC')
                    ->get()
                    ->result_array();

                return [
                    'type' => 'doughnut',
                    'title' => 'GMV per Campaign',
                    'labels' => array_column($data, 'label'),
                    'datasets' => [
                        [
                            'data' => array_column($data, 'gmv'),
                            'backgroundColor' => ['#8b39ff', '#4ade80', '#fbbf24', '#ef4444', '#3b82f6', '#ec4899']
                        ]
                    ]
                ];
        }

        return null;
    }

    private function is_safe_select_sql($sql)
    {
        $normalized = strtolower(trim($sql));

        if (!preg_match('/^select\s+/i', $normalized)) {
            return false;
        }

        $blocked = [
            'insert',
            'update',
            'delete',
            'truncate',
            'drop',
            'alter',
            'create',
            'replace',
            'rename',
            'grant',
            'revoke',
            'lock',
            'unlock',
            'call',
            'exec',
            'execute',
            'merge'
        ];

        foreach ($blocked as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $normalized)) {
                return false;
            }
        }

        if (strpos($normalized, ';') !== false) {
            return false;
        }

        return true;
    }

    private function safe_read_query($sql, $bindings = [])
    {
        if (!$this->is_safe_select_sql($sql)) {
            log_message('error', '[Toopai Voice] Blocked unsafe SQL: ' . $sql);
            return false;
        }

        return $this->db->query($sql, $bindings);
    }
}