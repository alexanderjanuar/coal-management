<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{

    protected $companies = [
        [
            'name' => 'PT Adaro Energy Indonesia',
            'email' => 'contact@adaro.com'
        ],
        [
            'name' => 'PT Bumi Resources',
            'email' => 'info@bumiresources.com'
        ],
        [
            'name' => 'PT Indo Tambangraya Megah',
            'email' => 'contact@banpu.co.id' 
        ],
        [
            'name' => 'PT Berau Coal Energy',
            'email' => 'info@beraucoal.co.id'
        ],
        [
            'name' => 'PT Bukit Asam',
            'email' => 'corsec@bukitasam.co.id'
        ],
        [
            'name' => 'PT Indika Energy',
            'email' => 'contact@indikaenergy.co.id'
        ],
        [
            'name' => 'PT Bayan Resources',
            'email' => 'info@bayan.com.sg'
        ],
        [
            'name' => 'PT Golden Energy Mines',
            'email' => 'corsec@goldenenergymines.com'
        ],
        [
            'name' => 'PT Resources Alam Indonesia',
            'email' => 'info@raintbk.com'
        ],
        [
            'name' => 'PT Delta Dunia Makmur',
            'email' => 'ir@deltadunia.com'
        ],
        [
            'name' => 'PT Toba Bara Sejahtra',
            'email' => 'corsec@tobabara.com'
        ],
        [
            'name' => 'PT Harum Energy',
            'email' => 'corsec@harumenergy.com'
        ]
    ];
 
    public function definition(): array
    {
        $company = $this->faker->unique()->randomElement($this->companies);
        return [
            'name' => $company['name'],
            'email' => $company['email'],
        ];
    }
}
