<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\County;
use Illuminate\Database\Seeder;

/**
 * Megyék + minta települések irányítószámmal (nagyobb városok, megyeszékhelyek).
 * A teljes országos lista CSV-ből külön importálható, ez a feladathoz elégséges mintaadat.
 */
class CountyCitySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Bács-Kiskun' => ['6000' => 'Kecskemét', '6500' => 'Baja', '6100' => 'Kiskunfélegyháza', '6300' => 'Kalocsa', '6400' => 'Kiskunhalas'],
            'Baranya' => ['7621' => 'Pécs', '7700' => 'Mohács', '7900' => 'Szigetvár', '7300' => 'Komló', '7800' => 'Siklós'],
            'Békés' => ['5600' => 'Békéscsaba', '5700' => 'Gyula', '5900' => 'Orosháza', '5540' => 'Szarvas', '5630' => 'Békés'],
            'Borsod-Abaúj-Zemplén' => ['3525' => 'Miskolc', '3600' => 'Ózd', '3700' => 'Kazincbarcika', '3950' => 'Sárospatak', '3980' => 'Sátoraljaújhely', '3400' => 'Mezőkövesd'],
            'Budapest' => ['1011' => 'Budapest I. kerület', '1051' => 'Budapest V. kerület', '1061' => 'Budapest VI. kerület', '1071' => 'Budapest VII. kerület', '1111' => 'Budapest XI. kerület', '1131' => 'Budapest XIII. kerület', '1141' => 'Budapest XIV. kerület'],
            'Csongrád-Csanád' => ['6720' => 'Szeged', '6800' => 'Hódmezővásárhely', '6600' => 'Szentes', '6900' => 'Makó', '6640' => 'Csongrád'],
            'Fejér' => ['8000' => 'Székesfehérvár', '2400' => 'Dunaújváros', '8060' => 'Mór', '2060' => 'Bicske', '2483' => 'Gárdony'],
            'Győr-Moson-Sopron' => ['9021' => 'Győr', '9400' => 'Sopron', '9200' => 'Mosonmagyaróvár', '9300' => 'Csorna', '9330' => 'Kapuvár'],
            'Hajdú-Bihar' => ['4024' => 'Debrecen', '4200' => 'Hajdúszoboszló', '4220' => 'Hajdúböszörmény', '4100' => 'Berettyóújfalu', '4150' => 'Püspökladány'],
            'Heves' => ['3300' => 'Eger', '3200' => 'Gyöngyös', '3000' => 'Hatvan', '3360' => 'Heves'],
            'Jász-Nagykun-Szolnok' => ['5000' => 'Szolnok', '5100' => 'Jászberény', '5300' => 'Karcag', '5200' => 'Törökszentmiklós', '5400' => 'Mezőtúr'],
            'Komárom-Esztergom' => ['2800' => 'Tatabánya', '2500' => 'Esztergom', '2890' => 'Tata', '2900' => 'Komárom', '2840' => 'Oroszlány'],
            'Nógrád' => ['3100' => 'Salgótarján', '2660' => 'Balassagyarmat', '3060' => 'Pásztó', '3070' => 'Bátonyterenye'],
            'Pest' => ['2000' => 'Szentendre', '2700' => 'Cegléd', '2600' => 'Vác', '2100' => 'Gödöllő', '2120' => 'Dunakeszi', '2030' => 'Érd', '2310' => 'Szigetszentmiklós', '2040' => 'Budaörs'],
            'Somogy' => ['7400' => 'Kaposvár', '8600' => 'Siófok', '7500' => 'Nagyatád', '8700' => 'Marcali', '8630' => 'Balatonboglár'],
            'Szabolcs-Szatmár-Bereg' => ['4400' => 'Nyíregyháza', '4700' => 'Mátészalka', '4600' => 'Kisvárda', '4320' => 'Nagykálló', '4440' => 'Tiszavasvári'],
            'Tolna' => ['7100' => 'Szekszárd', '7030' => 'Paks', '7200' => 'Dombóvár', '7090' => 'Tamási', '7150' => 'Bonyhád'],
            'Vas' => ['9700' => 'Szombathely', '9730' => 'Kőszeg', '9600' => 'Sárvár', '9900' => 'Körmend', '9500' => 'Celldömölk'],
            'Veszprém' => ['8200' => 'Veszprém', '8500' => 'Pápa', '8400' => 'Ajka', '8300' => 'Tapolca', '8230' => 'Balatonfüred'],
            'Zala' => ['8900' => 'Zalaegerszeg', '8800' => 'Nagykanizsa', '8360' => 'Keszthely', '8960' => 'Lenti', '8790' => 'Zalaszentgrót'],
        ];

        foreach ($data as $countyName => $cities) {
            $county = County::create(['name' => $countyName]);

            foreach ($cities as $zip => $name) {
                City::create([
                    'county_id' => $county->id,
                    'zip_code' => (string) $zip,
                    'name' => $name,
                ]);
            }
        }
    }
}
