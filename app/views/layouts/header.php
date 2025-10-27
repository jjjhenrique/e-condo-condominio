<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?><?= SITE_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 1.5rem 1rem;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h4 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            padding-left: 2rem;
        }
        
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left: 3px solid #3498db;
        }
        
        .sidebar-menu a i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
            width: 20px;
        }
        
        .sidebar-menu .menu-section {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            font-weight: 600;
            margin-top: 1rem;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            margin-bottom: 2rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .content-wrapper {
            padding: 0 2rem 2rem;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
        }
        
        .badge {
            padding: 0.35rem 0.65rem;
            font-weight: 500;
        }
        
        .btn {
            border-radius: 6px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="bi bi-box-seam"></i> E-Condo</h4>
            <small>Gestão de Encomendas</small>
        </div>
        
        <div class="sidebar-menu">
            <a href="<?= SITE_URL ?>/index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <div class="menu-section">Encomendas</div>
            
            <?php if (hasRole(['admin', 'porteiro'])): ?>
            <a href="<?= SITE_URL ?>/packages/receive.php">
                <i class="bi bi-plus-circle"></i> Receber Encomenda
            </a>
            <?php endif; ?>
            
            <a href="<?= SITE_URL ?>/packages/pickup.php">
                <i class="bi bi-check2-square"></i> Retirar Encomenda
            </a>
            
            <?php if (hasRole(['admin', 'porteiro', 'administracao'])): ?>
            <a href="<?= SITE_URL ?>/packages/transfer.php">
                <i class="bi bi-arrow-left-right"></i> Transferir Encomendas
            </a>
            <?php endif; ?>
            
            <a href="<?= SITE_URL ?>/packages/list.php">
                <i class="bi bi-list-ul"></i> Listar Encomendas
            </a>
            
            <div class="menu-section">Cadastros</div>
            
            <?php if (hasRole(['admin'])): ?>
            <a href="<?= SITE_URL ?>/residents/list.php">
                <i class="bi bi-people"></i> Condôminos
            </a>
            
            <a href="<?= SITE_URL ?>/villages/list.php">
                <i class="bi bi-buildings"></i> Quadra/Bloco/Rua
            </a>
            
            <a href="<?= SITE_URL ?>/houses/list.php">
                <i class="bi bi-house"></i> Village/Casa/Apt
            </a>
            <?php endif; ?>
            
            <div class="menu-section">Relatórios</div>
            
            <a href="<?= SITE_URL ?>/reports/packages.php">
                <i class="bi bi-file-earmark-bar-graph"></i> Relatório de Encomendas
            </a>
            
            <?php if (hasRole(['admin'])): ?>
            <a href="<?= SITE_URL ?>/reports/statistics.php">
                <i class="bi bi-graph-up"></i> Estatísticas
            </a>
            <?php endif; ?>
            
            <?php if (hasRole(['admin'])): ?>
            <div class="menu-section">Administração</div>
            
            <a href="<?= SITE_URL ?>/admin/users.php">
                <i class="bi bi-person-gear"></i> Usuários
            </a>
            
            <a href="<?= SITE_URL ?>/admin/settings.php">
                <i class="bi bi-gear"></i> Configurações
            </a>
            
            <a href="<?= SITE_URL ?>/admin/logs.php">
                <i class="bi bi-journal-text"></i> Logs do Sistema
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-link d-md-none" id="sidebarToggle">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                </div>
                
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr(getCurrentUser()['full_name'], 0, 1)) ?>
                    </div>
                    <div class="dropdown">
                        <a class="text-decoration-none text-dark dropdown-toggle" href="#" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <strong><?= htmlspecialchars(getCurrentUser()['full_name']) ?></strong>
                            <br>
                            <small class="text-muted">
                                <?php
                                $roles = [
                                    'admin' => 'Administrador',
                                    'porteiro' => 'Porteiro',
                                    'administracao' => 'Administração'
                                ];
                                echo $roles[getCurrentUser()['role']] ?? getCurrentUser()['role'];
                                ?>
                            </small>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>/profile.php">
                                <i class="bi bi-person"></i> Meu Perfil
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <?php
            // Exibir mensagens flash
            $flash = getFlash();
            if ($flash):
            ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
