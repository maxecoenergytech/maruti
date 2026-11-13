<?php

namespace Database\Seeders;

use App\Enums\ProductCategory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // 1. Hardware - Laptops & Desktops
            [
                'name' => 'Dell OptiPlex 7010 Desktop PC (i5 13th Gen, 16GB, 512GB SSD, Win 11 Pro)',
                'category' => ProductCategory::Hardware,
                'hsn_sac' => '84713010',
                'unit' => 'Set',
                'default_rate' => 54500.00,
                'gst_rate' => 18.00,
                'description' => 'Dell Commercial OptiPlex desktop tower with Dell 22" FHD Monitor, keyboard, mouse & 3-year ProSupport warranty.',
            ],
            [
                'name' => 'HP 250 G9 Business Laptop (Intel Core i3 12th Gen, 8GB RAM, 512GB SSD, 15.6" FHD)',
                'category' => ProductCategory::Hardware,
                'hsn_sac' => '84713020',
                'unit' => 'Pcs',
                'default_rate' => 38000.00,
                'gst_rate' => 18.00,
                'description' => 'Lightweight durable business laptop with Windows 11 Home & 1-year onsite warranty.',
            ],
            [
                'name' => 'Lenovo ThinkPad E14 Gen 5 (Intel Core i7 13th Gen, 16GB RAM, 1TB SSD)',
                'category' => ProductCategory::Hardware,
                'hsn_sac' => '84713020',
                'unit' => 'Pcs',
                'default_rate' => 78500.00,
                'gst_rate' => 18.00,
                'description' => 'Enterprise business laptop with aluminum chassis, backlit keyboard, fingerprint reader and 3-year Premier Support.',
            ],
            [
                'name' => 'HP LaserJet Pro MFP M126nw Multi-Function Network Printer',
                'category' => ProductCategory::Hardware,
                'hsn_sac' => '84433100',
                'unit' => 'Pcs',
                'default_rate' => 19500.00,
                'gst_rate' => 18.00,
                'description' => 'Monochrome laser printer with Print, Copy, Scan, Wi-Fi & Ethernet connectivity.',
            ],
            [
                'name' => 'Epson EcoTank L3250 Wi-Fi All-in-One Ink Tank Printer',
                'category' => ProductCategory::Hardware,
                'hsn_sac' => '84433200',
                'unit' => 'Pcs',
                'default_rate' => 15200.00,
                'gst_rate' => 18.00,
                'description' => 'High-yield color ink tank printer with wireless printing and borderless photo printing.',
            ],
            [
                'name' => 'Microtek Legend 1000VA / 12V Line Interactive UPS',
                'category' => ProductCategory::Hardware,
                'hsn_sac' => '85044090',
                'unit' => 'Pcs',
                'default_rate' => 4800.00,
                'gst_rate' => 18.00,
                'description' => 'Microprocessor based UPS with AVR and 20-30 minutes battery backup for PC setup.',
            ],
            [
                'name' => 'APC Smart-UPS 3000VA 230V LCD Rack/Tower Online UPS',
                'category' => ProductCategory::Hardware,
                'hsn_sac' => '85044090',
                'unit' => 'Pcs',
                'default_rate' => 84000.00,
                'gst_rate' => 18.00,
                'description' => 'High density double-conversion online power protection for network servers & data closets.',
            ],

            // 2. CCTV & Security
            [
                'name' => 'Hikvision 4MP Smart Hybrid Light IP Dome Camera (DS-2CD1143G2-LIU)',
                'category' => ProductCategory::CctvSecurity,
                'hsn_sac' => '85258090',
                'unit' => 'Pcs',
                'default_rate' => 3450.00,
                'gst_rate' => 18.00,
                'description' => '4MP IP dome camera with built-in microphone, human & vehicle detection, and 30m IR/white light.',
            ],
            [
                'name' => 'Hikvision 4MP Outdoor Bullet Camera (DS-2CD1043G2-LIU)',
                'category' => ProductCategory::CctvSecurity,
                'hsn_sac' => '85258090',
                'unit' => 'Pcs',
                'default_rate' => 3650.00,
                'gst_rate' => 18.00,
                'description' => 'IP67 weatherproof 4MP outdoor bullet camera with dual illumination and PoE support.',
            ],
            [
                'name' => 'Hikvision 16-Channel 4K NVR (DS-7616NI-K2)',
                'category' => ProductCategory::CctvSecurity,
                'hsn_sac' => '85219090',
                'unit' => 'Pcs',
                'default_rate' => 11500.00,
                'gst_rate' => 18.00,
                'description' => '16-channel network video recorder with 4K HDMI output, incoming bandwidth 160Mbps, 2 SATA interfaces (up to 20TB).',
            ],
            [
                'name' => 'Western Digital Purple 4TB Surveillance Internal Hard Disk',
                'category' => ProductCategory::CctvSecurity,
                'hsn_sac' => '84717020',
                'unit' => 'Pcs',
                'default_rate' => 8900.00,
                'gst_rate' => 18.00,
                'description' => 'Engineered for 24x7 always-on high-definition security surveillance systems with AllFrame technology.',
            ],
            [
                'name' => 'CP PLUS 8-Channel 5MP HD Cosmic DVR (CP-UVR-0801E1-CS)',
                'category' => ProductCategory::CctvSecurity,
                'hsn_sac' => '85219090',
                'unit' => 'Pcs',
                'default_rate' => 3800.00,
                'gst_rate' => 18.00,
                'description' => 'Supports HDCVI/AHD/TVI/CVBS/IP video inputs, H.265+ compression, and mobile remote view.',
            ],
            [
                'name' => 'Realtime Biometric Attendance System with Fingerprint & RFID (RS-20)',
                'category' => ProductCategory::CctvSecurity,
                'hsn_sac' => '85437099',
                'unit' => 'Pcs',
                'default_rate' => 6500.00,
                'gst_rate' => 18.00,
                'description' => 'Color LCD screen, 1,000 fingerprint capacity, USB & TCP/IP data transfer, desktop attendance software included.',
            ],

            // 3. Networking
            [
                'name' => 'D-Link 24-Port 10/100/1000 Gigabit Unmanaged Switch (DGS-1024D)',
                'category' => ProductCategory::Networking,
                'hsn_sac' => '85176290',
                'unit' => 'Pcs',
                'default_rate' => 6200.00,
                'gst_rate' => 18.00,
                'description' => 'Energy efficient 24-port Gigabit Ethernet switch with metal desktop/rackmount casing.',
            ],
            [
                'name' => 'TP-Link Omada 24-Port Gigabit Smart PoE+ Switch (TL-SG2428P)',
                'category' => ProductCategory::Networking,
                'hsn_sac' => '85176290',
                'unit' => 'Pcs',
                'default_rate' => 24500.00,
                'gst_rate' => 18.00,
                'description' => '24 PoE+ ports with 250W power budget, 4 SFP slots, cloud SDN centralized management.',
            ],
            [
                'name' => 'D-Link Cat6 UTP Solid Pure Copper Cable 305m Roll',
                'category' => ProductCategory::Networking,
                'hsn_sac' => '85444999',
                'unit' => 'Lot',
                'default_rate' => 8200.00,
                'gst_rate' => 18.00,
                'description' => '4-pair 23 AWG 250MHz verified solid copper structured cabling roll (305 meters / 1000 ft).',
            ],
            [
                'name' => 'Ruijie Reyee Wi-Fi 6 AX1800 Dual-Band Ceiling Access Point (RG-RAP2200-E)',
                'category' => ProductCategory::Networking,
                'hsn_sac' => '85176290',
                'unit' => 'Pcs',
                'default_rate' => 5400.00,
                'gst_rate' => 18.00,
                'description' => 'Up to 1775Mbps speed, gigabit PoE port, seamless roaming, free lifetime cloud management.',
            ],
            [
                'name' => 'Valrack 9U Wall Mount Network Server Rack (550mm Depth)',
                'category' => ProductCategory::Networking,
                'hsn_sac' => '85381010',
                'unit' => 'Pcs',
                'default_rate' => 4500.00,
                'gst_rate' => 18.00,
                'description' => 'Glass door with lock, 1 cooling fan, PDU 6-socket power strip, and cantilever shelf.',
            ],

            // 4. Software
            [
                'name' => 'TallyPrime Silver Single User License',
                'category' => ProductCategory::Software,
                'hsn_sac' => '997331',
                'unit' => 'Pcs',
                'default_rate' => 22500.00,
                'gst_rate' => 18.00,
                'description' => 'Perpetual desktop accounting and GST billing software for standalone single PC.',
            ],
            [
                'name' => 'TallyPrime Gold Multi-User Unlimited LAN License',
                'category' => ProductCategory::Software,
                'hsn_sac' => '997331',
                'unit' => 'Pcs',
                'default_rate' => 67500.00,
                'gst_rate' => 18.00,
                'description' => 'Perpetual accounting & inventory license for unlimited concurrent computers over LAN.',
            ],
            [
                'name' => 'Quick Heal Total Security Antivirus 3 User 3 Years License',
                'category' => ProductCategory::Software,
                'hsn_sac' => '997331',
                'unit' => 'Pcs',
                'default_rate' => 3100.00,
                'gst_rate' => 18.00,
                'description' => 'Ransomware protection, firewall, web security, anti-phishing, safe banking for 3 PCs.',
            ],
            [
                'name' => 'Microsoft 365 Business Standard (Annual Subscription per User)',
                'category' => ProductCategory::Software,
                'hsn_sac' => '997331',
                'unit' => 'Year',
                'default_rate' => 11200.00,
                'gst_rate' => 18.00,
                'description' => 'Desktop Office apps (Word, Excel, PowerPoint) + 1TB OneDrive cloud storage + 50GB business email.',
            ],

            // 5. Services
            [
                'name' => 'Comprehensive Annual Maintenance Contract (AMC) per Desktop PC',
                'category' => ProductCategory::Service,
                'hsn_sac' => '998713',
                'unit' => 'Year',
                'default_rate' => 2800.00,
                'gst_rate' => 18.00,
                'description' => 'Includes quarterly preventive maintenance, OS troubleshooting, virus cleaning & replacement of failed parts.',
            ],
            [
                'name' => 'CCTV Surveillance Camera Installation, Testing & Cabling',
                'category' => ProductCategory::Service,
                'hsn_sac' => '995469',
                'unit' => 'Pcs',
                'default_rate' => 450.00,
                'gst_rate' => 18.00,
                'description' => 'Per camera installation charge including mounting, BNC/RJ45 crimping, focus adjustment and DVR/NVR configuration.',
            ],
            [
                'name' => 'Onsite IT Support & Network Troubleshooting Call Visit (Guwahati City)',
                'category' => ProductCategory::Service,
                'hsn_sac' => '998713',
                'unit' => 'Lot',
                'default_rate' => 950.00,
                'gst_rate' => 18.00,
                'description' => 'Single callout engineer visit up to 3 hours for diagnosing PC, printer, or local area network issues.',
            ],
        ];

        foreach ($products as $productData) {
            Product::updateOrCreate(
                ['name' => $productData['name']],
                $productData
            );
        }
    }
}
