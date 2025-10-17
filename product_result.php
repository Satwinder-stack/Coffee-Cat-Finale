<?php

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function build_pricing_string($item) {
    if (isset($item['pricing']) && is_array($item['pricing'])) {
        $parts = [];
        foreach ($item['pricing'] as $p) {
            if (!is_array($p)) continue;
            $variant = isset($p['variant']) ? trim((string)$p['variant']) : '';
            $price = isset($p['price']) ? $p['price'] : '';
            if ($variant !== '') {
                $parts[] = ($variant !== '' ? $variant . ': ' : '') . $price;
            } else {
                $parts[] = (string)$price;
            }
        }
        return implode(', ', $parts);
    }
    if (isset($item['price'])) {
        return (string)$item['price'];
    }
    return '';
}

function flatten_products($data) {
    $rows = [];
    if (!is_array($data) || !isset($data['categories']) || !is_array($data['categories'])) {
        return $rows;
    }

    foreach ($data['categories'] as $cat) {
        $categoryName = isset($cat['name']) ? (string)$cat['name'] : '';

        if (isset($cat['items']) && is_array($cat['items'])) {
            foreach ($cat['items'] as $item) {
                $rows[] = [
                    'category' => $categoryName,
                    'subcategory' => '',
                    'id' => isset($item['id']) ? (string)$item['id'] : '',
                    'name' => isset($item['name']) ? (string)$item['name'] : '',
                    'description' => isset($item['description']) ? (string)$item['description'] : '',
                    'pricing' => build_pricing_string($item),
                    'image' => isset($item['image']) ? (string)$item['image'] : '',
                    'alt' => isset($item['alt']) ? (string)$item['alt'] : '',
                ];
            }
        }

        if (isset($cat['subcategories']) && is_array($cat['subcategories'])) {
            foreach ($cat['subcategories'] as $sub) {
                $subName = isset($sub['name']) ? (string)$sub['name'] : '';
                if (isset($sub['items']) && is_array($sub['items'])) {
                    foreach ($sub['items'] as $item) {
                        $rows[] = [
                            'category' => $categoryName,
                            'subcategory' => $subName,
                            'id' => isset($item['id']) ? (string)$item['id'] : '',
                            'name' => isset($item['name']) ? (string)$item['name'] : '',
                            'description' => isset($item['description']) ? (string)$item['description'] : '',
                            'pricing' => build_pricing_string($item),
                            'image' => isset($item['image']) ? (string)$item['image'] : '',
                            'alt' => isset($item['alt']) ? (string)$item['alt'] : '',
                        ];
                    }
                }
            }
        }
    }

    return $rows;
}

$filename = __DIR__ . '/data/products.json';
$error = null;
$data = null;

if (!file_exists($filename)) {
    $error = 'File not found: data/products.json';
} else {
    $raw = @file_get_contents($filename);
    if ($raw === false) {
        $error = 'Unable to read data/products.json';
    } else {
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = 'Invalid JSON: ' . json_last_error_msg();
        }
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Product Results</title>
    <style>
        :root { color-scheme: light dark; }
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
            margin: 0; background: #f7f7f9; color: #1f2328;
        }
        header { padding: 1rem 1.25rem; background: #fff; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; z-index: 10; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { margin: 0; font-size: 1.25rem; }
        main { padding: 1.25rem; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
        thead th { position: sticky; top: 0; background: #fafafa; text-align: left; border-bottom: 1px solid #e5e7eb; padding: 0.5rem 0.6rem; white-space: nowrap; }
        tbody td { border-bottom: 1px solid #f0f0f0; padding: 0.5rem 0.6rem; vertical-align: top; }
        .muted { color: #6b7280; }
        .error { color: #b91c1c; background: #fee2e2; border: 1px solid #fecaca; padding: 0.75rem 1rem; border-radius: 8px; }
        .cell-pre { margin: 0; white-space: pre-wrap; word-break: break-word; }
        .toolbar { display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.75rem; }
        .toolbar a, .toolbar button { background: #111827; color: #fff; border: 0; padding: 0.5rem 0.75rem; border-radius: 6px; text-decoration: none; cursor: pointer; font-size: 0.9rem; }
        .toolbar .secondary { background: #6b7280; }
        @media (prefers-color-scheme: dark) {
            body { background: #0b0f15; color: #e5e7eb; }
            header { background: #0f1720; border-color: #1f2937; }
            .card { background: #0f1720; border-color: #1f2937; }
            thead th { background: #0b111a; border-color: #1f2937; }
            tbody td { border-color: #1f2937; }
            .error { background: #2a0f0f; border-color: #7f1d1d; color: #fecaca; }
            .toolbar a, .toolbar button { background: #374151; }
            .toolbar .secondary { background: #4b5563; }
        }
        .truncate { max-width: 420px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Product Results</h1>
        </div>
    </header>
    <main>
        <div class="container">
            <?php if ($error): ?>
                <div class="error"><?php echo h($error); ?></div>
            <?php else: ?>
                <div class="card">
                    <div class="toolbar">
                        <a href="index.html" class="secondary">Home</a>
                        <a href="menu.html" class="secondary">Menu</a>
                    </div>
                    <?php
                        $rows = flatten_products($data);

                        if (empty($rows)) {
                            // Fallback: show raw JSON if structure is unexpected
                            echo '<p class="muted">No products found. Showing raw data for debugging.</p>';
                            echo '<table><thead><tr><th>value</th></tr></thead><tbody>';
                            echo '<tr><td><pre class="cell-pre">' . h(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . '</pre></td></tr>';
                            echo '</tbody></table>';
                        } else {
                            echo '<table>';
                            echo '<thead><tr>';
                            $headers = ['Category','Subcategory','ID','Name','Description','Pricing','Image','Alt'];
                            foreach ($headers as $col) echo '<th>' . h($col) . '</th>';
                            echo '</tr></thead>';
                            echo '<tbody>';
                            foreach ($rows as $r) {
                                echo '<tr>';
                                echo '<td>' . h($r['category']) . '</td>';
                                echo '<td>' . h($r['subcategory']) . '</td>';
                                echo '<td>' . h($r['id']) . '</td>';
                                echo '<td>' . h($r['name']) . '</td>';
                                echo '<td class="truncate" title="' . h($r['description']) . '">' . h($r['description']) . '</td>';
                                echo '<td>' . h($r['pricing']) . '</td>';
                                echo '<td>' . h($r['image']) . '</td>';
                                echo '<td>' . h($r['alt']) . '</td>';
                                echo '</tr>';
                            }
                            echo '</tbody>';
                            echo '</table>';
                        }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
