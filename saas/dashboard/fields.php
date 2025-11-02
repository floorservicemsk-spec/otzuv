<?php
/**
 * Конструктор полей формы
 */
define('SAAS_SYSTEM', true);
require_once '../config.php';
requireAuth();

$success = '';
$error = '';

// Получение всех полей пользователя
$stmt = $pdo->prepare("
    SELECT * FROM form_fields 
    WHERE user_id = ? 
    ORDER BY field_order ASC, id ASC
");
$stmt->execute([$_SESSION['user_id']]);
$fields = $stmt->fetchAll();

// Обработка сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_fields'])) {
    // Получаем данные из формы
    $fields_data = $_POST['fields'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($fields_data as $field_id => $data) {
            $stmt = $pdo->prepare("
                UPDATE form_fields 
                SET field_label = ?,
                    field_type = ?,
                    is_required = ?,
                    is_enabled = ?,
                    field_order = ?,
                    placeholder = ?
                WHERE id = ? AND user_id = ?
            ");
            
            $stmt->execute([
                $data['label'],
                $data['type'],
                isset($data['required']) ? 1 : 0,
                isset($data['enabled']) ? 1 : 0,
                (int)$data['order'],
                $data['placeholder'] ?? null,
                $field_id,
                $_SESSION['user_id']
            ]);
        }
        
        $pdo->commit();
        $success = 'Поля успешно сохранены!';
        logActivity('fields_update', 'Обновлены поля формы');
        
        // Перезагружаем данные
        $stmt = $pdo->prepare("
            SELECT * FROM form_fields 
            WHERE user_id = ? 
            ORDER BY field_order ASC, id ASC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $fields = $stmt->fetchAll();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Ошибка при сохранении: ' . $e->getMessage();
    }
}

// Обработка добавления нового поля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_field'])) {
    $field_key = 'custom_' . time();
    $field_label = trim($_POST['new_field_label'] ?? 'Новое поле');
    $field_type = $_POST['new_field_type'] ?? 'text';
    
    $max_order = $pdo->prepare("SELECT MAX(field_order) as max_order FROM form_fields WHERE user_id = ?");
    $max_order->execute([$_SESSION['user_id']]);
    $next_order = ($max_order->fetch()['max_order'] ?? 0) + 1;
    
    $stmt = $pdo->prepare("
        INSERT INTO form_fields (user_id, field_key, field_label, field_type, field_order, is_enabled)
        VALUES (?, ?, ?, ?, ?, 1)
    ");
    
    try {
        $stmt->execute([$_SESSION['user_id'], $field_key, $field_label, $field_type, $next_order]);
        $success = 'Новое поле добавлено!';
        
        // Перезагружаем
        $stmt = $pdo->prepare("
            SELECT * FROM form_fields 
            WHERE user_id = ? 
            ORDER BY field_order ASC, id ASC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $fields = $stmt->fetchAll();
    } catch (Exception $e) {
        $error = 'Ошибка при добавлении поля: ' . $e->getMessage();
    }
}

// Обработка удаления поля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_field'])) {
    $field_id = (int)$_POST['field_id'];
    
    $stmt = $pdo->prepare("DELETE FROM form_fields WHERE id = ? AND user_id = ?");
    $stmt->execute([$field_id, $_SESSION['user_id']]);
    
    $success = 'Поле удалено!';
    
    // Перезагружаем
    $stmt = $pdo->prepare("
        SELECT * FROM form_fields 
        WHERE user_id = ? 
        ORDER BY field_order ASC, id ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $fields = $stmt->fetchAll();
}

// Получение пользователя для form_id
$stmt = $pdo->prepare("SELECT form_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поля формы | Warranty SaaS</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .fields-container {
            background: white;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid var(--border-color);
        }
        
        .field-item {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }
        
        .field-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .field-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .drag-handle {
            cursor: grab;
            font-size: 20px;
            color: var(--text-secondary);
            user-select: none;
        }
        
        .drag-handle:active {
            cursor: grabbing;
        }
        
        .field-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 16px;
            align-items: start;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-weight: 600;
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .form-group input,
        .form-group select {
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
        }
        
        .checkbox-group {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        
        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .field-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        
        .btn-delete {
            padding: 8px 16px;
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-delete:hover {
            background: #ff453a;
        }
        
        .btn-save {
            padding: 14px 32px;
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 24px;
        }
        
        .btn-save:hover {
            background: #30d158;
            transform: translateY(-1px);
        }
        
        .add-field-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            margin-bottom: 24px;
        }
        
        .add-field-form {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
            gap: 12px;
            align-items: end;
        }
        
        .btn-add {
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-add:hover {
            background: #0077ed;
        }
        
        .preview-link {
            display: inline-block;
            padding: 12px 24px;
            background: var(--bg-primary);
            border-radius: 10px;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 600;
            transition: all 0.2s ease;
            margin-bottom: 24px;
        }
        
        .preview-link:hover {
            background: var(--border-color);
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 15px;
        }
        
        .alert-success {
            background: rgba(52, 199, 89, 0.15);
            border: 1px solid var(--success-color);
            color: var(--success-color);
        }
        
        .alert-error {
            background: rgba(255, 59, 48, 0.15);
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
        }
        
        .field-type-badge {
            display: inline-block;
            padding: 4px 10px;
            background: rgba(0, 113, 227, 0.1);
            color: var(--primary-color);
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .field-grid {
                grid-template-columns: 1fr;
            }
            
            .add-field-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <?php if ($success): ?>
                <div class="alert alert-success"><?= h($success) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>
            
            <h1 class="page-heading">Поля формы</h1>
            
            <a href="../form/<?= h($user['form_id']) ?>" target="_blank" class="preview-link">
                👁️ Предпросмотр формы
            </a>
            
            <!-- Добавление нового поля -->
            <div class="add-field-section">
                <h3 style="margin-bottom: 16px;">➕ Добавить новое поле</h3>
                <form method="POST" class="add-field-form">
                    <div class="form-group">
                        <label>Название поля</label>
                        <input type="text" name="new_field_label" placeholder="Например: Адрес доставки" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Тип поля</label>
                        <select name="new_field_type">
                            <option value="text">Текст</option>
                            <option value="email">Email</option>
                            <option value="tel">Телефон</option>
                            <option value="number">Число</option>
                            <option value="textarea">Длинный текст</option>
                            <option value="checkbox">Чекбокс</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="add_field" class="btn-add">Добавить</button>
                </form>
            </div>
            
            <!-- Список полей -->
            <form method="POST" id="fields-form">
                <div class="fields-container">
                    <?php if (empty($fields)): ?>
                        <p style="text-align: center; color: var(--text-secondary); padding: 40px;">
                            Нет полей. Добавьте первое поле выше.
                        </p>
                    <?php else: ?>
                        <?php foreach ($fields as $index => $field): ?>
                            <div class="field-item" data-field-id="<?= $field['id'] ?>">
                                <div class="field-header">
                                    <span class="drag-handle">⋮⋮</span>
                                    <span class="field-type-badge"><?= h($field['field_type']) ?></span>
                                    <strong style="flex: 1;"><?= h($field['field_label']) ?></strong>
                                    <button type="button" class="btn-delete" onclick="deleteField(<?= $field['id'] ?>)">🗑️ Удалить</button>
                                </div>
                                
                                <div class="field-grid">
                                    <div class="form-group">
                                        <label>Название поля</label>
                                        <input type="text" name="fields[<?= $field['id'] ?>][label]" value="<?= h($field['field_label']) ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Тип</label>
                                        <select name="fields[<?= $field['id'] ?>][type]">
                                            <option value="text" <?= $field['field_type'] === 'text' ? 'selected' : '' ?>>Текст</option>
                                            <option value="email" <?= $field['field_type'] === 'email' ? 'selected' : '' ?>>Email</option>
                                            <option value="tel" <?= $field['field_type'] === 'tel' ? 'selected' : '' ?>>Телефон</option>
                                            <option value="number" <?= $field['field_type'] === 'number' ? 'selected' : '' ?>>Число</option>
                                            <option value="textarea" <?= $field['field_type'] === 'textarea' ? 'selected' : '' ?>>Длинный текст</option>
                                            <option value="rating" <?= $field['field_type'] === 'rating' ? 'selected' : '' ?>>Рейтинг (звезды)</option>
                                            <option value="checkbox" <?= $field['field_type'] === 'checkbox' ? 'selected' : '' ?>>Чекбокс</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Порядок</label>
                                        <input type="number" name="fields[<?= $field['id'] ?>][order]" value="<?= $field['field_order'] ?>" min="0">
                                    </div>
                                </div>
                                
                                <div class="form-group" style="margin-top: 12px;">
                                    <label>Плейсхолдер (подсказка)</label>
                                    <input type="text" name="fields[<?= $field['id'] ?>][placeholder]" value="<?= h($field['placeholder']) ?>" placeholder="Например: Введите ваш адрес">
                                </div>
                                
                                <div class="checkbox-group" style="margin-top: 12px;">
                                    <label>
                                        <input type="checkbox" name="fields[<?= $field['id'] ?>][enabled]" <?= $field['is_enabled'] ? 'checked' : '' ?>>
                                        Включено
                                    </label>
                                    
                                    <label>
                                        <input type="checkbox" name="fields[<?= $field['id'] ?>][required]" <?= $field['is_required'] ? 'checked' : '' ?>>
                                        Обязательное
                                    </label>
                                </div>
                                
                                <input type="hidden" name="fields[<?= $field['id'] ?>][key]" value="<?= h($field['field_key']) ?>">
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($fields)): ?>
                    <button type="submit" name="save_fields" class="btn-save">💾 Сохранить все изменения</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <script>
        function deleteField(fieldId) {
            if (!confirm('Удалить это поле?')) return;
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="delete_field" value="1">
                <input type="hidden" name="field_id" value="${fieldId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        // Простое drag & drop для порядка полей
        let draggedElement = null;
        
        document.querySelectorAll('.drag-handle').forEach(handle => {
            handle.parentElement.parentElement.setAttribute('draggable', 'true');
            
            handle.parentElement.parentElement.addEventListener('dragstart', function(e) {
                draggedElement = this;
                this.style.opacity = '0.5';
            });
            
            handle.parentElement.parentElement.addEventListener('dragend', function(e) {
                this.style.opacity = '1';
            });
            
            handle.parentElement.parentElement.addEventListener('dragover', function(e) {
                e.preventDefault();
            });
            
            handle.parentElement.parentElement.addEventListener('drop', function(e) {
                e.preventDefault();
                if (draggedElement !== this) {
                    const container = this.parentElement;
                    const allItems = [...container.children];
                    const draggedIndex = allItems.indexOf(draggedElement);
                    const targetIndex = allItems.indexOf(this);
                    
                    if (draggedIndex < targetIndex) {
                        this.after(draggedElement);
                    } else {
                        this.before(draggedElement);
                    }
                    
                    // Обновляем порядок в инпутах
                    updateFieldOrder();
                }
            });
        });
        
        function updateFieldOrder() {
            const items = document.querySelectorAll('.field-item');
            items.forEach((item, index) => {
                const orderInput = item.querySelector('input[name*="[order]"]');
                if (orderInput) {
                    orderInput.value = index + 1;
                }
            });
        }
    </script>
</body>
</html>
