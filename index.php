<?php

// Função para carregar os dados dos grupos PET/ProPET
function loadPetData() {
    $data = [];
    $jsonFiles = glob('data/*.json');
    foreach ($jsonFiles as $file) {
        $jsonContent = file_get_contents($file);
        $jsonData = json_decode($jsonContent, true);
        if (is_array($jsonData)) {
            $data = array_merge($data, $jsonData);
        }
    }
    return $data;
}

// Função para filtrar os dados
function filterData($data, $category, $type, $campus) {
    if (empty($category) && empty($type) && empty($campus)) {
        return $data;
    }
    return array_filter($data, function($item) use ($category, $type, $campus) {
        $categoryMatch = empty($category) || $item['category'] === $category;
        $typeMatch = empty($type) || $item['type'] === $type;
        $campusMatch = empty($campus) || $item['campus'] === $campus;
        return $categoryMatch && $typeMatch && $campusMatch;
    });
}

// Função para procurar por PET/ProPET especificos ou Tutores
function searchData($data, $searchTerm) {
    if (empty($searchTerm)) {
        return $data;
    }
    return array_filter($data, function($item) use ($searchTerm) {
        $foundInName = isset($item['groupName']) && stripos($item['groupName'], $searchTerm) !== false;
        $foundInTutor = isset($item['tutor']) && stripos($item['tutor'], $searchTerm) !== false;
        return $foundInName || $foundInTutor;
    });
}

// Ordena um array de dados com base em um critério.
function sortData($data, $sortBy) {
    usort($data, function($a, $b) use ($sortBy) {
        switch ($sortBy) {
            case 'name_desc':
                return strcasecmp($b['groupName'], $a['groupName']);
            case 'campus_asc':
                return strcasecmp($a['campus'], $b['campus']);
            case 'campus_desc':
                return strcasecmp($b['campus'], $a['campus']);
            case 'name_asc':
            default:
                return strcasecmp($a['groupName'], $b['groupName']);
        }
    });
    return $data;
}

// Função para obter campus únicos
function getUniqueCampuses($data) {
    $campuses = array_column($data, 'campus');
    $filteredCampuses = array_filter($campuses);
    $uniqueCampuses = array_unique($filteredCampuses);
    sort($uniqueCampuses);
    return array_values($uniqueCampuses);
}

// LÓGICA PRINCIPAL DA PÁGINA

$allData = loadPetData();

$category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';
$type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : '';
$campus = isset($_GET['campus']) ? htmlspecialchars($_GET['campus']) : '';
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$sort = isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : 'name_asc';

$filteredData = filterData($allData, $category, $type, $campus);
$searchedData = searchData($filteredData, $search);
$finalData = sortData($searchedData, $sort);

$campuses = getUniqueCampuses($allData);

