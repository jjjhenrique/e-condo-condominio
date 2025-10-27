<?php
/**
 * Visualização Pública de QR Code
 * Permite que o condômino visualize o QR Code sem fazer login
 */

// Carregar configuração
require_once dirname(__DIR__) . '/config/config.php';

$code = $_GET['code'] ?? '';

if (empty($code)) {
    http_response_code(400);
    die('Código não fornecido');
}

// Buscar informações da encomenda (sem expor dados sensíveis)
try {
    $packageModel = new Package();
    $residentModel = new Resident();
    
    $package = $packageModel->findByTrackingCode($code);
    
    if (!$package) {
        $packageExists = false;
    } else {
        $packageExists = true;
        
        // Buscar informações do condômino
        $resident = $residentModel->findById($package['resident_id']);
        $residentName = $resident ? explode(' ', $resident['full_name'])[0] : 'Condômino';
        
        $status = $package['status'];
        $receivedDate = date('d/m/Y', strtotime($package['received_at']));
    }
} catch (Exception $e) {
    $packageExists = false;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - <?= htmlspecialchars($code) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .qr-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }
        .qr-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .qr-body {
            padding: 40px;
            text-align: center;
        }
        .qr-code-container {
            background: #f8f9fa;
            border: 3px solid #667eea;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }
        .tracking-code {
            font-size: 2rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            color: #667eea;
            letter-spacing: 3px;
            margin: 20px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: bold;
            margin: 10px 0;
        }
        .status-pendente {
            background: #fff3cd;
            color: #856404;
        }
        .status-retirada {
            background: #d4edda;
            color: #155724;
        }
        .instructions {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="qr-card">
        <div class="qr-header">
            <h1 class="mb-0"><i class="bi bi-box-seam"></i> E-Condo Packages</h1>
            <p class="mb-0 mt-2">Sistema de Gestão de Encomendas</p>
        </div>
        
        <div class="qr-body">
            <?php if ($packageExists): ?>
                <h2 class="mb-3">Olá, <?= htmlspecialchars($residentName) ?>!</h2>
                
                <div class="status-badge status-<?= $status ?>">
                    <?php if ($status === 'pendente'): ?>
                        <i class="bi bi-clock"></i> Aguardando Retirada
                    <?php elseif ($status === 'retirada'): ?>
                        <i class="bi bi-check-circle"></i> Já Retirada
                    <?php else: ?>
                        <i class="bi bi-info-circle"></i> <?= ucfirst($status) ?>
                    <?php endif; ?>
                </div>
                
                <div class="qr-code-container">
                    <p class="mb-2"><strong>Código de Retirada:</strong></p>
                    <div class="tracking-code"><?= htmlspecialchars($code) ?></div>
                    
                    <!-- QR Code SVG -->
                    <img src="<?= SITE_URL ?>/api/generate_qrcode.php?code=<?= urlencode($code) ?>&size=300" 
                         alt="QR Code" 
                         class="img-fluid mt-3"
                         style="max-width: 300px;"
                         onerror="this.style.display='none'; document.getElementById('qr-error').style.display='block';">
                    
                    <div id="qr-error" style="display:none;" class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Não foi possível carregar o QR Code visual.<br>
                        Use o código acima para retirar sua encomenda.
                    </div>
                </div>
                
                <?php if ($status === 'pendente'): ?>
                <div class="instructions">
                    <h6><i class="bi bi-info-circle"></i> Como Retirar:</h6>
                    <ol class="mb-0">
                        <li>Dirija-se à portaria</li>
                        <li>Apresente este código ou QR Code</li>
                        <li>Confirme sua identidade</li>
                        <li>Retire sua encomenda</li>
                    </ol>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-calendar"></i> Recebida em: <strong><?= $receivedDate ?></strong>
                </div>
                <?php else: ?>
                <div class="alert alert-success mt-3">
                    <i class="bi bi-check-circle"></i> Esta encomenda já foi retirada.
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                    <button onclick="shareQRCode()" class="btn btn-outline-primary">
                        <i class="bi bi-share"></i> Compartilhar
                    </button>
                </div>
                
            <?php else: ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <h5>Código Não Encontrado</h5>
                    <p class="mb-0">O código <strong><?= htmlspecialchars($code) ?></strong> não foi encontrado no sistema.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center pb-4">
            <small class="text-muted">
                <i class="bi bi-shield-check"></i> Página segura e verificada
            </small>
        </div>
    </div>
    
    <script>
    function shareQRCode() {
        if (navigator.share) {
            navigator.share({
                title: 'QR Code - E-Condo',
                text: 'Código de retirada: <?= htmlspecialchars($code) ?>',
                url: window.location.href
            }).catch(err => console.log('Erro ao compartilhar:', err));
        } else {
            // Fallback: copiar link
            navigator.clipboard.writeText(window.location.href);
            alert('Link copiado para a área de transferência!');
        }
    }
    </script>
</body>
</html>
