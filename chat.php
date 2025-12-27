<?php
session_start();
$page_title = "Contract Details";
include 'header.php';
include 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['contract_id'])) {
    echo "Access denied.";
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$contract_id = (int)$_GET['contract_id'];

// Fetch contract, user IDs, and names
$stmt = $conn->prepare("
    SELECT con.contract_id,
           c.user_id AS company_user_id, c.company_name,
           f.user_id AS freelancer_user_id, f.fname AS freelancer_fname, f.lname AS freelancer_lname
    FROM contracts con
    LEFT JOIN companies c ON c.company_id = con.company_id
    LEFT JOIN freelancers f ON f.freelancer_id = con.freelancer_id
    WHERE con.contract_id = ?
");
$stmt->bind_param("i", $contract_id);
$stmt->execute();
$contract = $stmt->get_result()->fetch_assoc();

if (!$contract) {
    echo "Contract not found.";
    exit;
}

// Check if logged-in user is either the company or freelancer
if ($user_id != $contract['company_user_id'] && $user_id != $contract['freelancer_user_id']) {
    echo "You are not authorized to chat in this contract.";
    exit;
}

// Determine receiver_id and chat partner name
if ($user_id == $contract['company_user_id']) {
    $receiver_id = $contract['freelancer_user_id'];
    $chat_partner_name = $contract['freelancer_fname'] . ' ' . $contract['freelancer_lname'];
} else {
    $receiver_id = $contract['company_user_id'];
    $chat_partner_name = $contract['company_name'];
}

// Send message
if (isset($_POST['send_message'])) {
    $message = trim($_POST['message']);
    if ($message !== '') {
        $stmt = $conn->prepare("
            INSERT INTO messages (contract_id, sender_id, receiver_id, message, send_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("iiis", $contract_id, $user_id, $receiver_id, $message);
        $stmt->execute();
    }
}

// Fetch all messages for this contract
$stmt = $conn->prepare("
    SELECT m.*, u.username AS sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.user_id
    WHERE m.contract_id = ? 
    ORDER BY m.send_at ASC
");
$stmt->bind_param("i", $contract_id);
$stmt->execute();
$messages = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Chat with <?= htmlspecialchars($chat_partner_name) ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 0;
        }

        .chat-container {
            max-width: 800px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            height: 80vh;
        }

        .chat-header {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            background: #f9fafb;
            border-radius: 12px 12px 0 0;
        }

        .chat-messages {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
        }

        .message {
            max-width: 70%;
            padding: 10px 14px;
            margin: 8px 0;
            border-radius: 20px;
            position: relative;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            word-wrap: break-word;
        }

        .user-message {
            background: #dcfce7;
            margin-left: auto;
            text-align: right;
        }

        .other-message {
            background: #f1f5f9;
            margin-right: auto;
        }

        .sender {
            font-weight: 600;
            font-size: 0.9em;
            margin-bottom: 4px;
            color: #374151;
        }

        .timestamp {
            font-size: 0.7em;
            color: #6b7280;
            margin-top: 4px;
        }

        .chat-input {
            display: flex;
            padding: 12px 16px;
            border-top: 1px solid #e2e8f0;
            background: #f9fafb;
            border-radius: 0 0 12px 12px;
        }

        .chat-input input {
            flex: 1;
            padding: 10px 14px;
            border-radius: 25px;
            border: 1px solid #d1d5db;
            outline: none;
            font-size: 1em;
        }

        .chat-input button {
            margin-left: 8px;
            padding: 10px 18px;
            border-radius: 25px;
            border: none;
            background: #3b82f6;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .chat-input button:hover {
            background: #2563eb;
        }
    </style>
</head>

<body>

    <div class="chat-container">
        <div class="chat-header">Chat: <?= htmlspecialchars($chat_partner_name) ?></div>
        <div class="chat-messages" id="messages">
            <?php while ($msg = $messages->fetch_assoc()): ?>
                <div class="message <?= $msg['sender_id'] == $user_id ? 'user-message' : 'other-message' ?>">
                    <div class="sender"><?= htmlspecialchars($msg['sender_name']) ?></div>
                    <div class="text"><?= htmlspecialchars($msg['message']) ?></div>
                    <div class="timestamp"><?= date("Y-m-d H:i", strtotime($msg['send_at'])) ?></div>
                </div>
            <?php endwhile; ?>
        </div>

        <form method="POST" class="chat-input">
            <input type="text" name="message" placeholder="Type your message..." required>
            <button type="submit" name="send_message">Send</button>
        </form>
    </div>

    <script>
        // Auto-scroll to bottom smoothly
        const messagesDiv = document.getElementById('messages');
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    </script>

</body>

</html>

<?php include 'footer.php' ?>