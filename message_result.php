<?php
// messages_result.php - Displays the contents of data/messages.json in a clean HTML table

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function is_assoc_array($arr) {
    if (!is_array($arr)) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function pick_rows_array($data) {
    if (!is_array($data)) {
        return [$data];
    }

    if (is_assoc_array($data)) {
        foreach (['messages','data','list','records','results','result','entries'] as $k) {
            if (isset($data[$k]) && is_array($data[$k])) {
                return pick_rows_array($data[$k]);
            }
        }
        return [$data];
    }

    return $data;
}

function flatten_value($v) {
    if (is_array($v) || is_object($v)) {
        $json = json_encode($v, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return '<pre class="cell-pre">' . h($json) . '</pre>';
    }
    if ($v === null) return 'null';
    if (is_bool($v)) return $v ? 'true' : 'false';
    return h((string)$v);
}

$filename = __DIR__ . '/data/messages.json';
$error = null;
$data = null;

if (!file_exists($filename)) {
    $error = 'File not found: data/messages.json';
} else {
    $raw = @file_get_contents($filename);
    if ($raw === false) {
        $error = 'Unable to read data/messages.json';
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
    <title>Messages Results</title>
    <style>
        :root {
            color-scheme: light dark;
        }
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
            margin: 0;
            background: #f7f7f9;
            color: #1f2328;
        }
        header {
            padding: 1rem 1.25rem;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            margin: 0;
            font-size: 1.25rem;
        }
        main {
            padding: 1.25rem;
        }
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        thead th {
            position: sticky;
            top: 0;
            background: #fafafa;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.5rem 0.6rem;
            white-space: nowrap;
        }
        tbody td {
            border-bottom: 1px solid #f0f0f0;
            padding: 0.5rem 0.6rem;
            vertical-align: top;
        }
        .muted {
            color: #6b7280;
        }
        .error {
            color: #b91c1c;
            background: #fee2e2;
            border: 1px solid #fecaca;
            padding: 0.75rem 1rem;
            border-radius: 8px;
        }
        .cell-pre {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .toolbar {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        .toolbar a, .toolbar button {
            background: #111827;
            color: #fff;
            border: 0;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .toolbar .secondary {
            background: #6b7280;
        }
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
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Messages Results</h1>
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
                        $rows = pick_rows_array($data);

                        // Compute headers from union of object keys when possible
                        $headers = [];
                        $use_single_value_col = false;
                        foreach ($rows as $row) {
                            if (is_array($row) && is_assoc_array($row)) {
                                $headers = array_values(array_unique(array_merge($headers, array_keys($row))));
                            } else {
                                $use_single_value_col = true;
                                $headers = ['value'];
                                break;
                            }
                        }

                        if (empty($rows)) {
                            echo '<p class="muted">No data found in messages.json.</p>';
                        } else {
                            echo '<table>';
                            echo '<thead><tr>';
                            foreach ($headers as $col) echo '<th>' . h($col) . '</th>';
                            echo '</tr></thead>';
                            echo '<tbody>';

                            foreach ($rows as $row) {
                                echo '<tr>';
                                if ($use_single_value_col) {
                                    echo '<td>' . flatten_value($row) . '</td>';
                                } else {
                                    foreach ($headers as $col) {
                                        $val = (is_array($row) && array_key_exists($col, $row)) ? $row[$col] : '';
                                        echo '<td>' . flatten_value($val) . '</td>';
                                    }
                                }
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
