<?php
/**
 * API - Gerar QR Code para código de retirada
 */

// Desabilitar output de erros para não corromper a imagem
error_reporting(0);
ini_set('display_errors', 0);

// Limpar qualquer output anterior
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

require_once '../config/config.php';

// Verificar se o código foi fornecido
$code = $_GET['code'] ?? '';

if (empty($code)) {
    ob_end_clean();
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Código não fornecido']);
    exit;
}

// Configurações do QR Code
$size = $_GET['size'] ?? 400; // Tamanho padrão 400x400
$size = min(max($size, 200), 1000); // Limitar entre 200 e 1000
$format = $_GET['format'] ?? 'image'; // 'image', 'url' ou 'base64'

try {
    // Tentar usar API do Google Charts primeiro
    $qrCodeUrl = "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl=" . urlencode($code) . "&choe=UTF-8&chld=M|2";
    
    // Configurar contexto para permitir conexões SSL
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $imageData = @file_get_contents($qrCodeUrl, false, $context);
    
    // Se falhar, gerar SVG localmente
    if ($imageData === false) {
        $imageData = generateQRCodeSVG($code, $size);
        $isSVG = true;
    } else {
        $isSVG = false;
    }
    
    if ($format === 'url') {
        // Retornar URL
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'qrcode_url' => $isSVG ? (SITE_URL . '/api/generate_qrcode.php?code=' . urlencode($code)) : $qrCodeUrl,
            'code' => $code,
            'type' => $isSVG ? 'svg' : 'png'
        ]);
    } elseif ($format === 'base64') {
        // Converter para base64
        $mimeType = $isSVG ? 'image/svg+xml' : 'image/png';
        $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'qrcode_base64' => $base64,
            'code' => $code,
            'type' => $isSVG ? 'svg' : 'png'
        ]);
    } else {
        // Limpar buffer antes de enviar imagem
        ob_end_clean();
        
        // Sempre gerar HTML com o código visual (mais compatível)
        header('Content-Type: text/html; charset=utf-8');
        echo generateQRCodeHTML($code, $size);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Gerar QR Code como HTML (máxima compatibilidade)
 */
function generateQRCodeHTML($text, $size) {
    $html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - ' . htmlspecialchars($text) . '</title>
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
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
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
        .instructions ol {
            margin-left: 20px;
            color: #333;
        }
        .instructions li {
            margin: 8px 0;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            color: #999;
            font-size: 12px;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 10px;
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
            <div class="code">' . htmlspecialchars($text) . '</div>
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
        
        <div style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 10px; padding: 15px; margin: 20px 0;">
            <strong>⚠️ Importante:</strong><br>
            Guarde este código até retirar sua encomenda
        </div>
        
        <div class="footer">
            E-Condo Packages System<br>
            Código gerado em ' . date('d/m/Y H:i') . '
        </div>
    </div>
</body>
</html>';
    
    return $html;
}

/**
 * Gerar QR Code como SVG (não requer extensão GD nem internet)
 */
function generateQRCodeSVG($text, $size) {
    // Ajustar tamanhos proporcionalmente
    $textSize = max(18, $size / 20);
    $codeSize = max(24, $size / 12);
    $smallText = max(12, $size / 30);
    
    $svg = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . "\n";
    $svg .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' . "\n";
    $svg .= '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '" xmlns="http://www.w3.org/2000/svg" version="1.1">' . "\n";
    
    // Fundo branco
    $svg .= '<rect x="0" y="0" width="' . $size . '" height="' . $size . '" fill="#ffffff"/>' . "\n";
    
    // Borda externa
    $svg .= '<rect x="10" y="10" width="' . ($size - 20) . '" height="' . ($size - 20) . '" fill="none" stroke="#e0e0e0" stroke-width="3"/>' . "\n";
    
    // Logo/Header
    $headerY = $size * 0.08;
    $headerHeight = $size * 0.1;
    $svg .= '<rect x="' . ($size/2 - $size*0.15) . '" y="' . $headerY . '" width="' . ($size*0.3) . '" height="' . $headerHeight . '" fill="#667eea" rx="8"/>' . "\n";
    $svg .= '<text x="' . ($size/2) . '" y="' . ($headerY + $headerHeight*0.7) . '" font-family="Arial, Helvetica, sans-serif" font-size="' . ($textSize*0.8) . '" font-weight="bold" fill="#ffffff" text-anchor="middle" dominant-baseline="middle">E-CONDO PACKAGES</text>' . "\n";
    
    // Título
    $titleY = $size * 0.25;
    $svg .= '<text x="' . ($size/2) . '" y="' . $titleY . '" font-family="Arial, Helvetica, sans-serif" font-size="' . $textSize . '" font-weight="bold" fill="#333333" text-anchor="middle" dominant-baseline="middle">CÓDIGO DE RETIRADA</text>' . "\n";
    
    // Linha separadora
    $lineY = $size * 0.3;
    $svg .= '<line x1="' . ($size*0.15) . '" y1="' . $lineY . '" x2="' . ($size*0.85) . '" y2="' . $lineY . '" stroke="#e0e0e0" stroke-width="2"/>' . "\n";
    
    // Box do código
    $boxY = $size * 0.38;
    $boxHeight = $size * 0.2;
    $svg .= '<rect x="' . ($size*0.15) . '" y="' . $boxY . '" width="' . ($size*0.7) . '" height="' . $boxHeight . '" fill="#f8f9fa" stroke="#667eea" stroke-width="4" rx="12"/>' . "\n";
    
    // Código principal
    $codeY = $boxY + $boxHeight/2;
    $svg .= '<text x="' . ($size/2) . '" y="' . $codeY . '" font-family="Courier New, Courier, monospace" font-size="' . $codeSize . '" font-weight="bold" fill="#000000" text-anchor="middle" dominant-baseline="middle">' . htmlspecialchars($text) . '</text>' . "\n";
    
    // Instruções
    $instrY = $size * 0.68;
    $svg .= '<text x="' . ($size/2) . '" y="' . $instrY . '" font-family="Arial, Helvetica, sans-serif" font-size="' . $smallText . '" fill="#666666" text-anchor="middle">Apresente este código na portaria</text>' . "\n";
    $svg .= '<text x="' . ($size/2) . '" y="' . ($instrY + $smallText*1.5) . '" font-family="Arial, Helvetica, sans-serif" font-size="' . $smallText . '" fill="#666666" text-anchor="middle">para retirar sua encomenda</text>' . "\n";
    
    // Ícone decorativo (caixa)
    $iconSize = $size * 0.08;
    $iconY = $size * 0.82;
    $svg .= '<rect x="' . ($size/2 - $iconSize/2) . '" y="' . $iconY . '" width="' . $iconSize . '" height="' . $iconSize . '" fill="none" stroke="#667eea" stroke-width="3" rx="4"/>' . "\n";
    $svg .= '<line x1="' . ($size/2 - $iconSize*0.3) . '" y1="' . ($iconY + $iconSize*0.4) . '" x2="' . ($size/2 + $iconSize*0.3) . '" y2="' . ($iconY + $iconSize*0.4) . '" stroke="#667eea" stroke-width="2"/>' . "\n";
    
    // Rodapé
    $footerY = $size * 0.95;
    $svg .= '<text x="' . ($size/2) . '" y="' . $footerY . '" font-family="Arial, Helvetica, sans-serif" font-size="' . ($smallText*0.9) . '" fill="#999999" text-anchor="middle">E-Condo Packages System</text>' . "\n";
    
    $svg .= '</svg>';
    
    return $svg;
}
