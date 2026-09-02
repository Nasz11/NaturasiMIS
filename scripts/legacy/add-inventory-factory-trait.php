<?php
$file = 'app/Models/InventoryItem.php';
$content = file_get_contents($file);
$content = str_replace(
    "class InventoryItem extends Model\r\n{",
    "class InventoryItem extends Model\r\n{\r\n    use HasFactory;",
    $content
);
file_put_contents($file, $content);
echo "Done\n";
