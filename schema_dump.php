<?php

$tables = ['bookings', 'guests', 'rooms', 'room_types', 'transactions', 'requested_items', 'room_services', 'users', 'staff'];
foreach($tables as $t) {
    echo "\n--- Table: $t ---\n";
    $cols = Schema::getColumns($t);
    foreach($cols as $c) {
        echo str_pad($c['name'], 25) . ' | ' . str_pad($c['type_name'], 15) . ' | ' . ($c['nullable'] ? 'NULL' : 'NOT NULL') . "\n";
    }
    echo "\nIndexes:\n";
    $idx = Schema::getIndexes($t);
    foreach($idx as $i) {
        echo '- ' . $i['name'] . ' [' . implode(', ', $i['columns']) . ']' . ($i['unique'] ? ' (UNIQUE)' : '') . "\n";
    }
    echo "\nForeign Keys:\n";
    $fk = Schema::getForeignKeys($t);
    foreach($fk as $f) {
        echo '- ' . implode(', ', $f['columns']) . ' -> ' . $f['foreign_table'] . '.' . implode(', ', $f['foreign_columns']) . "\n";
    }
}
