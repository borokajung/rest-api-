<?php

namespace App\Http\Controllers;

use App\Models\County;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CountyController extends Controller
{
    /**
     * @api {get} /api/counties Megyék listája
     * @apiName GetCounties
     * @apiGroup Counties
     * @apiVersion 1.0.0
     * @apiDescription Az összes megye név szerint rendezve, a hozzájuk tartozó települések számával. Keresés a megye nevében.
     *
     * @apiQuery {String} [search] Keresés a megye nevében (részleges egyezés)
     * @apiQuery {Boolean} [with_cities] Ha `1`/`true`, a megyékhez a települések is betöltődnek
     *
     * @apiSuccess {Object[]} counties Megyék
     * @apiSuccess {Number} counties.id Megye azonosító
     * @apiSuccess {String} counties.name Megye neve
     * @apiSuccess {Number} counties.cities_count Települések száma
     *
     * @apiSuccessExample {json} Sikeres válasz:
     *     HTTP/1.1 200 OK
     *     {
     *       "counties": [
     *         { "id": 1, "name": "Bács-Kiskun", "cities_count": 5 }
     *       ]
     *     }
     */
    public function index(Request $request): JsonResponse
    {
        $query = County::query()->withCount('cities')->orderBy('name');

        if ($search = $request->query('search')) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($request->boolean('with_cities')) {
            $query->with(['cities' => fn ($q) => $q->orderBy('name')]);
        }

        return response()->json(['counties' => $query->get()]);
    }

    /**
     * @api {get} /api/counties/:id Egy megye lekérése
     * @apiName GetCounty
     * @apiGroup Counties
     * @apiVersion 1.0.0
     * @apiDescription Egy megye adatai a hozzá tartozó településekkel.
     *
     * @apiParam {Number} id Megye azonosító
     *
     * @apiSuccess {Object} county A megye
     * @apiSuccess {Number} county.id Azonosító
     * @apiSuccess {String} county.name Név
     * @apiSuccess {Object[]} county.cities Települések
     *
     * @apiError (404) {String} message A megye nem található
     */
    public function show(County $county): JsonResponse
    {
        $county->load(['cities' => fn ($q) => $q->orderBy('name')]);

        return response()->json(['county' => $county]);
    }

    /**
     * @api {post} /api/counties Megye létrehozása
     * @apiName CreateCounty
     * @apiGroup Counties
     * @apiVersion 1.0.0
     * @apiPermission Auth
     *
     * @apiHeader {String} Authorization Bearer token (`Bearer <token>`)
     * @apiBody {String{..255}} name Megye neve (egyedi)
     *
     * @apiSuccess (201) {Object} county Az új megye
     * @apiSuccessExample {json} Sikeres válasz:
     *     HTTP/1.1 201 Created
     *     { "county": { "id": 21, "name": "Teszt megye" } }
     *
     * @apiError (401) {String} message Hiányzó vagy érvénytelen token
     * @apiError (422) {String} message Validációs hiba (pl. már létező név)
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:counties,name',
        ]);

        $county = County::create($data);

        return response()->json(['county' => $county], 201);
    }

    /**
     * @api {put} /api/counties/:id Megye módosítása
     * @apiName UpdateCounty
     * @apiGroup Counties
     * @apiVersion 1.0.0
     * @apiPermission Auth
     *
     * @apiHeader {String} Authorization Bearer token (`Bearer <token>`)
     * @apiParam {Number} id Megye azonosító
     * @apiBody {String{..255}} name Új név (egyedi)
     *
     * @apiSuccess {Object} county A módosított megye
     * @apiError (401) {String} message Hiányzó vagy érvénytelen token
     * @apiError (404) {String} message A megye nem található
     * @apiError (422) {String} message Validációs hiba
     */
    public function update(Request $request, County $county): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('counties', 'name')->ignore($county->id)],
        ]);

        $county->update($data);

        return response()->json(['county' => $county]);
    }

    /**
     * @api {delete} /api/counties/:id Megye törlése
     * @apiName DeleteCounty
     * @apiGroup Counties
     * @apiVersion 1.0.0
     * @apiPermission Auth
     * @apiDescription A megye törlésekor a hozzá tartozó települések is törlődnek (cascade).
     *
     * @apiHeader {String} Authorization Bearer token (`Bearer <token>`)
     * @apiParam {Number} id Megye azonosító
     *
     * @apiSuccess {String} message Visszajelzés
     * @apiSuccessExample {json} Sikeres válasz:
     *     HTTP/1.1 200 OK
     *     { "message": "County deleted" }
     *
     * @apiError (401) {String} message Hiányzó vagy érvénytelen token
     * @apiError (404) {String} message A megye nem található
     */
    public function destroy(County $county): JsonResponse
    {
        $county->delete();

        return response()->json(['message' => 'County deleted']);
    }
}
