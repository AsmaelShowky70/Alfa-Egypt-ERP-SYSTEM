<?php

namespace Database\Seeders;

use App\Models\BillOfMaterial;
use App\Models\Customer;
use App\Models\DailyInventoryCheck;
use App\Models\DailyInventoryCheckItem;
use App\Models\Factory;
use App\Models\FinancialTransaction;
use App\Models\HourlyMachineReport;
use App\Models\Machine;
use App\Models\MaterialRequisition;
use App\Models\MaterialRequisitionItem;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AlfaErpSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users (المديرون وفريق العمل بالصور والوظائف)
        $users = [
            [
                'name' => 'د. عزت الفيل',
                'email' => 'ezzat@alphaegypt.com',
                'password' => Hash::make('secret123'),
                'role' => 'owner',
                'job_title' => 'صاحب الشركة والمالك',
                'department' => 'مجلس الإدارة العليا',
                'phone' => '01001112233',
                'avatar' => null,
            ],
            [
                'name' => 'م. بولا الفيل',
                'email' => 'paula@alphaegypt.com',
                'password' => Hash::make('secret123'),
                'role' => 'gm',
                'job_title' => 'المدير العام',
                'department' => 'الإدارة العامة والتنفيذية',
                'phone' => '01002223344',
                'avatar' => null,
            ],
            [
                'name' => 'أبانوب مجدي',
                'email' => 'abanoub@alphaegypt.com',
                'password' => Hash::make('secret123'),
                'role' => 'sales',
                'job_title' => 'مدير المبيعات',
                'department' => 'المبيعات وخدمة العملاء',
                'phone' => '01223344551',
                'avatar' => 'uploads/users/abanoub.jpeg',
            ],
            [
                'name' => 'جرجس أنور',
                'email' => 'gerges@alphaegypt.com',
                'password' => Hash::make('secret123'),
                'role' => 'operations',
                'job_title' => 'مدير التشغيل والإنتاج',
                'department' => 'إدارة التشغيل والمصانع',
                'phone' => '01223344552',
                'avatar' => 'uploads/users/gerges.jpeg',
            ],
            [
                'name' => 'جورج وليم',
                'email' => 'george@alphaegypt.com',
                'password' => Hash::make('secret123'),
                'role' => 'warehouse',
                'job_title' => 'مدير المخازن',
                'department' => 'المخازن المركزية واللوجستيات',
                'phone' => '01223344553',
                'avatar' => 'uploads/users/george.jpeg',
            ],
            [
                'name' => 'مؤمن عاطف',
                'email' => 'moemen@alphaegypt.com',
                'password' => Hash::make('secret123'),
                'role' => 'purchasing',
                'job_title' => 'مدير المشتريات',
                'department' => 'المشتريات وسلاسل الإمداد',
                'phone' => '01223344554',
                'avatar' => 'uploads/users/moemen.jpeg',
            ],
            [
                'name' => 'عادل صادق',
                'email' => 'adel@alphaegypt.com',
                'password' => Hash::make('secret123'),
                'role' => 'finance',
                'job_title' => 'مدير الحسابات والمالية',
                'department' => 'الإدارة المالية والتكاليف',
                'phone' => '01223344555',
                'avatar' => 'uploads/users/adel.jpeg',
            ],
            [
                'name' => 'فادي أشرف',
                'email' => 'fadi@alphaegypt.com',
                'password' => Hash::make('secret123'),
                'role' => 'maintenance',
                'job_title' => 'مدير الصيانة والمعدات',
                'department' => 'الصيانة الهندسية والمرافق',
                'phone' => '01223344556',
                'avatar' => 'uploads/users/fadi.jpeg',
            ],
        ];

        $createdUsers = [];
        foreach ($users as $u) {
            $createdUsers[$u['role']] = User::updateOrCreate(['email' => $u['email']], $u);
        }

        // 2. Customers (العملاء ومطاعم مصر الكبرى)
        $customersData = [
            [
                'name' => 'كشري أبو طارق',
                'code' => 'CUST-001',
                'contact_person' => 'الحاج طارق أبو طارق',
                'phone' => '01009988771',
                'email' => 'order@aboutarek.com',
                'address' => 'وسط البلد - شارع شامبليون، القاهرة',
                'tax_number' => '100-245-891',
                'commercial_register' => '54321',
                'payment_terms' => 'آجل 15 يوم',
                'logo' => 'https://alphaegypt1.com/wp-content/uploads/2026/10/website-14.jpg',
                'notes' => 'عميل استراتيجي - تعاقد توريد كوفير 4*1 أزرق وكوفير 2*1 وكوفير حلو شهرياً',
            ],
            [
                'name' => 'بافلو برجر (Buffalo Burger)',
                'code' => 'CUST-002',
                'contact_person' => 'أ. أحمد الشناوي',
                'phone' => '01122334400',
                'email' => 'purchasing@buffaloburger.com',
                'address' => 'التجمع الخامس، القاهرة الجديدة',
                'tax_number' => '321-456-987',
                'commercial_register' => '78912',
                'payment_terms' => 'آجل 30 يوم',
                'logo' => 'https://alphaegypt1.com/wp-content/uploads/2026/10/website-11.png',
                'notes' => 'توريد كوفير شوكة ومعلقة ومنديل ومغلف مخصص + أكياس ورقية',
            ],
            [
                'name' => 'ب.لبن (B.Laban)',
                'code' => 'CUST-003',
                'contact_person' => 'أ. عمر بدر',
                'phone' => '01288997766',
                'email' => 'supply@blaban.eg',
                'address' => 'سموحة، الإسكندرية والفروع بالقاهرة',
                'tax_number' => '554-889-112',
                'commercial_register' => '99881',
                'payment_terms' => 'نقدي عند الاستلام',
                'logo' => 'https://alphaegypt1.com/wp-content/uploads/2026/10/website-9.png',
                'notes' => 'معالق حلو فاخرة وكوفير حلو مخصص بالأكياس المطبوعة',
            ],
            [
                'name' => 'كرم الشام (Karam El Sham)',
                'code' => 'CUST-004',
                'contact_person' => 'م. فراس السوري',
                'phone' => '01066778899',
                'email' => 'karam@karamelsham.com',
                'address' => 'مدينة نصر، القاهرة',
                'tax_number' => '778-990-123',
                'commercial_register' => '33445',
                'payment_terms' => 'آجل 15 يوم',
                'logo' => 'https://alphaegypt1.com/wp-content/uploads/2026/10/website-23.png',
                'notes' => 'توريد كوفير ساندوتشات وجوانتيات نايلون لخدمة المطاعم',
            ],
            [
                'name' => 'بازوكا فرايد تشيكن (Bazooka)',
                'code' => 'CUST-005',
                'contact_person' => 'أ. هيثم رضوان',
                'phone' => '01155443322',
                'email' => 'orders@bazookafoods.com',
                'address' => 'المهندسين، الجيزة',
                'tax_number' => '665-223-441',
                'commercial_register' => '11223',
                'payment_terms' => 'آجل 30 يوم',
                'logo' => 'https://alphaegypt1.com/wp-content/uploads/2026/10/website-29.png',
                'notes' => 'جوانتيات طعام مخصصة وكوفير وجبات سريعة ومنديل وايبس برائحة الليمون',
            ],
            [
                'name' => 'بريموز بيتزا (Primo\'s Pizza)',
                'code' => 'CUST-006',
                'contact_person' => 'أ. سامح عبد الفتاح',
                'phone' => '01099881122',
                'email' => 'contact@primospizza.eg',
                'address' => 'المعادي، القاهرة',
                'tax_number' => '443-882-190',
                'commercial_register' => '44556',
                'payment_terms' => 'آجل 15 يوم',
                'logo' => 'https://alphaegypt1.com/wp-content/uploads/2026/10/website-15.png',
                'notes' => 'أطقم كوفير مثلثات بيتزا وشوك وسكاكين بلاستيك فاخرة',
            ],
        ];

        $customers = [];
        foreach ($customersData as $cd) {
            $customers[$cd['code']] = Customer::updateOrCreate(['code' => $cd['code']], $cd);
        }

        // 3. Suppliers (الموردون والتصنيع الخارجي)
        $suppliersData = [
            [
                'name' => 'الشركة المصرية الدولية للبوليمرات',
                'code' => 'SUP-001',
                'category' => 'خامات',
                'contact_person' => 'م. حازم ممدوح',
                'phone' => '01011223388',
                'email' => 'sales@egy-polymers.com',
                'address' => 'المنطقة الصناعية الثالثة، مدينة 6 أكتوبر',
                'average_lead_time_days' => 2,
                'rating' => 4.9,
                'notes' => 'مورد خام خرز البولي بروبيلين والبولي إيثيلين',
            ],
            [
                'name' => 'مصنع النيل للحقن الدقيق (تصنيع خارجي عند الغير)',
                'code' => 'SUP-002',
                'category' => 'تصنيع خارجي عند الغير',
                'contact_person' => 'م. عماد توفيق',
                'phone' => '01277665544',
                'email' => 'info@nile-injection.com',
                'address' => 'مدينة العاشر من رمضان، الشرقية',
                'average_lead_time_days' => 4,
                'rating' => 4.8,
                'notes' => 'نوفر له خامات البلاستيك ويقوم بحقن المعالق والشوك بسعر مصنعية محدد للقطعة',
            ],
            [
                'name' => 'مصنع الأهرام للرولات والتغليف المرن (تصنيع عند الغير)',
                'code' => 'SUP-003',
                'category' => 'تصنيع خارجي عند الغير',
                'contact_person' => 'أ. مصطفى الشامي',
                'phone' => '01144556677',
                'email' => 'orders@ahram-rolls.com',
                'address' => 'المنطقة الحرة، مدينة نصر',
                'average_lead_time_days' => 5,
                'rating' => 4.7,
                'notes' => 'طباعة وتجهيز رولات التغليف المطبوعة للعملاء (أبو طارق، بافلو)',
            ],
            [
                'name' => 'دلتا للورق والمستلزمات الطبية والمناديل',
                'code' => 'SUP-004',
                'category' => 'خامات',
                'contact_person' => 'أ. حسام فؤاد',
                'phone' => '01055667788',
                'email' => 'info@delta-paper.com',
                'address' => 'مدينة العبور، القليوبية',
                'average_lead_time_days' => 3,
                'rating' => 4.9,
                'notes' => 'توريد مناديل خام بيور طبقتين وخامات السيلولوز',
            ],
            [
                'name' => 'شركة الشرق للزيوت العطرية ومحاليل الوايبس',
                'code' => 'SUP-005',
                'category' => 'خامات',
                'contact_person' => 'د. هاني يوسف',
                'phone' => '01299887711',
                'email' => 'fragrance@sharq-aroma.com',
                'address' => 'بدر الصناعية، القاهرة',
                'average_lead_time_days' => 2,
                'rating' => 4.6,
                'notes' => 'محلول كحول وعطور المناديل المعطرة الآمنة للأغذية',
            ],
            [
                'name' => 'المركز الألماني لقطع غيار الماكينات والسيور',
                'code' => 'SUP-006',
                'category' => 'قطع غيار',
                'contact_person' => 'م. بيتر ولسن',
                'phone' => '01033221199',
                'email' => 'parts@german-tech-eg.com',
                'address' => 'شبرا الخيمة، القليوبية',
                'average_lead_time_days' => 1,
                'rating' => 5.0,
                'notes' => 'سيور ناقلة، سكينات قطع كوفير، سخانات لحام هيدروليكية',
            ],
        ];

        $suppliers = [];
        foreach ($suppliersData as $sd) {
            $suppliers[$sd['code']] = Supplier::updateOrCreate(['code' => $sd['code']], $sd);
        }

        // 4. Factories / Production Lines (المصانع المستقلة كمراكز أرباح وتكاليف)
        $factoriesData = [
            [
                'name' => 'مصنع الكوڤير (المنتج الأساسي النهائي)',
                'code' => 'FAC-COUVERT',
                'type' => 'internal',
                'supervisor_name' => 'جرجس أنور (مدير التشغيل)',
                'supervisor_id' => $createdUsers['operations']->id,
                'operating_cost_per_hour' => 350.00,
                'markup_percentage' => 14.00,
                'description' => 'المصنع الرئيسي لتجميع وتغليف أطقم الكوڤير الأوتوماتيكية (4*1، 2*1، 1*1، وحلو)',
            ],
            [
                'name' => 'مصنع وخط ماكينة قص الجوانتيات',
                'code' => 'FAC-GLOVE-CUT',
                'type' => 'internal',
                'supervisor_name' => 'م. مينا كميل',
                'supervisor_id' => $createdUsers['operations']->id,
                'operating_cost_per_hour' => 180.00,
                'markup_percentage' => 14.00,
                'description' => 'قص وتفريغ جوانتيات البلاستيك الشفافة متعددة الاستخدام عبر اسطوانات القص الدوارة',
            ],
            [
                'name' => 'مصنع وخط تغليف الجوانتيات',
                'code' => 'FAC-GLOVE-PACK',
                'type' => 'internal',
                'supervisor_name' => 'أ. بيشوي ثابت',
                'supervisor_id' => $createdUsers['operations']->id,
                'operating_cost_per_hour' => 140.00,
                'markup_percentage' => 14.00,
                'description' => 'تعبئة الجوانتيات المقصوصة في أكياس 100 قطعة وعلب كرتونية وطباعة التواريخ',
            ],
            [
                'name' => 'مصنع حقن البلاستيك (تصنيع عند الغير)',
                'code' => 'FAC-PLASTIC-INJ',
                'type' => 'outsourced',
                'outsource_supplier_id' => $suppliers['SUP-002']->id,
                'supervisor_name' => 'م. عماد توفيق (خارجي)',
                'operating_cost_per_hour' => 220.00,
                'markup_percentage' => 14.00,
                'description' => 'حقن المعالق والشوك والسكاكين بالاسطمبات المخصصة عند مصنع النيل وشرائها للمخزن بـ 14%',
            ],
            [
                'name' => 'مصنع رولات التغليف (تصنيع عند الغير)',
                'code' => 'FAC-ROLLS',
                'type' => 'outsourced',
                'outsource_supplier_id' => $suppliers['SUP-003']->id,
                'supervisor_name' => 'أ. مصطفى الشامي (خارجي)',
                'operating_cost_per_hour' => 260.00,
                'markup_percentage' => 14.00,
                'description' => 'طباعة وتقطيع رولات السيلوفان والبولي بروبيلين المطبوعة بهوية العملاء',
            ],
            [
                'name' => 'مصنع خط الوايبس والمنديل المعطر',
                'code' => 'FAC-WIPES',
                'type' => 'internal',
                'supervisor_name' => 'كيميائي / مايكل عزمي',
                'supervisor_id' => $createdUsers['operations']->id,
                'operating_cost_per_hour' => 200.00,
                'markup_percentage' => 14.00,
                'description' => 'خط إنتاج المناديل المعطرة والوايبس الأوتوماتيكي مع التعقيم والتعطير والغلاف الفضي',
            ],
        ];

        $factories = [];
        foreach ($factoriesData as $fd) {
            $factories[$fd['code']] = Factory::updateOrCreate(['code' => $fd['code']], $fd);
        }

        // 5. Machines (الماكينات)
        $machinesData = [
            [
                'factory_id' => $factories['FAC-COUVERT']->id,
                'name' => 'خط تغليف الكوڤير الآلي ألفا-1',
                'code' => 'MCH-CVR-01',
                'model' => 'AlphaPack FlowWrap 400X',
                'serial_number' => 'AP-2024-8891',
                'rated_speed_units_per_hour' => 3600.00, // 3600 كوفير في الساعة (60 ق/دقيقة)
                'belt_speed_m_min' => 60.00,
                'status' => 'running',
                'maintenance_due_date' => Carbon::now()->addDays(20),
            ],
            [
                'factory_id' => $factories['FAC-COUVERT']->id,
                'name' => 'خط تغليف الكوڤير الآلي ألفا-2',
                'code' => 'MCH-CVR-02',
                'model' => 'AlphaPack FlowWrap 500Pro',
                'serial_number' => 'AP-2025-1002',
                'rated_speed_units_per_hour' => 4200.00, // 70 ق/دقيقة
                'belt_speed_m_min' => 70.00,
                'status' => 'running',
                'maintenance_due_date' => Carbon::now()->addDays(35),
            ],
            [
                'factory_id' => $factories['FAC-COUVERT']->id,
                'name' => 'خط تغليف الكوڤير الآلي ألفا-3',
                'code' => 'MCH-CVR-03',
                'model' => 'AlphaPack FlowWrap 600Pro',
                'serial_number' => 'AP-2025-1103',
                'rated_speed_units_per_hour' => 4500.00, // 75 ق/دقيقة
                'belt_speed_m_min' => 75.00,
                'status' => 'running',
                'maintenance_due_date' => Carbon::now()->addDays(40),
            ],
            [
                'factory_id' => $factories['FAC-COUVERT']->id,
                'name' => 'خط تغليف الكوڤير الآلي ألفا-4',
                'code' => 'MCH-CVR-04',
                'model' => 'AlphaPack FlowWrap Ultra 700',
                'serial_number' => 'AP-2025-1104',
                'rated_speed_units_per_hour' => 4800.00, // 80 ق/دقيقة
                'belt_speed_m_min' => 80.00,
                'status' => 'running',
                'maintenance_due_date' => Carbon::now()->addDays(45),
            ],
            [
                'factory_id' => $factories['FAC-GLOVE-CUT']->id,
                'name' => 'ماكينة قص الجوانتيات الدوارة عالية السرعة',
                'code' => 'MCH-GLV-CUT01',
                'model' => 'Rotary Die-Cut Master 200',
                'serial_number' => 'RDC-2023-441',
                'rated_speed_units_per_hour' => 8000.00, // 8000 قطعة بالساعة
                'belt_speed_m_min' => 133.00,
                'status' => 'running',
                'maintenance_due_date' => Carbon::now()->addDays(14),
            ],
            [
                'factory_id' => $factories['FAC-GLOVE-PACK']->id,
                'name' => 'ماكينة باكت وتغليف الجوانتيات 100 ق',
                'code' => 'MCH-GLV-PCK01',
                'model' => 'BagSeal Auto-100',
                'serial_number' => 'BSA-2024-009',
                'rated_speed_units_per_hour' => 120.00, // 120 باكت بالساعة
                'belt_speed_m_min' => 20.00,
                'status' => 'running',
                'maintenance_due_date' => Carbon::now()->addDays(18),
            ],
            [
                'factory_id' => $factories['FAC-GLOVE-PACK']->id,
                'name' => 'ماكينة باكت وتغليف الجوانتيات الآلي 2',
                'code' => 'MCH-GLV-PCK02',
                'model' => 'BagSeal Auto-200 Heavy Duty',
                'serial_number' => 'BSA-2025-018',
                'rated_speed_units_per_hour' => 150.00, // 150 باكت بالساعة
                'belt_speed_m_min' => 25.00,
                'status' => 'running',
                'maintenance_due_date' => Carbon::now()->addDays(30),
            ],
            [
                'factory_id' => $factories['FAC-WIPES']->id,
                'name' => 'ماكينة المناديل المعطرة والوايبس الأوتوماتيكية',
                'code' => 'MCH-WIPES-01',
                'model' => 'SachetWipe Pure 300',
                'serial_number' => 'SWP-2024-551',
                'rated_speed_units_per_hour' => 5000.00, // 5000 منديل معطر بالساعة
                'belt_speed_m_min' => 83.00,
                'status' => 'running',
                'maintenance_due_date' => Carbon::now()->addDays(25),
            ],
        ];

        $machines = [];
        foreach ($machinesData as $md) {
            $machines[$md['code']] = Machine::updateOrCreate(['code' => $md['code']], $md);
        }

        // 6. Raw Materials & Spare Parts (الخامات بأسعارها الحقيقية الدقيقة من أرض الواقع)
        $rawMaterialsData = [
            [
                'code' => 'RM-SPOON-ENG',
                'name' => 'معلقة إنجليزي بلاستيك أبيض فاخر',
                'category' => 'raw_material',
                'unit' => 'قطعة',
                'unit_cost' => 0.9000, // 90 قرش
                'current_stock' => 145000.00,
                'reorder_level' => 30000.00,
                'warehouse_location' => 'عنبر A - رف 1',
                'supplier_id' => $suppliers['SUP-002']->id,
                'source_factory_id' => $factories['FAC-PLASTIC-INJ']->id,
            ],
            [
                'code' => 'RM-TISSUE-DRY',
                'name' => 'منديل جاف طبقتين بيور (Pure 2-Ply)',
                'category' => 'raw_material',
                'unit' => 'قطعة',
                'unit_cost' => 0.2400, // 24 قرش
                'current_stock' => 95000.00,
                'reorder_level' => 25000.00,
                'warehouse_location' => 'عنبر B - رصة 3',
                'supplier_id' => $suppliers['SUP-004']->id,
                'source_factory_id' => null,
            ],
            [
                'code' => 'RM-TOOTHPICK',
                'name' => 'خلة صيني مغلفة ورقياً ومعقمة',
                'category' => 'raw_material',
                'unit' => 'قطعة',
                'unit_cost' => 0.0600, // 6 قروش
                'current_stock' => 220000.00,
                'reorder_level' => 50000.00,
                'warehouse_location' => 'عنبر A - رف 4',
                'supplier_id' => $suppliers['SUP-004']->id,
                'source_factory_id' => null,
            ],
            [
                'code' => 'RM-WIPE-SCENT',
                'name' => 'منديل معطر وايبس منعش مغلف فضي',
                'category' => 'raw_material',
                'unit' => 'قطعة',
                'unit_cost' => 0.2800, // 28 قرش
                'current_stock' => 65000.00,
                'reorder_level' => 20000.00,
                'warehouse_location' => 'عنبر C - تبريد ورطوبة',
                'supplier_id' => null,
                'source_factory_id' => $factories['FAC-WIPES']->id, // ينتج في مصنع الوايبس ويدخل المخزن
            ],
            [
                'code' => 'RM-WRAP-PRINT-ABU',
                'name' => 'غلاف سيلوفان مطبوع - كشري أبو طارق (أزرق)',
                'category' => 'packaging',
                'unit' => 'قطعة',
                'unit_cost' => 0.1800, // 18 قرش
                'current_stock' => 80000.00,
                'reorder_level' => 20000.00,
                'warehouse_location' => 'عنبر D - استاند رولات',
                'supplier_id' => $suppliers['SUP-003']->id,
                'source_factory_id' => $factories['FAC-ROLLS']->id,
            ],
            [
                'code' => 'RM-WRAP-CLEAR',
                'name' => 'غلاف سيلوفان شفاف 25 ميكرون',
                'category' => 'packaging',
                'unit' => 'قطعة',
                'unit_cost' => 0.1800, // 18 قرش
                'current_stock' => 110000.00,
                'reorder_level' => 25000.00,
                'warehouse_location' => 'عنبر D - رول 2',
                'supplier_id' => $suppliers['SUP-003']->id,
                'source_factory_id' => $factories['FAC-ROLLS']->id,
            ],
            [
                'code' => 'RM-SPOON-DESSERT',
                'name' => 'معلقة حلو صغيرة بلاستيك كريستال',
                'category' => 'raw_material',
                'unit' => 'قطعة',
                'unit_cost' => 0.5000, // 50 قرش
                'current_stock' => 75000.00,
                'reorder_level' => 15000.00,
                'warehouse_location' => 'عنبر A - رف 2',
                'supplier_id' => $suppliers['SUP-002']->id,
                'source_factory_id' => $factories['FAC-PLASTIC-INJ']->id,
            ],
            [
                'code' => 'RM-FORK-ENG',
                'name' => 'شوكة إنجليزي بلاستيك أبيض',
                'category' => 'raw_material',
                'unit' => 'قطعة',
                'unit_cost' => 0.9500, // 95 قرش
                'current_stock' => 45000.00,
                'reorder_level' => 12000.00,
                'warehouse_location' => 'عنبر A - رف 3',
                'supplier_id' => $suppliers['SUP-002']->id,
                'source_factory_id' => $factories['FAC-PLASTIC-INJ']->id,
            ],
            [
                'code' => 'RM-PE-FILM-GLOVE',
                'name' => 'رول فيلم بولي إيثيلين شفاف للجوانتيات',
                'category' => 'raw_material',
                'unit' => 'كجم',
                'unit_cost' => 85.0000, // 85 جنيه للكيلو
                'current_stock' => 4200.00,
                'reorder_level' => 1000.00,
                'warehouse_location' => 'عنبر E - رولات الخام',
                'supplier_id' => $suppliers['SUP-001']->id,
                'source_factory_id' => null,
            ],
            [
                'code' => 'RM-BOPP-CLEAR-KG',
                'name' => 'رول فيلم سيلوفان بوب كوڤير شفاف 25 ميكرون (صرف بالوزن)',
                'category' => 'packaging',
                'unit' => 'كجم',
                'unit_cost' => 145.0000,
                'current_stock' => 1850.00,
                'reorder_level' => 300.00,
                'warehouse_location' => 'عنبر D - رولات السيلوفان',
                'supplier_id' => $suppliers['SUP-003']->id,
                'source_factory_id' => $factories['FAC-ROLLS']->id,
            ],
            [
                'code' => 'RM-BOPP-PRINT-KG',
                'name' => 'رول سيلوفان كوڤير مطبوع ألوان مخصص (صرف بالوزن)',
                'category' => 'packaging',
                'unit' => 'كجم',
                'unit_cost' => 175.0000,
                'current_stock' => 1250.00,
                'reorder_level' => 250.00,
                'warehouse_location' => 'عنبر D - رولات السيلوفان',
                'supplier_id' => $suppliers['SUP-003']->id,
                'source_factory_id' => $factories['FAC-ROLLS']->id,
            ],
            [
                'code' => 'SP-CONVEYOR-BELT',
                'name' => 'سير ناقل حراري مضاد للاحتكاك 250 مم',
                'category' => 'spare_part',
                'unit' => 'متر',
                'unit_cost' => 650.0000,
                'current_stock' => 35.00,
                'reorder_level' => 10.00,
                'warehouse_location' => 'دولاب قطع الغيار - رف 1',
                'supplier_id' => $suppliers['SUP-006']->id,
                'source_factory_id' => null,
            ],
            [
                'code' => 'SP-HEATER-SEAL',
                'name' => 'سخان لحام أوتوماتيكي ماكينة الفلو باك',
                'category' => 'spare_part',
                'unit' => 'قطعة',
                'unit_cost' => 1200.0000,
                'current_stock' => 12.00,
                'reorder_level' => 4.00,
                'warehouse_location' => 'دولاب قطع الغيار - رف 2',
                'supplier_id' => $suppliers['SUP-006']->id,
                'source_factory_id' => null,
            ],
            [
                'code' => 'SP-CUTTER-BLADE',
                'name' => 'سكينة تقطيع كوفير فولاذ ألماني HSS',
                'category' => 'spare_part',
                'unit' => 'قطعة',
                'unit_cost' => 1850.0000,
                'current_stock' => 8.00,
                'reorder_level' => 3.00,
                'warehouse_location' => 'دولاب قطع الغيار - رف 3',
                'supplier_id' => $suppliers['SUP-006']->id,
                'source_factory_id' => null,
            ],
        ];

        $rawMaterials = [];
        foreach ($rawMaterialsData as $rm) {
            $rawMaterials[$rm['code']] = RawMaterial::updateOrCreate(['code' => $rm['code']], $rm);
        }

        // 7. Products (المنتجات التامة والـ BOM بأسعار واقعية)
        $productsData = [
            [
                'code' => 'PROD-CVR-4IN1-ABU',
                'name' => 'كوڤير 4 في 1 أزرق - كشري أبو طارق',
                'category' => 'couvert',
                'type' => 'finished_good',
                'unit' => 'قطعة',
                'factory_id' => $factories['FAC-COUVERT']->id,
                'customer_id' => $customers['CUST-001']->id,
                'cost_price' => 1.6600, // 0.90 + 0.24 + 0.06 + 0.28 + 0.18
                'selling_price' => 2.1500,
                'min_selling_price' => 1.8500,
                'current_stock' => 28000.00,
                'reorder_level' => 10000.00,
                'image' => 'https://alphaegypt1.com/wp-content/uploads/2026/10/website-14.jpg',
                'description' => 'الكوڤير الأشهر في مصر لكشري أبو طارق: معلقة إنجليزي + منديل جاف بيور + خلة صيني مغلفة + منديل معطر وايبس + غلاف أزرق مطبوع.',
            ],
            [
                'code' => 'PROD-CVR-2IN1',
                'name' => 'كوڤير 2 في 1 (معلقة + منديل جاف)',
                'category' => 'couvert',
                'type' => 'finished_good',
                'unit' => 'قطعة',
                'factory_id' => $factories['FAC-COUVERT']->id,
                'customer_id' => $customers['CUST-001']->id,
                'cost_price' => 1.3200, // 0.90 + 0.24 + 0.18
                'selling_price' => 1.7500,
                'min_selling_price' => 1.5000,
                'current_stock' => 19500.00,
                'reorder_level' => 8000.00,
                'image' => 'https://alphaegypt1.com/wp-content/uploads/2026/10/website-11.png',
                'description' => 'طقم اقتصادي للمطاعم السريعة: معلقة إنجليزي 90 قرش + منديل جاف 24 قرش + غلاف شفاف 18 قرش.',
            ],
            [
                'code' => 'PROD-CVR-1IN1',
                'name' => 'كوڤير 1 في 1 (معلقة مفردة مغلفة)',
                'category' => 'couvert',
                'type' => 'finished_good',
                'unit' => 'قطعة',
                'factory_id' => $factories['FAC-COUVERT']->id,
                'customer_id' => null, // متاح للبيع للغير من المخزن
                'cost_price' => 1.0800, // 0.90 + 0.18
                'selling_price' => 1.4500,
                'min_selling_price' => 1.2500,
                'current_stock' => 34000.00,
                'reorder_level' => 10000.00,
                'image' => 'https://alphaegypt1.com/wp-content/uploads/2026/10/website-9.png',
                'description' => 'معلقة إنجليزي 90 قرش داخل كيس شفاف 18 قرش للبيع للمطاعم أو البيع للغير من المخزن.',
            ],
            [
                'code' => 'PROD-CVR-DESSERT',
                'name' => 'كوڤير حلو (معلقة حلو + غلاف شفاف)',
                'category' => 'couvert',
                'type' => 'finished_good',
                'unit' => 'قطعة',
                'factory_id' => $factories['FAC-COUVERT']->id,
                'customer_id' => $customers['CUST-003']->id, // بلبن ومحلات الحلويات
                'cost_price' => 0.6800, // 0.50 + 0.18
                'selling_price' => 1.0000,
                'min_selling_price' => 0.8500,
                'current_stock' => 45000.00,
                'reorder_level' => 12000.00,
                'image' => 'https://alphaegypt1.com/wp-content/uploads/2026/10/website-23.png',
                'description' => 'معلقة حلو 50 قرش + غلاف شفاف 18 قرش مخصص للأرز بلبن والحلويات والايس كريم.',
            ],
            [
                'code' => 'PROD-GLOVES-BOX-100',
                'name' => 'علبة جوانتي بلاستيك استخدام طعام (100 قطعة)',
                'category' => 'gloves',
                'type' => 'finished_good',
                'unit' => 'علبة',
                'factory_id' => $factories['FAC-GLOVE-PACK']->id,
                'customer_id' => null,
                'cost_price' => 18.5000,
                'selling_price' => 28.0000,
                'min_selling_price' => 24.0000,
                'current_stock' => 1200.00,
                'reorder_level' => 300.00,
                'image' => 'https://alphaegypt1.com/wp-content/uploads/2026/10/website-15.png',
                'description' => 'علبة كرتونية تحتوي 100 جوانتي بلاستيك شفاف سميك مخصص لعمال المطاعم والطهي.',
            ],
            [
                'code' => 'PROD-WIPES-PACK-100',
                'name' => 'باكت مناديل معطرة وايبس فاخر (100 قطعة)',
                'category' => 'wipes',
                'type' => 'finished_good',
                'unit' => 'باكت',
                'factory_id' => $factories['FAC-WIPES']->id,
                'customer_id' => null,
                'cost_price' => 32.0000,
                'selling_price' => 48.0000,
                'min_selling_price' => 42.0000,
                'current_stock' => 850.00,
                'reorder_level' => 200.00,
                'image' => 'https://alphaegypt1.com/wp-content/uploads/2026/10/website-26.png',
                'description' => 'باكت يحتوي 100 منديل معطر برائحة نسيم البحر والليمون مخصص للمطاعم والبيع للغير.',
            ],
        ];

        $products = [];
        foreach ($productsData as $pd) {
            $products[$pd['code']] = Product::updateOrCreate(['code' => $pd['code']], $pd);
        }

        // 8. Bill of Materials (BOM) Setup
        // Couvert 4*1 Abu Tarek
        $bomCouvert4in1 = [
            ['raw_material_id' => $rawMaterials['RM-SPOON-ENG']->id, 'qty' => 1, 'waste' => 0, 'cost' => 0.9000],
            ['raw_material_id' => $rawMaterials['RM-TISSUE-DRY']->id, 'qty' => 1, 'waste' => 0, 'cost' => 0.2400],
            ['raw_material_id' => $rawMaterials['RM-TOOTHPICK']->id, 'qty' => 1, 'waste' => 0, 'cost' => 0.0600],
            ['raw_material_id' => $rawMaterials['RM-WIPE-SCENT']->id, 'qty' => 1, 'waste' => 0, 'cost' => 0.2800],
            ['raw_material_id' => $rawMaterials['RM-WRAP-PRINT-ABU']->id, 'qty' => 1, 'waste' => 0, 'cost' => 0.1800],
        ];
        foreach ($bomCouvert4in1 as $b) {
            BillOfMaterial::updateOrCreate(
                ['product_id' => $products['PROD-CVR-4IN1-ABU']->id, 'raw_material_id' => $b['raw_material_id']],
                [
                    'quantity_per_unit' => $b['qty'],
                    'waste_percentage' => $b['waste'],
                    'unit_cost_snapshot' => $b['cost'],
                    'total_cost_per_unit' => $b['qty'] * $b['cost'],
                ]
            );
        }

        // Couvert 2*1
        $bomCouvert2in1 = [
            ['raw_material_id' => $rawMaterials['RM-SPOON-ENG']->id, 'qty' => 1, 'waste' => 0, 'cost' => 0.9000],
            ['raw_material_id' => $rawMaterials['RM-TISSUE-DRY']->id, 'qty' => 1, 'waste' => 0, 'cost' => 0.2400],
            ['raw_material_id' => $rawMaterials['RM-WRAP-CLEAR']->id, 'qty' => 1, 'waste' => 0, 'cost' => 0.1800],
        ];
        foreach ($bomCouvert2in1 as $b) {
            BillOfMaterial::updateOrCreate(
                ['product_id' => $products['PROD-CVR-2IN1']->id, 'raw_material_id' => $b['raw_material_id']],
                [
                    'quantity_per_unit' => $b['qty'],
                    'waste_percentage' => $b['waste'],
                    'unit_cost_snapshot' => $b['cost'],
                    'total_cost_per_unit' => $b['qty'] * $b['cost'],
                ]
            );
        }

        // Couvert 1*1
        $bomCouvert1in1 = [
            ['raw_material_id' => $rawMaterials['RM-SPOON-ENG']->id, 'qty' => 1, 'waste' => 0, 'cost' => 0.9000],
            ['raw_material_id' => $rawMaterials['RM-WRAP-CLEAR']->id, 'qty' => 1, 'waste' => 0, 'cost' => 0.1800],
        ];
        foreach ($bomCouvert1in1 as $b) {
            BillOfMaterial::updateOrCreate(
                ['product_id' => $products['PROD-CVR-1IN1']->id, 'raw_material_id' => $b['raw_material_id']],
                [
                    'quantity_per_unit' => $b['qty'],
                    'waste_percentage' => $b['waste'],
                    'unit_cost_snapshot' => $b['cost'],
                    'total_cost_per_unit' => $b['qty'] * $b['cost'],
                ]
            );
        }

        // Couvert Helw
        $bomCouvertHelw = [
            ['raw_material_id' => $rawMaterials['RM-SPOON-DESSERT']->id, 'qty' => 1, 'waste' => 0, 'cost' => 0.5000],
            ['raw_material_id' => $rawMaterials['RM-WRAP-CLEAR']->id, 'qty' => 1, 'waste' => 0, 'cost' => 0.1800],
        ];
        foreach ($bomCouvertHelw as $b) {
            BillOfMaterial::updateOrCreate(
                ['product_id' => $products['PROD-CVR-DESSERT']->id, 'raw_material_id' => $b['raw_material_id']],
                [
                    'quantity_per_unit' => $b['qty'],
                    'waste_percentage' => $b['waste'],
                    'unit_cost_snapshot' => $b['cost'],
                    'total_cost_per_unit' => $b['qty'] * $b['cost'],
                ]
            );
        }

        // 9. Realistic Sales Orders illustrating the ENTIRE collaborative business cycle
        // Order 1: Abou Tarek (50,000 units of Couvert 4*1) - In Operations Scheduled
        $order1 = SalesOrder::updateOrCreate(
            ['order_number' => 'SO-2026-001'],
            [
                'customer_id' => $customers['CUST-001']->id,
                'sales_rep_id' => $createdUsers['sales']->id,
                'status' => 'operations_scheduled',
                'order_date' => Carbon::now()->subDays(2),
                'target_delivery_date' => Carbon::now()->addDays(5),
                'confirmed_delivery_date' => Carbon::now()->addDays(4),
                'operations_lead_time_days' => 3,
                'procurement_lead_time_days' => 0,
                'subtotal' => 107500.00, // 50,000 * 2.15
                'tax' => 15050.00,
                'discount' => 0,
                'total_amount' => 122550.00,
                'total_estimated_cost' => 83000.00, // 50,000 * 1.66
                'profit_margin_percentage' => 22.79,
                'has_loss_alarm' => false,
                'payment_status' => 'unpaid',
                'client_notes' => 'الطلبية الدورية الأسبوعية لفروع وسط البلد وشامبليون، الالتزام باللون الأزرق المعتمد.',
                'internal_notes' => 'الخامات متوفرة بالكامل بالمخزن، حدد التشغيل مدة العمل 3 أيام عمل.',
            ]
        );
        SalesOrderItem::updateOrCreate(
            ['sales_order_id' => $order1->id, 'product_id' => $products['PROD-CVR-4IN1-ABU']->id],
            [
                'quantity' => 50000.00,
                'unit_price' => 2.1500,
                'unit_cost' => 1.6600,
                'subtotal' => 107500.00,
                'total_cost' => 83000.00,
                'has_loss_item' => false,
                'customization_notes' => 'لوجو أبو طارق أزرق طباعة وجهين',
            ]
        );

        // Order 2: Buffalo Burger - In Procurement Delayed (Demonstrating missing materials workflow)
        $order2 = SalesOrder::updateOrCreate(
            ['order_number' => 'SO-2026-002'],
            [
                'customer_id' => $customers['CUST-002']->id,
                'sales_rep_id' => $createdUsers['sales']->id,
                'status' => 'procurement_delayed',
                'order_date' => Carbon::now()->subDays(1),
                'target_delivery_date' => Carbon::now()->addDays(4),
                'confirmed_delivery_date' => Carbon::now()->addDays(7), // تأثر بمدة المشتريات
                'operations_lead_time_days' => 2,
                'procurement_lead_time_days' => 3, // المشتريات أبلغت أنها تحتاج 3 أيام لتوريد النواقص
                'subtotal' => 70000.00, // 40,000 * 1.75
                'tax' => 9800.00,
                'discount' => 0,
                'total_amount' => 79800.00,
                'total_estimated_cost' => 52800.00,
                'profit_margin_percentage' => 24.57,
                'has_loss_alarm' => false,
                'payment_status' => 'unpaid',
                'client_notes' => 'مطلوب استلام الدفعة لفروع القاهرة والجيزة في أقرب وقت.',
                'internal_notes' => 'رول السيلوفان الخاص أوشك على النفاد؛ تم إصدار طلب شراء للمشتريات وأفاد مؤمن عاطف بالوصول خلال 3 أيام.',
            ]
        );
        SalesOrderItem::updateOrCreate(
            ['sales_order_id' => $order2->id, 'product_id' => $products['PROD-CVR-2IN1']->id],
            [
                'quantity' => 40000.00,
                'unit_price' => 1.7500,
                'unit_cost' => 1.3200,
                'subtotal' => 70000.00,
                'total_cost' => 52800.00,
                'has_loss_item' => false,
                'customization_notes' => 'طباعة هوية بافلو برجر بالأسود والأصفر',
            ]
        );

        // Order 3: Special contract with fixed price below current cost (ALARM DEMONSTRATION!)
        $order3 = SalesOrder::updateOrCreate(
            ['order_number' => 'SO-2026-003'],
            [
                'customer_id' => $customers['CUST-004']->id, // كرم الشام
                'sales_rep_id' => $createdUsers['sales']->id,
                'status' => 'confirmed',
                'order_date' => Carbon::now()->subHours(10),
                'target_delivery_date' => Carbon::now()->addDays(6),
                'confirmed_delivery_date' => Carbon::now()->addDays(6),
                'operations_lead_time_days' => 2,
                'procurement_lead_time_days' => 0,
                'subtotal' => 45000.00, // 30,000 * 1.50 (التكلفة 1.66) -> خسارة!
                'tax' => 6300.00,
                'discount' => 0,
                'total_amount' => 51300.00,
                'total_estimated_cost' => 49800.00, // 30,000 * 1.66 = 49,800 > 45,000!
                'profit_margin_percentage' => -10.67,
                'has_loss_alarm' => true, // إنذار خسارة مفعّل!
                'payment_status' => 'unpaid',
                'client_notes' => 'عقد سنوي قديم بسعر ثابت 1.50 جنيه للكوڤير الرباعي.',
                'internal_notes' => '⚠️ تنبيه للإدارة والحسابات: السعر التعاقدي أقل من تكلفة الخامات الحالية (خسارة 16 قرش في القطعة). تم تثبيت السعر بناء على تعليمات المدير العام مع إطلاق إنذار لإعادة التفاوض مستقبلاً.',
            ]
        );
        SalesOrderItem::updateOrCreate(
            ['sales_order_id' => $order3->id, 'product_id' => $products['PROD-CVR-4IN1-ABU']->id],
            [
                'quantity' => 30000.00,
                'unit_price' => 1.5000,
                'unit_cost' => 1.6600,
                'subtotal' => 45000.00,
                'total_cost' => 49800.00,
                'has_loss_item' => true,
                'customization_notes' => 'سعر تعاقدي قديم ثابت',
            ]
        );

        // Order 4: B.Laban Dessert Couvert - Completed and delivered
        $order4 = SalesOrder::updateOrCreate(
            ['order_number' => 'SO-2026-004'],
            [
                'customer_id' => $customers['CUST-003']->id,
                'sales_rep_id' => $createdUsers['sales']->id,
                'status' => 'completed',
                'order_date' => Carbon::now()->subDays(5),
                'target_delivery_date' => Carbon::now()->subDays(1),
                'confirmed_delivery_date' => Carbon::now()->subDays(1),
                'operations_lead_time_days' => 2,
                'procurement_lead_time_days' => 0,
                'subtotal' => 60000.00, // 60,000 * 1.00
                'tax' => 8400.00,
                'discount' => 0,
                'total_amount' => 68400.00,
                'total_estimated_cost' => 40800.00, // 60,000 * 0.68
                'profit_margin_percentage' => 32.00,
                'has_loss_alarm' => false,
                'payment_status' => 'paid',
                'client_notes' => 'معالق حلو بلبن لموسم الصيف والفروع الجديدة.',
                'internal_notes' => 'تم التسليم بالكامل واستلام الدفعة النقدية وإيداعها في الخزينة.',
            ]
        );
        SalesOrderItem::updateOrCreate(
            ['sales_order_id' => $order4->id, 'product_id' => $products['PROD-CVR-DESSERT']->id],
            [
                'quantity' => 60000.00,
                'unit_price' => 1.0000,
                'unit_cost' => 0.6800,
                'subtotal' => 60000.00,
                'total_cost' => 40800.00,
                'has_loss_item' => false,
                'customization_notes' => 'معلقة حلو زرقاء كريستال',
            ]
        );

        // 10. Production Orders (أوامر التشغيل)
        $prodOrder1 = ProductionOrder::updateOrCreate(
            ['order_number' => 'WO-2026-001'],
            [
                'sales_order_id' => $order1->id,
                'factory_id' => $factories['FAC-COUVERT']->id,
                'product_id' => $products['PROD-CVR-4IN1-ABU']->id,
                'machine_id' => $machines['MCH-CVR-01']->id,
                'planned_quantity' => 50000.00,
                'produced_quantity' => 28000.00,
                'defective_quantity' => 220.00,
                'scheduled_start_date' => Carbon::now()->subDay(),
                'scheduled_end_date' => Carbon::now()->addDays(2),
                'actual_start_date' => Carbon::now()->subDay(),
                'estimated_hours' => 14.00,
                'actual_hours' => 8.00,
                'status' => 'in_progress',
                'operations_manager_id' => $createdUsers['operations']->id,
                'instructions' => 'التأكد من دقة تغذية المناديل المعطرة وعدم إتلاف المغلف الفضي وسرعة السير 28 م/دقيقة.',
            ]
        );

        // 11. Material Requisition for Production Order 1
        $matReq1 = MaterialRequisition::updateOrCreate(
            ['req_number' => 'MR-2026-001'],
            [
                'production_order_id' => $prodOrder1->id,
                'status' => 'fully_issued',
                'requested_by' => $createdUsers['operations']->id,
                'issued_by' => $createdUsers['warehouse']->id,
                'requested_at' => Carbon::now()->subDay(),
                'issued_at' => Carbon::now()->subDay()->addHours(2),
                'notes' => 'تم صرف كامل الخامات لـ 50 ألف كوفير أبو طارق بموجب إذن الصرف رقم VOUCH-2026-001.',
            ]
        );

        foreach ($bomCouvert4in1 as $b) {
            MaterialRequisitionItem::updateOrCreate(
                ['material_requisition_id' => $matReq1->id, 'raw_material_id' => $b['raw_material_id']],
                [
                    'quantity_requested' => 50000.00 * $b['qty'],
                    'quantity_issued' => 50000.00 * $b['qty'],
                    'quantity_missing' => 0,
                    'unit_cost' => $b['cost'],
                ]
            );
        }

        // 12. Purchase Requisition (طلب شراء من المخزن للمشتريات بالنواقص)
        $pr1 = PurchaseRequisition::updateOrCreate(
            ['pr_number' => 'PR-2026-001'],
            [
                'warehouse_manager_id' => $createdUsers['warehouse']->id,
                'sales_order_id' => $order2->id,
                'production_order_id' => null,
                'status' => 'lead_time_quoted',
                'procurement_lead_time_days' => 3,
                'estimated_arrival_date' => Carbon::now()->addDays(3),
                'urgency' => 'high',
                'reason' => 'رصيد غلاف السيلوفان الشفاف وصل إلى 5,000 قطعة وهو دون حد الطلب، ومطلوب 40,000 لتشغيل طلب بافلو برجر.',
                'procurement_reply_notes' => 'تم التواصل مع مصنع الأهرام وأكد توريد رول 45,000 قطعة خلال 3 أيام بسعر 0.18 جنيه.',
            ]
        );

        PurchaseRequisitionItem::updateOrCreate(
            ['purchase_requisition_id' => $pr1->id, 'raw_material_id' => $rawMaterials['RM-WRAP-CLEAR']->id],
            [
                'needed_quantity' => 50000.00,
                'current_stock_at_request' => 5000.00,
                'supplier_id' => $suppliers['SUP-003']->id,
                'estimated_unit_price' => 0.1800,
                'lead_time_days' => 3,
                'notes' => 'مطلوب توريد عاجل جداً لارتباطنا بموعد تسليم العميل',
            ]
        );

        // 13. Purchase Orders: Internal Factory purchase with +14% markup
        // Central Warehouse purchasing from Internal Wipes Factory
        $poInternal = PurchaseOrder::updateOrCreate(
            ['po_number' => 'PO-INT-2026-001'],
            [
                'purchase_requisition_id' => null,
                'supplier_id' => null,
                'is_internal_factory' => true,
                'from_factory_id' => $factories['FAC-WIPES']->id,
                'destination_warehouse' => 'المخزن المركزي - قسم الخامات',
                'internal_markup_percentage' => 14.00,
                'status' => 'received_in_warehouse',
                'order_date' => Carbon::now()->subDays(3),
                'expected_delivery_date' => Carbon::now()->subDay(),
                'actual_delivery_date' => Carbon::now()->subDay(),
                'subtotal' => 24500.00, // 100,000 قطعة * 0.245 تكلفة تصنيع مباشرة
                'tax' => 0,
                'internal_markup_amount' => 3430.00, // 14% تكاليف إنتاج
                'total_amount' => 27930.00, // يدخل المخزن بقيمة 0.28 للقطعة
                'purchasing_manager_id' => $createdUsers['purchasing']->id,
                'notes' => 'طلب شراء داخلي من مصنع الوايبس للمخزن المركزي مع احتساب 14% تكلفة تشغيلية لتحديد ربحية المصنع المستقل.',
            ]
        );

        PurchaseOrderItem::updateOrCreate(
            ['purchase_order_id' => $poInternal->id, 'raw_material_id' => $rawMaterials['RM-WIPE-SCENT']->id],
            [
                'item_name' => 'منديل معطر وايبس مغلف فضي (إنتاج خط الوايبس الداخلي)',
                'quantity' => 100000.00,
                'unit_price' => 0.2793,
                'subtotal' => 27930.00,
                'received_quantity' => 100000.00,
                'notes' => 'شامل 14% هامش إنتاجي للخط',
            ]
        );

        // 14. Stock Movements (سندات المخزن للأرشفة والطباعة)
        // Voucher 1: Issue raw materials to production
        $vouch1 = StockMovement::updateOrCreate(
            ['voucher_number' => 'VOUCH-2026-001'],
            [
                'type' => 'issue_production',
                'reference_type' => 'production_order',
                'reference_id' => $prodOrder1->id,
                'warehouse_manager_id' => $createdUsers['warehouse']->id,
                'voucher_date' => Carbon::now()->subDay(),
                'total_value' => 83000.00,
                'received_or_delivered_by' => 'جرجس أنور (مشرف الوردية الأولى)',
                'is_archived' => true,
                'notes' => 'إذن صرف خامات تصنيع 50 ألف كوفير 4*1 لأمر التشغيل WO-2026-001',
            ]
        );

        foreach ($bomCouvert4in1 as $b) {
            $rm = RawMaterial::find($b['raw_material_id']);
            StockMovementItem::updateOrCreate(
                ['stock_movement_id' => $vouch1->id, 'item_id' => $b['raw_material_id'], 'item_type' => 'raw_material'],
                [
                    'item_name' => $rm->name,
                    'unit' => $rm->unit,
                    'quantity' => 50000.00 * $b['qty'],
                    'unit_cost' => $b['cost'],
                    'total_cost' => 50000.00 * $b['qty'] * $b['cost'],
                    'notes' => 'صرف لصالح خط تغليف الكوڤير',
                ]
            );
        }

        // Voucher 2: Delivery to Customer (Abou Tarek previous completed delivery)
        $vouch2 = StockMovement::updateOrCreate(
            ['voucher_number' => 'VOUCH-2026-002'],
            [
                'type' => 'delivery_customer',
                'reference_type' => 'sales_order',
                'reference_id' => $order4->id,
                'warehouse_manager_id' => $createdUsers['warehouse']->id,
                'voucher_date' => Carbon::now()->subDays(2),
                'total_value' => 60000.00,
                'received_or_delivered_by' => 'مندوب شركة ب.لبن (كابتن أحمد سامي)',
                'is_archived' => true,
                'notes' => 'إذن تسليم بضاعة مباعة للعميل ب.لبن - 60 ألف معلقة حلو مغلفة.',
            ]
        );

        StockMovementItem::updateOrCreate(
            ['stock_movement_id' => $vouch2->id, 'item_id' => $products['PROD-CVR-DESSERT']->id, 'item_type' => 'product'],
            [
                'item_name' => $products['PROD-CVR-DESSERT']->name,
                'unit' => 'قطعة',
                'quantity' => 60000.00,
                'unit_cost' => 0.6800,
                'total_cost' => 40800.00,
                'notes' => 'تم الفحص والتسليم بحالة ممتازة',
            ]
        );

        // 15. Daily Inventory Checks (الجرد اليومي)
        $invCheck = DailyInventoryCheck::updateOrCreate(
            ['check_code' => 'INV-2026-0315-01'],
            [
                'check_date' => Carbon::now()->format('Y-m-d'),
                'warehouse_manager_id' => $createdUsers['warehouse']->id,
                'category' => 'raw_materials',
                'status' => 'approved',
                'total_surplus_value' => 120.00,
                'total_deficit_value' => 45.00,
                'notes' => 'جرد يومي مفصل للمواد الخام الأساسية بالتعاون مع لجنة الجرد المشتركة.',
            ]
        );

        DailyInventoryCheckItem::updateOrCreate(
            ['daily_inventory_check_id' => $invCheck->id, 'item_id' => $rawMaterials['RM-SPOON-ENG']->id],
            [
                'item_type' => 'raw_material',
                'item_name' => $rawMaterials['RM-SPOON-ENG']->name,
                'unit' => 'قطعة',
                'system_quantity' => 145000.00,
                'actual_quantity' => 145050.00,
                'difference' => 50.00,
                'unit_cost' => 0.9000,
                'financial_impact' => 45.00,
                'reason' => 'فائض كرتونة توريد إضافية من وردية الأمس',
            ]
        );

        DailyInventoryCheckItem::updateOrCreate(
            ['daily_inventory_check_id' => $invCheck->id, 'item_id' => $rawMaterials['RM-TISSUE-DRY']->id],
            [
                'item_type' => 'raw_material',
                'item_name' => $rawMaterials['RM-TISSUE-DRY']->name,
                'unit' => 'قطعة',
                'system_quantity' => 95000.00,
                'actual_quantity' => 94900.00,
                'difference' => -100.00,
                'unit_cost' => 0.2400,
                'financial_impact' => -24.00,
                'reason' => 'تلف ناتج عن تجارب ضبط الماكينة',
            ]
        );

        // 16. Hourly Machine Reports (تقارير كل ساعة للماكينات مع التوصيات الذكية)
        $hours = [
            ['slot' => '08:00 - 09:00', 'qty' => 3450, 'speed' => 28.0, 'eff' => 95.8, 'waste' => 12, 'down' => 0, 'reason' => null],
            ['slot' => '09:00 - 10:00', 'qty' => 3520, 'speed' => 28.5, 'eff' => 97.7, 'waste' => 8, 'down' => 0, 'reason' => null],
            ['slot' => '10:00 - 11:00', 'qty' => 2800, 'speed' => 24.0, 'eff' => 77.8, 'waste' => 35, 'down' => 12, 'reason' => 'انحشار رول السيلوفان في سكينة اللحام الحراري'],
            ['slot' => '11:00 - 12:00', 'qty' => 3600, 'speed' => 29.0, 'eff' => 100.0, 'waste' => 5, 'down' => 0, 'reason' => null],
            ['slot' => '12:00 - 13:00', 'qty' => 3580, 'speed' => 28.8, 'eff' => 99.4, 'waste' => 6, 'down' => 0, 'reason' => null],
            ['slot' => '13:00 - 14:00', 'qty' => 3100, 'speed' => 26.0, 'eff' => 86.1, 'waste' => 18, 'down' => 8, 'reason' => 'تغيير بكرة رول السيلوفان المطبوع وضبط الحساس'],
            ['slot' => '14:00 - 15:00', 'qty' => 3620, 'speed' => 29.2, 'eff' => 100.5, 'waste' => 4, 'down' => 0, 'reason' => null],
            ['slot' => '15:00 - 16:00', 'qty' => 3550, 'speed' => 28.7, 'eff' => 98.6, 'waste' => 7, 'down' => 0, 'reason' => null],
        ];

        foreach ($hours as $h) {
            $rec = HourlyMachineReport::generateRecommendation(
                $h['eff'],
                $h['down'],
                $h['waste'],
                $h['speed'],
                28.5
            );

            HourlyMachineReport::updateOrCreate(
                [
                    'machine_id' => $machines['MCH-CVR-01']->id,
                    'report_date' => Carbon::now()->format('Y-m-d'),
                    'hour_slot' => $h['slot'],
                ],
                [
                    'factory_id' => $factories['FAC-COUVERT']->id,
                    'production_order_id' => $prodOrder1->id,
                    'operator_name' => 'أحمد عبد الستار (فني تشغيل أول)',
                    'units_produced' => $h['qty'],
                    'belt_speed_m_min' => $h['speed'],
                    'efficiency_rate' => $h['eff'],
                    'waste_units' => $h['waste'],
                    'downtime_minutes' => $h['down'],
                    'downtime_reason' => $h['reason'],
                    'actions_taken' => $h['down'] > 0 ? 'تم تنظيف فكوك اللحام وإعادة معايرة الفوتوسيل واستئناف السرعة الطبيعية.' : 'تشغيل منتظم ومراقبة جودة التغليف.',
                    'ai_smart_recommendation' => $rec,
                    'submitted_to_management' => true,
                    'created_by' => $createdUsers['operations']->id,
                ]
            );
        }

        // 17. Financial Transactions (الحسابات، الفواتير، التكاليف الثابتة والمتغيرة، وتكلفة المصانع)
        $financialTransactions = [
            [
                'transaction_number' => 'FT-2026-001',
                'type' => 'customer_payment',
                'category' => 'إيرادات مبيعات',
                'amount' => 68400.00,
                'transaction_date' => Carbon::now()->subDays(2),
                'payment_method' => 'تحويل بنكي',
                'sales_order_id' => $order4->id,
                'factory_id' => $factories['FAC-COUVERT']->id,
                'description' => 'سداد كامل فاتورة طلبية كشري ب.لبن رقم SO-2026-004',
            ],
            [
                'transaction_number' => 'FT-2026-002',
                'type' => 'factory_internal_14_markup',
                'category' => 'تكاليف تشغيل 14%',
                'amount' => 3430.00,
                'transaction_date' => Carbon::now()->subDay(),
                'payment_method' => 'قيد محاسبي داخلي',
                'purchase_order_id' => $poInternal->id,
                'factory_id' => $factories['FAC-WIPES']->id,
                'description' => 'إثبات هامش التكلفة الإنتاجية التشغيلية 14% المحملة لصالح مصنع الوايبس عند توريد 100 ألف قطعة للمخزن المركزي',
            ],
            [
                'transaction_number' => 'FT-2026-003',
                'type' => 'fixed_cost',
                'category' => 'كهرباء ومرافق',
                'amount' => 18500.00,
                'transaction_date' => Carbon::now()->subDays(6),
                'payment_method' => 'شيك مصرفي',
                'factory_id' => null,
                'description' => 'سداد فاتورة الكهرباء والجهد المتوسط للمجمع الصناعي لشهر مارس',
            ],
            [
                'transaction_number' => 'FT-2026-004',
                'type' => 'fixed_cost',
                'category' => 'أجور ورواتب',
                'amount' => 125000.00,
                'transaction_date' => Carbon::now()->subDays(10),
                'payment_method' => 'تحويل بنكي',
                'factory_id' => null,
                'description' => 'رواتب وأجور المهندسين والعمال والفنيين عن شهر فبراير',
            ],
            [
                'transaction_number' => 'FT-2026-005',
                'type' => 'variable_cost',
                'category' => 'مصاريف صيانة',
                'amount' => 4500.00,
                'transaction_date' => Carbon::now()->subDays(4),
                'payment_method' => 'نقدي',
                'factory_id' => $factories['FAC-COUVERT']->id,
                'description' => 'شراء قطع غيار وصيانة دورية لسخانات ماكينات التغليف الفلو باك',
            ],
        ];

        foreach ($financialTransactions as $ft) {
            FinancialTransaction::updateOrCreate(
                ['transaction_number' => $ft['transaction_number']],
                array_merge($ft, ['created_by' => $createdUsers['finance']->id])
            );
        }
    }
}
