<?php

namespace Database\Seeders;

use App\Enums\ProspectSource;
use App\Enums\ProspectStage;
use App\Models\Prospect;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProspectSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@marutisales.in')->first();
        $rahul = User::where('email', 'rahul@marutisales.in')->first();
        $priya = User::where('email', 'priya@marutisales.in')->first();

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $threeDaysAgo = Carbon::today()->subDays(3);
        $tomorrow = Carbon::tomorrow();
        $nextWeek = Carbon::today()->addDays(5);

        $prospects = [
            [
                'contact_person' => 'Jitu Deka',
                'company_name' => 'Saraighat Diagnostics & Clinic',
                'phone' => '+91 94351 11223',
                'email' => 'jitudeka.sdc@gmail.com',
                'source' => ProspectSource::WalkIn,
                'requirement' => '6 units Core i5 Desktop PCs + 650VA Microtek UPS for laboratory workstations.',
                'expected_value' => 240000.00,
                'stage' => ProspectStage::Negotiation,
                'lost_reason' => null,
                'next_follow_up_date' => $today->format('Y-m-d'), // Due today!
                'assigned_user_id' => $rahul->id,
            ],
            [
                'contact_person' => 'Partha Pratim Sarma',
                'company_name' => 'Assam Petrochemicals Regional Depot',
                'phone' => '+91 98640 22334',
                'email' => 'ppsarma@assampetro.gov.in',
                'source' => ProspectSource::Tender,
                'requirement' => 'Supply and installation of 32 IP Bullet Cameras (Hikvision) with optical fiber backbone.',
                'expected_value' => 485000.00,
                'stage' => ProspectStage::Quoted,
                'lost_reason' => null,
                'next_follow_up_date' => $yesterday->format('Y-m-d'), // Overdue!
                'assigned_user_id' => $priya->id,
            ],
            [
                'contact_person' => 'Deepak Agarwala',
                'company_name' => 'Kamrup Flour Mills Ltd',
                'phone' => '+91 94350 33445',
                'email' => 'deepak@kamrupflour.com',
                'source' => ProspectSource::Referral,
                'requirement' => 'Cisco 24-Port Gigabit PoE Managed Switches (4 units) + D-Link Cat6 cabling.',
                'expected_value' => 175000.00,
                'stage' => ProspectStage::Contacted,
                'lost_reason' => null,
                'next_follow_up_date' => $threeDaysAgo->format('Y-m-d'), // Overdue!
                'assigned_user_id' => $rahul->id,
            ],
            [
                'contact_person' => 'Anamika Goswami',
                'company_name' => 'Spring Valley Public School',
                'phone' => '+91 98540 44556',
                'email' => 'principal@springvalleyschool.in',
                'source' => ProspectSource::Website,
                'requirement' => '2 units 75" Maxhub Interactive Flat Panels for smart classrooms + wall mounts.',
                'expected_value' => 290000.00,
                'stage' => ProspectStage::New,
                'lost_reason' => null,
                'next_follow_up_date' => $today->format('Y-m-d'), // Due today!
                'assigned_user_id' => $priya->id,
            ],
            [
                'contact_person' => 'Bhaskar Jyoti Nath',
                'company_name' => 'Brahmaputra Engineering Works',
                'phone' => '+91 97060 55667',
                'email' => 'bhaskar@beworks.co.in',
                'source' => ProspectSource::PhoneCall,
                'requirement' => 'HP LaserJet MFP Multi-function printers (3 units) + Heavy duty thermal printer.',
                'expected_value' => 85000.00,
                'stage' => ProspectStage::Negotiation,
                'lost_reason' => null,
                'next_follow_up_date' => $tomorrow->format('Y-m-d'),
                'assigned_user_id' => $rahul->id,
            ],
            [
                'contact_person' => 'Ratul Kalita',
                'company_name' => 'Paltan Bazar Retail Electronics',
                'phone' => '+91 94353 66778',
                'email' => null,
                'source' => ProspectSource::WalkIn,
                'requirement' => '4 units Hikvision 5MP Dome Cameras + 4CH NVR + 1TB WD Purple HDD.',
                'expected_value' => 28000.00,
                'stage' => ProspectStage::Won,
                'lost_reason' => null,
                'next_follow_up_date' => null,
                'assigned_user_id' => $rahul->id,
            ],
            [
                'contact_person' => 'Nayan Moni Saikia',
                'company_name' => 'Upper Assam Tea Warehousing Corp',
                'phone' => '+91 98642 77889',
                'email' => 'warehouse@upperassamtea.com',
                'source' => ProspectSource::Referral,
                'requirement' => 'Realtime Biometric Face Recognition Attendance system with cloud software.',
                'expected_value' => 45000.00,
                'stage' => ProspectStage::Quoted,
                'lost_reason' => null,
                'next_follow_up_date' => $nextWeek->format('Y-m-d'),
                'assigned_user_id' => $priya->id,
            ],
            [
                'contact_person' => 'Ramesh Chandra Roy',
                'company_name' => 'Dhubri Jute Traders Syndicate',
                'phone' => '+91 94350 88991',
                'email' => 'roy.ramesh@dhubrijute.com',
                'source' => ProspectSource::PhoneCall,
                'requirement' => 'Dell Vostro 15th Gen Core i3 Laptops (4 units) with backpacks.',
                'expected_value' => 196000.00,
                'stage' => ProspectStage::Lost,
                'lost_reason' => 'Customer opted for online direct purchase from Dell store due to immediate credit card discount.',
                'next_follow_up_date' => null,
                'assigned_user_id' => $rahul->id,
            ],
            [
                'contact_person' => 'Debabrata Barman',
                'company_name' => 'Barman Hardware & Sanitary Mart',
                'phone' => '+91 97061 99001',
                'email' => null,
                'source' => ProspectSource::WalkIn,
                'requirement' => 'TallyPrime Gold Multi-User License + 3 Node LAN setup + Tally Server backup.',
                'expected_value' => 67000.00,
                'stage' => ProspectStage::Contacted,
                'lost_reason' => null,
                'next_follow_up_date' => $tomorrow->format('Y-m-d'),
                'assigned_user_id' => $admin->id,
            ],
            [
                'contact_person' => 'Simanta Pathak',
                'company_name' => 'Nalbari Agri Implements Cooperative',
                'phone' => '+91 98590 12344',
                'email' => 'nalbari.agricoop@assam.gov.in',
                'source' => ProspectSource::Tender,
                'requirement' => '10 KVA Online UPS with 60 minutes battery bank for cold storage control room.',
                'expected_value' => 320000.00,
                'stage' => ProspectStage::Quoted,
                'lost_reason' => null,
                'next_follow_up_date' => $today->format('Y-m-d'), // Due today!
                'assigned_user_id' => $rahul->id,
            ],
            [
                'contact_person' => 'Dr. Pallabi Chaliha',
                'company_name' => 'Chaliha Dental Care & Implant Clinic',
                'phone' => '+91 94351 34567',
                'email' => 'pallabi.chaliha@gmail.com',
                'source' => ProspectSource::Referral,
                'requirement' => 'Dental RVG Sensor Workstation PC + Apple iPad display bracket for patient counseling.',
                'expected_value' => 95000.00,
                'stage' => ProspectStage::Negotiation,
                'lost_reason' => null,
                'next_follow_up_date' => $yesterday->format('Y-m-d'), // Overdue!
                'assigned_user_id' => $priya->id,
            ],
            [
                'contact_person' => 'Mridul Gogoi',
                'company_name' => 'Sivasagar Petroleum Support Services',
                'phone' => '+91 98640 45678',
                'email' => 'mridul@sivasagarpetro.in',
                'source' => ProspectSource::Other,
                'requirement' => 'Explosion-proof ATEX certified surveillance camera for oil drilling yard.',
                'expected_value' => 420000.00,
                'stage' => ProspectStage::New,
                'lost_reason' => null,
                'next_follow_up_date' => $nextWeek->format('Y-m-d'),
                'assigned_user_id' => $rahul->id,
            ],
            [
                'contact_person' => 'Gautam Das',
                'company_name' => 'Barpeta Road Timber & Saw Mills',
                'phone' => '+91 94352 56789',
                'email' => null,
                'source' => ProspectSource::PhoneCall,
                'requirement' => '8-Port CCTV setup around timber perimeter with night color vision and audio recording.',
                'expected_value' => 55000.00,
                'stage' => ProspectStage::Contacted,
                'lost_reason' => null,
                'next_follow_up_date' => $today->format('Y-m-d'), // Due today!
                'assigned_user_id' => $priya->id,
            ],
            [
                'contact_person' => 'Alok Mazumdar',
                'company_name' => 'Guwahati Cold Storage & Logistics',
                'phone' => '+91 97062 67890',
                'email' => 'alok@ghycoldchain.com',
                'source' => ProspectSource::Website,
                'requirement' => 'Long range barcode wireless scanners (Zebra) + 2 thermal barcode printers.',
                'expected_value' => 110000.00,
                'stage' => ProspectStage::Quoted,
                'lost_reason' => null,
                'next_follow_up_date' => $tomorrow->format('Y-m-d'),
                'assigned_user_id' => $rahul->id,
            ],
            [
                'contact_person' => 'Tanmoy Sen',
                'company_name' => 'Silchar Medical Supplies Co',
                'phone' => '+91 98541 78901',
                'email' => 'tanmoy@silcharmed.com',
                'source' => ProspectSource::Referral,
                'requirement' => 'Annual AMC for 15 Desktops + 2 Servers + Monthly offsite cloud backup management.',
                'expected_value' => 78000.00,
                'stage' => ProspectStage::Lost,
                'lost_reason' => 'Existing local hardware vendor renewed contract at 15% lower price.',
                'next_follow_up_date' => null,
                'assigned_user_id' => $priya->id,
            ],
        ];

        foreach ($prospects as $prospectData) {
            Prospect::updateOrCreate(
                ['contact_person' => $prospectData['contact_person'], 'phone' => $prospectData['phone']],
                $prospectData
            );
        }
    }
}
