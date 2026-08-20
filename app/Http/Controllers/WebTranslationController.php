<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebTranslationController extends Controller
{
    private string $jsonPath;
    private array  $locales = ['ta', 'hi'];
    private array  $localeNames = ['ta' => 'Tamil', 'hi' => 'Hindi'];

    public function __construct()
    {
        $this->jsonPath = public_path('translations/');
    }

    public function index()
    {
        $en = $this->loadJson('en');
        $translations = [];
        foreach ($en as $key => $val) {
            $row = ['key' => $key, 'en' => $key];
            foreach ($this->locales as $locale) {
                $dict        = $this->loadJson($locale);
                $row[$locale] = $dict[$key] ?? '';
            }
            $translations[] = $row;
        }
        $localeNames = $this->localeNames;
        return view('web-translations.index', compact('translations', 'localeNames'));
    }

    public function update(Request $request)
    {
        $key    = $request->key;
        $locale = $request->locale;
        $value  = $request->value;
        if (!in_array($locale, $this->locales)) {
            return response()->json(['error'=>true,'message'=>'Invalid locale']);
        }
        $dict[$key] = $value;
        $dict = $this->loadJson($locale);
        $dict[$key] = $value;
        $this->saveJson($locale, $dict);
        return response()->json(['error'=>false,'message'=>'Saved!']);
    }

    public function addKey(Request $request)
    {
        $key = trim($request->key);
        if (!$key) return response()->json(['error'=>true,'message'=>'Key required']);
        $en = $this->loadJson('en');
        $en[$key] = $key;
        $this->saveJson('en', $en);
        foreach ($this->locales as $locale) {
            $dict = $this->loadJson($locale);
            if (!isset($dict[$key])) { $dict[$key] = ''; $this->saveJson($locale, $dict); }
        }
        return response()->json(['error'=>false,'message'=>'Key added!']);
    }

    public function deleteKey(Request $request)
    {
        $key = $request->key;
        foreach (array_merge(['en'], $this->locales) as $locale) {
            $dict = $this->loadJson($locale);
            unset($dict[$key]);
            $this->saveJson($locale, $dict);
        }
        return response()->json(['error'=>false,'message'=>'Key deleted!']);
    }

    private function loadJson(string $locale): array
    {
        $path = $this->jsonPath . $locale . '.json';
        if (!file_exists($path)) return [];
        return json_decode(file_get_contents($path), true) ?? [];
    }

    private function saveJson(string $locale, array $data): void
    {
        $path = $this->jsonPath . $locale . '.json';
        if (!is_dir(dirname($path))) @mkdir(dirname($path), 0755, true);
        file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
