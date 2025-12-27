<?php
session_start();
include 'config.php';

// ----------------------- ACCESS CHECK -----------------------
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Fetch company_id for this user
$stmt = $conn->prepare("SELECT company_id FROM companies WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$company_id = $row['company_id'] ?? 0;

// ----------------------- HANDLE STATUS UPDATE -----------------------
if (isset($_GET['action'], $_GET['id'])) {

    $proposal_id = (int)$_GET['id'];
    $action = $_GET['action'];

    if (in_array($action, ['accept', 'reject'])) {

        // Fetch proposal details
        $stmt = $conn->prepare("
            SELECT job_id, freelancer_id, bid_amount 
            FROM proposals 
            WHERE proposal_id = ?
        ");
        $stmt->bind_param("i", $proposal_id);
        $stmt->execute();
        $proposal = $stmt->get_result()->fetch_assoc();

        if ($proposal) {

            $job_id = (int)$proposal['job_id'];
            $freelancer_user_id = (int)$proposal['freelancer_id']; // this is user_id
            $bid_amount = (float)$proposal['bid_amount'];

            // Get actual freelancer_id from freelancers table
            $stmt2 = $conn->prepare("SELECT freelancer_id FROM freelancers WHERE user_id = ?");
            $stmt2->bind_param("i", $freelancer_user_id);
            $stmt2->execute();
            $res = $stmt2->get_result()->fetch_assoc();
            $freelancer_id = $res['freelancer_id'] ?? 0;

            if ($action === 'accept') {

                // Accept selected proposal
                $stmt = $conn->prepare("
                    UPDATE proposals p
                    JOIN jobs j ON p.job_id = j.jobs_id
                    SET p.status = 'accepted'
                    WHERE p.proposal_id = ?
                    AND j.company_id = ?
                ");
                $stmt->bind_param("ii", $proposal_id, $company_id);
                $stmt->execute();

                // Reject all other proposals of same job
                $stmt = $conn->prepare("
                    UPDATE proposals
                    SET status = 'rejected'
                    WHERE job_id = ?
                    AND proposal_id != ?
                ");
                $stmt->bind_param("ii", $job_id, $proposal_id);
                $stmt->execute();

                // Update job status to in_progress
                $stmt = $conn->prepare("
                    UPDATE jobs
                    SET status = 'in_progress'
                    WHERE jobs_id = ?
                    AND company_id = ?
                ");
                $stmt->bind_param("ii", $job_id, $company_id);
                $stmt->execute();

                // Insert into contracts table with correct freelancer_id
                $stmt = $conn->prepare("
                    INSERT INTO contracts
                    (job_id, company_id, freelancer_id, agreed_amount, status, start_date)
                    VALUES (?, ?, ?, ?, 'active', CURDATE())
                ");
                $stmt->bind_param(
                    "iiid",
                    $job_id,
                    $company_id,
                    $freelancer_id,
                    $bid_amount
                );
                $stmt->execute();
            } else {

                // Reject only this proposal
                $stmt = $conn->prepare("
                    UPDATE proposals p
                    JOIN jobs j ON p.job_id = j.jobs_id
                    SET p.status = 'rejected'
                    WHERE p.proposal_id = ?
                    AND j.company_id = ?
                ");
                $stmt->bind_param("ii", $proposal_id, $company_id);
                $stmt->execute();
            }
        }

        header("Location: proposals_receive.php");
        exit();
    }
}

// ----------------------- FETCH PROPOSALS -----------------------
$query = "
    SELECT 
        p.*,
        j.jobs_id,
        j.title,
        f.freelancer_id,
        u.username AS freelancer_name
    FROM proposals p
    JOIN jobs j ON p.job_id = j.jobs_id
    JOIN freelancers f ON p.freelancer_id = f.user_id
    JOIN users u ON f.user_id = u.user_id
    WHERE j.company_id = ?
    ORDER BY j.jobs_id, p.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();

include 'header.php';
?>

<style>
    .card {
        background-color: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .proposal-row {
        border-bottom: 1px solid #e5e7eb;
        padding: 10px 0;
    }

    .badge-pending {
        background: #fbbf24;
        color: #fff;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .badge-accept {
        background: #34d399;
        color: #fff;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .badge-reject {
        background: #ef4444;
        color: #fff;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .btn-accept {
        background: #10b981;
        color: #fff;
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
    }

    .btn-reject {
        background: #ef4444;
        color: #fff;
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
    }

    .btn-view {
        background: #3b82f6;
        color: #fff;
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
    }
</style>

<div class="max-w-5xl mx-auto mt-12 mb-12">
    <h2 class="text-3xl font-bold mb-6">Proposals Received</h2>

    <?php if ($result->num_rows === 0): ?>
        <p class="text-gray-600">No proposals received yet.</p>
    <?php else: ?>

        <?php $currentJob = null; ?>

        <?php while ($row = $result->fetch_assoc()): ?>

            <?php if ($currentJob !== $row['jobs_id']): ?>
                <?php if ($currentJob !== null): ?>
</div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        Job: <?= htmlspecialchars($row['title']) ?>
    </div>
    <div>
        <?php $currentJob = $row['jobs_id']; ?>
    <?php endif; ?>

    <div class="proposal-row">
        <strong>Freelancer:</strong> <?= htmlspecialchars($row['freelancer_name']) ?><br>
        <strong>Bid Amount:</strong> ₹<?= number_format($row['bid_amount']) ?><br>
        <strong>Status:</strong>

        <?php if ($row['status'] === "pending"): ?>
            <span class="badge-pending">Pending</span>
        <?php elseif ($row['status'] === "accepted"): ?>
            <span class="badge-accept">Accepted</span>
        <?php else: ?>
            <span class="badge-reject">Rejected</span>
        <?php endif; ?>

        <div style="margin-top:8px;">
            <?php if ($row['status'] === "pending"): ?>
                <a href="?action=accept&id=<?= $row['proposal_id'] ?>" class="btn-accept">Accept</a>
                <a href="?action=reject&id=<?= $row['proposal_id'] ?>" class="btn-reject">Reject</a>
            <?php endif; ?>

            <a href="freelancer_details.php?id=<?= (int)$row['freelancer_id'] ?>" class="btn-view">
                View Freelancer
            </a>
        </div>
    </div>

<?php endwhile; ?>

    </div>
</div>

<?php endif; ?>
</div>

<?php include 'footer.php'; ?>