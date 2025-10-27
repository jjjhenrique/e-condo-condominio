<?php
/**
 * Teste das APIs - Verificar se casas e condôminos estão sendo carregados
 */

require_once 'config/config.php';

// Verificar autenticação
if (!isLoggedIn()) {
    echo "Você precisa estar logado para testar as APIs.";
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de APIs - E-Condo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>🧪 Teste de APIs</h1>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Teste 1: Listar Villages</h5>
            </div>
            <div class="card-body">
                <?php
                $villageModel = new Village();
                $villages = $villageModel->getAll();
                
                if (empty($villages)) {
                    echo '<div class="alert alert-warning">⚠️ Nenhuma village cadastrada!</div>';
                    echo '<a href="' . SITE_URL . '/villages/create.php" class="btn btn-primary">Cadastrar Village</a>';
                } else {
                    echo '<div class="alert alert-success">✅ ' . count($villages) . ' village(s) encontrada(s)</div>';
                    echo '<ul>';
                    foreach ($villages as $v) {
                        echo '<li><strong>' . htmlspecialchars($v['name']) . '</strong> (ID: ' . $v['id'] . ')</li>';
                    }
                    echo '</ul>';
                }
                ?>
            </div>
        </div>

        <?php if (!empty($villages)): ?>
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Teste 2: Listar Casas por Village</h5>
            </div>
            <div class="card-body">
                <?php
                $houseModel = new House();
                
                foreach ($villages as $village):
                    $houses = $houseModel->getActiveByVillage($village['id']);
                ?>
                    <h6><?= htmlspecialchars($village['name']) ?></h6>
                    <?php if (empty($houses)): ?>
                        <div class="alert alert-warning">⚠️ Nenhuma casa cadastrada nesta village</div>
                        <a href="<?= SITE_URL ?>/houses/create.php?village_id=<?= $village['id'] ?>" class="btn btn-sm btn-primary mb-3">Cadastrar Casa</a>
                    <?php else: ?>
                        <div class="alert alert-success">✅ <?= count($houses) ?> casa(s) encontrada(s)</div>
                        <ul>
                            <?php foreach ($houses as $h): ?>
                                <li>Casa <?= htmlspecialchars($h['house_number']) ?> (ID: <?= $h['id'] ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <hr>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Teste 3: Listar Condôminos por Casa</h5>
            </div>
            <div class="card-body">
                <?php
                $residentModel = new Resident();
                $allHouses = $houseModel->getAllWithVillageInfo();
                
                if (empty($allHouses)):
                    echo '<div class="alert alert-warning">⚠️ Nenhuma casa cadastrada!</div>';
                else:
                    foreach ($allHouses as $house):
                        $residents = $residentModel->getByHouse($house['id']);
                ?>
                    <h6><?= htmlspecialchars($house['village_name']) ?> - Casa <?= htmlspecialchars($house['house_number']) ?></h6>
                    <?php if (empty($residents)): ?>
                        <div class="alert alert-warning">⚠️ Nenhum condômino cadastrado nesta casa</div>
                        <a href="<?= SITE_URL ?>/residents/create.php?house_id=<?= $house['id'] ?>" class="btn btn-sm btn-primary mb-3">Cadastrar Condômino</a>
                    <?php else: ?>
                        <div class="alert alert-success">✅ <?= count($residents) ?> condômino(s) encontrado(s)</div>
                        <ul>
                            <?php foreach ($residents as $r): ?>
                                <li><?= htmlspecialchars($r['full_name']) ?> - CPF: <?= htmlspecialchars($r['cpf']) ?> (ID: <?= $r['id'] ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <hr>
                <?php 
                    endforeach;
                endif;
                ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0">Teste 4: APIs AJAX</h5>
            </div>
            <div class="card-body">
                <p>Selecione uma village para testar o carregamento dinâmico:</p>
                
                <div class="mb-3">
                    <label for="test_village" class="form-label">Village:</label>
                    <select class="form-select" id="test_village">
                        <option value="">Selecione uma village</option>
                        <?php foreach ($villages as $v): ?>
                            <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="test_house" class="form-label">Casa:</label>
                    <select class="form-select" id="test_house">
                        <option value="">Selecione uma village primeiro</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="test_resident" class="form-label">Condômino:</label>
                    <select class="form-select" id="test_resident">
                        <option value="">Selecione uma casa primeiro</option>
                    </select>
                </div>
                
                <div id="test_result" class="mt-3"></div>
            </div>
        </div>

        <div class="mt-4 mb-5">
            <a href="<?= SITE_URL ?>/packages/receive.php" class="btn btn-primary">Ir para Receber Encomendas</a>
            <a href="<?= SITE_URL ?>/index.php" class="btn btn-secondary">Voltar ao Dashboard</a>
        </div>
        <?php endif; ?>
    </div>

    <script>
    document.getElementById('test_village').addEventListener('change', function() {
        const villageId = this.value;
        const houseSelect = document.getElementById('test_house');
        const residentSelect = document.getElementById('test_resident');
        const resultDiv = document.getElementById('test_result');
        
        houseSelect.innerHTML = '<option value="">Carregando...</option>';
        residentSelect.innerHTML = '<option value="">Selecione uma casa primeiro</option>';
        resultDiv.innerHTML = '';
        
        if (villageId) {
            const url = '<?= SITE_URL ?>/api/get_houses.php?village_id=' + villageId;
            resultDiv.innerHTML = '<div class="alert alert-info">Chamando: ' + url + '</div>';
            
            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    
                    if (data.error) {
                        resultDiv.innerHTML = '<div class="alert alert-danger">❌ Erro: ' + data.error + '</div>';
                        houseSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                        return;
                    }
                    
                    if (data.length === 0) {
                        resultDiv.innerHTML = '<div class="alert alert-warning">⚠️ Nenhuma casa encontrada</div>';
                        houseSelect.innerHTML = '<option value="">Nenhuma casa cadastrada</option>';
                        return;
                    }
                    
                    resultDiv.innerHTML = '<div class="alert alert-success">✅ ' + data.length + ' casa(s) carregada(s)</div>';
                    houseSelect.innerHTML = '<option value="">Selecione uma casa</option>';
                    data.forEach(house => {
                        const option = document.createElement('option');
                        option.value = house.id;
                        option.textContent = house.house_number;
                        houseSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultDiv.innerHTML = '<div class="alert alert-danger">❌ Erro: ' + error.message + '</div>';
                    houseSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                });
        } else {
            houseSelect.innerHTML = '<option value="">Selecione uma village primeiro</option>';
        }
    });

    document.getElementById('test_house').addEventListener('change', function() {
        const houseId = this.value;
        const residentSelect = document.getElementById('test_resident');
        const resultDiv = document.getElementById('test_result');
        
        residentSelect.innerHTML = '<option value="">Carregando...</option>';
        
        if (houseId) {
            const url = '<?= SITE_URL ?>/api/get_residents.php?house_id=' + houseId;
            resultDiv.innerHTML += '<div class="alert alert-info mt-2">Chamando: ' + url + '</div>';
            
            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    
                    if (data.error) {
                        resultDiv.innerHTML += '<div class="alert alert-danger mt-2">❌ Erro: ' + data.error + '</div>';
                        residentSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                        return;
                    }
                    
                    if (data.length === 0) {
                        resultDiv.innerHTML += '<div class="alert alert-warning mt-2">⚠️ Nenhum condômino encontrado</div>';
                        residentSelect.innerHTML = '<option value="">Nenhum condômino cadastrado</option>';
                        return;
                    }
                    
                    resultDiv.innerHTML += '<div class="alert alert-success mt-2">✅ ' + data.length + ' condômino(s) carregado(s)</div>';
                    residentSelect.innerHTML = '<option value="">Selecione um condômino</option>';
                    data.forEach(resident => {
                        const option = document.createElement('option');
                        option.value = resident.id;
                        option.textContent = resident.full_name + ' - ' + resident.cpf;
                        residentSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultDiv.innerHTML += '<div class="alert alert-danger mt-2">❌ Erro: ' + error.message + '</div>';
                    residentSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                });
        } else {
            residentSelect.innerHTML = '<option value="">Selecione uma casa primeiro</option>';
        }
    });
    </script>
</body>
</html>
