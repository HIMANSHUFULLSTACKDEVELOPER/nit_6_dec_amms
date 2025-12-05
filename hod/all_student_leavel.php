<?php
// Add this section after line 92 in your HOD dashboard (after the statistics section)

// Get leave applications statistics for the department
$leave_stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN la.status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN la.status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN la.status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM leave_applications la
    JOIN students s ON la.student_id = s.id
    WHERE s.department_id = $department_id";
$leave_stats_result = $conn->query($leave_stats_query);
$leave_stats = $leave_stats_result->fetch_assoc();

// Get recent leave applications (last 30 days)
$leave_applications_query = "SELECT 
    la.*,
    s.full_name as student_name,
    s.roll_number,
    s.email as student_email,
    c.class_name,
    c.section,
    c.year,
    t.full_name as teacher_name,
    t.email as teacher_email
    FROM leave_applications la
    JOIN students s ON la.student_id = s.id
    JOIN classes c ON la.class_id = c.id
    LEFT JOIN users t ON la.teacher_id = t.id
    WHERE s.department_id = $department_id
    AND la.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY la.created_at DESC
    LIMIT 50";
$leave_applications = $conn->query($leave_applications_query);
?>

<!-- Add this HTML section in your main content area, after the premium stats cards -->

<!-- Leave Applications Statistics Card -->
<div class="premium-stat-card" style="grid-column: span 2;">
    <div class="stat-icon-wrapper">📝</div>
    <div class="stat-details">
        <h4>Leave Applications (Last 30 Days)</h4>
        <div style="display: flex; gap: 30px; margin-top: 15px; flex-wrap: wrap;">
            <div>
                <div class="stat-value-large" style="font-size: 28px;"><?php echo $leave_stats['total']; ?></div>
                <small style="color: #666;">Total</small>
            </div>
            <div>
                <div class="stat-value-large" style="font-size: 28px; color: #ffc107;"><?php echo $leave_stats['pending']; ?></div>
                <small style="color: #666;">Pending</small>
            </div>
            <div>
                <div class="stat-value-large" style="font-size: 28px; color: #28a745;"><?php echo $leave_stats['approved']; ?></div>
                <small style="color: #666;">Approved</small>
            </div>
            <div>
                <div class="stat-value-large" style="font-size: 28px; color: #dc3545;"><?php echo $leave_stats['rejected']; ?></div>
                <small style="color: #666;">Rejected</small>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <a href="#leaveApplicationsTable" class="btn btn-primary btn-sm">📋 View All Applications</a>
        </div>
    </div>
</div>

