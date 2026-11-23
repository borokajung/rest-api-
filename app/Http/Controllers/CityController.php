<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * @api {get} /api/cities Települések listája (keresés, szűrés)
     * @apiName GetCities
     * @apiGroup Cities
     * @apiVersion 1.0.0
     * @apiDescription Lapozható településlista irányítószámmal. Kereshető névre és irányítószámra, szűrhető megyére. A szűrők kombinálhatók.
     *
     * @apiQuery {String} [search] Keresés a település nevében (részleges) vagy az irányítószámban (elejétől)
     * @apiQuery {Number} [county_id] Szűrés megye azonosító szerint
     * @apiQuery {String} [county] Szűrés megye neve szerint (részleges egyezés)
     * @apiQuery {Number{1-100}} [per_page=20] Elemek száma oldalanként
     * @apiQuery {Number} [page=1] Oldalszám
     *
     * @apiSuccess {Object[]} cities Települések
     * @apiSuccess {Number} cities.id Azonosító
     * @apiSuccess {Number} cities.county_id Megye azonosító
     * @apiSuccess {String} cities.zip_code Irányítószám
     * @apiSuccess {String} cities.name Település neve
     * @apiSuccess {Object} cities.county A megye
     * @apiSuccess {Object} meta Lapozási adatok
     * @apiSuccess {Number} meta.current_page Aktuális oldal
     * @apiSuccess {Number} meta.last_page Utolsó oldal
     * @apiSuccess {Number} meta.per_page Oldalméret
     * @apiSuccess {Number} meta.total Összes találat
     *
     * @apiExample {curl} Példa:
     *     curl "http://localhost:8000/api/cities?search=vác&county=pest"
     *
     * @apiSuccessExample {json} Sikeres válasz:
     *     HTTP/1.1 200 OK
     *     {
     *       "cities": [
     *         {
     *           "id": 52, "county_id": 14, "zip_code": "2600", "name": "Vác",
     *           "county": { "id": 14, "name": "Pest" }
     *         }
     *       ],
     *       "meta": { "current_page": 1, "last_page": 1, "per_page": 20, "total": 1 }
     *     }
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'county_id' => 'nullable|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = City::query()->with('county')->orderBy('name');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('zip_code', 'like', $search.'%');
            });
        }

        if ($countyId = $request->query('county_id')) {
            $query->where('county_id', $countyId);
        }

        if ($county = $request->query('county')) {
            $query->whereHas('county', fn ($q) => $q->where('name', 'like', '%'.$county.'%'));
        }

        $page = $query->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'cities' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    /**
     * @api {get} /api/cities/:id Egy település lekérése
     * @apiName GetCity
     * @apiGroup Cities
     * @apiVersion 1.0.0
     *
     * @apiParam {Number} id Település azonosító
     * @apiSuccess {Object} city A település a megyéjével
     * @apiError (404) {String} message A település nem található
     */
    public function show(City $city): JsonResponse
    {
        return response()->json(['city' => $city->load('county')]);
    }

    /**
     * @api {post} /api/cities Település létrehozása
     * @apiName CreateCity
     * @apiGroup Cities
     * @apiVersion 1.0.0
     * @apiPermission Auth
     *
     * @apiHeader {String} Authorization Bearer token (`Bearer <token>`)
     * @apiBody {Number} county_id Létező megye azonosítója
     * @apiBody {String{4}} zip_code Irányítószám (pontosan 4 számjegy)
     * @apiBody {String{..255}} name Település neve
     *
     * @apiSuccess (201) {Object} city Az új település
     * @apiSuccessExample {json} Sikeres válasz:
     *     HTTP/1.1 201 Created
     *     { "city": { "id": 101, "county_id": 14, "zip_code": "2600", "name": "Vác" } }
     *
     * @apiError (401) {String} message Hiányzó vagy érvénytelen token
     * @apiError (422) {String} message Validációs hiba
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'county_id' => 'required|exists:counties,id',
            'zip_code' => 'required|digits:4',
            'name' => 'required|string|max:255',
        ]);

        $city = City::create($data);

        return response()->json(['city' => $city->load('county')], 201);
    }

    /**
     * @api {put} /api/cities/:id Település módosítása
     * @apiName UpdateCity
     * @apiGroup Cities
     * @apiVersion 1.0.0
     * @apiPermission Auth
     *
     * @apiHeader {String} Authorization Bearer token (`Bearer <token>`)
     * @apiParam {Number} id Település azonosító
     * @apiBody {Number} [county_id] Új megye azonosító
     * @apiBody {String{4}} [zip_code] Új irányítószám
     * @apiBody {String{..255}} [name] Új név
     *
     * @apiSuccess {Object} city A módosított település
     * @apiError (401) {String} message Hiányzó vagy érvénytelen token
     * @apiError (404) {String} message A település nem található
     * @apiError (422) {String} message Validációs hiba
     */
    public function update(Request $request, City $city): JsonResponse
    {
        $data = $request->validate([
            'county_id' => 'sometimes|required|exists:counties,id',
            'zip_code' => 'sometimes|required|digits:4',
            'name' => 'sometimes|required|string|max:255',
        ]);

        $city->update($data);

        return response()->json(['city' => $city->load('county')]);
    }

    /**
     * @api {delete} /api/cities/:id Település törlése
     * @apiName DeleteCity
     * @apiGroup Cities
     * @apiVersion 1.0.0
     * @apiPermission Auth
     *
     * @apiHeader {String} Authorization Bearer token (`Bearer <token>`)
     * @apiParam {Number} id Település azonosító
     * @apiSuccess {String} message Visszajelzés
     * @apiSuccessExample {json} Sikeres válasz:
     *     HTTP/1.1 200 OK
     *     { "message": "City deleted" }
     *
     * @apiError (401) {String} message Hiányzó vagy érvénytelen token
     * @apiError (404) {String} message A település nem található
     */
    public function destroy(City $city): JsonResponse
    {
        $city->delete();

        return response()->json(['message' => 'City deleted']);
    }
}
