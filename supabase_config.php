<?php
// Fill these with your Supabase project details
// Get them from: https://app.supabase.com/project/_/settings/api

$SUPABASE_URL = getenv('SUPABASE_URL') ?: '';
$SUPABASE_ANON_KEY = getenv('SUPABASE_ANON_KEY') ?: '';

// Helper to echo config as JS vars safely
function supabase_js_config(): string {
    global $SUPABASE_URL, $SUPABASE_ANON_KEY;
    $url = htmlspecialchars($SUPABASE_URL, ENT_QUOTES);
    $key = htmlspecialchars($SUPABASE_ANON_KEY, ENT_QUOTES);
    return "window.__SUPABASE_URL='$url';window.__SUPABASE_ANON_KEY='$key';";
}




