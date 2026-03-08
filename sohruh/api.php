<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'verify_session':
        // Verify user session
        $session_token = $_POST['session_token'] ?? '';
        
        if ($session_token) {
            $sql = "SELECT user_id FROM user_sessions 
                    WHERE session_token = ? AND expires_at > NOW()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $session_token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                echo json_encode(['success' => true, 'user_id' => $row['user_id']]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid or expired session']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Session token required']);
        }
        break;
        
    case 'track_video':
        // Track video viewing
        $video_id = $_POST['video_id'] ?? 0;
        $user_id = $_POST['user_id'] ?? null;
        
        if ($video_id > 0) {
            if ($user_id) {
                // Update existing record or insert new one
                $sql = "INSERT INTO video_stats (user_id, video_id, watched_count, last_watched) 
                        VALUES (?, ?, 1, NOW()) 
                        ON DUPLICATE KEY UPDATE 
                        watched_count = watched_count + 1, 
                        last_watched = NOW()";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $user_id, $video_id);
                $stmt->execute();
            } else {
                // Track anonymous viewing (using session)
                session_start();
                $session_id = session_id();
                $sql = "INSERT INTO video_stats (user_id, video_id, watched_count, last_watched) 
                        VALUES (NULL, ?, 1, NOW()) 
                        ON DUPLICATE KEY UPDATE 
                        watched_count = watched_count + 1, 
                        last_watched = NOW()";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $video_id);
                $stmt->execute();
            }
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid video ID']);
        }
        break;
        
    case 'get_stats':
        // Get viewing statistics
        $user_id = $_GET['user_id'] ?? null;
        
        if ($user_id) {
            $sql = "SELECT video_id, watched_count, last_watched 
                    FROM video_stats 
                    WHERE user_id = ? 
                    ORDER BY last_watched DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $stats = [];
            while ($row = $result->fetch_assoc()) {
                $stats[] = $row;
            }
            
            echo json_encode(['success' => true, 'stats' => $stats]);
        } else {
            echo json_encode(['success' => false, 'error' => 'User ID required']);
        }
        break;
        
    case 'register':
        // User registration
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if ($username && $email && $password) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $email, $password_hash);
            
            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                echo json_encode(['success' => true, 'user_id' => $user_id]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Registration failed']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        }
        break;
        
    case 'login':
        // User login
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if ($username && $password) {
            $sql = "SELECT id, password_hash FROM users WHERE username = ? OR email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                if (password_verify($password, $row['password_hash'])) {
                    // Create session
                    $session_token = bin2hex(random_bytes(32));
                    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    $session_sql = "INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)";
                    $session_stmt = $conn->prepare($session_sql);
                    $session_stmt->bind_param("iss", $row['id'], $session_token, $expires_at);
                    $session_stmt->execute();
                    
                    echo json_encode(['success' => true, 'user_id' => $row['id'], 'session_token' => $session_token]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Invalid password']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'User not found']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing credentials']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

$conn->close();
?>
