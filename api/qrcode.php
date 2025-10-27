<?php
/**
 * API - Gerar QR Code (versão standalone)
 */

// Limpar buffer
while (ob_get_level()) {
    ob_end_clean();
}

// Verificar código
$code = $_GET['code'] ?? '';

if (empty($code)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Código não fornecido']);
    exit;
}

// Gerar HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - <?= htmlspecialchars($code) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .qr-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header p { font-size: 14px; opacity: 0.9; }
        .icon { font-size: 48px; margin-bottom: 10px; }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .code-box {
            background: #f8f9fa;
            border: 4px solid #667eea;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
        }
        .code {
            font-family: "Courier New", Courier, monospace;
            font-size: 36px;
            font-weight: bold;
            color: #000;
            letter-spacing: 3px;
            word-break: break-all;
        }
        .instructions {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
            border-radius: 5px;
        }
        .instructions h3 {
            color: #0066cc;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .instructions ol { margin-left: 20px; color: #333; }
        .instructions li { margin: 8px 0; font-size: 14px; }
        .alert {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            color: #999;
            font-size: 12px;
        }
        @media print {
            body { background: white; }
            .qr-card { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="qr-card">
        <div class="header">
            <div class="icon">📦</div>
            <h1>E-Condo Packages</h1>
            <p>Sistema de Gestão de Encomendas</p>
        </div>
        
        <div class="title">Código de Retirada</div>
        
        <div class="code-box">
            <!-- QR Code escaneável -->
            <div id="qrcode" style="display: inline-block; margin-bottom: 20px;"></div>
            
            <!-- Código em texto -->
            <div class="code"><?= htmlspecialchars($code) ?></div>
        </div>
        
        <div class="instructions">
            <h3>📋 Como Retirar sua Encomenda:</h3>
            <ol>
                <li>Dirija-se à portaria do condomínio</li>
                <li>Apresente este código ao porteiro</li>
                <li>Confirme sua identidade (documento)</li>
                <li>Retire sua encomenda</li>
            </ol>
        </div>
        
        <div class="alert">
            <strong>⚠️ Importante:</strong><br>
            Guarde este código até retirar sua encomenda
        </div>
        
        <div class="footer">
            E-Condo Packages System<br>
            Código gerado em <?= date('d/m/Y H:i') ?>
        </div>
    </div>
    
    <!-- Biblioteca QRCode.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Gerar QR Code
        new QRCode(document.getElementById("qrcode"), {
            text: "<?= htmlspecialchars($code) ?>",
            width: 200,
            height: 200,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    </script>
</body>
</html>