<!-- Leave Applications Table -->
<div class="table-container" id="leaveApplicationsTable" style="margin-top: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>📝 Department Leave Applications (Last 30 Days)</h3>
        <div style="display: flex; gap: 10px;">
            <select id="leaveStatusFilter" class="filter-select" onchange="filterLeaveApplications()">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>
    
    <?php if ($leave_applications && $leave_applications->num_rows > 0): ?>
    <div style="overflow-x: auto;">
        <table id="leaveApplicationsTableData">
            <thead>
                <tr>
                    <th>Application Date</th>
                    <th>Student Details</th>
                    <th>Class</th>
                    <th>Leave Type</th>
                    <th>Duration</th>
                    <th>Teacher</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($app = $leave_applications->fetch_assoc()): ?>
                <tr class="leave-row" data-status="<?php echo $app['status']; ?>">
                    <td>
                        <strong><?php echo date('d M Y', strtotime($app['created_at'])); ?></strong><br>
                        <small style="color: #666;"><?php echo date('h:i A', strtotime($app['created_at'])); ?></small>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($app['student_name']); ?></strong><br>
                        <small style="color: #666;">Roll: <?php echo htmlspecialchars($app['roll_number']); ?></small><br>
                        <small style="color: #999;"><?php echo htmlspecialchars($app['student_email']); ?></small>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($app['class_name']); ?><br>
                        <small style="color: #666;">
                            <?php echo htmlspecialchars($app['section']); ?> | Year <?php echo $app['year']; ?>
                        </small>
                    </td>
                    <td>
                        <span class="badge badge-info">
                            <?php echo ucfirst(htmlspecialchars($app['leave_type'])); ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                        $start = date('d M', strtotime($app['start_date']));
                        $end = date('d M Y', strtotime($app['end_date']));
                        $days = (strtotime($app['end_date']) - strtotime($app['start_date'])) / (60 * 60 * 24) + 1;
                        ?>
                        <strong><?php echo $start; ?> - <?php echo $end; ?></strong><br>
                        <small style="color: #666;">(<?php echo $days; ?> day<?php echo $days > 1 ? 's' : ''; ?>)</small>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($app['teacher_name'] ?? 'Not Assigned'); ?></strong><br>
                        <small style="color: #999;"><?php echo htmlspecialchars($app['teacher_email'] ?? 'N/A'); ?></small>
                    </td>
                    <td>
                        <?php 
                        $statusClass = $app['status'];
                        $statusIcon = $app['status'] === 'approved' ? '✅' : 
                                     ($app['status'] === 'rejected' ? '❌' : '⏳');
                        ?>
                        <span class="badge badge-<?php echo $statusClass === 'approved' ? 'success' : 
                                                              ($statusClass === 'rejected' ? 'danger' : 'warning'); ?>">
                            <?php echo $statusIcon; ?> <?php echo ucfirst($app['status']); ?>
                        </span>
                        <?php if ($app['updated_at'] && $app['updated_at'] != $app['created_at']): ?>
                            <br><small style="color: #999;">
                                Updated: <?php echo date('d M Y', strtotime($app['updated_at'])); ?>
                            </small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button onclick="viewLeaveDetails(<?php echo $app['id']; ?>)" class="btn btn-primary btn-sm">
                            👁️ View
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 60px 20px; color: #666;">
        <p style="font-size: 48px; margin-bottom: 20px;">📭</p>
        <p style="font-size: 18px;">No leave applications in the last 30 days</p>
        <p style="margin-top: 10px; color: #999;">Leave applications will appear here once students submit them.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Leave Application Details Modal -->
<div id="leaveDetailsModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>📝 Leave Application Details</h3>
            <button onclick="closeLeaveModal()" class="btn btn-secondary btn-sm">✕ Close</button>
        </div>
        <div id="leaveDetailsContent">
            <!-- Content will be loaded via JavaScript -->
        </div>
    </div>
</div>

<!-- Add these styles in your <style> section -->
<style>
.filter-select {
    padding: 10px 15px;
    border: 2px solid rgba(102, 126, 234, 0.3);
    border-radius: 10px;
    background: white;
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    cursor: pointer;
    transition: all 0.3s;
}

.filter-select:hover {
    border-color: #667eea;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.filter-select:focus {
    outline: none;
    border-color: #764ba2;
    box-shadow: 0 0 0 3px rgba(118, 75, 162, 0.2);
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 20px;
    max-height: 85vh;
    overflow-y: auto;
    width: 90%;
    animation: slideUp 0.3s ease;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.leave-detail-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
    border-left: 4px solid #667eea;
}

.leave-detail-item strong {
    color: #667eea;
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}
</style>

