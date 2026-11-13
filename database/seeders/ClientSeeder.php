<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $rahul = User::where('email', 'rahul@marutisales.in')->first();
        $priya = User::where('email', 'priya@marutisales.in')->first();
        $admin = User::where('email', 'admin@marutisales.in')->first();

        $clients = [
            [
                'company_name' => 'Pragjyotish Power Solutions Pvt Ltd',
                'contact_person' => 'Bikash Baruah',
                'phone' => '+91 94351 22334',
                'email' => 'purchase@pragjyotishpower.com',
                'gstin' => '18AAACP1234F1Z5',
                'billing_address' => 'Plot 42, VIP Road, Six Mile, Guwahati, Assam',
                'shipping_address' => 'Plot 42, VIP Road, Six Mile, Guwahati, Assam',
                'state' => 'Assam',
                'state_code' => '18',
                'assigned_user_id' => $rahul?->id,
                'notes' => 'Bulk client for corporate desktop setups and UPS systems.',
                'is_active' => true,
            ],
            [
                'company_name' => 'Brahmaputra Tea Estates Ltd',
                'contact_person' => 'Anurag Saikia',
                'phone' => '+91 98540 33445',
                'email' => 'it@brahmaputratea.com',
                'gstin' => '18AABCB5678K1Z9',
                'billing_address' => 'Heritage Tea Bhavan, R.K.B. Path, Dibrugarh, Assam',
                'shipping_address' => 'Garden Estate Office, Dikom, Dibrugarh, Assam',
                'state' => 'Assam',
                'state_code' => '18',
                'assigned_user_id' => $priya?->id,
                'notes' => 'Annual CCTV surveillance contract and Wi-Fi networking.',
                'is_active' => true,
            ],
            [
                'company_name' => 'Kaziranga Security & Patrol Services',
                'contact_person' => 'Hemanta Bora',
                'phone' => '+91 97061 55667',
                'email' => 'ops@kazirangasecurity.in',
                'gstin' => '18AAGCK9988D1Z2',
                'billing_address' => 'A.T. Road, Near Bus Stand, Jorhat, Assam',
                'shipping_address' => null,
                'state' => 'Assam',
                'state_code' => '18',
                'assigned_user_id' => $rahul?->id,
                'notes' => 'IP camera supplier partner in Upper Assam.',
                'is_active' => true,
            ],
            [
                'company_name' => 'Assam Poly Clinic & Diagnostic Centre',
                'contact_person' => 'Dr. Nilav Hazarika',
                'phone' => '+91 94350 88990',
                'email' => 'admin@polyclinictezpur.com',
                'gstin' => '18AACFA4321E1Z3',
                'billing_address' => 'Hospital Road, Mahabhairab, Tezpur, Assam',
                'shipping_address' => null,
                'state' => 'Assam',
                'state_code' => '18',
                'assigned_user_id' => $priya?->id,
                'notes' => 'Hospital server maintenance, diagnostic PC network.',
                'is_active' => true,
            ],
            [
                'company_name' => 'Kamakhya Logistics & Freight Corp',
                'contact_person' => 'Manas Pratim Das',
                'phone' => '+91 98642 11998',
                'email' => 'manas@kamakhyalogistics.in',
                'gstin' => '18AADCK6543M1Z8',
                'billing_address' => 'Transport Nagar, Khanapara, Guwahati, Assam',
                'shipping_address' => 'Warehouse Yard 3, Changsari, Kamrup Rural, Assam',
                'state' => 'Assam',
                'state_code' => '18',
                'assigned_user_id' => $rahul?->id,
                'notes' => 'Fleet monitoring GPS and barcode thermal printers.',
                'is_active' => true,
            ],
            [
                'company_name' => 'Green Valley Educational Trust',
                'contact_person' => 'Sangeeta Bhattacharjee',
                'phone' => '+91 94353 77112',
                'email' => 'trust@greenvalleyschool.edu.in',
                'gstin' => '18AAATG8877L1Z4',
                'billing_address' => 'College Road, Ambicapatty, Silchar, Assam',
                'shipping_address' => 'Senior Campus, Meherpur, Silchar, Assam',
                'state' => 'Assam',
                'state_code' => '18',
                'assigned_user_id' => $priya?->id,
                'notes' => 'Computer lab setup with 40 N-Computing thin clients.',
                'is_active' => true,
            ],
            [
                'company_name' => 'North East Cyber Infotech',
                'contact_person' => 'Deepjyoti Medhi',
                'phone' => '+91 98590 66332',
                'email' => 'contact@necyberinfo.com',
                'gstin' => '18AAGCN1122J1Z1',
                'billing_address' => 'Haibargaon Main Road, Nagaon, Assam',
                'shipping_address' => null,
                'state' => 'Assam',
                'state_code' => '18',
                'assigned_user_id' => $rahul?->id,
                'notes' => 'Hardware sub-dealer in Nagaon district.',
                'is_active' => true,
            ],
            [
                'company_name' => 'Barak Valley Builders & Infrastructure',
                'contact_person' => 'Faruk Choudhury',
                'phone' => '+91 94355 44889',
                'email' => 'projects@barakbuilders.com',
                'gstin' => '18AAAFB9900H1Z6',
                'billing_address' => 'National Highway Road, Hailakandi, Assam',
                'shipping_address' => null,
                'state' => 'Assam',
                'state_code' => '18',
                'assigned_user_id' => $admin?->id,
                'notes' => 'Site office Wi-Fi, biometric attendance machines.',
                'is_active' => true,
            ],
            [
                'company_name' => 'Meghalaya Minerals & Cements Ltd',
                'contact_person' => 'Donald Lyngdoh',
                'phone' => '+91 98630 11447',
                'email' => 'purchase@meghalayaminerals.com',
                'gstin' => '17AABCM7788P1Z0', // State Code 17 = Meghalaya (Interstate IGST test!)
                'billing_address' => 'Upland Road, Laitumkhrah, Shillong, Meghalaya',
                'shipping_address' => 'Plant Office, Lumshnong, East Jaintia Hills, Meghalaya',
                'state' => 'Meghalaya',
                'state_code' => '17',
                'assigned_user_id' => $rahul?->id,
                'notes' => 'Interstate buyer in Meghalaya. Requires IGST invoices.',
                'is_active' => true,
            ],
            [
                'company_name' => 'Eastern Steel & Hardware Traders',
                'contact_person' => 'Subhashish Ghosh',
                'phone' => '+91 98300 99887',
                'email' => 'easternsteel.kol@gmail.com',
                'gstin' => '19AAACE4455N1Z7', // State Code 19 = West Bengal (Interstate IGST test!)
                'billing_address' => 'Brabourne Road, Dalhousie, Kolkata, West Bengal',
                'shipping_address' => 'Godown Hub, Amingaon, Guwahati, Assam',
                'state' => 'West Bengal',
                'state_code' => '19',
                'assigned_user_id' => $priya?->id,
                'notes' => 'Kolkata head office with branch warehouse at Amingaon Guwahati.',
                'is_active' => true,
            ],
        ];

        foreach ($clients as $clientData) {
            Client::updateOrCreate(
                ['company_name' => $clientData['company_name']],
                $clientData
            );
        }
    }
}
