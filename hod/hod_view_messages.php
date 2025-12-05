<?php
require_once '../db.php';
checkRole(['hod']);

$user = getCurrentUser();
$hod_id = $user['id'];

// Handle mark as read
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $message_id = $_GET['mark_read'];
    $update_query = "UPDATE hod_messages SET is_read = 1, read_at = NOW() WHERE id = ? AND hod_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ii", $message_id, $hod_id);
    $update_stmt->execute();
    header("Location: hod_view_messages.php");
    exit();
}

// Get all messages for this HOD
$messages_query = "SELECT hm.*, u.full_name as sender_name 
                   FROM hod_messages hm 
                   JOIN users u ON hm.sent_by = u.id 
                   WHERE hm.hod_id = ? 
                   ORDER BY hm.created_at DESC";
$messages_stmt = $conn->prepare($messages_query);
$messages_stmt->bind_param("i", $hod_id);
$messages_stmt->execute();
$messages = $messages_stmt->get_result();

// Count unread messages
$unread_query = "SELECT COUNT(*) as unread_count FROM hod_messages WHERE hod_id = ? AND is_read = 0";
$unread_stmt = $conn->prepare($unread_query);
$unread_stmt->bind_param("i", $hod_id);
$unread_stmt->execute();
$unread_result = $unread_stmt->get_result();
$unread_count = $unread_result->fetch_assoc()['unread_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages from Admin - HOD Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 20px; padding: 30px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #667eea; }
        .header h1 { font-size: 28px; color: #2c3e50; }
        .header .back-btn { padding: 12px 24px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; }
        
        .stats-bar { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 20px; border-radius: 15px; margin-bottom: 30px; display: flex; justify-content: space-around; align-items: center; }
        .stat-item { text-align: center; }
        .stat-item .value { font-size: 36px; font-weight: 800; }
        .stat-item .label { font-size: 14px; opacity: 0.9; margin-top: 5px; }
        
        .message-card { background: white; border: 2px solid #e9ecef; border-radius: 15px; padding: 25px; margin-bottom: 20px; transition: all 0.3s; position: relative; }
        .message-card:hover { box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2); transform: translateY(-2px); }
        .message-card.unread { border-left: 5px solid #667eea; background: #f8f9ff; }
        .message-card.read { opacity: 0.8; }
        
        .message-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .message-sender { font-size: 18px; font-weight: 700; color: #2c3e50; }
        .message-date { font-size: 13px; color: #666; }
        
        .message-meta { display: flex; gap: 20px; margin-bottom: 15px; font-size: 13px; color: #666; }
        .message-meta span { display: flex; align-items: center; gap: 5px; }
        
        .message-content { color: #2c3e50; line-height: 1.8; font-size: 15px; margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 10px; }
        
        .teacher-list { margin-top: 15px; }
        .teacher-list h4 { font-size: 14px; color: #2c3e50; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
        .teacher-chip { display: inline-block; background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 8px 16px; border-radius: 20px; font-size: 13px; margin: 4px; font-weight: 600; }
        
        .message-actions { display: flex; gap: 10px; }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 14px; text-decoration: none; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-success { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
        .btn-sm { padding: 8px 16px; font-size: 12px; }
        
        .unread-badge { background: #dc3545; color: white; padding: 4px 10px; border-radius: 15px; font-size: 11px; font-weight: 700; position: absolute; top: 10px; right: 10px; }
        
        .empty-state { text-align: center; padding: 80px 20px; color: #999; }
        .empty-state i { font-size: 64px; margin-bottom: 20px; display: block; opacity: 0.5; }
        .empty-state h3 { color: #666; margin-bottom: 10px; }
        
        .filter-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .filter-tab { padding: 12px 24px; border: 2px solid #667eea; border-radius: 10px; background: white; color: #667eea; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .filter-tab.active { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        
        @media (max-width: 768px) {
            .container { padding: 15px; }
            .header { flex-direction: column; gap: 15px; align-items: flex-start; }
            .stats-bar { flex-direction: column; gap: 15px; }
            .message-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .filter-tabs { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-envelope"></i> Messages from Admin</h1>
                <p style="color: #666; margin-top: 5px;">Faculty assignment requests and notifications</p>
            </div>
            <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="stats-bar">
            <div class="stat-item">
                <div class="value"><?php echo $messages->num_rows; ?></div>
                <div class="label"><i class="fas fa-envelope"></i> Total Messages</div>
            </div>
            <div class="stat-item">
                <div class="value"><?php echo $unread_count; ?></div>
                <div class="label"><i class="fas fa-envelope-open"></i> Unread Messages</div>
            </div>
            <div class="stat-item">
                <div class="value"><?php echo $messages->num_rows - $unread_count; ?></div>
                <div class="label"><i class="fas fa-check-double"></i> Read Messages</div>
            </div>
        </div>

        <div class="filter-tabs">
            <button class="filter-tab active" onclick="filterMessages('all')">
                <i class="fas fa-list"></i> All Messages
            </button>
            <button class="filter-tab" onclick="filterMessages('unread')">
                <i class="fas fa-envelope"></i> Unread
            </button>
            <button class="filter-tab" onclick="filterMessages('read')">
                <i class="fas fa-check-double"></i> Read
            </button>
        </div>

        <div id="messages-container">
            <?php 
            if ($messages->num_rows > 0):
                while ($message = $messages->fetch_assoc()): 
                    $teacher_ids = json_decode($message['teacher_ids'], true);
                    $is_read = $message['is_read'];
            ?>
            <div class="message-card <?php echo $is_read ? 'read' : 'unread'; ?>" data-status="<?php echo $is_read ? 'read' : 'unread'; ?>">
                <?php if (!$is_read): ?>
                    <span class="unread-badge">NEW</span>
                <?php endif; ?>
                
                <div class="message-header">
                    <div class="message-sender">
                        <i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($message['sender_name']); ?>
                        <?php if ($is_read): ?>
                            <i class="fas fa-check-double" style="color: #28a745; font-size: 14px; margin-left: 8px;" title="Read"></i>
                        <?php endif; ?>
                    </div>
                    <div class="message-date">
                        <i class="fas fa-clock"></i> <?php echo date('d M Y, h:i A', strtotime($message['created_at'])); ?>
                    </div>
                </div>
                
                <div class="message-meta">
                    <span><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($message['academic_year']); ?></span>
                    <span><i class="fas fa-book"></i> Semester <?php echo htmlspecialchars($message['semester']); ?></span>
                    <?php if ($is_read && $message['read_at']): ?>
                        <span><i class="fas fa-eye"></i> Read on <?php echo date('d M Y', strtotime($message['read_at'])); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="message-content">
                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                </div>
                
                <?php if (!empty($teacher_ids)): ?>
                    <div class="teacher-list">
                        <h4><i class="fas fa-users"></i> Available Faculty Members (<?php echo count($teacher_ids); ?>):</h4>
                        <?php
                        foreach ($teacher_ids as $teacher_id) {
                            $teacher_query = "SELECT full_name FROM users WHERE id = ?";
                            $teacher_stmt = $conn->prepare($teacher_query);
                            $teacher_stmt->bind_param("i", $teacher_id);
                            $teacher_stmt->execute();
                            $teacher_result = $teacher_stmt->get_result();
                            if ($teacher_data = $teacher_result->fetch_assoc()) {
                                echo '<span class="teacher-chip">' . htmlspecialchars($teacher_data['full_name']) . '</span>';
                            }
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="message-actions">
                    <?php if (!$is_read): ?>
                        <a href="?mark_read=<?php echo $message['id']; ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-check"></i> Mark as Read
                        </a>
                    <?php endif; ?>
                    <a href="hod_manage_faculty_load.php?year=<?php echo urlencode($message['academic_year']); ?>&semester=<?php echo urlencode($message['semester']); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-users-cog"></i> Manage Faculty Load
                    </a>
                </div>
            </div>
            <?php 
                endwhile;
            else: 
            ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Messages Yet</h3>
                <p>You don't have any messages from the admin at this time.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterMessages(filter) {
            const cards = document.querySelectorAll('.message-card');
            const tabs = document.querySelectorAll('.filter-tab');
            
            // Update active tab
            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.closest('.filter-tab').classList.add('active');
            
            // Filter messages
            cards.forEach(card => {
                if (filter === 'all') {
                    card.style.display = 'block';
                } else {
                    const status = card.getAttribute('data-status');
                    card.style.display = status === filter ? 'block' : 'none';
                }
            });
            
            // Check if no messages visible
            const visibleCards = Array.from(cards).filter(card => card.style.display !== 'none');
            const container = document.getElementById('messages-container');
            
            if (visibleCards.length === 0) {
                const existingEmpty = container.querySelector('.empty-state');
                if (!existingEmpty) {
                    const emptyState = document.createElement('div');
                    emptyState.className = 'empty-state temp-empty';
                    emptyState.innerHTML = `
                        <i class="fas fa-inbox"></i>
                        <h3>No ${filter === 'unread' ? 'Unread' : 'Read'} Messages</h3>
                        <p>There are no ${filter} messages to display.</p>
                    `;
                    container.appendChild(emptyState);
                }
            } else {
                const tempEmpty = container.querySelector('.temp-empty');
                if (tempEmpty) tempEmpty.remove();
            }
        }
    </script>
</body>
</html>