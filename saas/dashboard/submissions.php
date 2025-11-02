<?php
/**
 * Просмотр заявок клиента
 */
define('SAAS_SYSTEM', true);
require_once '../config.php';
requireAuth();

// Получение всех заявок пользователя
$stmt = $pdo->prepare("
    SELECT * FROM form_submissions 
    WHERE user_id = ?
    ORDER BY submitted_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$submissions = $stmt->fetchAll();

// Получение полей для отображения названий
$stmt = $pdo->prepare("SELECT field_key, field_label FROM form_fields WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$fields_map = [];
foreach ($stmt->fetchAll() as $field) {
    $fields_map[$field['field_key']] = $field['field_label'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заявки | Warranty SaaS</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .submissions-table {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .submission-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .submission-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .submission-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .submission-header strong {
            font-size: 18px;
            color: var(--text-primary);
        }
        
        .submission-date {
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .submission-data {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 12px;
        }
        
        .data-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .data-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .data-value {
            font-size: 15px;
            color: var(--text-primary);
        }
        
        .rating-stars {
            color: #ffd700;
        }
        
        .empty-state {
            padding: 80px 20px;
            text-align: center;
            color: var(--text-secondary);
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }
        
        .empty-state-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .page-heading {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 32px;
            letter-spacing: -1px;
        }
        
        .export-btn {
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 24px;
        }
        
        .export-btn:hover {
            background: #0077ed;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1 class="page-heading">Заявки</h1>
                <?php if (count($submissions) > 0): ?>
                    <button class="export-btn" onclick="exportToCSV()">📥 Экспорт в CSV</button>
                <?php endif; ?>
            </div>
            
            <?php if (count($submissions) === 0): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <div class="empty-state-title">Пока нет заявок</div>
                    <p>Здесь будут отображаться все заявки с вашей формы</p>
                </div>
            <?php else: ?>
                <div class="submissions-table">
                    <?php foreach ($submissions as $submission): 
                        $data = json_decode($submission['form_data'], true);
                    ?>
                        <div class="submission-card">
                            <div class="submission-header">
                                <strong>#<?= $submission['id'] ?></strong>
                                <span class="submission-date"><?= date('d.m.Y H:i', strtotime($submission['submitted_at'])) ?></span>
                            </div>
                            <div class="submission-data">
                                <?php if ($data): ?>
                                    <?php foreach ($data as $key => $value): ?>
                                        <div class="data-row">
                                            <span class="data-label"><?= h($fields_map[$key] ?? $key) ?>:</span>
                                            <span class="data-value">
                                                <?php if (strpos($key, 'rating') !== false): ?>
                                                    <span class="rating-stars"><?= str_repeat('★', (int)$value) ?></span>
                                                <?php else: ?>
                                                    <?= h($value) ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function exportToCSV() {
            const table = document.getElementById('submissions-table');
            let csv = [];
            
            // Заголовки
            const headers = [];
            table.querySelectorAll('thead th').forEach(th => {
                headers.push(th.textContent.trim());
            });
            csv.push(headers.join(','));
            
            // Данные
            table.querySelectorAll('tbody tr').forEach(tr => {
                const row = [];
                tr.querySelectorAll('td').forEach(td => {
                    let text = td.textContent.trim();
                    // Удаление звездочек для рейтингов
                    text = text.replace(/★/g, '');
                    row.push('"' + text + '"');
                });
                csv.push(row.join(','));
            });
            
            // Скачивание файла
            const csvContent = csv.join('\n');
            const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            
            link.setAttribute('href', url);
            link.setAttribute('download', 'submissions_' + new Date().getTime() + '.csv');
            link.style.visibility = 'hidden';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>
