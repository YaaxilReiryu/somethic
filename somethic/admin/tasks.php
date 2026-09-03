<?php
$admin_title = 'Inventory Tasks Checklist (To-Do) — SOMETHIC';
require_once __DIR__ . '/includes/header.php';

$success_msg = '';
$error_msg = '';

// Handle Actions: Add, Toggle, Delete, Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $task = trim($_POST['task'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!empty($task)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO inventory_tasks (task, description, status) VALUES (?, ?, 'Pending')");
                $stmt->execute([$task, $description]);
                $success_msg = "Task '{$task}' added successfully to the inventory checklist.";
            } catch (PDOException $e) {
                $error_msg = "Error adding task: " . $e->getMessage();
            }
        } else {
            $error_msg = "Please enter a task name.";
        }
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $current_status = $_POST['current_status'] ?? 'Pending';
        $new_status = ($current_status === 'Completed') ? 'Pending' : 'Completed';

        try {
            $stmt = $pdo->prepare("UPDATE inventory_tasks SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$new_status, $id]);
            $success_msg = "Task marked as {$new_status}.";
        } catch (PDOException $e) {
            $error_msg = "Error updating task: " . $e->getMessage();
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM inventory_tasks WHERE id = ?");
            $stmt->execute([$id]);
            $success_msg = "Task removed from checklist.";
        } catch (PDOException $e) {
            $error_msg = "Error deleting task: " . $e->getMessage();
        }
    } elseif ($action === 'clear_completed') {
        try {
            $pdo->exec("DELETE FROM inventory_tasks WHERE status = 'Completed'");
            $success_msg = "All completed tasks have been cleared.";
        } catch (PDOException $e) {
            $error_msg = "Error clearing tasks: " . $e->getMessage();
        }
    }
}

// Filter
$filter = $_GET['filter'] ?? 'all';
$query = "SELECT * FROM inventory_tasks";
$params = [];

if ($filter === 'pending') {
    $query .= " WHERE status = 'Pending'";
} elseif ($filter === 'completed') {
    $query .= " WHERE status = 'Completed'";
}

$query .= " ORDER BY CASE WHEN status = 'Pending' THEN 0 ELSE 1 END, created_at DESC";

try {
    $tasks = $pdo->query($query)->fetchAll();
    
    // Counts
    $count_all = $pdo->query("SELECT COUNT(*) FROM inventory_tasks")->fetchColumn();
    $count_pending = $pdo->query("SELECT COUNT(*) FROM inventory_tasks WHERE status = 'Pending'")->fetchColumn();
    $count_completed = $pdo->query("SELECT COUNT(*) FROM inventory_tasks WHERE status = 'Completed'")->fetchColumn();
} catch (PDOException $e) {
    $tasks = [];
    $error_msg = "Error loading tasks: " . $e->getMessage();
}
?>

<!-- Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom" style="border-color: var(--somethic-border) !important;">
  <div>
    <h3 class="fw-bold mb-1" style="font-family: var(--font-serif);">Inventory Tasks Checklist (To-Do)</h3>
    <p class="text-muted small mb-0">
      Track daily stock replenishments, display rotations, dust protection checks, and boutique reservations prep.
    </p>
  </div>
  <?php if ($count_completed > 0): ?>
    <div class="mt-3 mt-md-0">
      <form method="POST" onsubmit="return confirm('Clear all completed tasks?');" class="d-inline">
        <input type="hidden" name="action" value="clear_completed">
        <button type="submit" class="btn btn-sm btn-outline-danger">
          <i class="bi bi-trash3 me-1"></i> Clear Completed (<?php echo (int)$count_completed; ?>)
        </button>
      </form>
    </div>
  <?php endif; ?>
</div>

