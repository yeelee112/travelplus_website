<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateTourCollections extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('tour_collections')) {
            $this->forge->addField([
                'id' => ['type'=>'INT','auto_increment'=>true],
                'slug' => ['type'=>'VARCHAR','constraint'=>100],
                'name_vi' => ['type'=>'VARCHAR','constraint'=>150],
                'name_en' => ['type'=>'VARCHAR','constraint'=>150,'default'=>''],
                'is_active' => ['type'=>'TINYINT','constraint'=>1,'default'=>1],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('slug');
            $this->forge->createTable('tour_collections');
        }
        if (!$this->db->tableExists('tour_collection_tours')) {
            $this->forge->addField(['collection_id'=>['type'=>'INT'], 'tour_id'=>['type'=>'INT']]);
            $this->forge->addKey(['collection_id','tour_id'], true);
            $this->forge->addKey('tour_id');
            $this->forge->createTable('tour_collection_tours');
        }
        $autumn = $this->db->table('tour_collections')->where('slug','mua-thu')->get()->getRowArray();
        if (!$autumn) {
            $this->db->table('tour_collections')->insert(['slug'=>'mua-thu','name_vi'=>'Mùa thu','name_en'=>'Autumn','is_active'=>1]);
            $autumn = ['id'=>$this->db->insertID()];
        }
        if ($this->db->fieldExists('is_autumn','tours')) {
            foreach ($this->db->table('tours')->select('id')->where('is_autumn',1)->get()->getResultArray() as $tour) {
                $row = ['collection_id'=>(int)$autumn['id'], 'tour_id'=>(int)$tour['id']];
                if (!$this->db->table('tour_collection_tours')->where($row)->countAllResults()) $this->db->table('tour_collection_tours')->insert($row);
            }
            $this->forge->dropColumn('tours','is_autumn');
        }
        $this->db->resetDataCache();
    }
    public function down()
    {
        if (!$this->db->fieldExists('is_autumn','tours')) $this->forge->addColumn('tours',['is_autumn'=>['type'=>'TINYINT','constraint'=>1,'default'=>0]]);
        $autumn = $this->db->table('tour_collections')->where('slug','mua-thu')->get()->getRowArray();
        if ($autumn) foreach ($this->db->table('tour_collection_tours')->where('collection_id',$autumn['id'])->get()->getResultArray() as $row) $this->db->table('tours')->where('id',$row['tour_id'])->update(['is_autumn'=>1]);
        $this->forge->dropTable('tour_collection_tours',true);
        $this->forge->dropTable('tour_collections',true);
        $this->db->resetDataCache();
    }
}
