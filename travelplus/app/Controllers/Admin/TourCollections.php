<?php
namespace App\Controllers\Admin;
use App\Services\TourCollectionService;
class TourCollections extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) return $redirect;
        $service = new TourCollectionService();
        $ready = $service->isReady();
        return view('admin/tour-collections/index', ['collections'=>$ready ? $service->all() : [], 'collectionsReady'=>$ready]);
    }
    public function save(?int $id = null)
    {
        if ($redirect = $this->requireAdmin()) return $redirect;
        if (!(new TourCollectionService())->isReady()) {
            return redirect()->to(site_url('admin/tour-collections'))->withInput()->with('error', TourCollectionService::SETUP_MESSAGE);
        }
        $db = db_connect();
        $existing = $id === null ? null : $db->table('tour_collections')->where('id',$id)->get()->getRowArray();
        if ($id !== null && !$existing) return redirect()->to(site_url('admin/tour-collections'))->with('error','Không tìm thấy bộ sưu tập.');
        if (!$this->validate(['name_vi'=>'required|max_length[150]', 'name_en'=>'permit_empty|max_length[150]'])) {
            return redirect()->back()->withInput()->with('error',implode(' ', $this->validator->getErrors()));
        }
        helper(['text','url']);
        $name = trim((string)$this->request->getPost('name_vi'));
        if ($name === '') return redirect()->back()->withInput()->with('error','Vui lòng nhập tên bộ sưu tập.');
        $slug = $existing['slug'] ?? url_title(convert_accented_characters($name), '-', true);
        $slug = substr($slug, 0, 90);
        if ($slug === '') return redirect()->back()->withInput()->with('error','Tên bộ sưu tập cần có chữ hoặc số.');
        if (!$existing) {
            $base = $slug; $suffix = 2;
            while ($db->table('tour_collections')->where('slug',$slug)->countAllResults()) $slug = $base . '-' . $suffix++;
        }
        $data = ['name_vi'=>$name,'name_en'=>trim((string)$this->request->getPost('name_en')), 'is_active'=>(string)$this->request->getPost('is_active') === '1' ? 1 : 0];
        try {
            if ($existing) $ok = $db->table('tour_collections')->where('id',$id)->update($data);
            else $ok = $db->table('tour_collections')->insert($data + ['slug'=>$slug]);
        } catch (\Throwable $e) { $ok = false; }
        if (!$ok) return redirect()->back()->withInput()->with('error','Chưa lưu được bộ sưu tập. Vui lòng thử lại.');
        return redirect()->to(site_url('admin/tour-collections'))->with('success','Đã lưu bộ sưu tập.');
    }
}
