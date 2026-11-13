<?php

namespace App\Support;

class GstHelper
{
    /**
     * Map of Indian State / UT 2-digit GST codes to State Names.
     */
    public const STATE_CODES = [
        '01' => 'Jammu and Kashmir',
        '02' => 'Himachal Pradesh',
        '03' => 'Punjab',
        '04' => 'Chandigarh',
        '05' => 'Uttarakhand',
        '06' => 'Haryana',
        '07' => 'Delhi',
        '08' => 'Rajasthan',
        '09' => 'Uttar Pradesh',
        '10' => 'Bihar',
        '11' => 'Sikkim',
        '12' => 'Arunachal Pradesh',
        '13' => 'Nagaland',
        '14' => 'Manipur',
        '15' => 'Mizoram',
        '16' => 'Tripura',
        '17' => 'Meghalaya',
        '18' => 'Assam',
        '19' => 'West Bengal',
        '20' => 'Jharkhand',
        '21' => 'Odisha',
        '22' => 'Chhattisgarh',
        '23' => 'Madhya Pradesh',
        '24' => 'Gujarat',
        '25' => 'Daman and Diu',
        '26' => 'Dadra and Nagar Haveli',
        '27' => 'Maharashtra',
        '28' => 'Andhra Pradesh (Old)',
        '29' => 'Karnataka',
        '30' => 'Goa',
        '31' => 'Lakshadweep',
        '32' => 'Kerala',
        '33' => 'Tamil Nadu',
        '34' => 'Puducherry',
        '35' => 'Andaman and Nicobar Islands',
        '36' => 'Telangana',
        '37' => 'Andhra Pradesh',
        '38' => 'Ladakh',
        '97' => 'Other Territory',
        '99' => 'Centre Jurisdiction',
    ];

    /**
     * Validates a 15-character Indian GSTIN.
     * Pattern: ^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$
     */
    public static function isValidGstin(?string $gstin): bool
    {
        if (blank($gstin)) {
            return false;
        }

        $gstin = strtoupper(trim($gstin));

        if (strlen($gstin) !== 15) {
            return false;
        }

        $pattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';
        if (!preg_match($pattern, $gstin)) {
            return false;
        }

        $stateCode = substr($gstin, 0, 2);

        return array_key_exists($stateCode, self::STATE_CODES);
    }

    /**
     * Extracts state code and state name from GSTIN.
     *
     * @return array{state_code: ?string, state_name: ?string}
     */
    public static function extractState(?string $gstin): array
    {
        if (blank($gstin) || strlen(trim($gstin)) < 2) {
            return ['state_code' => null, 'state_name' => null];
        }

        $code = substr(strtoupper(trim($gstin)), 0, 2);

        return [
            'state_code' => array_key_exists($code, self::STATE_CODES) ? $code : null,
            'state_name' => self::STATE_CODES[$code] ?? null,
        ];
    }
}
