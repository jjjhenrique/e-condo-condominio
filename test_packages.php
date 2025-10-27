<?php
/**
 * Teste - Listar Encomendas Cadastradas
 */

require_once 'config/config.php';

if (!isLoggedIn()) {
    echo "<h1>Você precisa estar logado</h1>";
    echo "<a href='login.php'>Fazer Login</a>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Teste - Encomendas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>🧪 Teste - Encomendas Cadastradas</h1>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Encomendas no Sistema</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    $packageModel = new Package();
                    $packages = $packageModel->findAll();
                    
                    if (empty($packages)) {
                        echo '<div class="alert alert-warning">';
                        echo '<h5>⚠️ Nenhuma encomenda cadastrada!</h5>';
                        echo '<p>Para testar o QR Code:</p>';
                        echo '<ol>';
                        echo '<li>Vá em <strong>Encomendas → Receber Encomenda</strong></li>';
                        echo '<li>Cadastre uma encomenda</li>';
                        echo '<li>Copie o código gerado</li>';
                        echo '<li>Use o código na URL do QR Code</li>';
                        echo '</ol>';
                        echo '<a href="packages/receive.php" class="btn btn-primary">Receber Encomenda</a>';
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-success">';
                        echo '<strong>✅ ' . count($packages) . ' encomenda(s) encontrada(s)</strong>';
                        echo '</div>';
                        
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-striped">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Código</th>';
                        echo '<th>Status</th>';
                        echo '<th>Recebida em</th>';
                        echo '<th>Ações</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        
                        foreach ($packages as $pkg) {
                            $statusBadge = [
                                'pendente' => '<span class="badge bg-warning">Pendente</span>',
                                'retirada' => '<span class="badge bg-success">Retirada</span>',
                                'transferida' => '<span class="badge bg-info">Transferida</span>'
                            ];
                            
                            echo '<tr>';
                            echo '<td><strong>' . htmlspecialchars($pkg['tracking_code']) . '</strong></td>';
                            echo '<td>' . ($statusBadge[$pkg['status']] ?? $pkg['status']) . '</td>';
                            echo '<td>' . date('d/m/Y H:i', strtotime($pkg['received_at'])) . '</td>';
                            echo '<td>';
                            echo '<a href="api/qrcode.php?code=' . urlencode($pkg['tracking_code']) . '" class="btn btn-sm btn-primary" target="_blank">';
                            echo '<i class="bi bi-qr-code"></i> Ver QR Code';
                            echo '</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                        
                        echo '<div class="alert alert-info mt-3">';
                        echo '<h6>📱 Como Testar:</h6>';
                        echo '<ol>';
                        echo '<li>Clique em "Ver QR Code" em qualquer encomenda acima</li>';
                        echo '<li>Você verá a página pública com o QR Code</li>';
                        echo '<li>Essa é a mesma página que o condômino verá pelo WhatsApp</li>';
                        echo '</ol>';
                        echo '</div>';
                    }
                    
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">';
                    echo '<strong>Erro:</strong> ' . $e->getMessage();
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="packages/receive.php" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Receber Nova Encomenda
            </a>
            <a href="packages/list.php" class="btn btn-primary">
                <i class="bi bi-list"></i> Listar Encomendas
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-house"></i> Dashboard
            </a>
        </div>
    </div>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</body>
</html>
