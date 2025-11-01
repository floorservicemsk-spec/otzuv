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
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: var(--bg-primary);
        }
        
        th {
            padding: 16px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 16px;
            border-top: 1px solid var(--border-color);
            font-size: 14px;
        }
        
        tr:hover {
            background: var(--bg-primary);
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
                    <table id="submissions-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Дата и время</th>
                                <th>Телефон</th>
                                <th>Имя</th>
                                <th>Email</th>
                                <th>Продажи</th>
                                <th>Доставка</th>
                                <th>Монтаж</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td><?= $submission['id'] ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($submission['submitted_at'])) ?></td>
                                <td><?= h($submission['phone']) ?></td>
                                <td><?= h($submission['name']) ?></td>
                                <td><?= h($submission['email']) ?></td>
                                <td><span class="rating-stars"><?= str_repeat('★', $submission['sales_rating']) ?></span></td>
                                <td><span class="rating-stars"><?= str_repeat('★', $submission['delivery_rating']) ?></span></td>
                                <td><span class="rating-stars"><?= str_repeat('★', $submission['installation_rating']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
