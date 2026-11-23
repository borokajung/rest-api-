<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\County;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::factory()->create([
            'email' => 'user@example.com',
            'password' => '12345678',
        ]);
    }

    private function authHeaders(): array
    {
        $this->makeUser();
        $token = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => '12345678',
        ])->json('token');

        return ['Authorization' => 'Bearer '.$token];
    }

    public function test_login_returns_token(): void
    {
        $this->makeUser();

        $this->postJson('/api/login', ['email' => 'user@example.com', 'password' => '12345678'])
            ->assertOk()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $this->makeUser();

        $this->postJson('/api/login', ['email' => 'user@example.com', 'password' => 'rossz'])
            ->assertStatus(401);
    }

    public function test_listing_is_public(): void
    {
        $pest = County::create(['name' => 'Pest']);
        City::create(['county_id' => $pest->id, 'zip_code' => '2600', 'name' => 'Vác']);

        $this->getJson('/api/counties')->assertOk()->assertJsonCount(1, 'counties');
        $this->getJson('/api/cities')->assertOk()->assertJsonCount(1, 'cities');
    }

    public function test_city_search_by_name_and_zip(): void
    {
        $pest = County::create(['name' => 'Pest']);
        City::create(['county_id' => $pest->id, 'zip_code' => '2600', 'name' => 'Vác']);
        City::create(['county_id' => $pest->id, 'zip_code' => '2700', 'name' => 'Cegléd']);

        $this->getJson('/api/cities?search=vác')->assertOk()->assertJsonCount(1, 'cities')
            ->assertJsonPath('cities.0.name', 'Vác');

        $this->getJson('/api/cities?search=27')->assertOk()->assertJsonCount(1, 'cities')
            ->assertJsonPath('cities.0.name', 'Cegléd');
    }

    public function test_city_filter_by_county(): void
    {
        $pest = County::create(['name' => 'Pest']);
        $heves = County::create(['name' => 'Heves']);
        City::create(['county_id' => $pest->id, 'zip_code' => '2600', 'name' => 'Vác']);
        City::create(['county_id' => $heves->id, 'zip_code' => '3300', 'name' => 'Eger']);

        $this->getJson('/api/cities?county_id='.$heves->id)->assertOk()
            ->assertJsonCount(1, 'cities')->assertJsonPath('cities.0.name', 'Eger');

        $this->getJson('/api/cities?county=pest')->assertOk()
            ->assertJsonCount(1, 'cities')->assertJsonPath('cities.0.name', 'Vác');
    }

    public function test_modifying_requires_token(): void
    {
        $this->postJson('/api/counties', ['name' => 'Teszt'])->assertStatus(401);
        $this->putJson('/api/counties/1', ['name' => 'Teszt'])->assertStatus(401);
        $this->deleteJson('/api/counties/1')->assertStatus(401);
        $this->postJson('/api/cities', [])->assertStatus(401);
    }

    public function test_crud_with_token(): void
    {
        $headers = $this->authHeaders();

        $countyId = $this->postJson('/api/counties', ['name' => 'Pest'], $headers)
            ->assertCreated()->json('county.id');

        $cityId = $this->postJson('/api/cities', [
            'county_id' => $countyId, 'zip_code' => '2600', 'name' => 'Vác',
        ], $headers)->assertCreated()->json('city.id');

        $this->putJson('/api/cities/'.$cityId, ['name' => 'Vác (módosítva)'], $headers)
            ->assertOk()->assertJsonPath('city.name', 'Vác (módosítva)');

        $this->deleteJson('/api/cities/'.$cityId, [], $headers)->assertOk();
        $this->deleteJson('/api/counties/'.$countyId, [], $headers)->assertOk();
        $this->getJson('/api/cities/'.$cityId)->assertNotFound();
    }

    public function test_validation_errors(): void
    {
        $headers = $this->authHeaders();
        $county = County::create(['name' => 'Pest']);

        $this->postJson('/api/cities', [
            'county_id' => $county->id, 'zip_code' => '12', 'name' => 'Vác',
        ], $headers)->assertStatus(422)->assertJsonValidationErrors('zip_code');

        $this->postJson('/api/counties', ['name' => 'Pest'], $headers)
            ->assertStatus(422)->assertJsonValidationErrors('name');
    }

    public function test_logout_invalidates_token(): void
    {
        $headers = $this->authHeaders();

        $this->postJson('/api/logout', [], $headers)->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
