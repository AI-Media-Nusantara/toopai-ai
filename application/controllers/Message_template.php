<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Message_template extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        $this->load->database();
        $this->load->model('User_log_model');
    }

    /**
     * Admin page for message templates
     * URL: /message_template/admin
     */
    public function admin() {
        $user_id = $this->session->userdata('user_id');
        
          $this->load->view('templates/new/header');
         $this->load->view('admin/message_templates');
        $this->load->view('templates/new/footer');
        
       
    }
/**
 * Remove banner from template
 * URL: /message_template/remove_banner
 */
public function remove_banner() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    
    if ($user_id != 1) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Access denied'
        ]));
    }
    
    $id = $this->input->post('id');
    
    if (empty($id)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Template ID required'
        ]));
    }
    
    $template = $this->db->select('banner_file')->where('id', $id)->get('message_templates')->row();
    
    if ($template && !empty($template->banner_file)) {
        $old_path = '/home/holasync/toopai.ai/' . $template->banner_file;
        if (file_exists($old_path)) {
            unlink($old_path);
        }
    }
    
    $this->db->where('id', $id)->update('message_templates', [
        'banner_file' => null,
        'banner_image' => null
    ]);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Banner removed successfully'
    ]));
}
    /**
     * Get message template by type and task
     * URL: /message_template/get?type=bd&task=1&stage=hunting
     */
    public function get() {
        $this->output->set_content_type('application/json');
        
        $type = $this->input->get('type');
        $task = $this->input->get('task');
        $stage = $this->input->get('stage');
        $id = $this->input->get('id');
        
        if (!empty($id)) {
            // Get by ID
            $template = $this->db->where('id', $id)->get('message_templates')->row();
        } else {
            // Get by type and task
            if (empty($type) || empty($task)) {
                return $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'Type and Task required'
                ]));
            }
            
            $this->db->where('type', $type);
            $this->db->where('task', $task);
            $this->db->where('is_active', 1);
            
            if (!empty($stage)) {
                $this->db->where('stage', $stage);
            }
            
            $template = $this->db->order_by('id', 'DESC')->limit(1)->get('message_templates')->row();
        }
        
        if ($template) {
            return $this->output->set_output(json_encode([
                'success' => true,
                'data' => $template
            ]));
        }
        
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Template not found'
        ]));
    }
    
    /**
     * Get all templates (for admin)
     * URL: /message_template/get_all?type=bd
     */
    public function get_all() {
        $this->output->set_content_type('application/json');
        
        $type = $this->input->get('type');
        $user_id = $this->session->userdata('user_id');
        
        // Hanya supervisor (user_id=1) yang bisa lihat semua template
        if ($user_id != 1) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Access denied'
            ]));
        }
        
        $this->db->order_by('type', 'ASC');
        $this->db->order_by('task', 'ASC');
        
        if (!empty($type)) {
            $this->db->where('type', $type);
        }
        
        $templates = $this->db->get('message_templates')->result();
        
        // Format response
        foreach ($templates as $t) {
            $t->banner_url = !empty($t->banner_file) ? base_url($t->banner_file) : ($t->banner_image ?? null);
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'data' => $templates,
            'total' => count($templates)
        ]));
    }
    
/**
 * Save/Update message template with file upload
 */
public function save() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    
    if ($user_id != 1) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Access denied. Only supervisor can edit templates.'
        ]));
    }
    
    $id = $this->input->post('id');
    $type = $this->input->post('type');
    $task = $this->input->post('task');
    $stage = $this->input->post('stage');
    $title = $this->input->post('title');
    $message_text = $this->input->post('message_text');
    $banner_title = $this->input->post('banner_title');
    $banner_description = $this->input->post('banner_description');
    
    if (empty($type) || empty($task) || empty($title) || empty($message_text)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Type, Task, Title, and Message Text required'
        ]));
    }
    
    // 🔥 UPLOAD GAMBAR DENGAN METHOD MANUAL (YANG SUDAH BERHASIL)
    $upload_path = '/home/holasync/toopai.ai/uploads/message_banners/';
    $banner_file = null;
    
    if (!empty($_FILES['banner_file']['name'])) {
        $file = $_FILES['banner_file'];
        
        // Validasi format
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Format file tidak didukung. Gunakan: JPG, JPEG, PNG, GIF, WEBP'
            ]));
        }
        
        // Validasi ukuran (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Ukuran file terlalu besar. Maksimal 5MB.'
            ]));
        }
        
        // Buat nama file unik
        $new_name = uniqid() . '.' . $ext;
        $target = $upload_path . $new_name;
        
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $banner_file = 'uploads/message_banners/' . $new_name;
            log_message('debug', 'File uploaded: ' . $banner_file);
        } else {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Gagal mengupload file. Silakan coba lagi.'
            ]));
        }
    }
    
    $data = [
        'type' => $type,
        'task' => $task,
        'stage' => $stage,
        'title' => $title,
        'message_text' => $message_text,
        'banner_title' => $banner_title,
        'banner_description' => $banner_description,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($banner_file) {
        $data['banner_file'] = $banner_file;
        $data['banner_image'] = $banner_file;
    }
    
    if (!empty($id)) {
        // Get old banner to delete if exists
        $old = $this->db->select('banner_file')->where('id', $id)->get('message_templates')->row();
        if ($old && !empty($old->banner_file) && $banner_file) {
            $old_path = '/home/holasync/toopai.ai/' . $old->banner_file;
            if (file_exists($old_path)) {
                unlink($old_path);
                log_message('debug', 'Old banner deleted: ' . $old_path);
            }
        }
        
        $this->db->where('id', $id)->update('message_templates', $data);
        $message = 'Template updated successfully';
    } else {
        $data['created_by'] = $user_id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('message_templates', $data);
        $message = 'Template created successfully';
    }
    
    $this->load->model('User_log_model');
    $this->User_log_model->log(
        $user_id,
        $this->session->userdata('username'),
        'ADMIN',
        'EDIT_MESSAGE_TEMPLATE',
        "Updated message template for {$type} Task {$task}"
    );
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => $message
    ]));
}

