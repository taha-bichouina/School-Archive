<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

include 'includes/header.php';
require_once 'includes/db_connect.php';

$page = 1;
$limit = 9999;
$offset = 0;

$sort_by = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'created_at';
$sort_order = isset($_GET['sort_order']) ? trim($_GET['sort_order']) : 'DESC';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$class_filter = isset($_GET['class']) ? trim($_GET['class']) : '';

// ✅ Correction ici : parenthèses autour des conditions de recherche
$query = "SELECT * FROM students WHERE (student_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR user_id_or_cin LIKE ?)";
$params = ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%'];

if (!empty($class_filter)) {
    $query .= " AND class_id = ?";
    $params[] = $class_filter;
}

$allowed_sort_columns = ['student_id', 'first_name', 'last_name', 'class_id', 'date_of_birth', 'date_of_start', 'date_of_end', 'season'];
if (in_array($sort_by, $allowed_sort_columns)) {
    $query .= " ORDER BY $sort_by $sort_order";
} else {
    $query .= " ORDER BY created_at DESC";
}

$query .= " LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
$conn->close();
?>

<div class="container-fluid">
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">All Students</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="window.print();">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                        <a href="export_results.php?search=<?php echo urlencode($search); ?>&class=<?php echo urlencode($class_filter); ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-file-csv me-1"></i> Export CSV
                        </a>
                        <a href="#" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search Bar -->
                    <form action="students.php" method="get" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control" placeholder="Search students by name, ID, or CIN">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>
                    <!-- Filters -->
                    <form action="students.php" method="get" class="mb-4">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($sort_order); ?>">
                        <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="classFilter" class="form-label">Filter by Class</label>
                                <select class="form-select" id="classFilter" name="class">
                                    <option value="" <?php echo empty($class_filter) ? 'selected' : ''; ?>>All Classes</option>
                                    <option value="TC" <?php echo ($class_filter === 'TC') ? 'selected' : ''; ?>>TC</option>
                                    <option value="1BAC" <?php echo ($class_filter === '1BAC') ? 'selected' : ''; ?>>1BAC</option>
                                    <option value="2BAC" <?php echo ($class_filter === '2BAC') ? 'selected' : ''; ?>>2BAC</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                            </div>
                        </div>
                    </form>
                    <!-- Students Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th><a href="?search=<?php echo urlencode($search); ?>&class=<?php echo urlencode($class_filter); ?>&sort_by=student_id&sort_order=<?php echo ($sort_by === 'student_id' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>">ID <i class="fas fa-sort"></i></a></th>
                                    <th><a href="?search=<?php echo urlencode($search); ?>&class=<?php echo urlencode($class_filter); ?>&sort_by=first_name&sort_order=<?php echo ($sort_by === 'first_name' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>">Name <i class="fas fa-sort"></i></a></th>
                                    <th>User ID/R/CIN</th>
                                    <th><a href="?search=<?php echo urlencode($search); ?>&class=<?php echo urlencode($class_filter); ?>&sort_by=class_id&sort_order=<?php echo ($sort_by === 'class_id' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>">Class <i class="fas fa-sort"></i></a></th>
                                    <th>Date of Birth</th>
                                    <th>Date of Start</th>
                                    <th>Date of End</th>
                                    <th>School Year</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($student = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['user_id_or_cin']); ?></td>
                                            <td><?php echo htmlspecialchars($student['class_id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['date_of_birth']); ?></td>
                                            <td><?php echo htmlspecialchars($student['date_of_start']); ?></td>
                                            <td><?php echo htmlspecialchars($student['date_of_end'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($student['season']); ?></td>
                                            <td>
                                                <a href="student_view.php?id=<?php echo $student['id']; ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="9" class="text-center">No students found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