$totalGroups = count($allData);
$petGroups = count(array_filter($allData, function($item) { return $item['category'] === 'PET'; }));
$propetGroups = count(array_filter($allData, function($item) { return $item['category'] === 'ProPET'; }));

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupos PET e ProPET da UFF</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <header>
        <div class="header-content">
            <h1><i class="fas fa-graduation-cap"></i> Grupos PET e ProPET da UFF</h1>
            <div class="stats">
                <div class="stat-item">
                    <span class="stat-label">Total</span>
                    <span class="stat-value"><?php echo $totalGroups; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">PET</span>
                    <span class="stat-value"><?php echo $petGroups; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">ProPET</span>
                    <span class="stat-value"><?php echo $propetGroups; ?></span>
                </div>
            </div>
        </div>
    </header>

    <section class="filters-section">
        <div class="filters-wrapper">
            <h2 class="filters-title"><i class="fas fa-search"></i> Filtros e Busca</h2>
            <form method="GET" class="filters-form">
                <div class="filters-row">
                    <div class="filter-group">
                        <label for="category">Categoria</label>
                        <select name="category" id="category">
                            <option value="">Todas as categorias</option>
                            <option value="PET" <?php echo ($category === 'PET') ? 'selected' : ''; ?>>PET</option>
                            <option value="ProPET" <?php echo ($category === 'ProPET') ? 'selected' : ''; ?>>ProPET</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="type">Tipo</label>
                        <select name="type" id="type">
                            <option value="">Todos os tipos</option>
                            <option value="Curso Único" <?php echo ($type === 'Curso Único') ? 'selected' : ''; ?>>Curso Único</option>
                            <option value="Conexão de saberes" <?php echo ($type === 'Conexão de saberes') ? 'selected' : ''; ?>>Conexão de saberes</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="campus">Campus</label>
                        <select name="campus" id="campus">
                            <option value="">Todos os campus</option>
                            <?php foreach ($campuses as $campusOption): ?>
                                <option value="<?php echo htmlspecialchars($campusOption); ?>" 
                                        <?php echo ($campus === $campusOption) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($campusOption); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="filters-row">
                    <div class="filter-group filter-search">
                        <label for="search">Buscar por grupo ou tutor</label>
                        <input type="text" name="search" id="search" 
                               placeholder="Digite o nome do grupo ou tutor..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="sort">Ordenar por</label>
                        <select name="sort" id="sort">
                            <option value="name_asc" <?php echo ($sort === 'name_asc') ? 'selected' : ''; ?>>Nome (A-Z)</option>
                            <option value="name_desc" <?php echo ($sort === 'name_desc') ? 'selected' : ''; ?>>Nome (Z-A)</option>
                            <option value="campus_asc" <?php echo ($sort === 'campus_asc') ? 'selected' : ''; ?>>Campus (A-Z)</option>
                            <option value="campus_desc" <?php echo ($sort === 'campus_desc') ? 'selected' : ''; ?>>Campus (Z-A)</option>
                        </select>
                    </div>
                </div>

                <div class="filters-actions">
                    <button type="submit" class="btn-apply">Aplicar Filtros</button>
                    <a href="index.php" class="btn-clear">Limpar Filtros</a>
                </div>
            </form>
        </div>
    </section>
    
    <?php

    $num_columns = 3;
    $columns = array_fill(0, $num_columns, []);
    $data_to_distribute = $finalData;

    // Distribui os cards entre as colunas
    foreach ($data_to_distribute as $index => $group) {
        $columns[$index % $num_columns][] = $group;
    }
    ?>

    <main>
        <section class="results-section">
            <div class="results-info">
                <p><i class="fas fa-chart-bar"></i> Exibindo <strong><?php echo count($finalData); ?></strong> de <strong><?php echo $totalGroups; ?></strong> grupos</p>
            </div>
            
            <div class="card-container">
                <?php if (empty($finalData)): ?>
                    <div class="no-results">
                        <p><i class="fas fa-exclamation-triangle"></i> Nenhum grupo encontrado com os filtros aplicados.</p>
                        <p>Tente ajustar os critérios de busca ou limpar os filtros.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($columns as $column_items): ?>
                        <div class="card-column">
                            <?php foreach ($column_items as $group): ?>
                                <article class="card">
                                    <details class="card-details">
                                        <summary class="card-summary">
                                            <div class="card-header">
                                                <h2><?php echo htmlspecialchars($group['groupName']); ?></h2>
                                                <div class="card-badges">
                                                    <span class="badge badge-<?php echo strtolower($group['category']); ?>">
                                                        <?php echo htmlspecialchars($group['category']); ?>
                                                    </span>
                                                    <span class="badge badge-type">
                                                        <?php echo htmlspecialchars($group['type']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-preview">
                                                <div class="preview-item">
                                                    <span class="preview-label"><i class="fas fa-chalkboard-teacher"></i> Tutor:</span>
                                                    <span class="preview-value"><?php echo htmlspecialchars($group['tutor']); ?></span>
                                                </div>
                                                <div class="preview-item">
                                                    <span class="preview-label"><i class="fas fa-map-marker-alt"></i> Campus:</span>
                                                    <span class="preview-value"><?php echo htmlspecialchars($group['campus']); ?></span>
                                                </div>
                                            </div>
                                            <div class="card-expand">
                                                <span class="expand-text">Ver mais detalhes</span>
                                                <span class="expand-icon">▼</span>
                                            </div>
                                        </summary>

                                        <div class="card-content">
                                            <div class="content-section">
                                                <h3>Informações Principais</h3>
                                                <div class="card-field">
                                                    <strong><i class="fas fa-chalkboard-teacher"></i> Tutor:</strong>
                                                    <span><?php echo htmlspecialchars($group['tutor']); ?></span>
                                                </div>
                                                
                                                <div class="card-field">
                                                    <strong><i class="fas fa-map-marker-alt"></i> Campus:</strong>
                                                    <span><?php echo htmlspecialchars($group['campus']); ?></span>
                                                </div>
                                                
                                                <div class="card-field">
                                                    <strong><i class="fas fa-calendar-alt"></i> Ano de criação:</strong>
                                                    <span><?php echo htmlspecialchars($group['creationDate']); ?></span>
                                                </div>
                                                
                                                <?php if (!empty($group['address'])): ?>
                                                    <div class="card-field">
                                                        <strong><i class="fas fa-building"></i> Endereço:</strong>
                                                        <span><?php echo htmlspecialchars($group['address']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="content-section">
                                                <h3>Contato e Links</h3>
                                                <div class="card-links">
                                                    <?php if (!empty($group['contactEmail'])): ?>
                                                        <a href="mailto:<?php echo htmlspecialchars($group['contactEmail']); ?>" 
                                                           class="card-link card-link-email" title="Enviar e-mail">
                                                           <i class="fas fa-envelope"></i> E-mail
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($group['website'])): ?>
                                                        <a href="<?php echo htmlspecialchars($group['website']); ?>" 
                                                           target="_blank" rel="noopener noreferrer" 
                                                           class="card-link card-link-website" title="Visitar site">
                                                           <i class="fas fa-globe"></i> Site
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($group['lattes'])): ?>
                                                        <a href="<?php echo htmlspecialchars($group['lattes']); ?>" 
                                                           target="_blank" rel="noopener noreferrer" 
                                                           class="card-link card-link-lattes" title="Ver currículo Lattes">
                                                           <i class="fas fa-file-alt"></i> Lattes
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($group['instagram'])): ?>
                                                        <a href="https://instagram.com/<?php echo htmlspecialchars($group['instagram']); ?>" 
                                                           target="_blank" rel="noopener noreferrer" 
                                                           class="card-link card-link-instagram" title="Ver Instagram">
                                                           <i class="fab fa-instagram"></i> Instagram
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </details>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p><i class="fas fa-rocket"></i> Projeto PET-Tele — Webpage Dinâmica com PHP e JSON (UFF, 2025)</p>
    </footer>
</body>
</html>