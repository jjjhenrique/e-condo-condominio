<?php
/**
 * Teste de Geração de QR Code
 */

$code = $_GET['code'] ?? 'PKG000123456';
$size = 400;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Teste QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>🧪 Teste de QR Code</h1>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Teste de Geração</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="code" class="form-control" 
                               value="<?= htmlspecialchars($code) ?>" 
                               placeholder="Digite o código">
                        <button type="submit" class="btn btn-primary">Gerar</button>
                    </div>
                </form>
                
                <h6>Código Atual: <strong><?= htmlspecialchars($code) ?></strong></h6>
                
                <hr>
                
                <h6>Método 1: Imagem Direta</h6>
                <div class="border p-3 mb-3">
                    <img src="api/generate_qrcode.php?code=<?= urlencode($code) ?>&size=300" 
                         alt="QR Code" 
                         style="max-width: 300px; border: 1px solid #ddd;"
                         onerror="this.style.display='none'; document.getElementById('error1').style.display='block';">
                    <div id="error1" style="display:none;" class="alert alert-danger">
                        ❌ Erro ao carregar imagem
                    </div>
                </div>
                
                <hr>
                
                <h6>Método 2: SVG Inline</h6>
                <div class="border p-3 mb-3">
                    <?php
                    // Gerar SVG diretamente
                    function generateQRCodeSVG($text, $size) {
                        $svg = '<?xml version="1.0" encoding="UTF-8"?>';
                        $svg .= '<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg">';
                        $svg .= '<rect width="' . $size . '" height="' . $size . '" fill="#ffffff"/>';
                        $svg .= '<rect x="10" y="10" width="' . ($size - 20) . '" height="' . ($size - 20) . '" fill="none" stroke="#e0e0e0" stroke-width="2"/>';
                        $svg .= '<rect x="' . ($size/2 - 60) . '" y="40" width="120" height="40" fill="#667eea" rx="5"/>';
                        $svg .= '<text x="' . ($size/2) . '" y="68" font-family="Arial, sans-serif" font-size="16" font-weight="bold" fill="#ffffff" text-anchor="middle">E-CONDO</text>';
                        $svg .= '<text x="' . ($size/2) . '" y="120" font-family="Arial, sans-serif" font-size="24" font-weight="bold" fill="#333333" text-anchor="middle">Código de Retirada</text>';
                        $svg .= '<line x1="60" y1="140" x2="' . ($size - 60) . '" y2="140" stroke="#e0e0e0" stroke-width="2"/>';
                        $svg .= '<rect x="' . ($size/2 - 120) . '" y="170" width="240" height="80" fill="#f8f9fa" stroke="#667eea" stroke-width="3" rx="10"/>';
                        $svg .= '<text x="' . ($size/2) . '" y="225" font-family="Courier New, monospace" font-size="32" font-weight="bold" fill="#000000" text-anchor="middle">' . htmlspecialchars($text) . '</text>';
                        $svg .= '<text x="' . ($size/2) . '" y="290" font-family="Arial, sans-serif" font-size="14" fill="#666666" text-anchor="middle">Apresente este código</text>';
                        $svg .= '<text x="' . ($size/2) . '" y="310" font-family="Arial, sans-serif" font-size="14" fill="#666666" text-anchor="middle">na portaria para retirar</text>';
                        $svg .= '<text x="' . ($size/2) . '" y="330" font-family="Arial, sans-serif" font-size="14" fill="#666666" text-anchor="middle">sua encomenda</text>';
                        $svg .= '<text x="' . ($size/2) . '" y="' . ($size - 20) . '" font-family="Arial, sans-serif" font-size="12" fill="#999999" text-anchor="middle">E-Condo Packages</text>';
                        $svg .= '</svg>';
                        return $svg;
                    }
                    
                    echo generateQRCodeSVG($code, 300);
                    ?>
                </div>
                
                <hr>
                
                <h6>Método 3: Testar API Diretamente</h6>
                <div class="mb-3">
                    <a href="api/generate_qrcode.php?code=<?= urlencode($code) ?>" 
                       target="_blank" 
                       class="btn btn-primary">
                        Abrir API em Nova Aba
                    </a>
                </div>
                
                <hr>
                
                <h6>Método 4: Base64</h6>
                <div class="mb-3">
                    <button onclick="testBase64()" class="btn btn-info">Testar Base64</button>
                    <div id="base64-result" class="mt-3"></div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="test_packages.php" class="btn btn-secondary">← Voltar para Teste de Encomendas</a>
        </div>
    </div>
    
    <script>
    function testBase64() {
        const code = '<?= htmlspecialchars($code) ?>';
        fetch('api/generate_qrcode.php?code=' + encodeURIComponent(code) + '&format=base64')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('base64-result').innerHTML = 
                        '<div class="alert alert-success">✅ Base64 gerado com sucesso!</div>' +
                        '<img src="' + data.qrcode_base64 + '" style="max-width: 300px; border: 1px solid #ddd;">';
                } else {
                    document.getElementById('base64-result').innerHTML = 
                        '<div class="alert alert-danger">❌ Erro: ' + data.error + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('base64-result').innerHTML = 
                    '<div class="alert alert-danger">❌ Erro: ' + error.message + '</div>';
            });
    }
    </script>
</body>
</html>
