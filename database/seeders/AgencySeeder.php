<?php

namespace Database\Seeders;

use App\Models\Agency;
use Illuminate\Database\Seeder;

class AgencySeeder extends Seeder
{
    public function run(): void
    {
        $agencies = [
            // Current institution
            [
                'name' => system_setting('system_name', 'CareNest'),
                'phone' => system_setting('phone', ''),
                'address' => system_setting('address_line_1', ''),
                'is_active' => true,
                'is_institution' => true,
            ],
            // Crisis contacts
            [
                'name' => 'Mercy Maricopa Integrated Care',
                'phone' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Adult Protective Services',
                'phone' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Bureau of Residential Facilities Licensing',
                'phone' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Poison Control',
                'phone' => '1-800-222-1222',
                'is_active' => true,
            ],
            [
                'name' => 'Emergency Services',
                'phone' => '911',
                'is_active' => true,
            ],
            [
                'name' => 'Behavioral Health Crisis Line',
                'phone' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Maricopa County Crisis Line',
                'phone' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Maricopa County Public Fiduciary',
                'phone' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Department of Behavioral Health Services',
                'phone' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Human Rights Advocacy',
                'phone' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Center for Disability Law',
                'phone' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Suicide Prevention Hotline',
                'phone' => '988',
                'is_active' => true,
            ],
        ];

        foreach ($agencies as $agency) {
            Agency::firstOrCreate(
                ['name' => $agency['name']],
                $agency
            );
        }
    }
}
