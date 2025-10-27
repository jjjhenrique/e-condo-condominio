<?php
// Teste simples - sem dependências
$code = $_GET['code'] ?? 'PKG000123456';
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
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
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
        .icon { font-size: 48px; margin-bottom: 10px; }
        h1 { font-size: 24px; margin-bottom: 5px; }
        .subtitle { font-size: 14px; opacity: 0.9; }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .code-box {
            background: #f8f9fa;
            border: 4px solid #667eea;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
        }
        .code {
            font-family: "Courier New", monospace;
            font-size: 36px;
            font-weight: bold;
            color: #000;
            letter-spacing: 3px;
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
        }
        .instructions ol {
            margin-left: 20px;
        }
        .instructions li {
            margin: 8px 0;
        }
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
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="icon">📦</div>
            <h1>E-Condo Packages</h1>
            <p class="subtitle">Sistema de Gestão de Encomendas</p>
        </div>
        
        <div class="title">Código de Retirada</div>
        
        <div class="code-box">
            <div class="code"><?= htmlspecialchars($code) ?></div>
        </div>
        
        <div class="instructions">
            <h3>📋 Como Retirar:</h3>
            <ol>
                <li>Dirija-se à portaria</li>
                <li>Apresente este código</li>
                <li>Confirme sua identidade</li>
                <li>Retire sua encomenda</li>
            </ol>
        </div>
        
        <div class="alert">
            <strong>⚠️ Importante:</strong><br>
            Guarde este código até retirar sua encomenda
        </div>
        
        <div class="footer">
            E-Condo Packages<br>
            Gerado em <?= date('d/m/Y H:i') ?>
        </div>
    </div>
</body>
</html>
