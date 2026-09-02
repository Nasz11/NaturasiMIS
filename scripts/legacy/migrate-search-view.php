<?php

echo "\n===========================================\n";
echo "  NaturasiMIS Search Fix\n";
echo "===========================================\n\n";

// ─── STEP 1: Update DashboardController to use search.index view ───
$controllerPath = __DIR__ . '/app/Http/Controllers/DashboardController.php';
$controller = file_get_contents($controllerPath);

$controller = str_replace(
    "return view('search.results', compact('q', 'inventory', 'production', 'batches'));",
    "return view('search.index', compact('q', 'inventory', 'production', 'batches'));",
    $controller
);

file_put_contents($controllerPath, $controller);
echo "[✓] Updated DashboardController to use new search view\n";

// ─── STEP 2: Update search/index.blade.php variable names ─────────
$bladePath = __DIR__ . '/resources/views/search/index.blade.php';
$blade = file_get_contents($bladePath);

// Fix variable names to match what controller sends
$blade = str_replace('$productionBatches', '$production', $blade);
$blade = str_replace('$inventoryItems',    '$inventory',  $blade);
$blade = str_replace('$query',             '$q',          $blade);

// Fix route names in data-fields (production link)
$blade = str_replace(
    "route('production.index')",
    "route('production.index')",
    $blade
);

file_put_contents($bladePath, $blade);
echo "[✓] Fixed variable names in search/index.blade.php\n";

// ─── STEP 3: Clear compiled views ─────────────────────────────────
$viewCache = __DIR__ . '/storage/framework/views';
if (is_dir($viewCache)) {
    $files = glob($viewCache . '/*.php');
    foreach ($files as $f) {
        unlink($f);
    }
    echo "[✓] Cleared view cache\n";
}

echo "\n===========================================\n";
echo "  Fix Complete! Now test your search.\n";
echo "===========================================\n\n";
