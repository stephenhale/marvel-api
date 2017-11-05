<?php
namespace App\Http\Controllers;

use Cache;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use GuzzleHttp\Client;

class HomeController extends Controller
{
    protected $client;

    public function __construct() {
        $ts = time();
        $hash = md5($ts . config('marvel.private_key') . config('marvel.public_key'));

        $this->client = new Client([
            'base_uri' => config('marvel.endpoint'),
            'query' => [
                'ts' => $ts,
                'hash' => $hash,
                'apikey' => config('marvel.public_key')
            ]
        ]);
    }

    public function comic(Request $request, $id)
    {
        $page_data = [];
        $response = $this->client->get('comics/' . $id);
        $response = json_decode($response->getBody(), true);

        $comic = $response['data']['results'][0];
        $page_data['comic'] = $comic;

        if (!empty($comic['series'])) {
            $series_response = $this->client->get($comic['series']['resourceURI']);
            $series_response = json_decode($series_response->getBody(), true);
            $page_data['series'] = $series_response['data']['results'][0];
        }

        return view('comic', $page_data);
    }

    public function comics(Request $request)
    {
        $search_term = '';
        if ($request->has('query')) {
            $search_term = $request->input('query');
            $query = $this->client->getConfig('query');
            $query['titleStartsWith'] = $search_term;

            $response = $this->client->get('comics', ['query' => $query]);
            $response = json_decode($response->getBody(), true);
            $comics = $response['data']['results'];
        } else {
            $comics = Cache::get('comics') ?? [];
            shuffle($comics);
            $comics = array_slice($comics, 0, 20);
        }

        return view('comics', [
            'comics' => $comics,
            'query' => $search_term
        ]);
    }

    public function characters(Request $request)
    {
        $characters = Cache::get('characters');
        $current_page = LengthAwarePaginator::resolveCurrentPage() ?? 1;
        $items_per_page = 8;

        $characters_collection = new Collection($characters);
        $current_page_results = $characters_collection->slice(($current_page - 1) * $items_per_page, $items_per_page)->all();
        $paginated_results = new LengthAwarePaginator($current_page_results, count($characters_collection), $items_per_page);

        return view('characters', [
            'paginated_results' => $paginated_results,
            'characters' => $characters
        ]);
    }
}
