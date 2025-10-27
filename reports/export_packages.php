<?php
/**
 * E-Condo Packages - Exportar Encomendas para Excel
 */

require_once '../config/config.php';

// Verificar autenticação
if (!isLoggedIn()) {
    die('Acesso negado');
}

$packageModel = new Package();

// Obter filtros
$filters = [
    'tracking_code' => $_GET['tracking_code'] ?? '',
    'resident_name' => $_GET['resident_name'] ?? '',
    'village_id' => $_GET['village_id'] ?? '',
    'current_location' => $_GET['location'] ?? $_GET['current_location'] ?? '',
    'status' => $_GET['status'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
];

// Remover filtros vazios
$filters = array_filter($filters, function($value) {
    return $value !== '';
});

// Buscar encomendas
if (!empty($filters)) {
    $packages = $packageModel->search($filters);
} else {
    $packages = $packageModel->getAllWithFullInfo();
}

// Gerar arquivo CSV
$filename = 'encomendas_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Abrir output
$output = fopen('php://output', 'w');

// BOM para UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalhos
$headers = [
    'Código',
    'Condômino',
    'CPF',
    'Telefone',
    'Village',
    'Casa',
    'Localização',
    'Status',
    'Recebida em',
    'Transferida em',
    'Retirada em',
    'Recebida por',
    'Transferida por',
    'Retirada por',
    'Observações'
];

fputcsv($output, $headers, ';');

// Dados
foreach ($packages as $package) {
    $row = [
        $package['tracking_code'],
        $package['resident_name'],
        $package['resident_cpf'],
        $package['resident_phone'],
        $package['village_name'],
        $package['house_number'],
        ucfirst($package['current_location']),
        ucfirst($package['status']),
        formatDateBR($package['received_at']),
        $package['transferred_at'] ? formatDateBR($package['transferred_at']) : '',
        $package['picked_up_at'] ? formatDateBR($package['picked_up_at']) : '',
        $package['received_by_name'] ?? '',
        $package['transferred_by_name'] ?? '',
        $package['picked_up_by_name'] ?? '',
        $package['observations'] ?? ''
    ];
    
    fputcsv($output, $row, ';');
}

fclose($output);
exit;
