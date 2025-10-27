<?php
/**
 * QR Code Generator
 * Gera QR Codes usando a biblioteca phpqrcode ou API externa
 */

class QRCodeGenerator {
    private $qrCodeDir;
    
    public function __construct() {
        $this->qrCodeDir = __DIR__ . '/../../public/qrcodes/';
        
        // Criar diretório se não existir
        if (!is_dir($this->qrCodeDir)) {
            mkdir($this->qrCodeDir, 0755, true);
        }
    }
    
    /**
     * Gerar QR Code e salvar como arquivo
     */
    public function generate($text, $filename = null, $size = 300) {
        if ($filename === null) {
            $filename = md5($text) . '.png';
        }
        
        $filepath = $this->qrCodeDir . $filename;
        
        // Se o arquivo já existe, retornar o caminho
        if (file_exists($filepath)) {
            return $this->getPublicUrl($filename);
        }
        
        // Tentar usar biblioteca phpqrcode se disponível
        if (class_exists('QRcode')) {
            QRcode::png($text, $filepath, QR_ECLEVEL_L, 10, 2);
            return $this->getPublicUrl($filename);
        }
        
        // Fallback: usar API do Google Charts
        $qrCodeUrl = "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl=" . urlencode($text) . "&choe=UTF-8";
        
        $imageData = @file_get_contents($qrCodeUrl);
        
        if ($imageData !== false) {
            file_put_contents($filepath, $imageData);
            return $this->getPublicUrl($filename);
        }
        
        // Fallback final: gerar imagem simples
        $this->generateSimpleImage($text, $filepath, $size);
        return $this->getPublicUrl($filename);
    }
    
    /**
     * Gerar QR Code e retornar como base64
     */
    public function generateBase64($text, $size = 300) {
        $filename = 'temp_' . md5($text . time()) . '.png';
        $filepath = $this->qrCodeDir . $filename;
        
        // Usar API do Google Charts
        $qrCodeUrl = "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl=" . urlencode($text) . "&choe=UTF-8";
        
        $imageData = @file_get_contents($qrCodeUrl);
        
        if ($imageData === false) {
            $imageData = $this->generateSimpleImageData($text, $size);
        }
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    
    /**
     * Obter URL pública do QR Code
     */
    private function getPublicUrl($filename) {
        return SITE_URL . '/public/qrcodes/' . $filename;
    }
    
    /**
     * Gerar imagem simples com o código
     */
    private function generateSimpleImage($text, $filepath, $size) {
        $imageData = $this->generateSimpleImageData($text, $size);
        file_put_contents($filepath, $imageData);
    }
    
    /**
     * Gerar dados da imagem simples
     */
    private function generateSimpleImageData($text, $size) {
        $image = imagecreate($size, $size);
        
        // Cores
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $blue = imagecolorallocate($image, 52, 58, 64);
        $lightGray = imagecolorallocate($image, 200, 200, 200);
        
        // Preencher fundo branco
        imagefill($image, 0, 0, $white);
        
        // Adicionar borda arredondada
        imagesetthickness($image, 3);
        imagerectangle($image, 15, 15, $size - 15, $size - 15, $lightGray);
        
        // Logo/Ícone (quadrado no topo)
        $iconSize = 40;
        $iconX = ($size - $iconSize) / 2;
        $iconY = 40;
        imagefilledrectangle($image, $iconX, $iconY, $iconX + $iconSize, $iconY + $iconSize, $blue);
        
        // Título
        $title = "E-Condo Packages";
        $titleFont = 3;
        $titleWidth = imagefontwidth($titleFont) * strlen($title);
        $titleX = ($size - $titleWidth) / 2;
        imagestring($image, $titleFont, $titleX, $iconY + $iconSize + 20, $title, $blue);
        
        // Linha separadora
        imageline($image, 50, $size / 2 - 40, $size - 50, $size / 2 - 40, $lightGray);
        
        // Label "Código de Retirada"
        $label = "Codigo de Retirada:";
        $labelFont = 3;
        $labelWidth = imagefontwidth($labelFont) * strlen($label);
        $labelX = ($size - $labelWidth) / 2;
        imagestring($image, $labelFont, $labelX, $size / 2 - 10, $label, $black);
        
        // Código principal (maior e em negrito)
        $codeFont = 5;
        $codeWidth = imagefontwidth($codeFont) * strlen($text);
        $codeX = ($size - $codeWidth) / 2;
        $codeY = $size / 2 + 20;
        
        // Fundo do código
        $padding = 10;
        imagefilledrectangle(
            $image, 
            $codeX - $padding, 
            $codeY - $padding, 
            $codeX + $codeWidth + $padding, 
            $codeY + imagefontheight($codeFont) + $padding,
            $lightGray
        );
        
        // Código
        imagestring($image, $codeFont, $codeX, $codeY, $text, $black);
        
        // Rodapé
        $footer = "Apresente este codigo na portaria";
        $footerFont = 2;
        $footerWidth = imagefontwidth($footerFont) * strlen($footer);
        $footerX = ($size - $footerWidth) / 2;
        imagestring($image, $footerFont, $footerX, $size - 50, $footer, $blue);
        
        // Converter para PNG
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return $imageData;
    }
    
    /**
     * Limpar QR Codes antigos (mais de 30 dias)
     */
    public function cleanOldQRCodes($days = 30) {
        $files = glob($this->qrCodeDir . '*.png');
        $now = time();
        $deleted = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * $days) {
                    unlink($file);
                    $deleted++;
                }
            }
        }
        
        return $deleted;
    }
}
