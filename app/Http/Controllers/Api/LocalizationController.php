<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LocalizationController extends Controller
{
    public function getTranslations(Request $request)
    {
        try {
            $locale = $request->get('locale', 'id');
            
            // Validate locale
            if (!in_array($locale, ['id', 'en'])) {
                $locale = 'id';
            }
            
            // Set app locale
            App::setLocale($locale);
            
            // Get all translations for the requested locale
            $translations = trans('messages', [], $locale);
            
            return response()->json([
                'status' => true,
                'locale' => $locale,
                'translations' => $translations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error loading translations: ' . $e->getMessage(),
                'locale' => 'id',
                'translations' => []
            ], 500);
        }
    }
    
    public function setLocale(Request $request)
    {
        $request->validate([
            'locale' => 'required|in:id,en'
        ]);
        
        $locale = $request->get('locale');
        App::setLocale($locale);
        
        return response()->json([
            'status' => true,
            'message' => __('messages.success'),
            'locale' => $locale
        ]);
    }
}