private function resize_image($source_path, $target_path, $max_width = 500, $max_height = 500) {
    $this->load->library('image_lib');
    
    $config['image_library'] = 'gd2';
    $config['source_image'] = $source_path;
    $config['new_image'] = $target_path;
    $config['maintain_ratio'] = true;
    $config['width'] = $max_width;
    $config['height'] = $max_height;
    $config['quality'] = '80%';
    
    $this->image_lib->initialize($config);
    
    if (!$this->image_lib->resize()) {
        log_message('error', 'Image resize error: ' . $this->image_lib->display_errors());
        return false;
    }
    
    $this->image_lib->clear();
    return true;
}
    
    /**
     * Delete message template
     * URL: /message_template/delete
     */
    public function delete() {
        $this->output->set_content_type('application/json');
        
        $user_id = $this->session->userdata('user_id');
        
        if ($user_id != 1) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Access denied'
            ]));
        }
        
        $id = $this->input->post('id');
        
        if (empty($id)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Template ID required'
            ]));
        }
        
        // Get banner file to delete
        $template = $this->db->select('banner_file')->where('id', $id)->get('message_templates')->row();
        if ($template && !empty($template->banner_file) && file_exists($template->banner_file)) {
            unlink($template->banner_file);
        }
        
        $this->db->where('id', $id)->delete('message_templates');
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Template deleted successfully'
        ]));
    }
    
    /**
     * Render message with dynamic variables
     * URL: /message_template/render
     */
    public function render() {
        $this->output->set_content_type('application/json');
        
        $template_id = $this->input->post('template_id');
        $brand_name = $this->input->post('brand_name');
        $commission = $this->input->post('commission');
        $creator_name = $this->input->post('creator_name');
        
        if (empty($template_id)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Template ID required'
            ]));
        }
        
        $template = $this->db->where('id', $template_id)->get('message_templates')->row();
        
        if (!$template) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Template not found'
            ]));
        }
        
        $message = $template->message_text;
        
        // Replace dynamic variables
        $replacements = [
            '{brand_name}' => $brand_name ?? '',
            '{commission}' => $commission ?? '',
            '{creator_name}' => $creator_name ?? ''
        ];
        
        foreach ($replacements as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => $message,
            'banner' => [
                'image' => !empty($template->banner_file) ? base_url($template->banner_file) : ($template->banner_image ?? null),
                'title' => $template->banner_title,
                'description' => $template->banner_description
            ]
        ]));
    }
    
    
    public function simple_upload() {
    echo '<form method="post" enctype="multipart/form-data" action="' . base_url('message_template/simple_upload_process') . '">
        <input type="file" name="userfile">
        <button type="submit">Upload</button>
    </form>';
}

public function simple_upload_process() {
    $upload_path = '/home/holasync/toopai.ai/uploads/message_banners/';
    
    // Cek folder
    echo "Path: " . $upload_path . "<br>";
    echo "Is dir: " . (is_dir($upload_path) ? 'Yes' : 'No') . "<br>";
    echo "Is writable: " . (is_writable($upload_path) ? 'Yes' : 'No') . "<br>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['userfile'])) {
        $file = $_FILES['userfile'];
        
        // Validasi file
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            die("Format tidak didukung. Gunakan: " . implode(', ', $allowed));
        }
        
        // Buat nama file unik
        $new_name = uniqid() . '.' . $ext;
        $target = $upload_path . $new_name;
        
        // Pindahkan file
        if (move_uploaded_file($file['tmp_name'], $target)) {
            echo "✅ Upload BERHASIL!<br>";
            echo "File: " . $new_name . "<br>";
            echo "Path: " . $target . "<br>";
            echo "URL: " . base_url('uploads/message_banners/' . $new_name);
        } else {
            echo "❌ Gagal memindahkan file.<br>";
            echo "Error: " . error_get_last()['message'];
        }
    } else {
        echo '
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="userfile">
            <button type="submit">Upload</button>
        </form>
        ';
    }
}



}