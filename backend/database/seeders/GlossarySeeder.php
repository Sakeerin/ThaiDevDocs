<?php

namespace Database\Seeders;

use App\Models\Glossary;
use Illuminate\Database\Seeder;

class GlossarySeeder extends Seeder
{
    public function run(): void
    {
        $terms = [
            ['term' => 'ตัวแปร', 'term_en' => 'Variable', 'definition' => 'ที่เก็บข้อมูลในโปรแกรมที่สามารถเปลี่ยนค่าได้'],
            ['term' => 'ฟังก์ชัน', 'term_en' => 'Function', 'definition' => 'กลุ่มคำสั่งที่ทำงานเฉพาะอย่างและเรียกใช้ซ้ำได้'],
            ['term' => 'อาร์เรย์', 'term_en' => 'Array', 'definition' => 'โครงสร้างข้อมูลแบบรายการที่เก็บค่าหลายค่า'],
            ['term' => 'ลูป', 'term_en' => 'Loop', 'definition' => 'การทำซ้ำชุดคำสั่งตามเงื่อนไข'],
            ['term' => 'เงื่อนไข', 'term_en' => 'Condition', 'definition' => 'การตรวจสอบเพื่อตัดสินใจว่าจะรันโค้ดส่วนใด'],
            ['term' => 'คลาส', 'term_en' => 'Class', 'definition' => 'แม่แบบสำหรับสร้างออบเจกต์ในการเขียนโปรแกรมเชิงวัตถุ'],
            ['term' => 'อินเทอร์เฟซ', 'term_en' => 'Interface', 'definition' => 'ข้อกำหนดของเมธอดที่คลาสต้อง implement'],
            ['term' => 'การสืบทอด', 'term_en' => 'Inheritance', 'definition' => 'การรับคุณสมบัติและพฤติกรรมจากคลาสแม่'],
            ['term' => 'DOM', 'term_en' => 'Document Object Model', 'definition' => 'โมเดลโครงสร้างเอกสาร HTML ที่ JavaScript ใช้จัดการหน้าเว็บ'],
            ['term' => 'API', 'term_en' => 'Application Programming Interface', 'definition' => 'ชุดกฎและเครื่องมือสำหรับให้โปรแกรมสื่อสารกัน'],
            ['term' => 'แอททริบิวต์', 'term_en' => 'Attribute', 'definition' => 'คุณสมบัติเพิ่มเติมของ HTML element เช่น id, class'],
            ['term' => 'เซเลกเตอร์', 'term_en' => 'Selector', 'definition' => 'รูปแบบที่ใช้เลือก element ใน CSS'],
        ];

        foreach ($terms as $term) {
            Glossary::firstOrCreate(
                ['term' => $term['term']],
                [
                    'term_en' => $term['term_en'],
                    'definition' => $term['definition'],
                    'definition_short' => mb_substr($term['definition'], 0, 120),
                    'is_approved' => true,
                ]
            );
        }
    }
}
