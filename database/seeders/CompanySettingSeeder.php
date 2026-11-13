<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        CompanySetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'MARUTI SALES & SERVICES',
                'tagline' => '(A Complete IT Solution)',
                'phone' => '+91 70022 53021',
                'email' => 'smarutisales@gmail.com',
                'website' => null,
                'gstin' => '18ABGFM5224K1Z2',
                'pan' => 'ABGFM5224K',
                'state' => 'Assam',
                'state_code' => '18',
                'address_line1' => 'Mahabir Market, Ground Floor, opp. Hotel Siroy Lily',
                'address_line2' => 'Solapara Road, Paltan Bazar',
                'city' => 'Guwahati',
                'pincode' => '781008',
                'bank_name' => 'State Bank of India',
                'bank_account_name' => 'Maruti Sales & Services',
                'bank_account_no' => '38472910482',
                'bank_ifsc' => 'SBIN0003295',
                'bank_branch' => 'G.S. Road Branch, Guwahati',
                'quotation_prefix' => 'MSS',
                'quotation_default_terms' => "1. Payment: 50% advance along with confirmed purchase order, remaining 50% upon delivery/installation.\n2. Warranty: Standard OEM warranty applicable. No warranty for physical, electrical surge or water damage.\n3. Taxes: GST is charged as applicable per official HSN/SAC code.\n4. Delivery & Installation: Within 3 to 7 working days from date of purchase order.\n5. Validity: This quotation is strictly valid for 15 days from the date of issue.",
                'signatory_title' => 'Proprietor / Authorized Signatory',
                'jurisdiction_text' => 'Subject to Guwahati jurisdiction',
                'show_signature_on_pdf' => false,
                'show_computer_generated_note' => false,
            ]
        );
    }
}