<!-- Add this JavaScript in your <script> section -->
<script>
// Filter leave applications by status
function filterLeaveApplications() {
    const filter = document.getElementById('leaveStatusFilter').value;
    const rows = document.querySelectorAll('.leave-row');
    
    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        if (filter === 'all' || status === filter) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// View leave application details
function viewLeaveDetails(leaveId) {
    // Show modal
    document.getElementById('leaveDetailsModal').style.display = 'flex';
    
    // Fetch details via AJAX
    fetch(`get_leave_details.php?id=${leaveId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayLeaveDetails(data.application);
            } else {
                document.getElementById('leaveDetailsContent').innerHTML = 
                    '<p style="color: red;">Error loading details. Please try again.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('leaveDetailsContent').innerHTML = 
                '<p style="color: red;">Error loading details. Please try again.</p>';
        });
}

// Display leave details in modal
function displayLeaveDetails(app) {
    const statusClass = app.status === 'approved' ? 'success' : 
                       (app.status === 'rejected' ? 'danger' : 'warning');
    const statusIcon = app.status === 'approved' ? '✅' : 
                      (app.status === 'rejected' ? '❌' : '⏳');
    
    let html = `
        <div class="leave-detail-item">
            <strong>📋 Subject</strong>
            ${app.subject || app.leave_type}
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div class="leave-detail-item">
                <strong>👨‍🎓 Student</strong>
                ${app.student_name}<br>
                <small style="color: #666;">Roll: ${app.roll_number}</small><br>
                <small style="color: #999;">${app.student_email}</small>
            </div>
            
            <div class="leave-detail-item">
                <strong>📚 Class</strong>
                ${app.class_name} - ${app.section}<br>
                <small style="color: #666;">Year ${app.year}</small>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div class="leave-detail-item">
                <strong>🏷️ Leave Type</strong>
                ${app.leave_type.charAt(0).toUpperCase() + app.leave_type.slice(1)}
            </div>
            
            <div class="leave-detail-item">
                <strong>📅 Duration</strong>
                ${formatDate(app.start_date)} to ${formatDate(app.end_date)}<br>
                <small style="color: #666;">(${app.total_days} day${app.total_days > 1 ? 's' : ''})</small>
            </div>
        </div>
        
        <div class="leave-detail-item">
            <strong>💬 Reason</strong>
            ${app.reason.replace(/\n/g, '<br>')}
        </div>
        
        ${app.attachment ? `
        <div class="leave-detail-item">
            <strong>📎 Attachment</strong>
            <a href="../uploads/leave_applications/${app.attachment}" target="_blank" class="btn btn-secondary btn-sm">
                View Attachment
            </a>
        </div>
        ` : ''}
        
        <div class="leave-detail-item">
            <strong>👨‍🏫 Teacher</strong>
            ${app.teacher_name || 'Not Assigned'}<br>
            ${app.teacher_email ? `<small style="color: #999;">${app.teacher_email}</small>` : ''}
        </div>
        
        <div class="leave-detail-item" style="border-left-color: ${statusClass === 'success' ? '#28a745' : (statusClass === 'danger' ? '#dc3545' : '#ffc107')};">
            <strong>📊 Status</strong>
            <span class="badge badge-${statusClass}" style="font-size: 14px; padding: 8px 16px;">
                ${statusIcon} ${app.status.toUpperCase()}
            </span>
            ${app.teacher_remarks ? `
            <div style="margin-top: 15px; padding: 15px; background: white; border-radius: 8px;">
                <strong style="color: #667eea;">👨‍🏫 Teacher's Remarks:</strong>
                <p style="margin-top: 8px; line-height: 1.6;">${app.teacher_remarks.replace(/\n/g, '<br>')}</p>
            </div>
            ` : ''}
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
            <div class="leave-detail-item">
                <strong>📆 Applied On</strong>
                ${formatDateTime(app.created_at)}
            </div>
            
            ${app.updated_at && app.updated_at !== app.created_at ? `
            <div class="leave-detail-item">
                <strong>🔄 Last Updated</strong>
                ${formatDateTime(app.updated_at)}
            </div>
            ` : ''}
        </div>
    `;
    
    document.getElementById('leaveDetailsContent').innerHTML = html;
}

// Close leave modal
function closeLeaveModal() {
    document.getElementById('leaveDetailsModal').style.display = 'none';
}

// Helper function to format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { day: '2-digit', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// Helper function to format date and time
function formatDateTime(dateString) {
    const date = new Date(dateString);
    const dateOptions = { day: '2-digit', month: 'short', year: 'numeric' };
    const timeOptions = { hour: '2-digit', minute: '2-digit', hour12: true };
    return date.toLocaleDateString('en-US', dateOptions) + ', ' + 
           date.toLocaleTimeString('en-US', timeOptions);
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('leaveDetailsModal');
    if (event.target === modal) {
        closeLeaveModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeLeaveModal();
    }
});
</script>