<!-- Alerts -->
<?php if (!empty($success_msg)): ?>
  <div class="alert alert-success alert-dismissible fade show small py-2 px-3 mb-4" role="alert">
    <i class="bi bi-check-circle-fill me-1"></i> <?php echo htmlspecialchars($success_msg); ?>
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
  <div class="alert alert-danger alert-dismissible fade show small py-2 px-3 mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($error_msg); ?>
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<div class="row g-4">
  <!-- Left: Add New Task Form -->
  <div class="col-lg-4">
    <div class="card-somethic p-4">
      <h5 class="fw-bold mb-3" style="font-family: var(--font-serif);">Add Inventory Task</h5>
      <form method="POST" action="tasks.php" class="form-somethic">
        <input type="hidden" name="action" value="add">
        
        <div class="mb-3">
          <label for="task" class="form-label">Task Title <span class="text-danger">*</span></label>
          <input type="text" id="task" name="task" class="form-control form-control-somethic" placeholder="e.g., Restock Luna Shoulder Bag" required>
        </div>

        <div class="mb-3">
          <label for="description" class="form-label">Details / Notes</label>
          <textarea id="description" name="description" rows="3" class="form-control form-control-somethic" placeholder="Shelf location, stock quantity needed, or special client instructions..."></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label text-muted small">Quick Template Ideas:</label>
          <div class="d-flex flex-wrap gap-1">
            <button type="button" class="btn btn-sm btn-light border py-0 px-2" style="font-size: 0.72rem;" onclick="document.getElementById('task').value='Verify morning pickup bags'; document.getElementById('description').value='Check reservation shelf and prepare dust bags.'">
              + Morning Pickups
            </button>
            <button type="button" class="btn btn-sm btn-light border py-0 px-2" style="font-size: 0.72rem;" onclick="document.getElementById('task').value='Restock display shelf A'; document.getElementById('description').value='Move 3 units of Mila Tote from backroom.'">
              + Restock Shelf
            </button>
            <button type="button" class="btn btn-sm btn-light border py-0 px-2" style="font-size: 0.72rem;" onclick="document.getElementById('task').value='Inspect gold turn-lock hardware'; document.getElementById('description').value='Perform quality check on Ava crossbody displays.'">
              + Hardware Check
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-somethic-dark w-100 py-2">
          <i class="bi bi-plus-circle me-1"></i> Add to Checklist
        </button>
      </form>
    </div>

    <!-- Summary Box -->
    <div class="card-somethic p-3 mt-4 text-center">
      <div class="row g-2">
        <div class="col-4 border-end" style="border-color: var(--somethic-border) !important;">
          <div class="text-muted small text-uppercase" style="font-size: 0.65rem;">Total</div>
          <div class="fs-5 fw-bold"><?php echo (int)$count_all; ?></div>
        </div>
        <div class="col-4 border-end" style="border-color: var(--somethic-border) !important;">
          <div class="text-muted small text-uppercase" style="font-size: 0.65rem;">Pending</div>
          <div class="fs-5 fw-bold text-warning"><?php echo (int)$count_pending; ?></div>
        </div>
        <div class="col-4">
          <div class="text-muted small text-uppercase" style="font-size: 0.65rem;">Done</div>
          <div class="fs-5 fw-bold text-success"><?php echo (int)$count_completed; ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Right: Checklist Items -->
  <div class="col-lg-8">
    <div class="card-somethic p-4">
      <!-- Filter Tabs -->
      <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom" style="border-color: var(--somethic-border) !important;">
        <div class="nav nav-pills gap-1" style="font-size: 0.8rem;">
          <a href="tasks.php?filter=all" class="nav-link px-3 py-1 <?php echo $filter === 'all' ? 'active bg-dark text-white' : 'text-dark bg-light'; ?>" style="border-radius: 20px;">
            All Tasks (<?php echo (int)$count_all; ?>)
          </a>
          <a href="tasks.php?filter=pending" class="nav-link px-3 py-1 <?php echo $filter === 'pending' ? 'active bg-dark text-white' : 'text-dark bg-light'; ?>" style="border-radius: 20px;">
            Pending (<?php echo (int)$count_pending; ?>)
          </a>
          <a href="tasks.php?filter=completed" class="nav-link px-3 py-1 <?php echo $filter === 'completed' ? 'active bg-dark text-white' : 'text-dark bg-light'; ?>" style="border-radius: 20px;">
            Completed (<?php echo (int)$count_completed; ?>)
          </a>
        </div>
      </div>

      <!-- Task Items List -->
      <?php if (empty($tasks)): ?>
        <div class="text-center py-5 text-muted">
          <i class="bi bi-clipboard-check fs-1 text-muted d-block mb-2"></i>
          <p class="fw-semibold mb-1">No tasks in this view</p>
          <p class="small text-muted mb-0">Use the left form to add your first inventory to-do task.</p>
        </div>
      <?php else: ?>
        <div class="list-group list-group-flush">
          <?php foreach ($tasks as $t): ?>
            <?php $isDone = ($t['status'] === 'Completed'); ?>
            <div class="list-group-item px-0 py-3 d-flex align-items-start justify-content-between gap-3 border-bottom" style="border-color: var(--somethic-border) !important;">
              <!-- Toggle Checkbox & Title -->
              <div class="d-flex align-items-start gap-3 flex-grow-1">
                <form method="POST" action="tasks.php?filter=<?php echo urlencode($filter); ?>" class="m-0 pt-1">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                  <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($t['status']); ?>">
                  <button type="submit" class="btn p-0 border-0 bg-transparent text-decoration-none" title="Click to toggle status">
                    <?php if ($isDone): ?>
                      <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <?php else: ?>
                      <i class="bi bi-circle text-muted fs-5"></i>
                    <?php endif; ?>
                  </button>
                </form>

                <div>
                  <div class="<?php echo $isDone ? 'text-decoration-line-through text-muted' : 'fw-bold text-dark'; ?>" style="font-size: 0.95rem;">
                    <?php echo htmlspecialchars($t['task']); ?>
                  </div>
                  <?php if (!empty($t['description'])): ?>
                    <p class="text-muted small mb-1 mt-1 <?php echo $isDone ? 'text-decoration-line-through' : ''; ?>">
                      <?php echo htmlspecialchars($t['description']); ?>
                    </p>
                  <?php endif; ?>
                  <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge <?php echo $isDone ? 'badge-status-completed' : 'badge-status-pending'; ?>" style="font-size: 0.65rem;">
                      <?php echo htmlspecialchars($t['status']); ?>
                    </span>
                    <small class="text-muted" style="font-size: 0.7rem;">
                      <i class="bi bi-clock me-1"></i><?php echo date('M d, H:i', strtotime($t['created_at'])); ?>
                    </small>
                  </div>
                </div>
              </div>

              <!-- Delete Button -->
              <div>
                <form method="POST" action="tasks.php?filter=<?php echo urlencode($filter); ?>" onsubmit="return confirm('Delete this task?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                  <button type="submit" class="btn btn-sm btn-light text-danger border-0 p-1 px-2" title="Delete Task">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
