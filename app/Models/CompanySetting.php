<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'tagline',
        'phone',
        'email',
        'website',
        'gstin',
        'pan',
        'state',
        'state_code',
        'address_line1',
        'address_line2',
        'city',
        'pincode',
        'bank_name',
        'bank_account_name',
        'bank_account_no',
        'bank_ifsc',
        'bank_branch',
        'quotation_prefix',
        'quotation_default_terms',
        'logo_path',
        'signature_path',
        'stamp_path',
        'signatory_title',
        'jurisdiction_text',
        'show_signature_on_pdf',
        'show_computer_generated_note',
    ];

    protected function casts(): array
    {
        return [
            'show_signature_on_pdf' => 'boolean',
            'show_computer_generated_note' => 'boolean',
        ];
    }

    /**
     * Singleton accessor for company settings.
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
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
            'quotation_default_terms' => "1. Payment: 50% advance along with confirmed purchase order, remaining 50% upon delivery/installation.\n2. Warranty: Standard OEM warranty applicable. No warranty for physical or water damage.\n3. Taxes: GST is charged as applicable per HSN/SAC code.\n4. Delivery: Within 3 to 7 working days from date of purchase order.\n5. Validity: This quotation is valid for 15 days from the date of issue.",
            'signatory_title' => 'Authorized Signatory',
            'jurisdiction_text' => 'Subject to Guwahati jurisdiction',
            'show_signature_on_pdf' => false,
            'show_computer_generated_note' => false,
        ]);
    }
}
